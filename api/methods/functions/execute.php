<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/evalmath.class.php");

$result = NULL;
$code = isset($_GET['code']) ? $_GET['code'] : NULL;
$vars = isset($_GET['vars']) ? @json_decode($_GET['vars'], true) : NULL;

if($project->cipher != "none")
{
	$data = isset($_REQUEST['data']) ? $_REQUEST['data'] : '';
	if(!empty($data))
	{
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$code = isset($enc_array['code']) ? $enc_array['code'] : '';
		$vars = isset($enc_array['vars']) ? @json_decode($enc_array['vars'], true) : NULL;
	}
}

data::close_conn();

//check values	
if(empty($code))
	pb_api_error_message(400, "invalid parameters: code");
	
//get function script
$func = null;
if(is_array($project->functions)) {
	for ($i=0; $i < sizeof($project->functions); $i++) {
		if($project->functions[$i]->code == $code) {
			$func = $project->functions[$i];
			break;
		}
	}
}

if(empty($func))
	pb_api_error_message(404, "function not found");

$script = $project->functions[$i]->script;

//replace variables
if(is_array($vars)) {
	foreach($vars as $key => $value){
		//only numeric
		if(is_numeric($value)) {
			$script = str_replace("[var:".$key."]", $value, $script);
		}
	}
}
//constants
for ($i=0; $i < sizeof($project->constants); $i++) {
	$script = str_replace("[const:".$project->constants[$i]->code."]", $project->constants[$i]->value, $script);	
}
//grouping functions
for ($i=0; $i < sizeof($func->groups); $i++) {
	$script = str_replace(sprintf("[%s]", $func->groups[$i]["formula"]) , $func->groups[$i]["value"], $script);
}

//evaluate script
$m = new EvalMath();
try {
	$result = $m->evaluate($script);
} catch (Exception $e) {
	pb_api_error_message(500, "function execution threw an exception");
}

if(is_numeric($result)) {
	die(pb_api_RIJNDAEL_encrypt(json_encode(array("result" => $result)), $project->api_secret, $project->cipher));
}

pb_api_error_message(500, "function execution threw an exception");
?>