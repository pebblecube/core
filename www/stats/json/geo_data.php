<?php
date_default_timezone_set('GMT');
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/stats_manager.php");
//redirect not logged in
pb_redirect_not_logged("/");

$fromdate = isset($_GET['from']) ? $_GET['from'] : 0;
$todate = isset($_GET['to']) ? $_GET['to'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : "events";
$offset = isset($_GET['dateoffset']) ? $_GET['dateoffset'] : 0;

if(empty($type))
	die("invalid request");
	
//last $span days
if(date_parse($todate))
	$to_date = strtotime($todate);
	
if(date_parse($fromdate))
	$from_date = strtotime($fromdate);

data::open_conn();

switch ($type) {
	case 'events':
		$cursor = stats_manager::get_geo_events_stats($prj_obj->id, $from_date, $to_date);
		break;
	case 'sessions':
		$cursor = stats_manager::get_geo_sessions_stats($prj_obj->id, $from_date, $to_date);
		break;
}

$countries_arr = array();
foreach ($cursor as $id => $value) 
{
	$countries = $value[$type];
	foreach ($countries as $key => $value) {
		$country_code = strtolower($value["country_code"]);
		if($country_code != "null")
		{
			if (array_key_exists($country_code, $countries_arr)) {
				$countries_arr[$country_code]["count"] += $value["count"];
			}
			else {
				$countries_arr[$country_code] = array("count" => $value["count"], "country_name" => $value["country_name"]);
			}
		}
	}
}

data::close_conn();

header('Content-type: application/json');
echo(json_encode($countries_arr));
?>