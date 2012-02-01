<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");

if(strtolower($action) == "add event")
{
	data::open_conn();
	
	$code = isset($_POST['code']) ? $_POST['code'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$typeof = isset($_POST['typeof']) ? $_POST['typeof'] : '';
	
	$array_label = isset($_POST['array_label']) ? $_POST['array_label'] : array();
	$array_typeof = isset($_POST['array_typeof']) ? $_POST['array_typeof'] : array();
	
	if(!empty($code) && !empty($description) & !empty($typeof))
	{
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		$event = new project_event();
		$event->code = $code;
		$event->description = $description;
		$event->typeof = $typeof;
		
		if(sizeof($array_label) > 0 && sizeof($array_typeof) > 0){
			$options = array();
			for ($i=0; $i < sizeof($array_label); $i++) { 
				if(!empty($array_label[$i]) && !empty($array_typeof[$i]))
					array_push($options, array("label" => strtolower($array_label[$i]), "typeof" => strtolower($array_typeof[$i])));
			}
			$event->options = $options;
		}
		
		$prj_obj->add_event($event);
		project_manager::update_project($prj_obj);
	}
	
	data::close_conn();
	
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/statssettings");
}

if(isset($_REQUEST['removeevent']))
{
	$code = isset($_REQUEST['removeevent']) ? $_REQUEST['removeevent'] : '';
	
	if(!empty($code))
	{
		data::open_conn();
		
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		$prj_obj->remove_event_by_code($code);
		project_manager::update_project($prj_obj);
		
		data::close_conn();
	}
	
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id)."/statssettings");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project stats settings</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
		
		$(document).ready(function(){
		    $("#update-stats-settings").validate();
		});
		
		function del_event(code)
		{
			if(confirm('are you sure?'))
				top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/statssettings?removeevent=' + escape(code);
		}
		
		function check_if_array()
		{
			if($("#typeof").val() == "array")
			{
				$("#array_values").show();
			}
			else
			{
				$("#array_values").hide();
			}
		}
		
		var array_items = Array();
		function add_label()
		{
			new_index = array_items.length + 1;
			str_html = "<div id=\"cont" + new_index + "\">label: <input type=\"text\" name=\"array_label[]\" id=\"label"+ new_index +"\" />" + 
			" type: <select name=\"array_typeof[]\" id=\"typeof" + new_index + "\"><option value=\"string\">string</option><option value=\"numeric\">numeric</option></select>" + 
			" <input class=\"button\" type=\"button\" value=\"+\" onclick=\"add_label(" + new_index + ")\" />" + 
			" <input class=\"button\" type=\"button\" value=\"-\" onclick=\"remove_label(" + new_index + ")\" /></div>";
			$("#array_values").append(str_html);
			
			if(array_items.indexOf("cont" + new_index) < 0)
				array_items.push("cont" + new_index);
		}
		
		function remove_label(index)
		{
			$("#cont" + index).remove();
			array_items.splice(index, 1);
		}
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/sub_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Project stats settings</h1>
		</div>
		<div class="grid_6">
			<h2>New event</h2>
			<form method="post" id="update-stats-settings">
				<div>Code:<br /><input class="required fixed" type="text" size="60" name="code" id="code"></div>
				<div>Description: <br /><input class="required fixed" type="text" size="60" name="description" id="description"></div>
				<div>Data type:
					<select name="typeof" id="typeof" onChange="check_if_array();">
						<option value="integer">integer</option>
						<option value="float">float</option>
						<option value="string">string</option>
						<option value="boolean">boolean</option>
						<option value="array">array</option>
					</select>
				</div>
				<div style="display:none;" id="array_values">
					<div id="cont0">
						label: <input type="text" value="" name="array_label[]" id="label0" />
						type: <select name="array_typeof[]" id="typeof0"><option value="string">string</option><option value="numeric">numeric</option></select>
						<input class="button" type="button" value="+" onclick="add_label(0)" />
					</div>
				</div>
				<div>
					<input type="hidden" name="_id" id="_id" value="<?php echo($prj_obj->id); ?>">
					<input class="button" id="action" name="action" type="submit" value="Add event">
				</div>
			</form>
		</div>		
		<div class="grid_6">
			<h2>Default events</h2>
			<ul>
				<li>GT (integer): user game time</li>
			</ul>
			<p>&nbsp;</p>
			<h2>Custom events</h2>
			<ul>
			<?php
			if($prj_obj->events)
			{
				for ($i=0; $i < sizeof($prj_obj->events); $i++) 
				{
					if(!empty($prj_obj->events[$i]->code))
					{
						echo("<li>");
						echo(sprintf("<a title=\"delete\" class=\"action delete\" href=\"javascript:del_event('%s')\">Delete</a>", $prj_obj->events[$i]->code));
						echo(sprintf(" %s (%s): %s", $prj_obj->events[$i]->code, $prj_obj->events[$i]->typeof, $prj_obj->events[$i]->description));
				
						if($prj_obj->events[$i]->typeof == "array")
						{
							echo " - ".json_encode($prj_obj->events[$i]->options, true);
						}
					
						echo("</li>");
					}
				}
			}
			?>
			</ul>
		</div>	
	</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>