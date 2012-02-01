<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Stats</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript" src="/gui/js/highcharts/js/highcharts.js"></script>
	<script type="text/javascript">
		// define graph options
		var d = new Date()
		var gmtHours = -d.getTimezoneOffset()/60;

		var options = {
			xAxis: {
				type: 'datetime',
				labels: {
					enabled: true,
					dateTimeLabelFormats: {
						day: '%b %e'
					}
				}
			},
			yAxis : {
				title: {
					text : ''
				},
				min : 0
			},
			legend:{
				enabled: false
			},
			tooltip: {
				shared: true,
				crosshairs: true
			},
			chart : {},
			title : {}
		}
		
		$(document).ready(function() {
			<?php 
			if(isset($_REQUEST["sessions"]))
				echo("load_sessions();");
			if(isset($_REQUEST["numeric"]))
				echo("reload_events_numeric();");
			if(isset($_REQUEST["var"]))
				echo("reload_events_var();");
			if(isset($_REQUEST["arrays"]))
				echo("load_array();");
			?>
		});
		
		function load_array()
		{
			$("#array_options").html("");
			//load all the items
			jQuery.getJSON('/stats/json/array_get_labels.php?_id=<?php echo $prj_obj->id; ?>&label=' + $("#var_array").val(), function(data) {
				//get options
				$.each(data, function(key, val) {
					$("#array_options").append("<input onclick=\"load_array_data();\" type=\"checkbox\" name=\"option_"+ key +"\" value=\""+ val["label"] +"\" checked=\"checked\" id=\"option_"+ key +"\"><label for=\"option_"+ key +"\">"+ val["label"] +"</label>&nbsp;");
				});
				//load array
				load_array_data();
			});
		}
		
		function load_array_data()
		{
			values_array = [];
			//get a list of checkbox selected
			$('#array_options').children("input[type='checkbox']:checked").each(function() {
				values_array.push(jQuery(this).val());
			});
			jQuery.getJSON('/stats/json/array_get_data.php?_id=<?php echo $prj_obj->id; ?>&event=' + $("#var_array").val() + '&options=' + encodeURIComponent(values_array.join(",")) + '&dateoffset=' + encodeURIComponent(gmtHours), function(data) {
				options_arrays = options;
				options_arrays.chart.renderTo =  'container_arrays';
				options_arrays.title.text = 'array graph - last month';
				options_arrays.plotOptions = {
				        column: {
				            stacking: 'normal'
				        }
				    }
				chart = new Highcharts.Chart(options_arrays);
				for (var i=0; i < data.length; i++) {
					chart.addSeries(data[i], false);
				};
				chart.redraw();
			});
		}
		
		function load_sessions()
		{
			jQuery.getJSON('/stats/json/sessions_count.php?_id=<?php echo $prj_obj->id; ?>&dateoffset=' + encodeURIComponent(gmtHours) + '&from=' + encodeURIComponent($('#sessions_from').val()) + '&to=' + encodeURIComponent($('#sessions_to').val()),
				function(data) {
					options_sessions = options;
					options_sessions.chart.renderTo =  'container_sessions';
					options_sessions.title.text = 'sessions count - last month';
					chart = new Highcharts.Chart(options_sessions);
					chart.addSeries({ data : data, name : 'sessions', pointInterval: 24 * 3600 * 1000 });
				}
			);
		}
		
		function reload_events_numeric()
		{
			var code = $("#numeric_event").val();
			var group = $("#numeric_event_group").val();
			
			jQuery.getJSON('/stats/json/events_data.php?_id=<?php echo $prj_obj->id; ?>&group=' + group + '&code=' + code + '&dateoffset=' + encodeURIComponent(gmtHours),
				function(data) {
					options_events_numeric = options;
					options_events_numeric.chart.renderTo =  'container_numeric_events';
					options_events_numeric.title.text =  code + " - " + group + ' - last month';
					chart = new Highcharts.Chart(options_events_numeric);
					chart.addSeries({ data : data, name : code, pointInterval: 24 * 3600 * 1000 });
				}
			);
		}
		
		function reload_events_var()
		{
			var code = $("#var_event").val();
			var group = $("#var_event_group").val();
			
			if(group == "values")
			{
				jQuery.getJSON('/stats/json/events_values.php?_id=<?php echo $prj_obj->id; ?>&code=' + code + '&dateoffset=' + encodeURIComponent(gmtHours),
					function(data) {
						options_events_var_columns = options;
						options_events_var_columns.chart.renderTo =  'container_var_events';
						options_events_var_columns.title.text =  code + " - " + group + ' - last month';
						
						options_events_var_columns.plotOptions = {
						        column: {
						            stacking: 'normal'
						        }
						    }
						
						chart = new Highcharts.Chart(options_events_var_columns);
						for (key in data){
							chart.addSeries({ data : data[key], name : key, type : "column", stack: 0, pointInterval: 24 * 3600 * 1000 });
						}
					}
				);
			}
			else
			{
				jQuery.getJSON('/stats/json/events_data.php?_id=<?php echo $prj_obj->id; ?>&group=' + group + '&code=' + code + '&dateoffset=' + encodeURIComponent(gmtHours),
					function(data) {
						options_events_var = options;
						options_events_var.chart.renderTo =  'container_var_events';
						options_events_var.title.text =  code + " - " + group + ' - last month';
						chart = new Highcharts.Chart(options_events_var);
						chart.addSeries({ data : data, name : code, pointInterval: 24 * 3600 * 1000 });
					}
				);
			}
		}
	</script>
	<script src="/gui/js/worldmap.js" type="text/javascript" charset="utf-8"></script>
	<link type="text/css" href="/gui/css/jquery/vader/jquery-ui-1.8.9.custom.css" rel="stylesheet" />	
	<script type="text/javascript" src="/gui/js/datepicker/jquery-ui-1.8.9.custom.min.js"></script>
	<script type="text/javascript" charset="utf-8">
	var worldSettings = {
	    id: "worldmap",
	    bgcolor: "#FFFFFF",
	    fgcolor: "#cccccc",
	    bordercolor: "#000000",
	 	borderwidth: 0.5,
		padding: 10
	}
	
	$(document).ready(function() {
		<?php 
		if(isset($_REQUEST["geo"])){
		?>
		WorldMap(worldSettings);
		$( "#geo_from" ).datepicker({ dateFormat: 'yy-mm-dd' });
		$( "#geo_to" ).datepicker({ dateFormat: 'yy-mm-dd' });
		display_map("", "", $('#geo_type').val());
		<?php 
		}
		if(isset($_REQUEST["sessions"])) {
		?>
		$( "#sessions_from" ).datepicker({ dateFormat: 'yy-mm-dd' });
		$( "#sessions_to" ).datepicker({ dateFormat: 'yy-mm-dd' });
		<?php 
		}
		?>
	});
	
	function geo_dates()
	{
		display_map($( "#geo_from" ).val(), $( "#geo_to" ).val(), $('#geo_type').val());
	}
	
	function geo_all()
	{
		display_map("", "", $('#geo_type').val());
	}
	
	function display_map(from, to, type)
	{
		//load geo data
		jQuery.getJSON('/stats/json/geo_data.php?_id=<?php echo $prj_obj->id; ?>&from='+ from + '&to=' + to + '&type=' + type + '&dateoffset=' + encodeURIComponent(gmtHours), function(data) {
				worldSettings.detail = {};
				countries_array = [];
				
				data = generateCloud(data);
				
				$.each(data, function(key, val) {
			    	worldSettings.detail[key] = val["color"];
					countries_array.push(val);
			  	});
				WorldMap(worldSettings);
				countries_array.sort(function(a,b) { return parseInt(a.count) - parseInt(b.count) } );
				$("#geo_values").html("");
				$("#geo_values").append("<table id=\"geo_values_ul\"><thead><th>country</th><th>count</th></thead><tbody></tbody></table>");
				for (var i = countries_array.length - 1; i >= 0; i--){
					$("#geo_values_ul tbody").append("<tr><td>"+ countries_array[i]["country_name"] + "</td><td>"+ countries_array[i]["count"] +"</td></tr>");
				};
			}
		);
	}
	
	function get_color(min,max,val) {
		colors = ['#95BAE5', '#638EBF', '#4572A7'];
		var diff = ( max == min ? 1 : (max - min) / 2 );
	    color_num = Math.round((val - min) / diff);
		return colors[color_num];
	}
	
	function generateCloud(data) {
		var min = 10000000000;
		var max = 0;
	
		$.each(data, function(key, val) {
			if(val["count"] > max) 
				max = val["count"];
			if(val["count"] < min)
				min = val["count"];
		});
	    
		max = Math.log(max);
	    min = Math.log(min);
	
		$.each(data, function(key, val) {
			val["log"] = Math.log(val["count"]);
			val["color"] = get_color(min,max, val["log"]);
		});
		return data;
	}
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/sub_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_3">
			<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/stats_menu.inc.php");?>
		</div>
		<div class="grid_9">
			<h1>Stats</h1>
			<?php 
			//********************************************************************************
			//sessions
			if(isset($_REQUEST["sessions"])) {
			?>
				<p></p>
				<h2>Sessions</h2>
				From date: 
				<input type="text" id="sessions_from" name="sessions_from" value="" /> to date: <input type="text" id="sessions_to" name="sessions_to" value="" />
				<input class="button" id="sessions_display" name="sessions_display" type="button" value="filter" onclick="load_sessions();" />
				<div id="container_sessions"></div>
			<?php 
			}
			//********************************************************************************
			?>
			
			<?php
			//********************************************************************************
			//numeric
			if(isset($_REQUEST["numeric"])) {
			?>
				<p></p>
				<h2>Numeric events</h2>
				<select name="numeric_event" id="numeric_event" onchange="reload_events_numeric();">
					<option value="gt">gt - integer</option>
					<?php
					if($prj_obj->events)
					{
						for ($i=0; $i < sizeof($prj_obj->events); $i++) 
						{
							if($prj_obj->events[$i]->typeof == "integer" or $prj_obj->events[$i]->typeof == "float")
							{
								echo(sprintf("<option value=\"%s\">%s - %s</option>", $prj_obj->events[$i]->code, $prj_obj->events[$i]->code, $prj_obj->events[$i]->typeof));
							}
						}
					}
					?>
				</select>
				<select id="numeric_event_group" name="numeric_event_group" onchange="reload_events_numeric();">
					<option value="count">count</option>
					<option value="sum">sum</option>
					<option value="avg">avg</option>
				</select>
				<div id="container_numeric_events"></div>
			<?php 
			}
			//********************************************************************************
			?>
			
			<?php
			//********************************************************************************
			// values
			if(isset($_REQUEST["var"])) {
			?>
			<p></p>
			<h2>String / Boolean events</h2>
			<select name="var_event" id="var_event" onchange="reload_events_var();">
				<?php
					for ($i=0; $i < sizeof($prj_obj->events); $i++) 
					{
						if($prj_obj->events[$i]->typeof == "string" or $prj_obj->events[$i]->typeof == "boolean")
							echo(sprintf("<option value=\"%s\">%s - %s</option>", $prj_obj->events[$i]->code, $prj_obj->events[$i]->code, $prj_obj->events[$i]->typeof));
					}
				?>
			</select>
			<select id="var_event_group" name="var_event_group" onchange="reload_events_var();">
				<option value="count">count</option>
				<option value="values">values</option>
			</select>
			<div id="container_var_events"></div>
			<?php 
			}
			//********************************************************************************
			?>
			
			<?php
			//********************************************************************************
			// arrays
			if(isset($_REQUEST["arrays"])) {
			?>
			<p></p>
			<h2>Arrays</h2>
			<select name="var_array" id="var_array" onchange="load_array();">
				<?php
				for ($i=0; $i < sizeof($prj_obj->events); $i++) 
				{
					if($prj_obj->events[$i]->typeof == "array")
						echo(sprintf("<option value=\"%s\">%s</option>", $prj_obj->events[$i]->code, $prj_obj->events[$i]->code));
				}
				?>
			</select>
			<span id="array_options"></span>
			<div id="container_arrays"></div>
			<?php 
			}
			//********************************************************************************
			?>
			
			<?php 
			//********************************************************************************
			//geo
			if(isset($_REQUEST["geo"])) {
			?>
			<p></p>
			<h2>Geo</h2>
			Type: 
			<select name="geo_type" id="geo_type">
				<option value="events">events</option>
				<option value="sessions">sessions</option>
			</select>
			<br />
			<br />
			From date: 
			<input type="text" id="geo_from" name="geo_from" value="" /> to date: <input type="text" id="geo_to" name="geo_to" value="" />
			<input class="button" id="btndisplay" name="btndisplay" type="button" value="filter" onclick="geo_dates();" />
			<input class="button" id="btnall" name="btnall" type="button" value="all" onclick="geo_all();" />
			<p>&nbsp;</p>
			<canvas id="worldmap" width="900" height="400"></canvas>
			<div id="geo_values"></div>
			<?php 
			}
			//********************************************************************************
			?>
		</div>
	</div>		
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>