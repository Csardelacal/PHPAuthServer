<?php

/**
 * The request class is in charge of properly establishing HTTP connections with 
 * the remote server, retrieving the data and returning it or an error in the 
 * event of it failing.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class Request
{
	
	private $url;
	
	/**
	 * Parameters are handled separately from the URL just because they can happen
	 * to be a pain and are way more comfy to handle in array form.
	 *
	 * @var string[]
	 */
	private $parameters;
	
	/**
	 * Depending on whether this array contains data we will be sending a POST or
	 * a GET request. Also, if it contains data this will be the post payload.
	 *
	 * @var mixed[]|null
	 */
	private $postData;
	
	/**
	 * 
	 * @param string $url
	 * @param string[] $parameters
	 */
	public function __construct($url, $parameters = Array()) {
		$this->url = $url;
		$this->parameters = $parameters;
	}
	
	/**
	 * This contains the data that will be posted to the server when the request
	 * is sent. This can be overriden by passing a parameter to the send method.
	 * 
	 * @param string[] $data
	 */
	public function setPostData($data) {
		$this->postData = $data;
	}
	
	/**
	 * 
	 * @param string[] $data MAy contain postdata
	 *
	 * @return mixed
	 */
	public function send($data = null) {
		#Find the appropriate data
		if (!$data) { $data = $this->postData; }
		
		#Assemble the full URI
		$url = $this->url;
		if (!empty($this->parameters)) { $url.= '?' . http_build_query($this->parameters); }
		
		#Prepare the cURL request
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_REFERER, 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		
		#If data is there to be posted we will send that
		if (!empty($data)) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		
		#Get the cURL response
		$response = curl_exec($ch);
		
		#If the request was not okay we will return an error
		$http_response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if ($http_response_code !== 200) {
			throw new \Exception('Target rejected the request (' . curl_error($ch) . ')', 1605141533);
		}
		
		#Return the response we received
		return $response;
	}

	
}
