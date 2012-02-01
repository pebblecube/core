<?php
//gets a file saved before
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/games_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//values
$file_id = isset($_GET['k']) ? $_GET['k'] : '';
if($project->cipher != "none")
{
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$file_id = isset($enc_array['k']) ? $enc_array['k'] : '';
	}
}

//check values
if(empty($file_id))
	pb_api_error_message(400, "invalid parameters: k");

//get file data from db
$game = games_manager::get_by_id_and_project($file_id, $project->id);

//close db conn
data::close_conn();

if(!isset($game))
{
	pb_api_error_message(404, "game not found");
}
else
{
	
	$session_key = isset($_POST['session_key']) ? $_POST['session_key'] : null;
	$time_file = isset($_POST['time']) ? $_POST['time'] : time();
	$file_name = isset($_POST['file_name']) ? $_POST['file_name'] : '';
	
	//open db conn
	data::open_conn();
	
	//check api_sig
	$project = project_manager::get_by_api_sig($api_key, $api_sig);
	if(!isset($project))
		pb_api_error_message(404, "application not found");
		
	//check if session exists
	$db_event_session = null;
	if($session_key != null)
	{
		$session = session_manager::get_active_session($session_key, $project->id);
		if(!isset($session))
			pb_api_error_message(404, "session not found");
		
		$db_event_session = $session->id;
	}
	
	$game->time = $time_file;
	//filename
	if(empty($file_name))
		$file_name = stripslashes($_FILES['file']['name']);
	$saved_game->filename = $file_name;
	
	games_manager::save($saved_game);
	$target_path = GLOBAL_WWW_FILE_PATH.GLOBAL_GAMES_FOLDER.DIRECTORY_SEPARATOR.$project->id.DIRECTORY_SEPARATOR.$game->id;
	
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
		$log_obj->add_object(MongoDBRef::create("projects_saved_games", $game->id));
		$log_obj->msg = "game updated";
		api_logger::add($log_obj);
		//***************************
		
		if(S3_ENABLED)
		{
			$s3 = new AmazonS3();
			$file_resource = fopen($target_path, 'r');
			// Upload file to the games bucket
			$game->idponse = $s3->create_object(GLOBAL_GAMES_S3_BUCKET, $project->id."/".$game->id, array(
				'fileUpload' => $file_resource,
				'acl' => AmazonS3::ACL_PUBLIC
			));
			unset($s3);
			//if it's ok delete local file
			if(!$game->idponse->isOK())
			{
				//remove db record
				games_manager::remove($game->id);
				pb_api_error_message(500, "error occured saving file");
			}
			unlink($target_path);
		}
	}
	else
	{
		//open db conn
		data::close_conn();
		pb_api_error_message(500, "error occured saving file");
	}
	
	//close db conn
	data::close_conn();
	
	$return_msg = array("t" => time());
	die(pb_api_RIJNDAEL_encrypt(json_encode($return_msg), $project->api_secret, $project->cipher));
}
?>