<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pb.php';

class achievements_manager 
{
	
	public static function grant_achievement($achievement)
	{
		//TODO: add cache
		$projects_achievements = data::$database->projects_achievements;
		$achievement_array = $achievement->toArray();
		return $projects_achievements->insert($achievement_array) ?  $achievement_array['_id'] : NULL;
	}
	
	public static function get_achievement($project_id, $user_id, $achievement_code)
	{
		//TODO: add cache
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
			
		$projects_achievements = data::$database->projects_achievements;
		$achievement_array = $projects_achievements->findOne(array('project_id' => $project_id, 'user_id' => $user_id, 'achievement_code' => $achievement_code));
		return new achievement($achievement_array);
	}
	
	public static function revoke_achievement($project_id, $user_id, $achievement_code)
	{
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
			
		$projects_achievements = data::$database->projects_achievements;
		$projects_achievements->remove(array('project_id' => $project_id, 'user_id' => $user_id, 'achievement_code' => $achievement_code));
	}
	
	public static function get_achievement_board($project_id, $achievement_code, $from_time, $to_time, $page_index = 1, $page_size = 9999, $order = -1)
	{
		//TODO: add cache
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		$skip = (int)($page_size * ($page_index - 1));
		
		$projects_achievements = data::$database->projects_achievements;
		if($from_time == 0 && $to_time == 0)
		{
			return array(
						"count" => $projects_achievements->count(array('project_id' => $project_id, 'achievement_code' => $achievement_code)), 
						"data" => $projects_achievements->find(array('project_id' => $project_id, 'achievement_code' => $achievement_code))->sort(array("value" => $order))->skip($skip)->limit($page_size)
					);
		}
		else
		{
			return array(
						"count" => $projects_achievements->count(array('project_id' => $project_id, 'achievement_code' => $achievement_code, 'time' => array('$gte' => $from_time, '$lte' => $to_time))), 
						"data" => $projects_achievements->find(array('project_id' => $project_id, 'achievement_code' => $achievement_code, 'time' => array('$gte' => $from_time, '$lte' => $to_time)))->sort(array("value" => $order))->skip($skip)->limit($page_size)
					);
		}
	}
	
	public static function get_achievement_by_user_id($project_id, $user_id)
	{
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
			
		if(!is_object($user_id))
			$user_id = new MongoId($user_id);
			
		$projects_achievements = data::$database->projects_achievements;
		return $projects_achievements->find(array('project_id' => $project_id, 'user_id' => $user_id))->sort(array("time" => -1));
	}
}
?>