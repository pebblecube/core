<?php 
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
pb_redirect_not_logged("/docs");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Pebblecube - <?php get_site_name(); ?> - <?php get_page_clean_title(); ?></title>
	<?php get_header(); ?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
</head>
<body id="<?php get_page_slug(); ?>" >
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div id="sub-header">
		<div id="prj-name">Api docs</div>
		<ul>
			<?php get_navigation(return_page_slug()); ?>
		</ul>
	</div>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_3">
			<?php go_child_menu(); ?>
		</div>
		<div class="grid_9">
			<h1><?php get_page_title(); ?></h1>
			<div class="postcontent" style="display: table;">
				<?php get_page_content(); ?>					
				<p>&nbsp;</p>
			</div>
		</div>
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>