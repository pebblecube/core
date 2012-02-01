<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/developer_manager.php");
//redirect not logged in
pb_redirect_not_logged("/prj/dashboard");

$prj_id = isset($_REQUEST['_id']) ? $_REQUEST['_id'] : '';
if(empty($prj_id))
	header("location: /devs/dashboard");
	
$action = isset($_POST['action']) ? $_POST['action'] : '';

data::open_conn();

//get object
$prj_obj = project_manager::get_by_id($prj_id);

//check if exists
if($prj_obj == null)
	header("location: /devs/dashboard");
	
//check id deleted
if($prj_obj->status != 1)
	header("location: /devs/dashboard");
	
//current user obj
$current_user_obj = developer_manager::get_by_id(pb_dev_id());
//check if user has access to current project
$needle = array('project_id' => $prj_obj->id);
$key = pb_array_in_array_search($needle, $current_user_obj->projects);
if(!is_numeric($key))
{
	//not exists
	header("location: /devs/dashboard");
}
else
{	
	$current_user_is_admin = $current_user_obj->projects[$key]['level'] == 1;
}

data::close_conn();
?>