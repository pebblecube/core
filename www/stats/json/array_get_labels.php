<?php
date_default_timezone_set('GMT');
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/stats_manager.php");

//redirect not logged in
pb_redirect_not_logged("/");

$label = isset($_GET['label']) ? $_GET['label'] : "";

$array_labels = array();
if(!empty($label)) {
	for ($i=0; $i < sizeof($prj_obj->events); $i++) {
		if($prj_obj->events[$i]->typeof == "array" && $prj_obj->events[$i]->code == $label) {
			$array_labels = $prj_obj->events[$i]->options;
		}
	}
}

header('Content-type: application/json');
echo(json_encode($array_labels));
?>