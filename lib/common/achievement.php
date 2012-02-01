<?php
/**
 * saved game class
 *
 **/
class achievement
{
	function __construct($array = NULL)
	{
		$this->time = time();
		$this->project_id = null;
		$this->session_id = null;
		$this->token_id = null;
		$this->user_id = null;
		
		if(is_array($array))
		{
			$this->id = $array['_id'];
			$this->project_id = array_key_exists("project_id", $array) ? $array['project_id'] : null;
			$this->session_id = array_key_exists("session_id", $array) ? $array['session_id'] : null;
			$this->token_id = array_key_exists("token_id", $array) ? $array['token_id'] : null;
			$this->user_id = array_key_exists("user_id", $array) ? $array['user_id'] : null;
			$this->user_name = array_key_exists("user_name", $array) ? $array['user_name'] : "";
			$this->achievement_code = array_key_exists("achievement_code", $array) ? $array['achievement_code'] : "";
			$this->time = array_key_exists("time", $array) ? $array['time'] : time();
		}
	}
	
	/**
	 * project id
	 *
	 * @var MongoId
	 **/
	var $id;
	
	/**
	 * reference to project
	 *
	 * @var MongoId
	 */
	var $project_id;
	
	/**
	 * reference to the user session
	 *
	 * @var MongoId
	 */
	var $session_id;
	
	/**
	 * reference to the user token
	 *
	 * @var MongoId
	 */
	var $token_id;
	
	/**
	 * reference to the user id
	 *
	 * @var MongoId
	 */
	var $user_id;
	
	/**
	 * User name
	 *
	 * @var string
	 */
	var $user_name;
	
	/**
	 * code of the achievement
	 *
	 * @var string
	 */
	var $achievement_code;
	
	/**
	 * time stamp
	 *
	 * @var integer
	 */
	var $time;
		
	/**
	 * Returns the object in array format
	 *
	 * @return void
	 */
	function toArray()
	{
		return array(
		  	'project_id' => $this->project_id,
		  	'session_id' => $this->session_id,
		  	'token_id' => $this->token_id,
		  	'user_id' => $this->user_id,
		  	'user_name' => $this->user_name,
		  	'achievement_code' => $this->achievement_code,
		  	'time' => $this->time
		);
	}
	
} // END class 
?>