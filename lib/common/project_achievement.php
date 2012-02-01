<?php
/**
 * project scoreboard
 *
 **/
class project_achievement
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
			$this->file_name = array_key_exists("file_name", $array) ? $array['file_name'] : ""; //achievements badge file name
		}
	}
	
	var $code;
	
	var $title;
	
	var $description;
	
	var $type;
	
	var $status;
	
	var $file_name;	
	
	public function original_path($project_id)
	{
		return empty($this->file_name) ? "" : $this->original_folder($project_id).DIRECTORY_SEPARATOR.$this->file_name;
	}
	
	public function original_folder($project_id)
	{
		return empty($this->file_name) ? "" : GLOBAL_WWW_FILE_PATH.GLOBAL_ACHIEVEMENTS_FOLDER.DIRECTORY_SEPARATOR.$project_id;
	}
	
	public function url($project_id, $size = "") // if size is 0 returns the default
	{
		return empty($this->file_name) ? "/gui/img/badge".$size.".png" : GLOBAL_ACHIEVEMENTS_URL."/".$project_id."/".$this->file_name;
	}
	
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
		  	'status' => new MongoInt32($this->status),
		  	'file_name' => $this->file_name
		);
	}
}
?>