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
$board = isset($_GET['board']) ? $_GET['board'] : '';
$page_index = isset($_GET['index']) ? $_GET['index'] : 1;
$page_size = isset($_GET['size']) ? $_GET['size'] : 100;
$from = isset($_GET['from']) ? $_GET['from'] : 0;
$to = isset($_GET['to']) ? $_GET['to'] : 0;
$user_token = isset($_GET['user_token']) ? $_GET['user_token'] : NULL;

if($project->cipher != "none")
{
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$board = isset($enc_array['board']) ? $enc_array['board'] : '';
		$page_index = isset($enc_array['index']) ? $enc_array['index'] : 1;
		$page_size = isset($enc_array['size']) ? $enc_array['size'] : 100;
		$from = isset($enc_array['from']) ? $enc_array['from'] : 0;
		$to = isset($enc_array['to']) ? $enc_array['to'] : 0;
		$user_token = isset($enc_array['user_token']) ? $enc_array['user_token'] : NULL;
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
if(empty($board))
	pb_api_error_message(400, "invalid parameters: board");
	
//check if board exists
$board_exists = false;
for ($i=0; $i < sizeof($project->scoreboards); $i++) 
{
	$board_exists = ($project->scoreboards[$i]->status > 0 && $project->scoreboards[$i]->code == $board);
}
if(!$board_exists)
	pb_api_error_message(404, "scoreboard not found");
	
if($user_token != NULL) {
	$access_token = account_manager::check_access_token_project($user_token, $project->id);
	if(!isset($access_token))
		pb_api_error_message(404, "user_token not found");

	$user_token = $access_token['user_id'];
}
			
$results = games_manager::get_board($project->id, $board, $from, $to, $page_index, $page_size, -1, $user_token);
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
		"scores" => array()
	);
		
	foreach ($games as $id => $value) {
		$score = new score($value);
		$response_array['scores'][] = array(
				"username" => $score->user_name,
				"userid" => sprintf("%s", $score->user_id),
				"value" => $score->value,
				"time" => $score->time
		);
	}
	
	die(pb_api_RIJNDAEL_encrypt(json_encode($response_array), $project->api_secret, $project->cipher));
}
else
{
	pb_api_error_message(404, "no scores found");
}
?>