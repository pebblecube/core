<?php
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
$time_file = isset($_POST['time']) ? $_POST['time'] : time();
$file_name = isset($_POST['file_name']) ? $_POST['file_name'] : '';

if($project->cipher != "none")
{
	$data = isset($_POST['data']) ? $_POST['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$session_key = isset($enc_array['session_key']) ? $enc_array['session_key'] : null;
		$token_key = isset($enc_array['user_token']) ? $enc_array['user_token'] : null;
		$time_file = isset($enc_array['time']) ? $enc_array['time'] : time();
		$file_name = isset($enc_array['file_name']) ? $enc_array['file_name'] : '';
	}
}

//check values
if(empty($token_key))
	pb_api_error_message(400, "invalid parameters: user_token");

if(isset($_FILES['file']) and  $_FILES['file']['tmp_name'] != '')
{
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
	
	$saved_game = new saved_game();
	$saved_game->project_id = $project->id;
	$saved_game->session_id = $db_event_session;
	$saved_game->token_id = $access_token['_id'];
	$saved_game->user_id = $access_token['user_id'];
	$saved_game->user_name = $access_token['user_name'];
	$saved_game->time = $time_file;
	//filename
	if(empty($file_name))
		$file_name = stripslashes($_FILES['file']['name']);
	$saved_game->filename = $file_name;
	
	$res = games_manager::save($saved_game);
	$target_path = GLOBAL_WWW_FILE_PATH.GLOBAL_GAMES_FOLDER.DIRECTORY_SEPARATOR.$project->id.DIRECTORY_SEPARATOR.$res;
	
	if(!is_dir(GLOBAL_WWW_FILE_PATH.GLOBAL_GAMES_FOLDER.DIRECTORY_SEPARATOR.$project->id))
		mkdir(GLOBAL_WWW_FILE_PATH.GLOBAL_GAMES_FOLDER.DIRECTORY_SEPARATOR.$project->id);
		
	$file = $_FILES['file']['tmp_name'];
	//try to move the file
	if(move_uploaded_file($file, $target_path))
	{
		//***************************
		//add a log for the apis
		$log_obj = new api_log();
		$log_obj->project_id = $project->id;
		$log_obj->add_object(MongoDBRef::create("projects_saved_games", $res));
		$log_obj->msg = "game saved";
		api_logger::add($log_obj);
		//***************************
		
		if(S3_ENABLED)
		{
			//s3 sdk
			require_once('AWSSDKforPHP/sdk.class.php');

			$s3 = new AmazonS3();
			$file_resource = fopen($target_path, 'r');
			// Upload file to the games bucket
			$response = $s3->create_object(GLOBAL_GAMES_S3_BUCKET, $project->id."/".$res, array(
				'fileUpload' => $file_resource,
				'acl' => AmazonS3::ACL_PUBLIC
			));
			unset($s3);
			//if it's ok delete local file
			if(!$response->isOK())
			{
				//remove db record
				games_manager::remove($res);
				pb_api_error_message(500, "error occured saving file");
			}
			fclose($file_resource);
			unlink($target_path);
		}
		
		//close db conn
		data::close_conn();
		
		//returns session id
		$return_game = array("k" => sprintf("%s", $res));
		die(pb_api_RIJNDAEL_encrypt(json_encode($return_game), $project->api_secret, $project->cipher));
	}
	else
	{
		//open db conn
		data::close_conn();
		pb_api_error_message(500, "error occured saving file");
	}
}
else
{
	pb_api_error_message(400, "invalid parameters: file");
}
?>