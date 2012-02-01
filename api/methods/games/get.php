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
	$response_array = array(
			"k" => sprintf("%s", $game->id),
			"name" => $game->filename,
			"url" => $game->url(),
			"time" => $game->time
		);
	
	die(pb_api_RIJNDAEL_encrypt(json_encode($response_array), $project->api_secret, $project->cipher));
}
?>