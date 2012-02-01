<?php
//gets a file saved before
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/account_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/games_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//values
$user_token = isset($_GET['user_token']) ? $_GET['user_token'] : '';
$page_index = isset($_GET['index']) ? $_GET['index'] : 1;
$page_size = isset($_GET['size']) ? $_GET['size'] : 100;

if($project->cipher != "none")
{
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$user_token = isset($enc_array['user_token']) ? $enc_array['user_token'] : '';
		$page_index = isset($enc_array['index']) ? $enc_array['index'] : 1;
		$page_size = isset($enc_array['size']) ? $enc_array['size'] : 100;
	}
}

if(!is_numeric($page_index))
	$page_index = 1;

if((int)$page_index < 1)
	$page_index = 1;
	
if(!is_numeric($page_size))
	$page_size = 1;

if((int)$page_size < 1 || (int)$page_size > 100)
	$page_size = 100;

//check values
if(empty($user_token))
	pb_api_error_message(400, "invalid parameters: user_token");
	
//check user token
$access_token = account_manager::check_access_token_project($user_token, $project->id);
if(!isset($access_token))
	pb_api_error_message(404, "user_token not found");

$results = games_manager::list_by_user_token($user_token, $page_index, $page_size);

if(isset($results["data"]))
{	
	$games_count = $results["count"];
	$games = $results["data"];
	$page_count = 0;
	if($games_count > 0)
		$page_count = ceil($games_count/$page_size);
	
	$response_array = array(
			"pages" => $page_count,
			"items" => $games_count,
			"games" => array()
		);
	
	foreach ($games as $id => $value) {
		$game = new saved_game($value);
		array_push($response_array['games'], array(
				"k" => sprintf("%s", $game->id),
				"name" => $game->filename,
				"url" => $game->url(),
				"time" => $game->time
		));
	}
	
	die(pb_api_RIJNDAEL_encrypt(json_encode($response_array), $project->api_secret, $project->cipher));
}
else
{
	pb_api_error_message(404, "no games found");
}
?>