<?php
/**
 * developer
 *
 **/
class developer
{
	/**
	 * constructor
	 *
	 * @param array $array
	 */
	function __construct($array = NULL) 
	{
		//set developer values
		if(is_array($array))
		{
			$this->id = $array['_id'];
			$this->email = $array['email'];
			$this->password = $array['password'];
			$this->username = $array['username'];
			$this->status = $array['status'];
			$this->projects = array_key_exists('projects', $array) ? $array['projects'] : array();
		}
		//set up projects array
		if(!is_array($this->projects))
			$this->projects = array();
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
	 * user's projects
	 *
	 * @var array
	 **/
	var $projects;
		
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
		  	'status' => $this->status,
		  	'username' => $this->username,
			'projects' => $this->projects
		);
	}
	
	/**
	 * add a new project to user object
	 *
	 * @param mix $project_id
	 * @param int $level
	 * @return void
	 */
	function add_project($project_id, $level = 0)
	{
		//check if is object or string
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
			
		//set up projects array
		if(!is_array($this->projects))
			$this->projects = array();
		
		if(
			!in_array(array("project_id" => $project_id, "level" => 1, "status" => 1), $this->projects)
			&&
			!in_array(array("project_id" => $project_id, "level" => 0, "status" => 1), $this->projects)
		)
		{
			//add to array
			array_push($this->projects, array("project_id" => $project_id, "level" => $level, "status" => 1));
		}
	}
	
} // END class
?>