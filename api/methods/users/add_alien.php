<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

$user_type = isset($_POST['ut']) ? $_POST['ut'] : 'private';
$alien_auth_service = isset($_POST['alien_auth_service']) ? $_POST['alien_auth_service'] : '';
$alien_id = isset($_POST['alien_id']) ? $_POST['alien_id'] : '';
$alien_token = isset($_POST['alien_token']) ? $_POST['alien_token'] : '';
$alien_secret = isset($_POST['alien_secret']) ? $_POST['alien_secret'] : '';
$user_username = isset($_POST['user_username']) ? $_POST['user_username'] : '';
if($project->cipher != "none")
{
	$data = isset($_POST['data']) ? $_POST['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$user_type = isset($enc_array['ut']) ? $enc_array['ut'] : 'private';
		$alien_auth_service = isset($enc_array['alien_auth_service']) ? $enc_array['alien_auth_service'] : '';
		$alien_id = isset($enc_array['alien_id']) ? $enc_array['alien_id'] : '';
		$alien_token = isset($enc_array['alien_token']) ? $enc_array['alien_token'] : '';
		$alien_secret = isset($enc_array['alien_secret']) ? $enc_array['alien_secret'] : '';
		$user_username = isset($enc_array['user_username']) ? $enc_array['user_username'] : '';
	}
}

if(empty($alien_id) && empty($alien_token) && empty($alien_secret))
	pb_api_error_message(400, "invalid parameters, specify at least one of these: alien_id, alien_token, alien_secret");

//create a new alien user object
$user = new user();
$user->email = $alien_id.$alien_token.$alien_secret."@pebblecube.com";
$user->alien_id = $alien_id;
$user->alien_token = $alien_token;
$user->alien_secret = $alien_secret;
$user->alien_auth_service = $alien_auth_service;
$user->username = $user_username;
$user->status = 1;
$user->type = 0;

if($user_type == "public")
	$user->type = 1;
else
{
	$user->project_id = $project->id;
	$user->type = 0;
}

$user_id = account_manager::create_user($user);
if(is_numeric($user_id))
{
	if($user_id <= 0)
		pb_api_error_message(400, "user already exists");
}
else
{
	if($user_id == NULL)
		pb_api_error_message(500, "error creating user");
}

//***************************
//add a log for the apis
$log_obj = new api_log();
$log_obj->project_id = $project->id;
$log_obj->add_object(MongoDBRef::create("users", $user_id));
$log_obj->msg = "alien added";
api_logger::add($log_obj);
//***************************

$user = account_manager::get_by_id($user_id);
$user->email = sprintf("%s", $user_id);
$user->password = md5($user->email);
account_manager::update_user($user);

data::close_conn();

$return_id = array("id" => sprintf("%s", $user_id), "auth_token" => md5($user_id.md5($user_id)));
die(pb_api_RIJNDAEL_encrypt(json_encode($return_id), $project->api_secret, $project->cipher));
?>