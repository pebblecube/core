<?php
/**
 * saved game class
 *
 **/
class saved_game
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
			$this->filename = array_key_exists("filename", $array) ? $array['filename'] : "";
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
	 * code of the event
	 *
	 * @var string
	 */
	var $filename;
	
	/**
	 * time stamp
	 *
	 * @var integer
	 */
	var $time;
	
	public function url()
	{
		return GLOBAL_GAMES_URL."/".$this->project_id."/".$this->id;
	}
	
	public function folder()
	{
		return GLOBAL_WWW_FILE_PATH.GLOBAL_GAMES_FOLDER.DIRECTORY_SEPARATOR.$this->project_id;
	}
	
	public function path()
	{
		return $this->folder().DIRECTORY_SEPARATOR.$this->id;
	}
	
	function toArray()
	{
		return array(
		  	'project_id' => $this->project_id,
		  	'session_id' => $this->session_id,
		  	'token_id' => $this->token_id,
		  	'user_id' => $this->user_id,
		  	'user_name' => $this->user_name,
		  	'filename' => $this->filename,
		  	'time' => $this->time
		);
	}
	
} // END class 
?>