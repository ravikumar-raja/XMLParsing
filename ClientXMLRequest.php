<?php
/**
 * Implement the ClientXMLRequest
 *
 * @author  Ravi <r.ravimailid@gmail.com>
 */

class ClientXMLRequest
{

	//Set Server API URL
	private  $url = 'http://localhost/Voiceworks/XMLParsing/ServerXMLResponse.php';

	private $requestXML = null;

	public function __construct(RequestXML $requestXML) {
        	$this->requestXML = $requestXML;
   	}

	/**
	 * Function used by Client to send XML over HTTP POST
	 *
	 */
	public function sendXMLOverPost() 
	{
		
		//Initiate cURL
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $this->url);
		//Set the Content-Type to text/xml.
		curl_setopt ($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
		 
		//Set CURLOPT_POST to true to send a POST request.
		curl_setopt($curl, CURLOPT_POST, true);
		 
		//Attach the XML string to the body of our request.
		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->requestXML);
		 
		//Tell cURL that we want the response to be returned as
		//a string instead of being dumped to the output.
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		 
		//Execute the POST request and send our XML.
		$result = curl_exec($curl);

		//Do some basic error checking.
		if(curl_errno($curl)){
			throw new Exception(curl_error($curl));
		}
		
		//Close the cURL handle.
		curl_close($curl);

		//return the response output.
		return $result;
	}

}

// Ping Request
$pingXML = file_get_contents(__DIR__. '\\request\ping_request.xml');
$pingRequestXML = new RequestXML($pingXML);
$pingRequest = new ClientXMLRequest($pingRequestXML);
echo $pingRequest->sendXMLOverPost();

//Reverse Request
$reverseXML = file_get_contents(__DIR__. '\\request\reverse_request.xml');
$requestRequestXML = new RequestXML($reverseXML);
$reverseRequest = new ClientXMLRequest($requestRequestXML);
echo $reverseRequest->sendXMLOverPost();
