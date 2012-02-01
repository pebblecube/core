<?php
//returns an auth token that associate user to an app
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//acquire values
$user_type = isset($_GET['ut']) ? $_GET['ut'] : 'private';
$auth_token = isset($_GET['auth_token']) ? $_GET['auth_token'] : '';

if($project->cipher != "none")
{
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$user_type = isset($enc_array['ut']) ? $enc_array['ut'] : 'private';
		$auth_token = isset($enc_array['auth_token']) ? $enc_array['auth_token'] : '';
	}
}

//check params
if(empty($auth_token) || empty($api_key) || empty($api_sig))
	pb_api_error_message(400, "invalid parameters: auth_token, api_key, api_sig");

//check user auth_token
if($user_type == 'public')
	$token_project_id = NULL;
else
	$token_project_id = $project->id;

$user = account_manager::exists_auth_token($auth_token, $token_project_id);
if(!isset($user))
	pb_api_error_message(404, "user not found");

//create-return user token
$res = account_manager::create_api_access_token($user["_id"], $user["username"], $project->id, $api_sig);

//***************************
//add a log for the apis
$log_obj = new api_log();
$log_obj->project_id = $project->id;
$log_obj->add_object(MongoDBRef::create("users", $user["_id"]));
$log_obj->msg = "token released";
api_logger::add($log_obj);
//***************************

data::close_conn();

if(!isset($res))
	pb_api_error_message(403, "your request has been refused");

$return_token = array("t" => sprintf("%s", $res["_id"]));
die(pb_api_RIJNDAEL_encrypt(json_encode($return_token), $project->api_secret, $project->cipher));
?>