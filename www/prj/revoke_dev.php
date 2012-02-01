<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");

$prj_id = isset($_GET['_id']) ? $_GET['_id'] : '';
$user_id = isset($_GET['u']) ? $_GET['u'] : '';

if(!empty($prj_id) && !empty($user_id))
{
	data::open_conn();

	//only and admin of the project can revoke perms
	if($current_user_is_admin)
	{
		//remove user token
		project_manager::revoke_dev_access($prj_obj->id, $user_id);
	}

	data::close_conn();
}

header("location: /prj/".$prj_id, true);
?>