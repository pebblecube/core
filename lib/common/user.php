<?php
/**
 * user
 *
 **/
class user
{
	/**
	 * constructor
	 *
	 * @param array $array
	 */
	function __construct($array = NULL) 
	{
		$this->project_id = NULL;
		//set user values
		if(is_array($array))
		{
			$this->id = $array['_id'];
			$this->email = $array['email'];
			$this->password = $array['password'];
			$this->username = $array['username'];
			$this->status = $array['status'];
			$this->type = $array['type'];
			$this->project_id = $array['project_id'];
			$this->auth_token = md5(strtolower($this->email).$this->password);
			$this->tokens = array_key_exists('tokens', $array) ? $array['tokens'] : array();
			$this->alien_id = $array['alien_id'];
			$this->alien_token = $array['alien_token'];
			$this->alien_secret = $array['alien_secret'];
			$this->alien_auth_service = $array['alien_auth_service'];
		}
			
		//set up tokens array
		if(!is_array($this->tokens))
			$this->tokens = array();
	}
	
	/**
	 * user id
	 *
	 * @var MongoId
	 **/
	var $id;
	
	/**
	 * user email
	 *
	 * @var string
	 **/
	var $email;
	
	/**
	 * user password
	 *
	 * @var string
	 **/
	var $password;
	
	/**
	 * md5(username + md5(password))
	 *
	 * @var string
	 **/
	var $auth_token;
	
	/**
	 * user full name / username
	 *
	 * @var string
	 **/
	var $username;
	
	/**
	 * user status
	 *
	 * @var int
	 **/
	var $status;
	
	/**
	 * user type
	 *
	 * @var int
	 **/
	var $type;
	
	/**
	 * user's tokens
	 *
	 * @var array
	 **/
	var $tokens;
	
	/**
	 * project id
	 *
	 * @var MongoId
	 **/
	var $project_id;
	
	/**
	 * id on external auth services
	 *
	 * @var string
	 **/
	var $alien_id;
	
	/**
	 * token on external auth service
	 *
	 * @var string
	 **/
	var $alien_token;
	
	/**
	 * eventual secret on external auth service
	 *
	 * @var string
	 **/
	var $alien_secret;
	
	/**
	 * label that identifies the service used
	 *
	 * @var string
	 **/
	var $alien_auth_service;
	
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
			'email' => $this->email,
		  	'password' => $this->password,
			'auth_token' => md5(strtolower($this->email).$this->password),
		  	'status' => $this->status,
		  	'type' => $this->type,
			'project_id' => $this->project_id,
			'username' => $this->username,
			'tokens' => $this->tokens,
			'alien_id' => $this->alien_id,
			'alien_token' => $this->alien_token,
			'alien_secret' => $this->alien_secret,
			'alien_auth_service' => $this->alien_auth_service
		);
	}
	
	/**
	 * adds a new access toke to user object
	 *
	 * @param string $token_id 
	 * @param string $project_id 
	 * @return void
	 */
	function add_token($token_id, $project_id)
	{
		//check if is object or string
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
			
		if(!is_object($token_id))
			$token_id = new MongoId($token_id);
			
		//set up projects array
		if(!is_array($this->tokens))
			$this->tokens = array();
			
		if(!in_array(array("token_id" => $token_id, "project_id" => $project_id), $this->tokens))
		{
			//add to array
			array_push($this->projects, array("token_id" => $token_id, "project_id" => $project_id));
		}
	}

} // END class
?>