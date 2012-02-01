<?php
/**
 * project class
 *
 **/
class project
{
	/**
	 * constructor
	 *
	 * @param array $array
	 */
	function __construct($array = NULL) 
	{
		$this->links = array();
		$this->files = array();
		$this->screenshots = array();
		$this->events = array();
		$this->scoreboards = array();
		$this->achievements = array();
		$this->maps = array();
		$this->constants = array();
		$this->functions = array();
		$this->cipher = "none";
		
		//set project values
		if(is_array($array))
		{
			$this->id = $array['_id'];
			$this->name = array_key_exists("name", $array) ? $array['name'] : "";
			$this->description = array_key_exists("description", $array) ? $array['description'] : "";
			$this->url = array_key_exists("url", $array) ? $array['url'] : "";
			$this->owner = array_key_exists("owner", $array) ? $array['owner'] : "";
			$this->api_key = $array['api_key'];
			$this->api_secret = $array['api_secret'];
			$this->api_sig = array_key_exists('api_sig', $array) ? $array['api_sig'] : md5($this->api_key.$this->api_secret);
			$this->status = $array['status'];
			$this->developers = array_key_exists('developers', $array) ? $array['developers'] : array();
			$this->cipher = array_key_exists("cipher", $array) ? $array['cipher'] : "none";
			
			if(array_key_exists('design', $array))
				$this->design = new project_design($array['design']);
			
			if(array_key_exists('files', $array))
				$this->add_files($array['files']);
				
			if(array_key_exists('links', $array))
				$this->add_links($array['links']);
			
			if(array_key_exists('screenshots', $array))
				$this->add_screenshots($array['screenshots']);
			
			if(array_key_exists('events', $array))
				$this->add_events($array['events']);
				
			if(array_key_exists('scoreboards', $array))
				$this->add_scoreboards($array['scoreboards']);
				
			if(array_key_exists('achievements', $array))
				$this->add_achievements($array['achievements']);
			
			if(array_key_exists('maps', $array))
				$this->add_maps($array['maps']);
			
			if(array_key_exists('constants', $array))
				$this->add_constants($array['constants']);
				
			if(array_key_exists('functions', $array))
				$this->add_functions($array['functions']);
		}
		
		//set up project arrays
		if(!is_array($this->developers))
			$this->developers = array();
		
		//set up new design object if empty	
		if(!isset($this->design))
			$this->design = new project_design();
	}
	
	/**
	 * project id
	 *
	 * @var MongoId
	 **/
	var $id;
	
	/**
	 * project name
	 *
	 * @var string
	 **/
	var $name;
	
	/**
	 * project description
	 *
	 * @var string
	 **/
	var $description;
	
	/**
	 * project unique url
	 *
	 * @var string
	 **/
	var $url;
	
	/**
	 * project owner
	 *
	 * @var MongoId
	 **/
	var $owner;
	
	/**
	 * api key
	 *
	 * @var string
	 **/
	var $api_key;
	
	/**
	 * api secret
	 *
	 * @var string
	 **/
	var $api_secret;
	
	/**
	 * MD5(api_key + api_secret)
	 *
	 * @var string
	 **/
	var $api_sig;
	
	/**
	 * users id
	 *
	 * @var Array[MongoId]
	 **/
	var $developers;
	
	/**
	 * undocumented class variable
	 *
	 * @var status
	 **/
	var $status;
	
	/**
	 * list of links
	 *
	 * @var Array[project_link]
	 **/
	var $links;
	
	/**
	 * list of screenshots
	 *
	 * @var Array[project_screenshot]
	 **/
	var $screenshots;
	
	/**
	 * project constants array
	 *
	 * @var Array[project_constant]
	 **/
	var $constants;
	
	/**
	 * project functions array
	 *
	 * @var Array[project_function]
	 **/
	var $functions;
	
	/**
	 * list of downlodable files
	 *
	 * @var Array[project_file]
	 **/
	var $files;
	
	/**
	 * list of custom events
	 *
	 * @var Array[project_event]
	 */
	var $events;
	
	
	/**
	 * list of custom scoreboards
	 *
	 * @var Array[project_scoreboard]
	 */
	var $scoreboards;
	
	/**
	 * cipher that the developer wants to use in the communication
	 *
	 * @var string
	 **/
	var $enc_cipher;
	
	/**
	 * List project maps
	 *
	 * @var Array[project_map]
	 **/
	var $maps;
	
	/**
	 * adds a dev to the collection
	 *
	 * @param MongoId $user_id
	 * @return void
	 **/
	function add_developer($user_id)
	{
		//check if is object or string
		if(!is_object($user_id))
			$user_id = new MongoId($user_id);
		
		if(!in_array($user_id, $this->developers))	
			array_push($this->developers, $user_id);
	}
	
	function add_link($link)
	{
		if(!in_array($link, $this->links))	
			array_push($this->links, $link);
	}
	
	function add_links($array)
	{
		if(sizeof($array) > 0)
		{
			for ($i=0; $i < sizeof($array); $i++) 
				$this->add_link(new project_link($array[$i]));
		}
	}
	
	function add_file($file)
	{
		if(!in_array($file, $this->files))	
			array_push($this->files, $file);
	}
	
	function add_files($array)
	{
		if(sizeof($array) > 0)
		{
			for ($i=0; $i < sizeof($array); $i++)
				$this->add_file(new project_file($array[$i]));
		}
	}
	
	function add_screenshot($screenshot)
	{
		if(!in_array($screenshot, $this->screenshots))	
			array_push($this->screenshots, $screenshot);
	}
	
	function add_screenshots($array)
	{
		if(sizeof($array) > 0)
		{
			for ($i=0; $i < sizeof($array); $i++)
				$this->add_screenshot(new project_screenshot($array[$i]));
		}
	}
	
	function add_constant($constant) {
		if(!in_array($constant, $this->constants)) {
			array_push($this->constants, $constant);
		}
	}
	
	function add_constants($array) {
		if(sizeof($array) > 0) {
			for ($i=0; $i < sizeof($array); $i++) {
				$this->add_constant(new project_constant($array[$i]));
			}
		}
	}
	
	function add_function($function) {
		if(!in_array($function, $this->functions)) {
			array_push($this->functions, $function);
		}
	}
	
	function add_functions($array) {
		
		if(!is_array($this->functions))
			$this->functions = array();
		
		if(sizeof($array) > 0) {
			for ($i=0; $i < sizeof($array); $i++) {
				$this->add_function(new project_function($array[$i]));
			}
		}
	}
	
	function add_event($event)
	{
		//if(!in_array($event, $this->events))	
		$exists = false;
		for ($i=0; $i < sizeof($this->events); $i++) { 
			if($this->events[$i]->code == $event->code)
			{
				$exists = true;
				break;
			}
		}
		if(!$exists)
			array_push($this->events, $event);
	}
	
	function add_events($array)
	{
		if(sizeof($array) > 0)
		{
			for ($i=0; $i < sizeof($array); $i++)
				$this->add_event(new project_event($array[$i]));
		}
	}
	
	function add_scoreboard($event)
	{
		if(!in_array($event, $this->scoreboards))	
			array_push($this->scoreboards, $event);
	}
	
	function add_scoreboards($array)
	{
		if(sizeof($array) > 0)
		{
			for ($i=0; $i < sizeof($array); $i++)
				$this->add_scoreboard(new project_scoreboard($array[$i]));
		}
	}
	
	function add_achievement($achievement)
	{
		if(!in_array($achievement, $this->achievements))	
			array_push($this->achievements, $achievement);
	}
	
	function add_achievements($array)
	{
		if(sizeof($array) > 0)
		{
			for ($i=0; $i < sizeof($array); $i++)
				$this->add_achievement(new project_achievement($array[$i]));
		}
	}
	
	function add_map($map) {
		if(!in_array($map, $this->maps)) {
			array_push($this->maps, $map);
		}
	}
	
	function add_maps($array) {
		if(sizeof($array) > 0) {
			for ($i=0; $i < sizeof($array); $i++) {
				$this->add_map(new project_map($array[$i]));
			}
		}
	}
	
	/**
	 * removes a defined event
	 *
	 **/
	function remove_event_by_code($code)
	{
		if(!empty($code))
		{
			for ($i=0; $i < sizeof($this->events); $i++) 
			{
				if($this->events[$i]->code == $code)
				{
					unset($this->events[$i]);
					break;
				}
			}
		}
	}
	
	function get_event_by_code($code) {
		if(!empty($code)) {
			for ($i=0; $i < sizeof($this->events); $i++) {
				if($this->events[$i]->code == $code) {
					return $this->events[$i];
				}
			}
		}
		return NULL;
	}
	
	/**
	 * returns JSON of the object
	 *
	 * @return string
	 **/
	function toJson() {
		return json_encode($this->toArray());
	}
	
	/**
	 * returns object array representation
	 *
	 * @return Array
	 **/
	function toArray() {
		$prj_array = array(
		  	'name' => $this->name,
			'description' => $this->description,
		  	'url' => $this->url,
		  	'owner' => $this->owner,
		  	'api_key' => $this->api_key,
		  	'api_secret' => $this->api_secret,
			'api_sig' => isset($this->api_sig) ? $this->api_sig : md5($this->api_key.$this->api_secret),
		  	'developers' => $this->developers,
		  	'status' => $this->status,
		  	'design' => null,
			'links' => $this->links,
			'files' => $this->files,
			'screenshots' => $this->screenshots,
			'events' => $this->events,
			'scoreboards' => $this->scoreboards,
			'achievements' => $this->achievements,
			'maps' => $this->maps,
			'constants' => $this->constants,
			'functions' => $this->functions,
			'cipher' => $this->cipher
		);
		
		if(isset($this->design))
			$prj_array['design'] = $this->design->toArray();
		
		return $prj_array;
	}

} // END class 
?>