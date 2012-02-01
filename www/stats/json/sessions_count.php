<?php
date_default_timezone_set('GMT');
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/stats_manager.php");
//redirect not logged in
pb_redirect_not_logged("/");

$span = isset($_GET['span']) ? $_GET['span'] : 30;
$fromdate = isset($_GET['from']) ? $_GET['from'] : '';
$todate = isset($_GET['to']) ? $_GET['to'] : '';

$offset = isset($_GET['dateoffset']) ? $_GET['dateoffset'] : 0;

data::open_conn();

$date = date_create(date("Y").'-'.date("m") .'-'.date("d"))->add(new DateInterval('P1D'));
$date_check = date_create(date("Y").'-'.date("m") .'-'.date("d"))->sub(new DateInterval('P'.$span.'D'));

//last $span days
$to_date = date_timestamp_get($date);
$from_date = date_timestamp_get($date->sub(new DateInterval('P'.$span.'D')));
if(!empty($fromdate) && !empty($todate)) {
	if(date_parse($todate) && date_parse($fromdate)) {
		$to_date = strtotime($todate);
		$from_date = strtotime($fromdate);
		$span = (($to_date - $from_date)/(60*60*24))+1;
	}
}

$cursor = stats_manager::get_sessions_count($prj_obj->id, $from_date, $to_date);
$sessions = array();
$days = array();
foreach ($cursor as $id => $value) {
	array_push($days, $value["time"]);
	if(sizeof($value["sessions"]) > 0)
		array_push($sessions, array(($value["time"] + ($offset * 60 * 60))*1000, $value["sessions"]["num"])); //, date("c", $value["time"])
	else
		array_push($sessions, array(($value["time"] + ($offset * 60 * 60))*1000, 0)); //, date("c", $value["time"])
}

//fill empty days
if(sizeof($sessions) < $span)
{
	for($i=0; $i <$span; $i++)
	{
		$day = date_timestamp_get($date_check->add(new DateInterval('P1D')));
		//search the days missing
		if(!in_array($day, $days))
			array_push($sessions, array(($day + ($offset * 60 * 60))*1000, 0)); //, date("c", $day)
	}
}
data::close_conn();
header('Content-type: application/json');
echo(json_encode($sessions));
?>