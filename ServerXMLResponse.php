<?php

require_once('DOMValidator.php');

/**
 * Handle XML request & build XML response
 * @author  Ravi <r.ravimailid@gmail.com>
 */
interface XML {

	public function xmlUpdate($resArr) : string;

	public function xmlHeaderParser($xml, $method) : array;

}

interface XMLResponse extends XML {

	public function ping($xml) : string;

	public function reverse($xml) : string;

	public function nack($xml, $errorMessage) : string;
}

class ServerXMLResponse implements XMLResponse {	
	
	/**
	 * Convert response arrays for different requests into XML
	 * @return XML as string 
	 */
	public function xmlUpdate($resArr) : string
    	{	
		$dom    	= new DOMDocument('1.0', 'utf-8'); 
		$root   	= $dom->createElement($resArr['type']);      
		$header 	= $dom->createElement('header');
		$type   	= $dom->createElement('type', $resArr['type']); 
		$header->appendChild($type); 
		$sender 	= $dom->createElement('sender', $resArr['sender']); 
		$header->appendChild($sender); 
		$recipient  = $dom->createElement('recipient', $resArr['recipient']); 
		$header->appendChild($recipient); 
		$reference  = $dom->createElement('reference', $resArr['reference']); 
		$header->appendChild($reference); 
		$timestamp  = $dom->createElement('timestamp', $resArr['timestamp']); 
		$header->appendChild($timestamp);
		$root->appendChild($header);
		
		$body 	    = $dom->createElement('body');
		
		if ($resArr['type'] == 'ping_response') {
			$echo     = $dom->createElement('echo', $resArr['echo']);
			$body->appendChild($echo); 
		} elseif ($resArr['type'] == 'reverse_response') {
			$string     = $dom->createElement('string', $resArr['string']);
			$body->appendChild($string); 
			$reverse     = $dom->createElement('reverse', $resArr['reverse']);
			$body->appendChild($reverse); 
		} else {
			$error     = $dom->createElement('error');
			$body->appendChild($error); 
			$code     = $dom->createElement('code');
			$error->appendChild($code);
			$message  = $dom->createElement('message', $resArr['message']);
			$error->appendChild($message);		
		}	
		$root->appendChild($body);
		$dom->appendChild($root); 
		$xml = $dom->saveXML();
		return $xml;
	}
	
	/**
	 * Parse & assign values to header response
	 * @return array  
	 */
	public function xmlHeaderParser($xml, $method) : array
	{
		$sender = $xml->header[0]->recipient;
		$recipient = $xml->header[0]->sender;
		$reference = $xml->header[0]->reference;
		$timestamp = time();
		
		$parentArray = array('type'=>$method,
							  'sender'=>$sender,
							  'recipient' => $recipient,
							  'reference' => $reference,
							  'timestamp'=>$timestamp);	
		return $parentArray;
	}
	
	/**
	 * Parse & assign values to ping body response
	 * @return string  
	 */
	public function ping($xml) : string
	{
		$parentArray = $this->xmlHeaderParser($xml,'ping_response');
		$echoreq = $xml->body[0]->echo;
		$ChildArray = array('echo'=> $echoreq);
		$tempArr = array_merge($parentArray,$ChildArray);
		$res = $this->xmlUpdate($tempArr);
		return $res;
		
	}
	
	/**
	 * Parse & assign values to reverse body response
	 * @return string  
	 */
	public function reverse($xml) : string
	{
		$parentArray = $this->xmlHeaderParser($xml,'reverse_response');
		$string = $xml->body[0]->string;
		$revstring = strrev($string);
		$ChildArray = array('string'=> $string,'reverse'=> $revstring);
		$tempArr =array_merge($parentArray,$ChildArray);
		$res = $this->xmlUpdate($tempArr);
		return $res;
	}
	
	/**
	 * Parse & assign values to nack body response
	 * @return string  
	 */
	public function nack($xml, $errorMessage) : string
	{
		$parentArray = $this->xmlHeaderParser($xml,'nack');
		$httpcode = http_response_code(404);
		$ChildArray = array('code'=> $httpcode,
					  'message'=> $errorMessage);
		$tempArr = array_merge($parentArray,$ChildArray);
		$res = $this->xmlUpdate($tempArr);
		return $res;
	} 
}



//Pull the XML request in the string format
$postData = file_get_contents('php://input'); 

// Interprets a string of XML into an object
$xml = simplexml_load_string(trim($postData));

//Pull the feed request type from the object 
$ReqType = $xml->header[0]->type;

//Initialise the DOMValidator class and pass the feed request type 
$validator = new DomValidator($ReqType);

//Validate the feeds and exception handling
$validated = $validator->validateFeeds($postData);

//Initialise the ServerXMLResponse class and get the response
$xmlResponse = new ServerXMLResponse;

/* Response to the client on the basis of request type */

//Ping Request Response
if ($validated && $ReqType == 'ping_request'){
	echo $ping_response = $xmlResponse->ping($xml);
}
//Reverse Request Response
elseif ($validated && $ReqType == 'reverse_request'){
	echo $reverse_response = $xmlResponse->reverse($xml);
} 
//Handle errors using Nack message
else {
	$messages = $validator->displayErrors();
	$errorMessage = implode(",",$messages);
	echo $nack = $xmlResponse->nack($xml,$errorMessage);
}
