<?php
/**
* project maps
*/
class project_map {

	function __construct($array = NULL) {
		if(is_array($array)) {
			$this->code = array_key_exists("code", $array) ? $array['code'] : "";
			$this->title = array_key_exists("title", $array) ? $array['title'] : "";
			$this->status = array_key_exists("status", $array) ? $array['status'] : 1;
			$this->description = array_key_exists("description", $array) ? $array['description'] : "";
		}
	}
	
	/**
	 * map code
	 *
	 * @var string
	 **/
	var $code;
	
	/**
	 * Map name
	 *
	 * @var string
	 */
	var $title;
	
	/**
	 * Map desription
	 *
	 * @var string
	 */
	var $description;
	
	/**
	 * 0 removed, 1 active
	 *
	 * @var int
	 */
	var $status;
	
	function toArray() {
		return array(
		  	'code' => $this->code,
		  	'title' => $this->title,
		  	'status' => new MongoInt32($this->status),
		  	'description' => $this->description
		);
	}
}

/**
 * track segment of a specific session on a map
 */
class project_map_trackseg {
	
	function __construct($array = NULL) {
		$this->points = array();
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
	 * code of the map
	 *
	 * @var string
	 */
	var $map_code;
	
	var $time_start;
	
	var $time_stop;
	
	/**
	 * list of map points
	 *
	 * @var Array[project_map_point]
	 **/
	var $points;
	
	function add_point($point)
	{
		if(!in_array($point, $this->points))	
			array_push($this->points, $point);
	}
	
	function add_points($array)
	{
		if(sizeof($array) > 0)
		{
			for ($i=0; $i < sizeof($array); $i++)
				$this->add_point(new project_map_point($array[$i]));
		}
	}
	
	function toArray() {
		return array(
		  	'project_id' => $this->project_id,
			'session_id' => $this->session_id,
		  	'map_code' => $this->map_code,
		  	'time_start' => $this->time_start,
		  	'time_stop' => $this->time_stop,
		  	'points' => $this->points
		);
	}
}

/**
 * standard 3d cartesian point
 */
class project_map_point {
	
	function __construct($array = NULL) {
		$this->x = 0;
		$this->y = 0;
		$this->z = 0;
		if(is_array($array)) {
			$this->x = array_key_exists("x", $array) ? $array['x'] : "";
			$this->y = array_key_exists("y", $array) ? $array['y'] : "";
			$this->z = array_key_exists("z", $array) ? $array['z'] : "";
		}
	}
	
	/**
	 * x coordinate
	 *
	 * @var double
	 **/
	var $x;
	
	/**
	 * y coordinate
	 *
	 * @var double
	 **/
	var $y;
	
	/**
	 * z coordinate
	 *
	 * @var double
	 **/
	var $z;
}
?>