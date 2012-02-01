<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/games_manager.php");

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
	
data::open_conn();

$page_index = isset($_POST['page']) ? $_POST['page'] : 1;
$from = isset($_POST['from']) ? $_POST['from'] : '';
$to = isset($_POST['to']) ? $_POST['to'] : '';

$from = strtotime($from);
$to = strtotime($to);

if(!is_numeric($from))
	$from = time()-24*60*60*7; //7 days
	
if(!is_numeric($to))
	$to = time();

$page_size = 100;

$results = games_manager::get_board($prj_obj->id, $board->code, $from, $to, $page_index, $page_size);
data::close_conn();

if($results)
{
	$scores_count = $results["count"];
	$scores = $results["data"];
	$page_count = 0;
	if($scores_count > 0 )
		$page_count = ceil($scores_count/$page_size);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project scoreboards</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<link type="text/css" href="/gui/css/jquery/vader/jquery-ui-1.8.9.custom.css" rel="stylesheet" />	
	<script type="text/javascript" src="/gui/js/datepicker/jquery-ui-1.8.9.custom.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
		    $("#updates").validate();
			$( "#from" ).datepicker({ dateFormat: 'yy-mm-dd' });
			$( "#to" ).datepicker({ dateFormat: 'yy-mm-dd' });
		});
		
		function res(all)
		{
			if(all)
			{
				$( "#from" ).val("");
				$( "#to" ).val("");
			}
			
			$( "#scores" ).submit();
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
			<h2>Scores</h2>
			<form method="post" id="scores">
				<div>
					From date: 
						<input type="text" id="from" name="from" value="<?php echo(date("Y-m-d", $from)); ?>" /> to date: <input type="text" id="to" name="to" value="<?php echo(date("Y-m-d", $to)); ?>" />
					<input class="button" id="btndisplay" name="btndisplay" type="button" value="Display" onclick="res(false);" />
					<input class="button" id="btnall" name="btnall" type="button" value="All" onclick="res(true);" />
				</div>
			</form>
		</div>
		<div class="grid_12">
			<h2><?php echo(htmlspecialchars($prj_obj->scoreboards[$index]->title)); ?></h2>
			<ul>
				<?php 
				foreach ($scores as $id => $value) {
				?>
				<li>
					<?php echo(htmlspecialchars($value["value"])); ?>
					-
					<?php echo(htmlspecialchars($value["user_name"])); ?>
					(<?php echo(date("Y/m/d", $value["time"])); ?>)
				</li>
				<?php
				}
				?>
			</ul>
			<?php
			if($page_count > 1)
			{		
				
				echo "<p>page<select id=\"pager\">";
				for($i=1; $i <= $page_count; $i++)
				{
					echo " <option value=\"?page={$i}\"";
					if($i == $page_index)
						echo " selected=\"selected\"";
					echo ">".$i;
					echo "</option> ";
				}
				echo "</select> <input onclick=\"location.href=jQuery('#pager').val();\" type=\"button\" value=\"go!\" /></p>";
			}
			?>
		</div>
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>