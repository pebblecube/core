<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

$auth_token = isset($_GET['auth_token']) ? $_GET['auth_token'] : '';
$alt_user = isset($_GET['id']) ? $_GET['id'] : NULL;

if($project->cipher != "none")
{
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$auth_token = isset($enc_array['auth_token']) ? $enc_array['auth_token'] : '';
		$alt_user = isset($enc_array['id']) ? $enc_array['id'] : NULL;
	}
}

if(empty($auth_token))
	pb_api_error_message(400, "invalid parameters: auth_token");

$user = account_manager::exists_auth_token($auth_token, $project->id);
if(!isset($user))
	pb_api_error_message(404, "user not found");
	
if($alt_user != NULL)
{
	$user = account_manager::get_by_id($alt_user);
	if(!isset($user))
		pb_api_error_message(404, "user not found");
	
	unset($user['email']);
}

data::close_conn();

$user["id"] = sprintf("%s", $user['_id']);
unset($user['password']);
unset($user['status']);
unset($user['type']);
unset($user['project_id']);
unset($user['tokens']);
unset($user['_id']);

die(pb_api_RIJNDAEL_encrypt(json_encode($user), $project->api_secret, $project->cipher));
?>