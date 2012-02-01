<?php
/**
* project functions constants
*/
class project_constant
{
	function __construct($array = NULL) {
		if(is_array($array)) {
			$this->code = array_key_exists("code", $array) ? $array['code'] : "";
			$this->description = array_key_exists("description", $array) ? $array['description'] : "";
			$this->value = array_key_exists("value", $array) ? $array['value'] : "";
		}
	}
	
	/**
	 * constant code
	 *
	 * @var string
	 **/
	var $code;
	
	/**
	 * constant description
	 *
	 * @var string
	 **/
	var $description;
	
	/**
	 * constant value
	 *
	 * @var float
	 **/
	var $value;
	
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
	function toArray() {
		return array(
		  	'code' => $this->code,
		  	'description' => $this->description,
			'value' => $this->value
		);
	}
}
?>