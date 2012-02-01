<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");
//s3 sdk
require_once('AWSSDKforPHP/sdk.class.php');

data::open_conn();

if(strtolower($action) == "add")
{		
	$code = isset($_POST['code']) ? $_POST['code'] : '';
	$title = isset($_POST['title']) ? $_POST['title'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$type = isset($_POST['type']) ? $_POST['type'] : 0;
	
	if(!empty($code) && !empty($title))
	{
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		$achieve = new project_achievement();
		$achieve->code = $code;
		$achieve->title = $title;
		$achieve->description = $description;
		$achieve->type = $type;		
		$achieve->file_name = "";
		//check if there is a file
		if(isset($_FILES['file']) and  $_FILES['file']['tmp_name'] != '')
		{	
			$achieve->file_name = sizeof($prj_obj->achievements).".".pb_get_file_extension($_FILES['file']['name']);
			
			if(!is_dir($achieve->original_folder($prj_id)))
				mkdir($achieve->original_folder($prj_id));
				
			$target_path = $achieve->original_path($prj_id);	
			$file = $_FILES['file']['tmp_name'];
		
			if(move_uploaded_file($file, $target_path))
			{
				//generates 3 sizes
				if(S3_ENABLED)
				{
					//saves on s3
					$s3 = new AmazonS3();
					$file_resource = fopen($target_path, 'r');
					// Upload file to the games bucket
					$response = $s3->create_object(GLOBAL_ACHIEVEMENTS_S3_BUCKET, $prj_id."/".$achieve->file_name, array(
						'fileUpload' => $file_resource,
						'acl' => AmazonS3::ACL_PUBLIC
					));
					unset($s3);
				}
			}
		}
		
		$prj_obj->add_achievement($achieve);
		project_manager::update_project($prj_obj);		
	}
	
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/achievements");
}

if(isset($_REQUEST['removeachievement']))
{
	$index = isset($_REQUEST['removeachievement']) ? $_REQUEST['removeachievement'] : -1;
	
	if($index >= 0)
	{	
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		if(isset($prj_obj->achievements[$index]))
		{
			//update project obj
			$prj_obj->achievements[$index]->status = 0;
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/achievements");
}

data::close_conn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project achievements</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
		$(document).ready(function(){
		    $("#add").validate();
		});
		
		function del_achievement(index)
		{
			if(confirm('are you sure?'))
				top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/achievements?removeachievement=' + escape(index);
		}
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/sub_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Project achievements</h1>
		</div>	
		<div class='clear'>&nbsp;</div>
		<div class="grid_6">
			<h2>Add new achievement</h2>
			<form method="post" id="add" enctype="multipart/form-data">
				<div>
					Code (max 10 chars):
					<br />
					<input value="" class="required" type="text" size="10" maxlength="10" name="code" id="code">
				</div>
				<div>
					Name:
					<br />
					<input value="" class="required" type="text" size="50" name="title" id="title">
				</div>
				<div>
					Description: <br />
					<textarea class="fixed" name="description" id="description" rows="8" cols="60"></textarea>
				</div>
				<div>
					Badge: <br />
					<input type="file" name="file" value="" id="file" />
				</div>
				<div>
					<!-- 
					<br /><input type="checkbox" id="type" name="type" value="1"><label for="type">public board (visible on project page)</label>
					<br /><br />
					-->
					<input class="button" id="action" name="action" type="submit" value="Add">
				</div>
			</form>
		</div>
		<div class="grid_6">
			<h2>Current achievements</h2>
			<?php
			if($prj_obj->achievements)
			{
				echo("<ul>");
				$printed = 0;
				for ($i=0; $i < sizeof($prj_obj->achievements); $i++) 
				{
					if($prj_obj->achievements[$i]->status > 0)
					{
						echo("<li>");
						echo("<a class=\"action delete\" title=\"delete\" href=\"javascript:del_achievement(".$i.")\">d</a>");
						echo("<a class=\"action edit\" title=\"edit\" href=\"/prj/".sprintf("%s", $prj_obj->id)."/achievements/edit/$i\">e</a>");
						echo("<a class=\"action list\" title=\"boards\" href=\"/prj/".sprintf("%s", $prj_obj->id)."/achievements/boards/$i\">b</a>");
						echo(sprintf("%s - %s - <a href=\"%s\" target=\"_blank\">badge</a>", $prj_obj->achievements[$i]->code, $prj_obj->achievements[$i]->title,  $prj_obj->achievements[$i]->url($prj_obj->id))); //, $prj_obj->achievements[$i]->type == 1 ? "public" : "private"
						echo("</li>");
						$printed++;
					}
				}
				
				if($printed == 0)
					echo "<li>no achievements</li>";
					
				echo("</ul>");
			}
			else
			{
				echo "<p>no achievements</p>";
			}
			?>
		</div>
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>