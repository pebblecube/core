<?php
/**
 * api log class
 *
 **/
class api_log
{
	/**
	 * constructor
	 *
	 */
	function __construct() 
	{
		$this->server = array(		
			"HTTP_HOST" => isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : NULL,
			"HTTP_USER_AGENT" => isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : NULL,
			"HTTP_ACCEPT_LANGUAGE" => isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : NULL,
			"REMOTE_ADDR" => isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : NULL,
			"QUERY_STRING" => isset($_SERVER["QUERY_STRING"]) ? $_SERVER["QUERY_STRING"] : NULL,
			"REQUEST_METHOD" => isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : NULL,
			"REQUEST_URI" => isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : NULL,
			"SCRIPT_NAME" => isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER["SCRIPT_NAME"] : NULL,
			"REQUEST_TIME" => isset($_SERVER["REQUEST_TIME"]) ? $_SERVER["REQUEST_TIME"] : NULL
		);
		
		if(!is_array($this->objects))
			$this->objects = array();
	}
	
	/**
	 * project id
	 *
	 * @var mongoId
	 **/
	var $project_id;
	
	/**
	 * array of MongoDBRef
	 *
	 * @var array
	 **/
	var $objects;
	
	/**
	 * Server and execution environment information
	 *
	 * @var array
	 **/
	var $server;
	
	var $geo = null;
	
	var $msg = "";
	
	/**
	 * adds an mongo reference at the object
	 *
	 * @param MongoDBRef $object 
	 * @return void
	 */
	function add_object($object)
	{
		array_push($this->objects, $object);
	}

}
?>