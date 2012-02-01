<?php
//stops an app session
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/session_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");

//values
$session_key = isset($_REQUEST['session_key']) ? $_REQUEST['session_key'] : '';
$events = isset($_REQUEST['events']) ? @json_decode($_REQUEST['events'], true) : NULL;
if($project->cipher != "none")
{
	$data = isset($_REQUEST['data']) ? $_REQUEST['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$session_key = isset($enc_array['session_key']) ? $enc_array['session_key'] : '';
		$events = isset($enc_array['events']) ? @json_decode($enc_array['events'], true) : NULL;
	}
}

//check values	
if(empty($session_key)) {
	pb_api_error_message(400, "invalid parameters: session_key");
}

if(empty($events)) {
	pb_api_error_message(400, "invalid parameters: events");
}
		
if(!is_array($events)) {
	pb_api_error_message(400, "invalid parameters: events");
}

//check if session exists
$session = session_manager::get_active_session($session_key, $project->id);
if(!isset($session)) {
	pb_api_error_message(404, "session not found");
}

//ref to session
$db_event_session = new MongoId($session->id);
$db_event_user_id = $session->user_id;
$db_event_user_name = $session->user_name;
//ref to project
$db_event_project = new MongoId($project->id);

//I'll check every event
for ($i=0; $i < sizeof($events); $i++) { 
	$sent_event = $events[$i];
	$db_event_value = NULL;
	$db_event_time = array_key_exists('time', $sent_event) ? new MongoInt32($sent_event['time']) : new MongoInt32(time());
	
	$db_event_type = "";
	//check if is a default event
	switch ($sent_event['code']) {
		case 'GT':
		case 'gt':
			$sent_event['code'] = strtolower($sent_event['code']);
			$db_event_type = "integer";
			$db_event_value = new MongoInt32($sent_event['value']);
			break;
		default: //custom event
			$db_event_value = $sent_event['value'];
			for($k = 0; $k < sizeof($project->events); $k++)
			{
				if($project->events[$k]->code == $sent_event['code'])
				{
					$db_event_type = $project->events[$k]->typeof;
					break;
				}
			}
			break;
	}
	
	if(!empty($db_event_type))
	{
		$session_event = new session_event();
		
		$session_event->project_id = $db_event_project;
		$session_event->session_id = $db_event_session;
		$session_event->user_id = $db_event_user_id;
		$session_event->user_name = $db_event_user_name;
		
		$session_event->code = $sent_event['code'];
		$session_event->value = $db_event_value;
		$session_event->time = $db_event_time;
		$session_event->client_ip = $_SERVER['REMOTE_ADDR'];
		$session_event->datatype = $db_event_type;
		//add event
		$event_id = session_manager::send_event($session_event);
		
		//***************************
		//add a log for the apis
		$log_obj = new api_log();
		$log_obj->project_id = $project->id;
		$log_obj->add_object(MongoDBRef::create("sessions_events", $event_id));
		$log_obj->msg = "event sent";
		api_logger::add($log_obj);
		//***************************
	}
}

//close db conn
data::close_conn();

//returns session id
$return_delta = array("t" => sprintf("%s", time()));
die(pb_api_RIJNDAEL_encrypt(json_encode($return_delta), $project->api_secret, $project->cipher));
?>