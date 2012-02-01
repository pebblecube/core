<?php
//gets a file saved before
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/session_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/games_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//values
$session_key = isset($_POST['session_key']) ? $_POST['session_key'] : null;
$token_key = isset($_POST['user_token']) ? $_POST['user_token'] : null;
$board = isset($_POST['board']) ? $_POST['board'] : '';
$value = isset($_POST['value']) ? $_POST['value'] : '';
$time_score = isset($_POST['time']) ? $_POST['time'] : time();

if($project->cipher != "none")
{
	$data = isset($_POST['data']) ? $_POST['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$session_key = isset($enc_array['session_key']) ? $enc_array['session_key'] : null;
		$token_key = isset($enc_array['user_token']) ? $enc_array['user_token'] : null;
		$board = isset($enc_array['board']) ? $enc_array['board'] : '';
		$value = isset($enc_array['value']) ? $enc_array['value'] : '';
		$time_score = isset($enc_array['time']) ? $enc_array['time'] : time();
	}
}

//check values
if(empty($token_key))
	pb_api_error_message(400, "invalid parameters: user_token");
	
if(empty($board))
	pb_api_error_message(400, "invalid parameters: board");
	
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

//check if board exists
$board_exists = false;
for ($i=0; $i < sizeof($project->scoreboards); $i++)
{
	if(!$board_exists)
		$board_exists = ($project->scoreboards[$i]->status > 0 && $project->scoreboards[$i]->code == $board);
}
if(!$board_exists)
	pb_api_error_message(404, "scoreboard not found");

$score = new score();
$score->project_id = $project->id;
$score->session_id = $db_event_session;
$score->token_id = $access_token['_id'];
$score->user_id = $access_token['user_id'];
$score->user_name = $access_token['user_name'];
$score->time = $time_score;
$score->scoreboard_code = $board;
$score->value = $value;

$res = games_manager::save_score($score);

//***************************
//add a log for the apis
$log_obj = new api_log();
$log_obj->project_id = $project->id;
$log_obj->add_object(MongoDBRef::create("projects_score", $res));
$log_obj->msg = "score saved";
api_logger::add($log_obj);
//***************************

//returns server time
$return_msg = array("t" => time());
die(pb_api_RIJNDAEL_encrypt(json_encode($return_msg), $project->api_secret, $project->cipher));
?>