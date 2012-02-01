<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pb.php';

class stats_manager 
{
	public static function get_sessions_count($project_id, $from_date, $to_date)
	{
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		$projects_stats = data::$database->projects_stats;
		return $projects_stats->find(array('project_id' => $project_id, "time" => array('$gte' => $from_date, '$lte' => $to_date)), array('sessions' => 1, 'time' => 1))->sort(array("time" => 1));
	}
	
	public static function get_events_stats($code, $project_id, $from_date, $to_date)
	{
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		$projects_stats = data::$database->projects_stats;
		return $projects_stats->find(array('project_id' => $project_id, 'events.code' => $code, "time" => array('$gte' => $from_date, '$lte' => $to_date)), array('events' => 1, 'time' => 1))->sort(array("time" => 1));
	}
	
	public static function get_exports($project_id, $page_index = 1, $page_size = 9999)
	{
		//TODO: memcached
		$skip = (int)($page_size * ($page_index - 1));
		$projects_stats_exports = data::$database->projects_stats_exports;
		return array(
						"count" => $projects_stats_exports->count(array('project_id' => $project_id)), 
						"data" => $projects_stats_exports->find(array('project_id' => $project_id))->sort(array("time" => -1))->skip($skip)->limit($page_size)
						);
	}
	
	public static function delete_export_file($export_id, $project_id)
	{
		if(!is_object($export_id))
			$export_id = new MongoId($export_id);
				
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
					
		//get file name
		$projects_stats_exports = data::$database->projects_stats_exports;
		$obj = $projects_stats_exports->findOne(array('_id' => $export_id, 'project_id' => $project_id));
		if(is_array($obj))
		{
			//delete file
			if(file_exists(GLOBAL_WWW_FILE_PATH.GLOBAL_STATS_FOLDER."/".$project_id."/".$obj["name"]))
				unlink(GLOBAL_WWW_FILE_PATH.GLOBAL_STATS_FOLDER."/".$project_id."/".$obj["name"]);
			//delete record
			$projects_stats_exports->remove(array('_id' => $export_id));
			//TODO: amazon s3
		}
	}
	
	public static function get_geo_events_stats($project_id, $from_date, $to_date)
	{
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		$projects_stats_geo = data::$database->projects_stats_geo;
		
		if($from_date > 0 && $to_date > 0)
			return $projects_stats_geo->find(array('project_id' => $project_id, "time" => array('$gte' => $from_date, '$lte' => $to_date)), array('events' => 1, 'time' => 1))->sort(array("time" => 1));
		else
			return $projects_stats_geo->find(array('project_id' => $project_id), array('events' => 1, 'time' => 1))->sort(array("time" => 1));
	}
	
	public static function get_geo_sessions_stats($project_id, $from_date, $to_date)
	{
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		$projects_stats_geo = data::$database->projects_stats_geo;
		
		if($from_date > 0 && $to_date > 0)
			return $projects_stats_geo->find(array('project_id' => $project_id, "time" => array('$gte' => $from_date, '$lte' => $to_date)), array('sessions' => 1, 'time' => 1))->sort(array("time" => 1));
		else
			return $projects_stats_geo->find(array('project_id' => $project_id), array('sessions' => 1, 'time' => 1))->sort(array("time" => 1));	
	}
	
	public static function get_array_events_stats($code, $project_id, $from_date, $to_date)
	{
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		$projects_stats = data::$database->projects_stats;
		return $projects_stats->find(array('project_id' => $project_id, 'events.code' => $code, 'events.data.datatype' => "array", "time" => array('$gte' => $from_date, '$lte' => $to_date)), array('events' => 1, 'time' => 1))->sort(array("time" => 1));
	}
}
?>