<?php
/**
 * access token class
 *
 **/
class token
{
	/**
	 * constructor
	 *
	 * @param array $array
	 */
	function __construct($array = NULL)
	{
		//set session values
		if(is_array($array))
		{
			$this->user_id = $array['user_id'];
			$this->user_name = $array['user_name'];
			$this->project_id = $array['project_id'];
			$this->timestamp = $array['timestamp'];
			$this->api_sig = $array['api_sig'];
		}
	}
	
	/**
	 * user id
	 *
	 * @var MongoId
	 **/
	var $user_id;
		
	var $user_name;
	
	/**
	 * project id
	 *
	 * @var MongoId
	 **/
	var $project_id;
	
	/**
	 * last token update
	 *
	 * @var int
	 */
	var $timestamp;
	
	/**
	 * api signature of the application
	 *
	 * @var string
	 */
	var $api_sig;
	
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
		return array(
		  	'user_id' => $this->user_id,
		  	'user_name' => $this->user_name,
		  	'project_id' => $this->project_id,
		  	'timestamp' => $this->timestamp,
		  	'api_sig' => $this->api_sig
		);
	}
	
} // END class 
?>