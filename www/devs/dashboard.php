<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/developer_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
pb_redirect_not_logged("/devs/dashboard");

data::open_conn();
$dev_id = pb_dev_id();
$dev_obj = developer_manager::get_by_id($dev_id);
data::close_conn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Dashboard</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<div>
				<span style="float: right;">
					<?php echo pb_get_dev_avatar_img($dev_id, 49); ?>
				</span>
				<h1>Howdy, <?php echo($dev_obj->username); ?>!</h1>
			</div>
			<h2>My projects</h2>
			<ul>
			<?php
			if($dev_obj->projects)
			{
				data::open_conn();
				for ($i=0; $i < sizeof($dev_obj->projects); $i++) 
				{
					if($dev_obj->projects[$i]['status'] == 1)
					{
						$prj = project_manager::get_by_id($dev_obj->projects[$i]['project_id']);
						echo("<li><a href=\"/prj/".sprintf("%s", $prj->id)."\">".htmlspecialchars($prj->name)."</a></li>");
					}
				}
				data::close_conn();
			}
			?>
			</ul>
			<div style="margin-top: 10px;"></div>
			<input type="button" class="button" value="Add new project" onclick="top.location.href = '/prj/add';"/>
		</div>
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>