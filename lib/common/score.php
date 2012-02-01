<?php
/**
 * saved game class
 *
 **/
class score
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
			$this->scoreboard_code = array_key_exists("scoreboard_code", $array) ? $array['scoreboard_code'] : "";
			$this->value = array_key_exists("value", $array) ? $array['value'] : "";
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
		
	var $user_name;
	
	/**
	 * code of the scoreboard
	 *
	 * @var string
	 */
	var $scoreboard_code;
	
	/**
	 * value
	 *
	 * @var string
	 */
	var $value;	
	
	/**
	 * time stamp
	 *
	 * @var integer
	 */
	var $time;
		
	function toArray()
	{
		return array(
		  	'project_id' => $this->project_id,
		  	'session_id' => $this->session_id,
		  	'token_id' => $this->token_id,
		  	'user_id' => $this->user_id,
		  	'user_name' => $this->user_name,
		  	'scoreboard_code' => $this->scoreboard_code,
		  	'value' => $this->value,
		  	'time' => $this->time
		);
	}
	
} // END class 
?>