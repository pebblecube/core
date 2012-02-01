<?php
/**
 * undocumented class
 *
 **/
class session_event
{	
	function __construct($array = NULL) 
	{
		$this->client_ip_geo = null;
	}
	
	/**
	 * reference to project
	 *
	 * @var MongoDBRef
	 */
	var $project_id;
	
	/**
	 * reference to the user session
	 *
	 * @var MongoDBRef
	 */
	var $session_id;
	
	/**
	 * code of the event
	 *
	 * @var string
	 */
	var $code;
	
	/**
	 * value of the event
	 *
	 * @var mixed
	 */
	var $value;
	
	/**
	 * time stamp
	 *
	 * @var integer
	 */
	var $time;
	
	/**
	 * client ip
	 *
	 * @var string
	 **/
	var $client_ip;
	
	/**
	 * data type
	 *
	 * @var string
	 **/
	var $datatype;
		
	var $user_id;
	
	var $user_name;
	
	var $client_ip_geo;
	
	/**
	 * returns object array representation
	 *
	 * @return Array
	 **/
	function toArray()
	{
		return array(
		  	'project_id' => $this->project_id,
		  	'session_id' => $this->session_id,
		  	'code' => $this->code,
		  	'value' => $this->value,
		  	'time' => $this->time,
			'client_ip' => $this->client_ip,
			'datatype' => $this->datatype,
			'user_id' => $this->user_id,
			'user_name' => $this->user_name,
			'client_ip_geo' => $this->client_ip_geo
		);
	}

} // END class 
?>