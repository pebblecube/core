<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/achievements_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//values
$achievement_code = isset($_GET['code']) ? $_GET['code'] : '';
$page_index = isset($_GET['index']) ? $_GET['index'] : 1;
$page_size = isset($_GET['size']) ? $_GET['size'] : 100;
$from = isset($_GET['from']) ? $_GET['from'] : 0;
$to = isset($_GET['to']) ? $_GET['to'] : 0;

if($project->cipher != "none")
{
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$achievement_code = isset($enc_array['code']) ? $enc_array['code'] : '';
		$page_index = isset($enc_array['index']) ? $enc_array['index'] : 1;
		$page_size = isset($enc_array['size']) ? $enc_array['size'] : 100;
		$from = isset($enc_array['from']) ? $enc_array['from'] : 0;
		$to = isset($enc_array['to']) ? $enc_array['to'] : 0;
	}
}

//check values
if(empty($achievement_code))
	pb_api_error_message(400, "invalid parameters: code");
	
$achieve = null;
//get achievement details
if(is_array($project->achievements))
{
	for ($i=0; $i < sizeof($project->achievements); $i++) { 
		if($achievement_code == $project->achievements[$i]->code) {
			$achieve = $project->achievements[$i];
		}
	}
}

if($achieve != null)
{
	//get list of users granted
	$results = achievements_manager::get_achievement_board($project->id, $achievement_code, $from, $to, $page_index, $page_size);
	if(isset($results["data"]))
	{
		$achievements_count = $results["count"];
		$achievements = $results["data"];
		$page_count = 0;
		if($achievements_count > 0)
			$page_count = ceil($achievements_count/$page_size);
		
		$response_array = array(
				"pages" => $page_count,
				"items" => $achievements_count,
				"achievements" => array()
			);
		
		foreach ($achievements as $id => $value) {
			$achievement = new achievement($value);
			$response_array['achievements'][] = array(
				"username" => $achievement->user_name,
				"userid" => sprintf("%s", $achievement->user_id),
				"time" => $achievement->time
			);
		}
		die(pb_api_RIJNDAEL_encrypt(json_encode($response_array), $project->api_secret, $project->cipher));
	}
}
else
{
	//close conn
	data::close_conn();
	
	pb_api_error_message(404, "achievement not found");	
}
?>