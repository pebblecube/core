<?php
//stops an app session
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/session_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//values
$session_key = isset($_POST['session_key']) ? $_POST['session_key'] : '';
$segment = isset($_POST['segment']) ? @json_decode($_POST['segment'], true) : NULL;
$map_code = isset($_POST['map']) ? $_POST['map'] : '';
$time_start = isset($_POST['start']) ? $_POST['start'] : time();
$time_stop = isset($_POST['stop']) ? $_POST['stop'] : time();
if($project->cipher != "none") {
	$data = isset($_POST['data']) ? $_POST['data'] : '';
	if(!empty($data)) {
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$session_key = isset($enc_array['session_key']) ? $enc_array['session_key'] : '';
		$segment = isset($enc_array['segment']) ? @json_decode($enc_array['segment'], true) : NULL;
		$map_code = isset($enc_array['map']) ? $enc_array['map'] : '';
		$time_start = isset($enc_array['start']) ? $enc_array['start'] : time();
		$time_stop = isset($enc_array['stop']) ? $enc_array['stop'] : time();
	}
}

if(!is_numeric($time_start))
	$time_start = time();
	
if(!is_numeric($time_stop))
	$time_stop = time();

//check values
if(empty($session_key)) {
	pb_api_error_message(400, "invalid parameters: session_key");
}

if(empty($segment)) {
	pb_api_error_message(400, "invalid parameters: segment");
}
		
if(!is_array($segment)) {
	pb_api_error_message(400, "invalid parameters: segment");
}

if(empty($map_code))
	pb_api_error_message(400, "invalid parameters: map");

//check if session exists
$session = session_manager::get_active_session($session_key, $project->id);
if(!isset($session)) {
	pb_api_error_message(404, "session not found");
}

//check if map exists
$map_exists = false;
for ($i=0; $i < sizeof($project->maps); $i++)
{
	if(!$map_exists)
		$map_exists = ($project->maps[$i]->status > 0 && $project->maps[$i]->code == $map_code);
}
if(!$map_exists)
	pb_api_error_message(404, "map not found");

//new segment
$map_segment = new project_map_trackseg();
$map_segment->project_id = new MongoId($project->id);
$map_segment->session_id = new MongoId($session->id);
$map_segment->map_code = $map_code;
$map_segment->time_start = $time_start;
$map_segment->time_stop = $time_stop;
$map_segment->add_points($segment);

//add event
$segment_id = session_manager::track_map_segment($map_segment);

//***************************
//add a log for the apis
$log_obj = new api_log();
$log_obj->project_id = $project->id;
$log_obj->add_object(MongoDBRef::create("project_map_trackseg", $segment_id));
$log_obj->msg = "segment tracked";
api_logger::add($log_obj);
//***************************

//close db conn
data::close_conn();

//returns track id
$return = array("s" => sprintf("%s", $segment_id));
die(pb_api_RIJNDAEL_encrypt(json_encode($return), $project->api_secret, $project->cipher));
?>