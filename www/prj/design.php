<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");

data::open_conn();

if($action == "update design")
{
	$prj_obj = project_manager::get_by_id($prj_id, FALSE);
	
	$body_color = isset($_POST['body_color']) ? $_POST['body_color'] : '';
	$primary_text_color = isset($_POST['primary_text_color']) ? $_POST['primary_text_color'] : '';
	$seconday_text_color = isset($_POST['seconday_text_color']) ? $_POST['seconday_text_color'] : '';
	$link_color = isset($_POST['link_color']) ? $_POST['link_color'] : '';
	$background_color = isset($_POST['background_color']) ? $_POST['background_color'] : '';
	
	$prj_obj = project_manager::get_by_id($prj_id, FALSE);
	//$prj_obj->design = new project_design();
	$prj_obj->design->body_color = $body_color;
	$prj_obj->design->primary_text_color = $primary_text_color;
	$prj_obj->design->seconday_text_color = $seconday_text_color;
	$prj_obj->design->link_color = $link_color;
	$prj_obj->design->background_color = $background_color;
	
	//header image
	if(isset($_FILES['header_image']) and  $_FILES['header_image']['tmp_name'] != '')
	{
		$filename = stripslashes($_FILES['header_image']['name']);
		$extension = pb_get_file_extension($filename);
		$prj_obj->design->header_image = project_manager::design_set_custom_header($prj_obj->id, $_FILES['header_image']['tmp_name'], $extension);
	}
	
	project_manager::update_project($prj_obj);
}

data::close_conn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>project design</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<link rel="stylesheet" media="screen" type="text/css" href="/gui/js/colorpicker/colorpicker.css" />
	<script type="text/javascript" src="/gui/js/colorpicker/colorpicker.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
		
			$('#body_color').ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val('#' + hex);
					$(el).ColorPickerHide();
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				},
				onChange: function (hsb, hex, rgb) {
					$('#body_color').val('#' + hex);
				}
			})
			.bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
			});
			
			$('#primary_text_color').ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val('#' + hex);
					$(el).ColorPickerHide();
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				},
				onChange: function (hsb, hex, rgb) {
					$('#primary_text_color').val('#' + hex);
				}
			})
			.bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
			});
			
			$('#seconday_text_color').ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val('#' + hex);
					$(el).ColorPickerHide();
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				},
				onChange: function (hsb, hex, rgb) {
					$('#seconday_text_color').val('#' + hex);
				}
			})
			.bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
			});
			
			$('#link_color').ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val('#' + hex);
					$(el).ColorPickerHide();
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				},
				onChange: function (hsb, hex, rgb) {
					$('#link_color').val('#' + hex);
				}
			})
			.bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
			});
			
			$('#background_color').ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val('#' + hex);
					$(el).ColorPickerHide();
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				},
				onChange: function (hsb, hex, rgb) {
					$('#background_color').val('#' + hex);
				}
			})
			.bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
			});
			
		});
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/sub_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>project design</h1>
			<form method="post" id="update-design" enctype="multipart/form-data">
				<div>Body Color:
					<br />
					<input type="text" size="40" value="<?php echo(htmlspecialchars($prj_obj->design->body_color)); ?>" name="body_color" id="body_color">
				</div>
				<div>
					Primary Text Color:
					<br />
					<input type="text" size="40" value="<?php echo(htmlspecialchars($prj_obj->design->primary_text_color)); ?>" name="primary_text_color" id="primary_text_color">
				</div>
				<div>
					Secondary Text Color:
					<br />
					<input type="text" value="<?php echo(htmlspecialchars($prj_obj->design->seconday_text_color)); ?>" size="40" name="seconday_text_color" id="seconday_text_color">
				</div>
				<div>
					Link Color:
					<br />
					<input type="text" size="40" value="<?php echo(htmlspecialchars($prj_obj->design->link_color)); ?>" name="link_color" id="link_color">
				</div>
				<div>
					Background Color:
					<br />
					<input type="text" size="40" value="<?php echo(htmlspecialchars($prj_obj->design->background_color)); ?>" name="background_color" id="background_color">
				</div>
				<div>
					Header image:
					<br />
					<input type="file"  value="<?php echo(htmlspecialchars($prj_obj->design->header_image)); ?>" name="header_image" id="header_image" />
					<br />(975 pixels wide, 40-180 pixels tall, .jpg, .gif or .png, 2mb max)
					<?php
					if(!empty($prj_obj->design->header_image))
					{
						echo("<br /><a href=\"".GLOBAL_HEADER_URL."/".$prj_obj->design->header_image."\" target=\"_blank\">current header</a>");
						echo("");
					}
					?>
				</div>
				<p>&nbsp;</p>
				<div>
					<input class="button" type="button" name="preview" value="preview" onclick="window.open('/prj/<?php echo($prj_id); ?>/design/preview');" id="preview">
					<input type="hidden" name="_id" id="_id" value="<?php echo($prj_obj->id); ?>">
					<input id="action" name="action" type="submit" value="update design">
				</div>
			</form>
		</div>
	</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>