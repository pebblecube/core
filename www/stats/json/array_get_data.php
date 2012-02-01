<?php
date_default_timezone_set('GMT');
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/stats_manager.php");
//redirect not logged in
pb_redirect_not_logged("/");

$span = isset($_GET['span']) ? $_GET['span'] : 30;
$offset = isset($_GET['dateoffset']) ? $_GET['dateoffset'] : 0;
$options = isset($_GET['options']) ? $_GET['options'] : "";
$event = isset($_GET['event']) ? $_GET['event'] : "";

//last $span days
$date = date_create(date("Y").'-'.date("m") .'-'.date("d"))->add(new DateInterval('P1D'));
$date_check = date_create(date("Y").'-'.date("m") .'-'.date("d"))->sub(new DateInterval('P'.$span.'D'));
$to_date = date_timestamp_get($date);
$from_date = date_timestamp_get($date->sub(new DateInterval('P'.$span.'D')));

//get event details
$event_obj = null;
for($k = 0; $k < sizeof($prj_obj->events); $k++)
{
	if($prj_obj->events[$k]->code == $event)
	{
		$event_obj = $prj_obj->events[$k];
	}
}

$matrix = array();

if($event_obj != null)
{
	data::open_conn();

	//for each label check if numeric or value
	$labels = explode(",", $options);
	
	//create an array for each label
	for ($i=0; $i < sizeof($labels); $i++) {
		$matrix[$labels[$i]] = array("name" => $labels[$i], "typeof" => null, "values" => null);
	}
	
	//get the datatype
	for ($i=0; $i < sizeof($event_obj->options); $i++) { 
		if(in_array($event_obj->options[$i]["label"], $labels))
		{
			$matrix[$event_obj->options[$i]["label"]]["typeof"] = $event_obj->options[$i]["typeof"];
		}
	}
	
	//get all the array data
	$cursor = stats_manager::get_array_events_stats($event_obj->code, $prj_obj->id, $from_date, $to_date);
	foreach ($cursor as $id => $value) {
		$time = $value["time"];
		//loop for each value
		for($k =0; $k < sizeof($value["events"]); $k++)
		{
			//parsing only the event selected
			if($value["events"][$k]["code"] == $event_obj->code)
			{
				//looking only for the label selected
				for ($i=0; $i < sizeof($value["events"][$k]["values"]); $i++) {
					$label_value_obj = $value["events"][$k]["values"][$i];
					if(in_array($label_value_obj["option"], $labels))
					{
						$matrix[$label_value_obj["option"]]["values"][] = array("count" => $label_value_obj["count"], "value" => isset($label_value_obj["value"]) ? $label_value_obj["value"] : null, "time" => $time);
					}
				}
			}
		}
	}
	data::close_conn();
}

$res = array();
$days = array();
//now that I have the matrix i'll create the sequences fro the graphs
$point_interval = 24 * 3600 * 1000;
foreach ($matrix as $key => $item) {
	$typeof = $item["typeof"];
	$values = $item["values"];
	if($values != null)
	{
		$cur_res_pos = sizeof($res);
		if($typeof == "numeric")
		{
			$res[] = array("name" => $item["name"], "data" => array(), "pointInterval" => $point_interval, "type" => "line");
			foreach ($values as $key => $value) {
				$res[$cur_res_pos]["data"][] = array(($value["time"] + ($offset * 60 * 60))*1000, $value["count"]);
				$res[$cur_res_pos]["key"] = $item["name"];
				$days[$item["name"]][] = $value["time"];
			}
		}
		else
		{
			$res_tmp = array();
			foreach ($values as $key => $value) {
				$res_tmp[$value["value"]][] = array(($value["time"] + ($offset * 60 * 60))*1000, $value["count"]);
				$days[$item["name"].$value["value"]][] = $value["time"];
			}
		
			foreach ($res_tmp as $key => $value) {
				$res[] = array(
							"stack" => $item["name"], 
							"key" => $item["name"].$key, 
							"data" => $value, 
							"pointInterval" => $point_interval,
							"type" => "column",
							"name" => $item["name"] . " - " . $key
							);
			}
		}
	}
}

//filling missing days
$date_check = date_create(date("Y").'-'.date("m") .'-'.date("d"))->sub(new DateInterval('P'.$span.'D'));
for($i=0; $i < $span; $i++)
{
	$day = date_timestamp_get($date_check->add(new DateInterval('P1D')));
	for ($k=0; $k < sizeof($res); $k++) 
	{ 
		$stack = $res[$k]["key"];
		//search the days missing
		if(!in_array($day, $days[$stack]))
			$res[$k]["data"][] = array(($day + ($offset * 60 * 60))*1000, 0);
	}
}

//header('Content-type: application/json');
echo(json_encode($res));
?>