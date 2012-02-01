<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/achievements_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//values
$token_key = isset($_POST['user_token']) ? $_POST['user_token'] : null;
$achievement_code = isset($_POST['code']) ? $_POST['code'] : '';

if($project->cipher != "none")
{
	$data = isset($_POST['data']) ? $_POST['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$token_key = isset($enc_array['user_token']) ? $enc_array['user_token'] : null;
		$achievement_code = isset($enc_array['code']) ? $enc_array['code'] : '';
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
	
//revoke achievement
achievements_manager::revoke_achievement($project->id, $access_token['user_id'], $achievement_code);

//***************************
//add a log for the apis
$log_obj = new api_log();
$log_obj->project_id = $project->id;
$log_obj->msg = sprintf("revoke - achievement %s - user %s", $achievement_code, $access_token['user_id']);
api_logger::add($log_obj);
//***************************

//returns server time
$return_msg = array("t" => time());
die(pb_api_RIJNDAEL_encrypt(json_encode($return_msg), $project->api_secret, $project->cipher));
?>