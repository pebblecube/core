<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");

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
		$board = new project_scoreboard();		
		$board->code = $code;
		$board->title = $title;
		$board->description = $description;
		$board->type = $type;		
		$prj_obj->add_scoreboard($board);
		project_manager::update_project($prj_obj);
	}
	
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/scoreboards");
}

if(isset($_REQUEST['removeboard']))
{
	$index = isset($_REQUEST['removeboard']) ? $_REQUEST['removeboard'] : -1;
	
	if($index >= 0)
	{	
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		if(isset($prj_obj->scoreboards[$index]))
		{
			//update project obj
			$prj_obj->scoreboards[$index]->status = 0;
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/scoreboards");
}

data::close_conn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project scoreboards</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
		$(document).ready(function(){
		    $("#add").validate();
		});
		
		function del_board(index)
		{
			if(confirm('are you sure?'))
				top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/scoreboards?removeboard=' + escape(index);
		}
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/sub_menu.inc.php");?>
		<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Project scoreboards</h1>
		</div>	
		<div class='clear'>&nbsp;</div>
		<div class="grid_6">
			<h2>Add new scoreboard</h2>
			<form method="post" id="add">
				<div>
					Board code (max 10 chars):
					<br />
					<input value="" class="required" type="text" size="10" maxlength="10" name="code" id="code">
				</div>
				<div>
					Board name:
					<br />
					<input value="" class="required" type="text" size="50" name="title" id="title">
				</div>
				<div>
					Board description: <br />
					<textarea class="fixed" name="description" id="description" rows="8" cols="60"></textarea>
				</div>
				<div>
					<!-- 
					<input type="checkbox" id="type" name="type" value="1"><label for="type">public board (visible on project page)</label>
					<br /><br />
					-->
					<input class="button" id="action" name="action" type="submit" value="Add">
				</div>
			</form>
		</div>
		<div class="grid_6">
			<h2>Current boards</h2>
			<?php
			if($prj_obj->scoreboards)
			{
				
				$printed = 0;
				echo("<ul>");
				for ($i=0; $i < sizeof($prj_obj->scoreboards); $i++) 
				{
					if($prj_obj->scoreboards[$i]->status > 0)
					{
						echo("<li>");
						echo("<a class=\"action delete\" title=\"delete\" href=\"javascript:del_board(".$i.")\">d</a>");
						echo("<a class=\"action edit\" title=\"edit\" href=\"/prj/".sprintf("%s", $prj_obj->id)."/scoreboards/edit/$i\">e</a>");
						echo("<a class=\"action list\" title=\"scores\" href=\"/prj/".sprintf("%s", $prj_obj->id)."/scoreboards/scores/$i\">s</a>");
						echo(sprintf("%s - %s", $prj_obj->scoreboards[$i]->code, $prj_obj->scoreboards[$i]->title)); //, $prj_obj->scoreboards[$i]->type == 1 ? "public" : "private")
						echo("</li>");
						$printed++;
					}
				}
				if($printed == 0)
					echo "<li>no boards</li>";
					
				echo("</ul>");
			}
			else
			{
				echo "<p>no boards</p>";
			}
			?>
		</div>
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>