<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pb.php';

class games_manager 
{
	public static function save($game)
	{
		$projects_saved_games = data::$database->projects_saved_games;
		$game_array = $game->toArray();
		return $projects_saved_games->insert($game_array) ?  $game_array['_id'] : NULL;
	}
	
	public static function update($game)
	{
		$projects_saved_games = data::$database->projects_saved_games;
		$game_array = $game->toArray();
		$projects_saved_games->update($game_array);
	}
	
	public static function remove($id)
	{
		if(!is_object($id))
			$id = new MongoId($id);
				
		$projects_saved_games = data::$database->projects_saved_games;
		$projects_saved_games->remove(array("_id" => $id));
	}
	
	public static function get_by_id_and_project($id, $project_id)
	{
		if(!is_object($id))
			$id = new MongoId($id);
		
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);

		$projects_saved_games = data::$database->projects_saved_games;
		$game_array = $projects_saved_games->findOne(array('_id' => $id, 'project_id' => $project_id));
		return isset($game_array) ? new saved_game($game_array) : NULL;
	}
	
	public static function list_by_user_token($user_token, $page_index = 1, $page_size = 9999)
	{
		if(!is_object($user_token))
			$user_token = new MongoId($user_token);
		
		$skip = (int)($page_size * ($page_index - 1));
		
		$projects_saved_games = data::$database->projects_saved_games;
		return array(
						"count" => $projects_saved_games->count(array('token_id' => $user_token)), 
						"data" => $projects_saved_games->find(array('token_id' => $user_token))->sort(array("time" => -1))->skip($skip)->limit($page_size)
						);
	}
	
	public static function list_by_project($project_id, $page_index = 1, $page_size = 9999)
	{
		//TODO: add cache
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		$skip = (int)($page_size * ($page_index - 1));
		
		$projects_saved_games = data::$database->projects_saved_games;
		return array(
						"count" => $projects_saved_games->count(array('project_id' => $project_id)), 
						"data" => $projects_saved_games->find(array('project_id' => $project_id))->sort(array("time" => -1))->skip($skip)->limit($page_size)
						);
	}
	
	public static function save_score($score)
	{
		//TODO: add cache
		$projects_scores = data::$database->projects_scores;
		$score_array = $score->toArray();
		return $projects_scores->insert($score_array) ?  $score_array['_id'] : NULL;
	}
		
	public static function get_board($project_id, $scoreboard_code, $from_time, $to_time, $page_index = 1, $page_size = 9999, $order = -1, $user_id = NULL)
	{			
		//TODO: add cache
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		$skip = (int)($page_size * ($page_index - 1));
		
		$projects_scores = data::$database->projects_scores;
		if($from_time == 0 && $to_time == 0)
		{
			$array_filer = array('project_id' => $project_id, 'scoreboard_code' => $scoreboard_code);
			
			if($user_id != NULL) {
				if(!is_object($user_id)) {
					$user_id = new MongoId($user_id);
				}
				$array_filer['user_id'] = $user_id;
			}
			
			return array(
				"count" => $projects_scores->count($array_filer), 
				"data" => $projects_scores->find($array_filer)->sort(array("value" => $order))->skip($skip)->limit($page_size)
			);
		}
		else
		{
			$array_filer = array('project_id' => $project_id, 'scoreboard_code' => $scoreboard_code, 'time' => array('$gte' => $from_time, '$lte' => $to_time));
			
			if($user_id != NULL) {
				if(!is_object($user_id)) {
					$user_id = new MongoId($user_id);
				}
				$array_filer['user_id'] = $user_id;
			}
			
			return array(
				"count" => $projects_scores->count($array_filer), 
				"data" => $projects_scores->find($array_filer)->sort(array("value" => $order))->skip($skip)->limit($page_size)
			);
		}
	}
}
?>