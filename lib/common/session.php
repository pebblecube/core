<?php
/**
 * Session class
 *
 **/
class session
{
	/**
	 * constructor
	 *
	 * @param array $array
	 */
	function __construct($array = NULL) 
	{
		$this->client_ip_geo = null;
		//set session values
		if(is_array($array))
		{
			$this->id = $array['_id'];
			$this->client_ip = $array['client_ip'];
			$this->project_id = $array['project_id'];
			$this->token_id = $array['token_id'];
			$this->access_token = new token($array['access_token']);
			$this->time_start = $array['time_start'];
			$this->time_stop = $array['time_stop'];
			$this->version = $array['version'];
			$this->user_id = $array['user_id'];
			$this->user_name = $array['user_name'];
			$this->client_ip_geo = $array['client_ip_geo'];
		}
	}
	
	/**
	 * client ip
	 *
	 * @var string
	 **/
	var $client_ip;
	
	var $client_ip_geo;
	
	/**
	 * project id
	 *
	 * @var MongoId
	 **/
	var $project_id;
	
	/**
	 * token id
	 *
	 * @var MongoId
	 **/
	var $token_id;
	
	/**
	 * user access token
	 *
	 * @var token
	 **/
	var $access_token;
	
	/**
	 * start timestamp
	 *
	 * @var int
	 **/
	var $time_start;
	
	/**
	 * stop timestamp
	 *
	 * @var int
	 **/
	var $time_stop;
	
	/**
	 * app version
	 *
	 * @var string
	 **/
	var $version;
	
	var $user_id;
	
	var $user_name;
	
	/**
	 * returns JSON of the object
	 *
	 * @return string
	 **/
	function toJson()
	{
		return json_encode($this->toArray());
	}
	
	/**
	 * returns object array representation
	 *
	 * @return Array
	 **/
	function toArray()
	{
		$session_array = array(
		  	'client_ip' => $this->client_ip,
		  	'project_id' => $this->project_id,
			'token_id' => NULL,
			'access_token' => NULL,
		  	'time_start' => $this->time_start,
		  	'time_stop' => $this->time_stop,
		  	'version' => $this->version,
		  	'user_id' => $this->user_id,
		  	'user_name' => $this->user_name,
		  	'client_ip_geo' => $this->client_ip_geo
		);
		
		if(isset($this->access_token))
			$session_array['access_token'] = $this->access_token->toArray();
		
		if(isset($this->token_id))
			$session_array['token_id'] = $this->token_id;
		
		return $session_array;
	}
	
} // END class 
?>