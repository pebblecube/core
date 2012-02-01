<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");

data::open_conn();

if(strtolower($action) == "add")
{		
	$code = isset($_POST['code']) ? $_POST['code'] : '';
	$title = isset($_POST['title']) ? $_POST['title'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	
	if(!empty($code) && !empty($title))
	{
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		$map = new project_map();
		$map->code = $code;
		$map->title = $title;
		$map->description = $description;
		$map->status = 1;
		$prj_obj->add_map($map);
		project_manager::update_project($prj_obj);
	}
	
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/maps");
}

if(isset($_REQUEST['removemap']))
{
	$index = isset($_REQUEST['removemap']) ? $_REQUEST['removemap'] : -1;
	
	if($index >= 0) {	
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		if(isset($prj_obj->maps[$index])) {
			//update project obj
			$prj_obj->maps[$index]->status = 0;
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/maps");
}

data::close_conn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project maps</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
		$(document).ready(function() {
		    $("#add").validate();
		});
		
		function del_map(index) {
			if(confirm('are you sure?')) {
				top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/maps?removemap=' + escape(index);
			}
		}
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/sub_menu.inc.php");?>
		<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Project maps</h1>
		</div>	
		<div class='clear'>&nbsp;</div>
		<div class="grid_6">
			<h2>Add new map</h2>
			<form method="post" id="add">
				<div>
					Map code (max 10 chars):
					<br />
					<input value="" class="required" type="text" size="10" maxlength="10" name="code" id="code">
				</div>
				<div>
					Map name:
					<br />
					<input value="" class="required" type="text" size="50" name="title" id="title">
				</div>
				<div>
					Map description: <br />
					<textarea class="fixed" name="description" id="description" rows="8" cols="60"></textarea>
				</div>
				<div>
					<input class="button" id="action" name="action" type="submit" value="Add">
				</div>
			</form>
		</div>
		<div class="grid_6">
			<h2>Current maps</h2>
			<?php
			if($prj_obj->maps)
			{
				$printed = 0;
				echo("<ul>");
				for ($i=0; $i < sizeof($prj_obj->maps); $i++) 
				{
					if($prj_obj->maps[$i]->status > 0)
					{
						echo("<li>");
						echo("<a class=\"action delete\" title=\"delete\" href=\"javascript:del_map(".$i.")\">d</a>");
						echo("<a class=\"action edit\" title=\"edit\" href=\"/prj/".sprintf("%s", $prj_obj->id)."/maps/edit/$i\">e</a>");
						echo("<a class=\"action list\" title=\"export\" href=\"/prj/".sprintf("%s", $prj_obj->id)."/maps/export/$i\">ex</a>");
						echo(sprintf("%s - %s", $prj_obj->maps[$i]->code, $prj_obj->maps[$i]->title));
						echo("</li>");
						$printed++;
					}
				}
				if($printed == 0)
					echo "<li>no maps</li>";
					
				echo("</ul>");
			}
			else
			{
				echo "<p>no maps</p>";
			}
			?>
		</div>
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>