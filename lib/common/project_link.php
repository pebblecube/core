<?php
/**
 * project link
 *
 **/
class project_link
{
	
	function __construct($array = NULL) 
	{
		if(is_array($array))
		{
			$this->url = array_key_exists("url", $array) ? $array['url'] : "";
			$this->description = array_key_exists("description", $array) ? $array['description'] : "";
		}
	}
	
	/**
	 * downloadable item file name
	 *
	 * @var string
	 **/
	var $url;
	
	/**
	 * description
	 *
	 * @var string
	 **/
	var $description;
	
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
		  	'url' => $this->url,
		  	'description' => $this->description
		);
	}
	
} // END class 
?>