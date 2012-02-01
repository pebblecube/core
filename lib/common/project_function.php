<?php
/**
* project functions
*/
class project_function
{
	function __construct($array = NULL) {
		
		$this->variables = array();
		$this->constants = array();
		$this->groups = array();
		
		if(is_array($array)) {
			$this->code = array_key_exists("code", $array) ? $array['code'] : "";
			$this->script = array_key_exists("script", $array) ? $array['script'] : "";
			$this->description = array_key_exists("description", $array) ? $array['description'] : "";
			$this->variables = array_key_exists("variables", $array) ? $array['variables'] : array();
			$this->constants = array_key_exists("constants", $array) ? $array['constants'] : array();
			$this->groups = array_key_exists("groups", $array) ? $array['groups'] : array();
		}
	}
	
	/**
	 * function code
	 *
	 * @var string
	 **/
	var $code;
	
	/**
	 * function description
	 *
	 * @var string
	 **/
	var $description;
	
	/**
	 * function script
	 *
	 * @var string
	 **/
	var $script;
	
	/**
	 * function variables array
	 *
	 * @var array
	 */
	var $variables;
	
	/**
	 * function constants
	 *
	 * @var array
	 **/
	var $constants;
	
	/**
	 * grouping functions
	 *
	 * @var array
	 */
	var $groups;
	
	function parse_script($script = NULL) {
		
		if($script == NULL) {
			$script = $this->script;
		}
		
		if(!empty($script)) {
			//get all the variables used in the function
			preg_match_all('/\[var\:(?P<var>[a-z]+)\]/', $script, $matches);
			if(array_key_exists("var", $matches)) {
				$this->variables = $matches["var"];
			}
			//get all the constants
			preg_match_all('/\[const\:(?P<const>[a-zA-Z0-9_\-\.]+)\]/', $script, $matches);
			if(array_key_exists("const", $matches)) {
				$this->constants = $matches["const"];
			}
			//clear groups
			unset($this->groups);
			$this->groups = array();
			//gets all the grouping on the stats
			preg_match_all('/\[(?P<group>[min|max|sum|count|avg]+)\:(?P<event>[a-zA-Z0-9_\-\.#]+)\]/', $script, $matches);
			if(array_key_exists("group", $matches) && array_key_exists("event", $matches)) {
				for ($i=0; $i < sizeof($matches["group"]); $i++) {
					if($matches["group"][$i] != "var" && $matches["group"][$i] != "const") {
						//check if days are specified
						$event = explode("#", $matches["event"][$i]);
						$this->groups[] = array(
								"group" => $matches["group"][$i],
								"event" => $event[0],
								"days" => sizeof($event) > 1 ? (int)$event[1] : 0,
								"formula" => $matches["group"][$i].":".$event[0].(sizeof($event) > 1 ? "#".$event[1] : ""),
								"value" => 0
							);
					}
				}
			}
		}
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
	function toArray() {
		return array(
		  	'code' => $this->code,
		  	'description' => $this->description,
			'script' => $this->script,
			'variables' => $this->variables,
			'constants' => $this->constants,
			'groups' => $this->groups
		);
	}
}
?>