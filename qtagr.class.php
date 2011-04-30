<?php
/**
 * qTAGr.com 
 * PHP API class
 * 
 * @author  Marek Ventur, qTAGr.com
 * @license GNU General Public License
 * @version 0.1
 * 
 */
 
class Qtagr {
	
	private $apiKey;
	private $userToken;
	
	/**
	 * Constructor
	 * @param string API Key
	 */
	 public function Qtagr($apiKey, $userToken=null) {
	 	$this->apiKey = $apiKey;	
	 	$this->userToken = $userToken;	
	 }
	
	
	/**
	 * Generate user token. Step 1 for user authorization
	 * @param  string URL to which the user will be redirected after login 
	 * @return mixed  data
	 */
	public function generateUserToken($redirectURL) {
		$data = $this->request('generateUserToken', 
			array(
				'redirectURL'=>$redirectURL
			));	
		$this->handleErrorCodes($data);
		
		return $data;			
	}
	
	/**
	 * Returns info for logged in user. userToken has to be set
	 * @return mixed  data
	 */
	public function getUserInfo() {
		$data = $this->request('getUserInfo', 
			array());	
		$this->handleErrorCodes($data);
		
		return $data;			
	}
	
	/**
	 * Request information to a tag
	 * @param  string full gTAGr URL, exactly like from the scan (e.g. http://qtagr.com/123456)
	 * @param  string "doNotCount", "hit" or "scan"     
	 * @return mixed  data
	 */
	public function getTag($url, $requestType='doNotCount', $maxComments=25) {
		if (($requestType != 'doNotCount') && ($requestType != 'hit') && ($requestType != 'scan')) 
			throw new InvalidArgumentException('Request type should be "doNotCount", "hit" or "scan"');
		
		$data = $this->request('getTag', 
			array(
				'url'=>$url,
				'requestType'=>$requestType,
				'maxComments'=>$maxComments
			));	
		$this->handleErrorCodes($data);
		
		return $data;			
	}
	
	/**
	 * Comment on tag
	 * @param  string full gTAGr URL, exactly like from the scan (e.g. http://qtagr.com/123456)
	 * @param  string "doNotCount", "hit" or "scan"     
	 * @return mixed  data
	 */
	public function comment($url, $comment) {
		$data = $this->request('comment', 
			array(
				'url'=>$url,
				'comment'=>$comment
			));	
		$this->handleErrorCodes($data);
		
		return $data;			
	}
	
	/**
	 * Generates a tag 
	 * @return mixed  data
	 */
	public function generateTag() {	
		$data = $this->request('generateTag', array());	
		$this->handleErrorCodes($data);
		return $data;			
	}
	
	/**
	 * Text-claim a tag
	 * @param  string full gTAGr URL, exactly like from the scan (e.g. http://qtagr.com/123456)
	 * @param  string title     
	 * @param  string text     
	 * @return mixed  data
	 */
	public function claimText($url, $title, $text, $lat=null, $lon=null) {
		$in = array(
					'url'=>$url,
					'title'=>$title,
					'text'=>$text				
				);	
		if ($lon != null) {
			$in['lat'] = $lat;
			$in['lon'] = $lon;
		}	
			
		$data = $this->request('claimText', $in);	
			
		$this->handleErrorCodes($data);
		
		return $data;			
	}
	
	/**
	 * Rating-claim a tag
	 * @param  string full gTAGr URL, exactly like from the scan (e.g. http://qtagr.com/123456)
	 * @param  string title        
	 * @return mixed  data
	 */
	public function claimRating($url, $title, $lat=null, $lon=null) {
		$in = array(
					'url'=>$url,
					'title'=>$title			
				);	
		if ($lon != null) {
			$in['lat'] = $lat;
			$in['lon'] = $lon;
		}	
			
		$data = $this->request('claimRating', $in);	
			
		$this->handleErrorCodes($data);
		
		return $data;			
	}
	
	/**
	 * Rate a rating-tag
	 * @param  string  full gTAGr URL, exactly like from the scan (e.g. http://qtagr.com/123456)
	 * @param  integer rating {1,2,3,4,5}        
	 * @return mixed   data
	 */
	public function rate($url, $rating) {
		$rating *= 1;			
		$data = $this->request('rate', array(
					'url'=>$url,
					'rating'=>$rating			
				));	
			
		$this->handleErrorCodes($data);
		
		return $data;		
	}
	

	/**
	 * URL-claim a tag
	 * @param  string full gTAGr URL, exactly like from the scan (e.g. http://qtagr.com/123456)
	 * @param  string contentURL     
	 * @param  string title     
	 * @param  string description     
	 * @return mixed  data
	 */
	public function claimURL($url, $contentURL, $title, $description, $direct=false, $lat=null, $lon=null) {
		$in = array(
					'url'=>$url,
					'contentURL'=>$contentURL,
					'title'=>$title,
					'description'=>$description	,
					'direct'=>$direct?'1':'0'	
				);
		if ($lon != null) {
			$in['lat'] = $lat;
			$in['lon'] = $lon;
		}	
		
		$data = $this->request('claimURL', $in);	
		$this->handleErrorCodes($data);
		
		return $data;			
	}

	/**
	 * URL-claim a tag
	 * @param  string full gTAGr URL, exactly like from the scan (e.g. http://qtagr.com/123456) 
	 * @param  string title     
	 * @param  string content as BASE64     
	 * @return mixed  data
	 */
	public function claimPicture($url, $title, $pictureData, $lat=null, $lon=null) {
		$in = array(
					'url'=>$url,
					'title'=>$title,
					'pictureData'=>base64_encode($pictureData)
				);
		if ($lon != null) {
			$in['lat'] = $lat;
			$in['lon'] = $lon;
		}	
		
		$data = $this->request('claimPicture', $in);	
		$this->handleErrorCodes($data);
		
		return $data;			
	}
	
	/**
	 * Start an API request
	 * @param  string Function name
	 * @param  array  Data for the request   
	 * @return mixed  object
	 */
	private function request($function, $data) {
		$curl = curl_init();
		$data['apiKey'] = $this->apiKey;
		if ($this->userToken != null)
			$data['userToken'] = $this->userToken;
		curl_setopt($curl, CURLOPT_HEADER, FALSE);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($curl, CURLOPT_USERAGENT, 'qTAGr PHP library');
		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_URL, "http://api.qtagr.com/v1/".$function.".json");
		$result = curl_exec($curl);
		if (curl_errno($curl)) 
			throw new QtagrException(500, 'Server error: ' . curl_error($curl));
		return json_decode($result);	
		
	}
	
	/**
	 * Checks for an error and raises exception when found
	 */
	private function handleErrorCodes($data) {
		if ($data->success == 0) 
			throw new QtagrException($data->errorCode, $data->errorMessage);	
			
	}
	
}	

/**
 * qTAGr Exceprion class
 */
class QtagrException extends Exception {
	
	var $errorCode;
	var $errorMessage;
	
	public function QtagrException($errorCode, $errorMessage) {
		parent::__construct($code.': '.$message);
		$this->errorCode = $errorCode;
		$this->errorMessage = $errorMessage;	
	}
	
	public function getErrorCode() {
		return $this->errorCode;
	}
	
	public function getErrorMessage() {
		return $this->errorMessage;
	}
	
}
?>