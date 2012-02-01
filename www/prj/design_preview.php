<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo(htmlspecialchars($prj_obj->name)); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="icon" type="image/png" href="/gui/img/favicon.png" />
	<link rel="stylesheet" type="text/css" href="/gui/css/project.css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<script type="text/javascript" src="/gui/js/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
	<script type="text/javascript" src="/gui/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<link rel="stylesheet" type="text/css" href="/gui/js/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
	<style type="text/css">
	<?php
	if(!empty($prj_obj->design->background_color))
		echo("body { background:none repeat scroll 0 0 ".htmlspecialchars($prj_obj->design->background_color)."; }");
	
	if(!empty($prj_obj->design->body_color))
		echo("#containerWrapper { background-color: ".htmlspecialchars($prj_obj->design->body_color)."; }");
	
	if(!empty($prj_obj->design->link_color))
		echo("#content a { color: ".htmlspecialchars($prj_obj->design->link_color)."; }");
	
	if(!empty($prj_obj->design->primary_text_color))
		echo("#content h1, #content h2, #content h3 { color: ".htmlspecialchars($prj_obj->design->primary_text_color)."; }");
	
	if(!empty($prj_obj->design->seconday_text_color))
		echo("#content div, #content p { color: ".htmlspecialchars($prj_obj->design->seconday_text_color)."; }");
	?>
	</style>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$("a[rel=screenshot]").fancybox({
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'titlePosition'		: 'inside'
			});
		});
	</script>
</head>
<body>
	<div id="container">
		<div id="containerWrapper">
			<div id="content">
				<div id="imgHeader">
					<a href="http://<?php echo($prj_obj->url); ?>.pebblecube.com">
						<?php
						//check if header exists
						if(is_file(GLOBAL_WWW_FILE_PATH.GLOBAL_DOWNLOADS_FOLDER.DIRECTORY_SEPARATOR.$prj_obj->design->header_image))
						{
							$header_url = GLOBAL_HEADER_URL."/".$prj_obj->design->header_image;	
						}
						else
						{
							$header_url = "/gui/img/default_header.png";
						}
						?>
						<img src="<?php echo($header_url); ?>">
					</a> 
				</div>
				<div id="projectData">
					<h1><?php echo(htmlspecialchars($prj_obj->name)); ?></h1>
					<div>
						<?php echo(htmlspecialchars($prj_obj->description)); ?>
					</div>
				</div>
				<p>&nbsp;</p>
				<div id="projectScreenShots">
					<h3>screenshots</h3>
					<?php
					if($prj_obj->screenshots)
					{
						echo("<ul>");
						for ($i=0; $i < sizeof($prj_obj->screenshots); $i++) 
						{
							echo("<li>");
							echo(sprintf("<a rel=\"screenshot\" href=\"%s\" title=\"%s\"><img width=\"100\" src=\"%s\"></a>", 
								$prj_obj->screenshots[$i]->url($prj_obj->id), 
								htmlspecialchars($prj_obj->screenshots[$i]->description),
								$prj_obj->screenshots[$i]->url($prj_obj->id)));
							echo("</li>");
						}
						echo("</ul>");
					}
					?>
				</div>
				<div id="projectFiles">
					<h3>files</h3>
					<?php
					if($prj_obj->files)
					{
						echo("<ul>");
						for ($i=0; $i < sizeof($prj_obj->files); $i++) 
						{
							echo("<li>");
							echo(sprintf("<a target=\"_blank\" href=\"%s\">%s</a><p>%s</p>", $prj_obj->files[$i]->url($prj_obj->id), $prj_obj->files[$i]->file_name, $prj_obj->files[$i]->description));
							echo("</li>");
						}
						echo("</ul>");
					}
					?>
				</div>
				<div id="projectLinks">
					<h3>links</h3>
					<?php
					if($prj_obj->links)
					{
						echo("<ul>");
						for ($i=0; $i < sizeof($prj_obj->links); $i++) 
						{
							echo("<li>");
							echo(sprintf("<a href=\"%s\" target=\"_blank\">%s</a>", $prj_obj->links[$i]->url, $prj_obj->links[$i]->description));
							echo("</li>");
						}
						echo("</ul>");
					}
					?>
				</div>
			</div>
			<div id="footer">
				<a href="http://www.pebblecube.com"><div id="footerLogo"><span class="hidden">pebblecube</span></div></a>
				<ul id="footerlinks">
				    <li><a href="http://www.pebblecube.com/tos">terms of use</a></li>
				    <li><a href="http://www.pebblecube.com/privacy">privacy</a></li>
				    <li><a href="http://www.pebblecube.com/copyright">copyright policy</a></li>
				    <li><a href="http://www.pebblecube.com/help">help</a></li>
				</ul>
				&nbsp;
			</div>
		</div>
	</div>
</body>
</html>