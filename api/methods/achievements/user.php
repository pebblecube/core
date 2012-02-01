<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/achievements_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//values
$token_key = isset($_GET['user_token']) ? $_GET['user_token'] : null;

if($project->cipher != "none")
{
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$token_key = isset($enc_array['user_token']) ? $enc_array['user_token'] : null;
	}
}

//check values
if(empty($token_key))
	pb_api_error_message(400, "invalid parameters: user_token");
	
//check user token
$access_token = account_manager::check_access_token_project($token_key, $project->id);
if(!isset($access_token))
	pb_api_error_message(404, "user_token not found");
	
//get all achievements by token
$user_achievements = achievements_manager::get_achievement_by_user_id($project->id, $access_token["user_id"]);

$result = array();
foreach($user_achievements as $id => $value) {
	$result[] = array(
		"code" => $value['achievement_code'],
		"time" => $value['time']
	);
}

//close conn
data::close_conn();

//return achievement obj
die(pb_api_RIJNDAEL_encrypt(json_encode($result), $project->api_secret, $project->cipher));
?>