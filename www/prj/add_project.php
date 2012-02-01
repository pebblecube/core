<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/developer_manager.php");
//redirect not logged in
pb_redirect_not_logged("/prj/add");

$name = isset($_POST['name']) ? $_POST['name'] : '';
$url = isset($_POST['url']) ? $_POST['url'] : '';

if(!empty($url) && !empty($name))
{
	//check url
	if (!preg_match("/^([a-zA-Z0-9\-])+/", $url))
	{
		$err_message = "invalid url";
	}
	else
	{
		data::open_conn();
		
		//get current dev
		$user_id = pb_dev_id();
		$dev_obj = developer_manager::get_by_id($user_id, FALSE); //no cache
		
		if($dev_obj)
		{
			//set object values
			$prj = new project();
			$prj->name = $name;
			$prj->url = $url;
			$prj->api_key = project_manager::gen_random_key();
			$prj->api_secret = project_manager::gen_random_key();
			$prj->owner = $dev_obj->id;
			$prj->status = 1;
			$prj->add_developer($dev_obj->id);
			//add project
			$res = project_manager::add($prj);
			data::close_conn();
			//check result
			if(is_object($res))
			{
				//associate project to the user object
				$dev_obj->add_project($res, 1);
				//update user
				developer_manager::update_developer($dev_obj);
				
				//redirect to prj page
				header("location: /prj/".sprintf("%s", $res), true);
			}
			else
			{
				switch($res)
				{
					case 0:
						$err_message = "url already exists";
						break;
					default:
						$err_message = "error in insert";
						break;
				}
			}
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - New project</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
	
		jQuery.validator.addMethod("prjurl", function(value, element) { 
		  return this.optional(element) || /^[a-zA-Z0-9\-]/.test(value); 
		}, "Please specify the correct url for your project");
	
		$(document).ready(function(){
		    $("#add").validate();
		});
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>New project</h1>
			<?php
			if(isset($err_message))
				echo("<h2 class=\"error\">$err_message</h2>")
			?>
			<form method="post" id="add">
				<div>Project name:<br />
				<input class="required fixed" type="text" size="60" name="name" id="name"></div>
				<div>Project url: 
					<br />
				<input class="required prjurl fixed" type="text" size="60" name="url" id="url"></div>
				<div><input class="button" type="submit" value="Submit"></div>
			</form>
		</div>
	</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>