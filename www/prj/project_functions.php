<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");

data::open_conn();

if($action == "add constant")
{		
	$code = isset($_POST['constant_code']) ? $_POST['constant_code'] : '';
	$value = isset($_POST['constant_value']) ? $_POST['constant_value'] : 0;
	$description = isset($_POST['constant_description']) ? $_POST['constant_description'] : '';
	
	if(!empty($code) && is_numeric($value))
	{
		$constant = new project_constant();
		$constant->code = $code;
		$constant->value = (float)$value;
		$constant->description = $description;
		$prj_obj->add_constant($constant);
		project_manager::update_project($prj_obj);
	}
	
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/functions");
}

if($action == "add function") {
	$code = isset($_POST['func_code']) ? $_POST['func_code'] : '';
	$script = isset($_POST['func_script']) ? $_POST['func_script'] : '';
	$description = isset($_POST['func_description']) ? $_POST['func_description'] : '';
	
	if(!empty($code) && !empty($script)) {
		$function = new project_function();
		$function->code = $code;
		$function->script = $script;
		$function->description = $description;
		$function->parse_script();
		$prj_obj->add_function($function);
		project_manager::update_project($prj_obj);
	}

	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/functions");
}

if(isset($_REQUEST['updateconst'])) {
	$index = isset($_REQUEST['updateconst']) ? $_REQUEST['updateconst'] : -1;
	
	if($index >= 0) {	
		if(isset($prj_obj->constants[$index])) {
			$value = isset($_POST['value']) ? $_POST['value'] : 0;
			$prj_obj->constants[$index]->value = $value;
			project_manager::update_project($prj_obj);
			die("updated");
		}
	}
}

if(isset($_REQUEST['removeconst'])) {
	
	$index = isset($_REQUEST['removeconst']) ? $_REQUEST['removeconst'] : -1;
	
	if($index >= 0) {	
		if(isset($prj_obj->constants[$index])) {
			//update project obj
			unset($prj_obj->constants[$index]);
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/functions");
}

if(isset($_REQUEST['removefunction'])) {
	
	$index = isset($_REQUEST['removefunction']) ? $_REQUEST['removefunction'] : -1;
	
	if($index >= 0) {	
		if(isset($prj_obj->functions[$index])) {
			//update project obj
			unset($prj_obj->functions[$index]);
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/functions");
}


data::close_conn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project functions</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
		$(document).ready(function(){
		    $("#add").validate();
			$("#addfunc").validate();
		});
		
		function del_const(index) {
			if(confirm('are you sure?'))
				top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/functions?removeconst=' + escape(index);
		}
		
		function edit_func(index) {
			top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/functions/edit/' + escape(index);
		}
		
		function test_func(index) {
			edit_func(index);
		}
		
		function del_func(index) {
			if(confirm('are you sure?'))
				top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/functions?removefunction=' + escape(index);
		}
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/sub_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Project functions<span>(<a href="#" onclick="$('#help').toggle();">?</a>)</span></h1>
			<div class="postcontent" id="help" style="display:none;">
				<p>example: [var:x] + 2 / ([sum:event_code#10] + ([var:y] * [const:constant_code]))</p>
				<p>function scripting:</p>
				<ul>
					<li>operators allowed: +, -, * and /</li>
					<li>operands are written inside square brackets, e.g.: [VAR:x], [CONST:constant_code]</li>
					<li>grouping operations allowed on stats are: min, max, sum, count and avg.</li>
					<li>is possible specify a time frame on group functions, e.g.: [SUM:event_code#10] returns sum of an event values during in the last 10 days (current day if not specified)</li>
				</ul>
				<p>&nbsp;</p>
			</div>
		</div>
		<div class="grid_6">
			<h2>Current functions</h2>
			<?php
			if($prj_obj->functions) {
				if(sizeof($prj_obj->functions) > 0) {
					echo("<ul>");
					for ($i=0; $i < sizeof($prj_obj->functions); $i++) {
						echo("<li>");
						echo("<a class=\"action delete\" title=\"delete\" href=\"javascript:del_func(".$i.")\">Delete</a>");
						echo("<a class=\"action edit\" title=\"edit\" href=\"javascript:edit_func(".$i.")\">Edit</a>");
						echo("<a class=\"action test\" title=\"test\" href=\"javascript:test_func(".$i.")\">Test</a>");
						echo(sprintf("%s - %s", $prj_obj->functions[$i]->code, $prj_obj->functions[$i]->description));
						echo("</li>");
					}
					echo("</ul>");
				}
				else {
					echo "<p>no functions</p>";
				}
			}
			else {
				echo "<p>no functions</p>";
			}
			?>
			<p>&nbsp;</p>
			<h2>New function</h2>
			<form method="post" id="addfunc">
				<div>
					Code (max 10 chars):
					<br />
					<input value="" class="required" type="text" size="10" maxlength="10" name="func_code" id="func_code">
				</div>
				<div>
					Script: <br />
					<textarea class="fixed" name="func_script" id="func_script" rows="8" cols="50"></textarea>
				</div>
				<div>
					description: <br />
					<textarea class="fixed" name="func_description" id="func_description" rows="3" cols="50"></textarea>
				</div>
				<div>
					<input class="button" id="action" name="action" type="submit" value="add function">
				</div>
			</form>
		</div>
		<div class="grid_6">
			<h2>current constants</h2>
			<?php
			if($prj_obj->constants) {
				if(sizeof($prj_obj->constants) > 0) {
					echo("<ul>");
					for ($i=0; $i < sizeof($prj_obj->constants); $i++) {
						echo("<li>");
						echo("<a class=\"action delete\" title=\"delete\" href=\"javascript:del_const(".$i.")\">d</a>");
						echo(sprintf("%s - %s", $prj_obj->constants[$i]->code, $prj_obj->constants[$i]->value));
						echo("</li>");
					}
					echo("</ul>");
				}
				else {
					echo "<p>no constants</p>";
				}
			}
			else {
				echo "<p>no constants</p>";
			}
			?>
			<p>&nbsp;</p>
			<h2>constants</h2>
			<form method="post" id="add">
				<div>
					code (max 10 chars):
					<br />
					<input value="" class="required" type="text" size="10" maxlength="10" name="constant_code" id="constant_code">
				</div>
				<div>
					value:
					<br />
					<input value="" class="required number fixed" type="text" size="40" name="constant_value" id="constant_value">
				</div>
				<div>
					description: <br />
					<textarea class="fixed" name="constant_description" id="constant_description" rows="3" cols="40"></textarea>
				</div>
				<div>
					<input class="button" id="action" name="action" type="submit" value="add constant">
				</div>
			</form>
		</div>
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>