<?php
//stops an app session
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/session_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");	
	
//check if project wants encryption
$session_key = isset($_GET['session_key']) ? $_GET['session_key'] : '';
$time_stop = isset($_GET['time']) ? $_GET['time'] : time();
if($project->cipher != "none")
{
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$session_key = isset($enc_array['session_key']) ? $enc_array['session_key'] : '';
		$time_stop = isset($enc_array['time']) ? $enc_array['time'] : time();
	}
}	

if(empty($session_key))
	pb_api_error_message(400, "invalid parameters: session_key");


//check if session exists
$session = session_manager::get_active_session($session_key, $project->id);
if(!isset($session))
		pb_api_error_message(404, "session not found");

//update session
$time_stop = session_manager::stop($session->id, $time_stop);

//***************************
//add a log for the apis
$log_obj = new api_log();
$log_obj->project_id = $project->id;
$log_obj->add_object(MongoDBRef::create("sessions", $session->id));
$log_obj->msg = "session stopped";
api_logger::add($log_obj);
//***************************

//close db conn
data::close_conn();

//returns session id
$return_delta = array("t" => sprintf("%s", ($time_stop - $session->time_start)));
die(pb_api_RIJNDAEL_encrypt(json_encode($return_delta), $project->api_secret, $project->cipher));
?>