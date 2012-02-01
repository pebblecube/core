<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");

$index = isset($_GET['index']) ? $_GET['index'] : -1;
$board = null;

if($index >= 0)
{
	if(is_array($prj_obj->maps))
		if($index < sizeof($prj_obj->maps))
			$board = $prj_obj->maps[$index];
}

if($board == null)
	header("location: /");
	
if(strtolower($action) == "update")
{
	$code = isset($_POST['code']) ? $_POST['code'] : '';
	$title = isset($_POST['title']) ? $_POST['title'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	
	if(!empty($code) && !empty($title)) {
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		if($prj_obj->maps[$index]->code == $code) {
			$prj_obj->maps[$index]->title = $title;
			$prj_obj->maps[$index]->description = $description;
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/maps");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project maps</title>
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
			<h1>Project maps</h1>
			<form method="post" id="update">
				<div>
					Map code (max 10 chars):
					<br />
					<input readonly value="<?php echo(htmlspecialchars($board->code)); ?>" class="required" type="text" size="10" maxlength="10" name="code" id="code">
				</div>
				<div>
					Map name:
					<br />
					<input value="<?php echo(htmlspecialchars($board->title)); ?>" class="required" type="text" size="50" name="title" id="title">
				</div>
				<div>
					Map description: <br />
					<textarea name="description" id="description" rows="8" cols="60"><?php echo(htmlspecialchars($board->description)); ?></textarea>
				</div>
				<div>
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