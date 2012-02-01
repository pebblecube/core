<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/achievements_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//values
$session_key = isset($_POST['session_key']) ? $_POST['session_key'] : null;
$token_key = isset($_POST['user_token']) ? $_POST['user_token'] : null;
$achievement_code = isset($_POST['code']) ? $_POST['code'] : '';
$time_achievement = isset($_POST['time']) ? $_POST['time'] : time();

if($project->cipher != "none")
{
	$data = isset($_POST['data']) ? $_POST['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$session_key = isset($enc_array['session_key']) ? $enc_array['session_key'] : null;
		$token_key = isset($enc_array['user_token']) ? $enc_array['user_token'] : null;
		$achievement_code = isset($enc_array['code']) ? $enc_array['code'] : '';
		$time_achievement = isset($enc_array['time']) ? $enc_array['time'] : time();
	}
}

//check values
if(empty($token_key))
	pb_api_error_message(400, "invalid parameters: user_token");
	
if(empty($achievement_code))
	pb_api_error_message(400, "invalid parameters: code");
	
//check user token
$access_token = account_manager::check_access_token_project($token_key, $project->id);
if(!isset($access_token))
	pb_api_error_message(404, "user_token not found");

//check if session exists
$db_event_session = null;
if($session_key != null)
{
	$session = session_manager::get_active_session($session_key, $project->id);
	if(!isset($session))
		pb_api_error_message(404, "session not found");
	
	$db_event_session = $session->id;
}

//check if achievement exists
$achievement_exists = false;
for ($i=0; $i < sizeof($project->achievements); $i++)
{
	if(!$achievement_exists)
		$achievement_exists = ($project->achievements[$i]->status > 0 && $project->achievements[$i]->code == $achievement_code);
}
if(!$achievement_exists)
	pb_api_error_message(404, "achievement not found");

$achievement = new achievement();
$achievement->project_id = $project->id;
$achievement->session_id = $db_event_session;
$achievement->token_id = $access_token['_id'];
$achievement->user_id = $access_token['user_id'];
$achievement->user_name = $access_token['user_name'];
$achievement->achievement_code = $achievement_code;
$achievement->time = $time_achievement;

$res = achievements_manager::grant_achievement($achievement);

//***************************
//add a log for the apis
$log_obj = new api_log();
$log_obj->project_id = $project->id;
$log_obj->add_object(MongoDBRef::create("projects_achievements", $res));
$log_obj->msg = "achievement granted";
api_logger::add($log_obj);
//***************************

//close conn
data::close_conn();

//returns server time
$return_msg = array("t" => time());
die(pb_api_RIJNDAEL_encrypt(json_encode($return_msg), $project->api_secret, $project->cipher));
?>