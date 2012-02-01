<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/achievements_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//values
$achievement_code = isset($_GET['code']) ? $_GET['code'] : '';
$token_key = isset($_GET['user_token']) ? $_GET['user_token'] : null;

if($project->cipher != "none")
{
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$achievement_code = isset($enc_array['code']) ? $enc_array['code'] : '';
		$token_key = isset($enc_array['user_token']) ? $enc_array['user_token'] : null;
	}
}


//check values
if(empty($achievement_code))
	pb_api_error_message(400, "invalid parameters: code");

$achieve = null;
//get achievement details
if(is_array($project->achievements))
{
	for ($i=0; $i < sizeof($project->achievements); $i++) { 
		if($achievement_code == $project->achievements[$i]->code) {
			$achieve = $project->achievements[$i];
		}
	}
}

if($achieve != null)
{
	//remove un-wanted properties
	unset($achieve->type);
	unset($achieve->status);
	
	//change image url
	$achieve->badge_url = $achieve->url($project->id);
	unset($achieve->file_name);
	
	if($token_key != null)
	{
		//check user token
		$access_token = account_manager::check_access_token_project($token_key, $project->id);
		if(!isset($access_token))
			pb_api_error_message(404, "user_token not found");
		
		//get user achievement
		$user_achievement = achievements_manager::get_achievement($project->id, $access_token["user_id"], $achieve->code)->toArray();
		if($user_achievement["user_id"] != null)
		{
			$user_achievement["achievement"] = $achieve;
			$achieve = $user_achievement;
			unset($achieve['user_id']);
			unset($achieve['project_id']);
			unset($achieve['token_id']);
			unset($achieve['session_id']);
		}
	}
	//close conn
	data::close_conn();
	
	//return achievement obj
	die(pb_api_RIJNDAEL_encrypt(json_encode($achieve), $project->api_secret, $project->cipher));
}
else
{
	//close conn
	data::close_conn();
	
	pb_api_error_message(404, "achievement not found");	
}
?>