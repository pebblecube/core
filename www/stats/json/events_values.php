<?php
date_default_timezone_set('GMT');
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/stats_manager.php");
//redirect not logged in
pb_redirect_not_logged("/");

$span = isset($_GET['span']) ? $_GET['span'] : 30;
$offset = isset($_GET['dateoffset']) ? $_GET['dateoffset'] : 0;

$event_code = isset($_GET['code']) ? $_GET['code'] : "";

if(empty($event_code))
	die("invalid request");

data::open_conn();

//last $span days
$date = date_create(date("Y").'-'.date("m") .'-'.date("d"));
$to_date = date_timestamp_get($date);
$from_date = date_timestamp_get($date->sub(new DateInterval('P'.$span.'D')));

$cursor = stats_manager::get_events_stats($event_code, $prj_obj->id, $from_date, $to_date);
$events = array();
$days = array();
foreach ($cursor as $id => $value) {
	for($k =0; $k < sizeof($value["events"]); $k++)
	{
		if($value["events"][$k]["code"] == $event_code)
		{
			for($j =0; $j < sizeof($value["events"][$k]["values"]); $j++)
			{
				$val = $value["events"][$k]["values"][$j]["value"];
				$cnt = $value["events"][$k]["values"][$j]["count"];
				
				if(!array_key_exists($val, $events))
					$events[$val] = array();
				
				if(!array_key_exists($val, $days))
					$days[$val] = array();
					
				array_push($events[$val], array(($value["time"] + ($offset * 60 * 60))*1000, $cnt));
				array_push($days[$val], $value["time"]);
			}
		}
	}
}

$keys = array_keys($events);
for ($m=0; $m < sizeof($keys) ; $m++) 
{
	$val = $keys[$m];
	if(sizeof($events[$val]) < $span)
	{
		$date_check = date_create(date("Y").'-'.date("m") .'-'.date("d"))->sub(new DateInterval('P'.$span.'D'));
		for($i=0; $i < $span; $i++)
		{
			$day = date_timestamp_get($date_check->add(new DateInterval('P1D')));
			//search the days missing
			if(!in_array($day, $days[$val]))
				array_push($events[$val], array(($day + ($offset * 60 * 60))*1000, 0));
		}
	}
}

data::close_conn();

header('Content-type: application/json');
echo(json_encode($events));
?>