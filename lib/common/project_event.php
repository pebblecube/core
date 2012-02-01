<?php
/**
 * project custom event
 *
 */
class project_event
{
	function __construct($array = NULL) 
	{
		$this->options = array();
		if(is_array($array))
		{
			$this->code = array_key_exists("code", $array) ? $array['code'] : "";
			$this->description = array_key_exists("description", $array) ? $array['description'] : "";
			$this->typeof = array_key_exists("typeof", $array) ? $array['typeof'] : "";
			$this->options = array_key_exists("options", $array) ? $array['options'] : array();
		}
	}
	
	/**
	 * custom event code
	 *
	 * @var string
	 */
	var $code;
	
	/**
	 * Description of the event
	 *
	 * @var string
	 */
	var $description;
	
	/**
	 * data type: integer, float, string, boolean, array
	 *
	 * @var string
	 */
	var $typeof;
	
	
	var $options;
	
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
		  	'description' => $this->description,
		  	'typeof' => $this->typeof,
		  	'options' => $this->options
		);
	}
	
} // END class 
?>