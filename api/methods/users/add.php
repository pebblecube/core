<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

$user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';
$user_username = isset($_POST['user_username']) ? $_POST['user_username'] : '';
$user_password = isset($_POST['user_password']) ? $_POST['user_password'] : '';
$user_type = isset($_POST['ut']) ? $_POST['ut'] : 'private';
if($project->cipher != "none")
{
	$data = isset($_POST['data']) ? $_POST['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$user_email = isset($enc_array['user_email']) ? $enc_array['user_email'] : '';
		$user_username = isset($enc_array['user_username']) ? $enc_array['user_username'] : '';
		$user_password = isset($enc_array['user_password']) ? $enc_array['user_password'] : '';
		$user_type = isset($enc_array['ut']) ? $enc_array['ut'] : 'private';
	}
}

//check params
if(empty($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL))
	pb_api_error_message(400, "invalid parameter: user_email");

if(empty($user_username))
	pb_api_error_message(400, "invalid parameter: user_username");
	
if(empty($user_password))
	pb_api_error_message(400, "invalid parameter: user_password");
	
//create a new user object
$user = new user();
$user->email = $user_email;
$user->password = md5($user_password);
$user->status = 1;
$user->username = $user_username;
$user->project_id = $project->id;
$user->type = 0;

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
$log_obj->msg = "user added";
api_logger::add($log_obj);
//***************************

data::close_conn();

$return_id = array("id" => sprintf("%s", $user_id));
die(pb_api_RIJNDAEL_encrypt(json_encode($return_id), $project->api_secret, $project->cipher));
?>