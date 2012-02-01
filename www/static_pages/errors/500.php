<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="icon" type="image/png" href="/gui/img/favicon.png" />
	<title>500</title>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<script type="text/javascript" src="/gui/js/canvastutorial/lib.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){			
			init();
			initbricks();
		});
		
		function restart() {
			reset_values();
		}
	</script>
	<style>
		body {
			background-color:#000; color:#FFF; font-family: 'courier new', arial, sans-serif; text-align:center;}
			a { color:#FFF;}
			p {font-size:12px;}
	</style>
</head>
<body style="">
<h1>#500: oooh shit!</h1>
<canvas id="canvas" width="600" height="300"></canvas>
<p>
	<input type="button" value="restart" onclick="restart();" />
</p>
<p>&nbsp;</p>
<p>
	this game has been brought to you by <a target="_blank" href="http://billmill.org/static/canvastutorial/index.html">Bill Mill</a>
</p>
</body>
</html>
