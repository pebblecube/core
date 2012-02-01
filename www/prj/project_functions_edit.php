<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");

$index = isset($_GET['index']) ? $_GET['index'] : -1;
$func = null;

if($index >= 0)
{
	if(is_array($prj_obj->functions))
		if($index < sizeof($prj_obj->functions))
			$func = $prj_obj->functions[$index];
}

if($func == null)
	header("location: /");
	
if(strtolower($action) == "update")
{
	$code = isset($_POST['code']) ? $_POST['code'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$script = isset($_POST['script']) ? $_POST['script'] : '';
	
	if(!empty($code) && !empty($script)) {
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		if($prj_obj->functions[$index]->code == $code)
		{
			$prj_obj->functions[$index]->description = $description;
			$prj_obj->functions[$index]->script = $script;
			$prj_obj->functions[$index]->parse_script();
			project_manager::update_project($prj_obj);
		}		
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/functions");
}
	
function get_const_value($const_name, $prj_constants) {
	for ($i=0; $i < sizeof($prj_constants); $i++) { 
		if($prj_constants[$i]->code == $const_name) {
			return $prj_constants[$i]->value;
		}
	}
	return "not found";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project functions</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
		$(document).ready(function(){
		    $("#update").validate();
		});
		
		function eval_func() {
			var script = "<?php echo htmlspecialchars($func->script, ENT_QUOTES, 'utf-8') ?>";
			var values = [];
			<?php
			//variables
			for ($i=0; $i < sizeof($func->variables); $i++) {
				echo(sprintf("values.push({ var: \"[var:%s]\", value : $(\"#var_%s\").val()}); \n",
				$func->variables[$i], $func->variables[$i]));
			}
			//constants
			for ($i=0; $i < sizeof($func->constants); $i++) {
				echo(sprintf("values.push({ var: \"[const:%s]\", value : $(\"#const_%s\").val()}); \n",
				$func->constants[$i], $func->constants[$i]));
			}
			//events
			for ($i=0; $i < sizeof($func->groups); $i++) {
				echo(sprintf("values.push({ var: \"[%s]\", value : $(\"#group_%s\").val()}); \n",
				$func->groups[$i]["formula"], str_replace(array(":", "#"), "-", $func->groups[$i]["formula"])));
			}
			?>
			for (var i=0; i < values.length; i++) {
				script = script.replace(values[i].var, values[i].value);
			}
			$("#result_container").html("result: <strong>" + eval(script) + "</strong>");
		}
		
		function save_const(id, index) {
			$.post("/prj/<?php echo htmlspecialchars(sprintf("%s", $prj_obj->id), ENT_QUOTES, 'utf-8') ?>/functions", { updateconst: index, value: $("#const_" + id).val() });
		}
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/sub_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Project functions <span>(<a href="#" onclick="$('#help').toggle();">?</a>)</span></h1>
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
			<h2>Function details</h2>
			<form method="post" id="update">
				<div>
					Code (max 10 chars):
					<br />
					<input readonly value="<?php echo(htmlspecialchars($func->code)); ?>" class="required" type="text" size="10" maxlength="10" name="code" id="code">
				</div>
				<div>
					Script:
					<br />
					<textarea class="fixed" name="script" id="script" rows="8" cols="60"><?php echo(htmlspecialchars($func->script)); ?></textarea>
				</div>
				<div>
					Description: <br />
					<textarea class="fixed" name="description" id="description" rows="3" cols="60"><?php echo(htmlspecialchars($func->description)); ?></textarea>
				</div>
				<div>
					<input class="button" id="cancel" name="cancel" type="button" value="Cancel" onclick="location.href = '../'">
					<input class="button" id="action" name="action" type="submit" value="Update">
				</div>
			</form>
		</div>
		<div class="grid_6">
			<h2>Test</h2>
			<ul>
			<?php
			if(sizeof($func->constants) > 0) {
				echo "<li class=\"group\"><strong>constants:</strong></li>";
				for ($i=0; $i < sizeof($func->constants); $i++) {
					if(!empty($func->constants[$i])) {
						echo(sprintf("<li><label for=\"\">%s: </label><input type=\"text\" name=\"const_%s\" value=\"%s\" id=\"const_%s\"> <a href=\"javascript:save_const('%s', %d);\">save</a></li>", 
								$func->constants[$i], 
								$func->constants[$i], 
								get_const_value($func->constants[$i], $prj_obj->constants),
								$func->constants[$i], $func->constants[$i], $i));
					}
				}
			}
			
			if(sizeof($func->variables) > 0) {
				echo "<li class=\"group\"><strong>variables:</strong></li>";
				for ($i=0; $i < sizeof($func->variables); $i++) { 
					echo(sprintf("<li><label for=\"\">%s: </label><input type=\"text\" name=\"var_%s\" value=\"0\" id=\"var_%s\"></li>", $func->variables[$i], $func->variables[$i], $func->variables[$i]));
				}
			}
			
			if(sizeof($func->groups) > 0) {
				echo "<li class=\"group\"><strong>stats:</strong></li>";
				for ($i=0; $i < sizeof($func->groups); $i++) {
					$formula = str_replace(array(":", "#"), "-", $func->groups[$i]["formula"]);
					echo(sprintf("<li><label for=\"\">%s: </label><input type=\"text\" name=\"group_%s\" value=\"%s\" id=\"group_%s\"></li>", $func->groups[$i]["formula"], $formula, $func->groups[$i]["value"], $formula));
				}
			}
			?>
			</ul>
			<p>&nbsp;</p>
			<p><input type="button" name="test" value="Run test" onclick="eval_func();" id="test">
			<span id="result_container"></span></p>
		</div>
		
		</div>
		<div class='clear'>&nbsp;</div>
		<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
	</body>
	</html>