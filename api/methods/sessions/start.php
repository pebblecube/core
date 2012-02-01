<?php
//start a new app session
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/session_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");
	
//check if project wants encryption
$user_token = isset($_GET['user_token']) ? $_GET['user_token'] : '';
$time_start = isset($_GET['time']) ? $_GET['time'] : time();
$app_version = isset($_GET['version']) ? $_GET['version'] : '';
if($project->cipher != "none")
{
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$user_token = isset($enc_array['user_token']) ? $enc_array['user_token'] : '';
		$time_start = isset($enc_array['time']) ? $enc_array['time'] : time();
		$app_version = isset($enc_array['version']) ? $enc_array['version'] : '';
	}
}

if(!is_numeric($time_start))
	$time_start = time();
	
//create a new session
$session = new session();
$session->client_ip = $_SERVER['REMOTE_ADDR'];
$session->project_id = $project->id;
$session->time_start = (int)$time_start;
$session->version = (string)$app_version;

//if token exists check if exists
if(!empty($user_token))
{
	$access_token = account_manager::check_access_token_project($user_token, $project->id);
	if(!isset($access_token))
		pb_api_error_message(404, "user_token not found");
	
	$session->token_id = $access_token['_id'];
	$session->user_id = $access_token['user_id'];
	$session->user_name = $access_token['user_name'];
	$session->access_token = new token($access_token);
}

//save in db
$res = session_manager::start($session);

if(!isset($res))
{
	data::close_conn();
	pb_api_error_message(403, "your request has been refused");
}

//***************************
//add a log for the apis
$log_obj = new api_log();
$log_obj->project_id = $project->id;
$log_obj->add_object(MongoDBRef::create("sessions", $res));
$log_obj->msg = "session started";
api_logger::add($log_obj);
//***************************

//close db conn
data::close_conn();
//returns session id
$return_session = array("k" => sprintf("%s", $res), "t" => time());
die(pb_api_RIJNDAEL_encrypt(json_encode($return_session), $project->api_secret, $project->cipher));
?>