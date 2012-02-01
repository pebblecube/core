<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");
//s3 sdk
require_once('AWSSDKforPHP/sdk.class.php');

$index = isset($_GET['index']) ? $_GET['index'] : -1;
$achieve = null;

if($index >= 0)
{
	if(is_array($prj_obj->achievements))
		if($index < sizeof($prj_obj->achievements))
			$achieve = $prj_obj->achievements[$index];
}

if($achieve == null)
	header("location: /");
	
if(strtolower($action) == "update")
{
	$code = isset($_POST['code']) ? $_POST['code'] : '';
	$title = isset($_POST['title']) ? $_POST['title'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$type = isset($_POST['type']) ? $_POST['type'] : 0;
	
	if(!empty($code) && !empty($title))
	{
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		if($prj_obj->achievements[$index]->code == $code)
		{
			$prj_obj->achievements[$index]->title = $title;
			$prj_obj->achievements[$index]->description = $description;
			$prj_obj->achievements[$index]->type = $type;
			//check if there is a file
			if(isset($_FILES['file']) and  $_FILES['file']['tmp_name'] != '')
			{	
				$achieve->file_name = $index.".".pb_get_file_extension($_FILES['file']['name']);
				
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
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/achievements");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project achievements</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
		$(document).ready(function(){
		    $("#update").validate();
		});
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/sub_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Project achievements</h1>
			<form method="post" id="update" enctype="multipart/form-data">
				<div>
					Code (max 10 chars):
					<br />
					<input readonly value="<?php echo(htmlspecialchars($achieve->code)); ?>" class="required" type="text" size="10" maxlength="10" name="code" id="code">
				</div>
				<div>
					Name:
					<br />
					<input value="<?php echo(htmlspecialchars($achieve->title)); ?>" class="required" type="text" size="50" name="title" id="title">
				</div>
				<div>
					Description: <br />
					<textarea name="description" id="description" rows="8" cols="60"><?php echo(htmlspecialchars($achieve->description)); ?></textarea>
				</div>
				<div>
					Badge: <br />
					<input type="file" name="file" value="" id="file" />
					<?php 
					if(!empty($achieve->file_name)){
					?>
					<a href="<?php echo $achieve->url($prj_id); ?>" target="_blank">current badge</a>
					<?php 
					}
					?>
				</div>
				<div>
					<!--
					<br />
					<input type="checkbox" id="type" name="type" value="1"<?php echo($achieve->type == 1 ? " checked" : ""); ?>><label for="type">public board (visible on project page)</label>
					<br /><br />
					-->
					<input class="button" id="cancel" name="cancel" type="button" value="Cancel" onclick="location.href = '../'">
					<input class="button" id="action" name="action" type="submit" value="Update">
				</div>
			</form>
		</div>
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>