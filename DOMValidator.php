<?php
/**
 * Class to validate the XML DOM 
 * @author     Ravi <r.ravimailid@gmail.com>
 */
class DOMValidator {
	
	//FeedSchema path to validate
	protected $feedSchema;
	
	//Formatted Custom error message
	public $errorDetails;
	
	
	
	//Initialise DOMDocument and setting feedSchema path
	public function __construct($ReqType) {
		$this->handler = new \DOMDocument('1.0', 'utf-8');
		$this->feedSchema = __DIR__. '\\xsd\\'. $ReqType . '.xsd';
		
	}	

	/**
	 * Custom exception handling for setting error message
	 * return error message
	 */
	private function libXMLDisplayError($error) : string
    {
        $errorString = "Error $error->code in $error->file (Line:{$error->line}):";
        $errorString .= trim($error->message);
        return $errorString;
    }
	
	/**
	 * Fetch libxml errors
	 * @return error message
	 */		
	private function libXMLDisplayErrors() : array
    {
        $errors = libxml_get_errors();
		$result    = [];
        foreach ($errors as $error) {
            $result[] = $this->libXMLDisplayError($error);
        }

        libxml_clear_errors();
        return $result;
    }	

	/**
	 * Function to validate feeds on several conditions
	 * @return false if non-validated, true if validated.
	 */	
	public function validateFeeds($feeds) : string
    {
       if (!class_exists('DOMDocument')) {
			$this->errorDetails = 'Schema is Missing, Please add schema to feedSchema property';
            
        }
        if (!file_exists($this->feedSchema)) {
			$this->errorDetails = 'Schema is Missing, Please add schema to feedSchema property';
			
        }
        libxml_use_internal_errors(true);
       
       
        $this->handler->loadXML($feeds, LIBXML_NOBLANKS);
		$this->handler->saveXML();
		
		//Condition to check if schema is validated
        if (!$this->handler->schemaValidate($this->feedSchema)) {
           $this->errorDetails = $this->libXMLDisplayErrors();
		   return false;		   
        } 
		else {
           return true;
        }
    }
	
	/**
	 * Pull error message after validating the feed 
	 * @return error message
	 */	
	public function displayErrors()
    {
        return $this->errorDetails;
    }		
	
}
