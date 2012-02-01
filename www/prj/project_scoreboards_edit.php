<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");

$index = isset($_GET['index']) ? $_GET['index'] : -1;
$board = null;

if($index >= 0)
{
	if(is_array($prj_obj->scoreboards))
		if($index < sizeof($prj_obj->scoreboards))
			$board = $prj_obj->scoreboards[$index];
}

if($board == null)
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
		if($prj_obj->scoreboards[$index]->code == $code)
		{
			$prj_obj->scoreboards[$index]->title = $title;
			$prj_obj->scoreboards[$index]->description = $description;
			$prj_obj->scoreboards[$index]->type = $type;
			project_manager::update_project($prj_obj);
		}		
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/scoreboards");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project scoreboards</title>
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
			<h1>Project scoreboards</h1>
			<form method="post" id="update">
				<div>
					Board code (max 10 chars):
					<br />
					<input readonly value="<?php echo(htmlspecialchars($board->code)); ?>" class="required" type="text" size="10" maxlength="10" name="code" id="code">
				</div>
				<div>
					Board name:
					<br />
					<input value="<?php echo(htmlspecialchars($board->title)); ?>" class="required" type="text" size="50" name="title" id="title">
				</div>
				<div>
					Board description: <br />
					<textarea name="description" id="description" rows="8" cols="60"><?php echo(htmlspecialchars($board->description)); ?></textarea>
				</div>
				<div>
					<!-- 
					<input type="checkbox" id="type" name="type" value="1"<?php echo($board->type == 1 ? " checked" : ""); ?>><label for="type">public board (visible on project page)</label>
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