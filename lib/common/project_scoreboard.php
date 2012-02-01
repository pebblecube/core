<?php
/**
 * project scoreboard
 *
 **/
class project_scoreboard
{
	function __construct($array = NULL) 
	{
		$this->status = 1;
		if(is_array($array))
		{
			$this->code = array_key_exists("code", $array) ? $array['code'] : "";
			$this->title = array_key_exists("title", $array) ? $array['title'] : "";
			$this->description = array_key_exists("description", $array) ? $array['description'] : "";
			$this->type = array_key_exists("type", $array) ? $array['type'] : 0; // 0 private, 1 public
			$this->status = array_key_exists("status", $array) ? $array['status'] : 1; // 0 removed, 1 active
		}
	}
	
	var $code;
	
	var $title;
	
	var $description;
	
	var $type;
	
	var $status;
	
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
		  	'code' => $this->code,
		  	'title' => $this->title,
		  	'description' => $this->description,
		  	'type' => new MongoInt32($this->type),
		  	'status' => new MongoInt32($this->status)
		);
	}
}
?>