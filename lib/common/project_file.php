<?php
/**
 * project file attachment
 *
 **/
class project_file
{
	function __construct($array = NULL) 
	{
		if(is_array($array))
		{
			$this->file_name = array_key_exists("file_name", $array) ? $array['file_name'] : "";
			$this->description = array_key_exists("description", $array) ? $array['description'] : "";
			$this->timestamp = array_key_exists("timestamp", $array) ? $array['timestamp'] : time();
		}
	}
	
	/**
	 * downloadable item file name
	 *
	 * @var string
	 **/
	var $file_name;
	
	/**
	 * description
	 *
	 * @var string
	 **/
	var $description;
	
	/**
	 * timestamp upload
	 *
	 * @var int
	 **/
	var $timestamp;
	
	/**
	 * returns the complete file path
	 *
	 * @param string $project_id
	 * @return void
	 */
	public function path($project_id)
	{
		return $this->folder($project_id).DIRECTORY_SEPARATOR.$this->file_name;
	}
	
	/**
	 * returns the file url
	 *
	 * @param string $project_id 
	 * @return void
	 */
	public function url($project_id)
	{
		return GLOBAL_DOWNLOADS_URL."/".$project_id."/".$this->file_name;
	}
	
	/**
	 * returns the file folder
	 *
	 * @param string $project_id 
	 * @return void
	 */
	public function folder($project_id)
	{
		return GLOBAL_WWW_FILE_PATH.GLOBAL_DOWNLOADS_FOLDER.DIRECTORY_SEPARATOR.$project_id;
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
		  	'file_name' => $this->file_name,
		  	'description' => $this->description,
		  	'timestamp' => $this->timestamp
		);
	}
	
} // END class 
?>