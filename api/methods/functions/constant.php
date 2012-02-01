<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/util/api_logger.php");

//check api_key and api_sig
include("../inc/top_standard_check.inc.php");
data::close_conn();

$code = isset($_GET['code']) ? $_GET['code'] : NULL;

if($project->cipher != "none") {
	$data = isset($_GET['data']) ? $_GET['data'] : '';
	if(!empty($data)) {
		parse_str(pb_api_RIJNDAEL_decrypt($data, $project->api_secret, $project->cipher), $enc_array);
		$code = isset($enc_array['code']) ? $enc_array['code'] : NULL;
	}
}

//check values
if(empty($code))
	pb_api_error_message(400, "invalid parameters: code");

$constant = NULL;

if(!empty($code)) {
	for ($i=0; $i < sizeof($project->constants); $i++) { 
		if($project->constants[$i]->code == $code) {
			$constant = $project->constants[$i]->toArray();
			unset($constant["description"]);
			break;
		}
	}
}
die(pb_api_RIJNDAEL_encrypt(json_encode($constant), $project->api_secret, $project->cipher));
?>