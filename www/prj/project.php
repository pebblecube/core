<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/dev_page_init.inc.php");

data::open_conn();

if(strtolower($action) == "update")
{
	//update values
	$name = isset($_POST['name']) ? $_POST['name'] : '';
	$url = isset($_POST['url']) ? $_POST['url'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	
	if(!empty($url) && !empty($name))
	{
		if($url != $prj_obj->url)
		{
			//check if url exists
			if(project_manager::url_exists($url))
				$url = $prj_obj->url;
		}
		
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		$prj_obj->name = $name;
		$prj_obj->description = $description;
		$prj_obj->url = $url;
		project_manager::update_project($prj_obj);
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id));
}

if(strtolower($action) == "update security")
{
	$cipher = isset($_POST['cipher']) ? $_POST['cipher'] : 'none';
	if(!empty($cipher))
	{
		if($cipher == "128" || $cipher == "192" || $cipher == "256" || $cipher == "none")
		{
			$prj_obj = project_manager::get_by_id($prj_id, FALSE);
			$prj_obj->cipher = $cipher;
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id));
}

if(strtolower($action) == "delete")
{
	project_manager::delete_project($prj_id);
	//redirect
	header("location: /devs/dashboard");
}

if(strtolower($action) == "add")
{
	$email = isset($_POST['email']) ? $_POST['email'] : '';
	//check email exists
	if($developer = developer_manager::developer_exists($email, "", TRUE))
	{
		//add user to project
		$prj_obj->add_developer($developer->id);
		project_manager::update_project($prj_obj);
		//add project to user object
		$developer->add_project($prj_obj->id);
		developer_manager::update_developer($developer);
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id));
}

if(strtolower($action) == "add link")
{
	$url = isset($_POST['url']) ? $_POST['url'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	if(!empty($url) && !empty($description))
	{
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		$link = new project_link();
		$link->url = $url;
		$link->description = $description;
		$prj_obj->add_link($link);
		project_manager::update_project($prj_obj);
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id));
}

if(strtolower($action) == "add file")
{
	if(isset($_FILES['file']) and  $_FILES['file']['tmp_name'] != '')
	{
		$description = isset($_POST['description']) ? $_POST['description'] : '';
		$filename = stripslashes($_FILES['file']['name']);
		
		//save file on disk
		if(project_manager::save_project_file($prj_id, $_FILES['file']))
		{
			$prj_obj = project_manager::get_by_id($prj_id, FALSE);
			$file = new project_file();
			$file->file_name = $filename;
			$file->description = $description;
			$file->timestamp = time();
			$prj_obj->add_file($file);
			project_manager::update_project($prj_obj);
		}
	}	
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id));
}

if(strtolower($action) == "add screenshot")
{
	if(isset($_FILES['file']) and  $_FILES['file']['tmp_name'] != '')
	{
		$description = isset($_POST['description']) ? $_POST['description'] : '';
		$filename = stripslashes($_FILES['file']['name']);
		
		//save file on disk
		if(project_manager::save_project_screenshot($prj_id, $_FILES['file']))
		{
			$prj_obj = project_manager::get_by_id($prj_id, FALSE);
			$file = new project_screenshot();
			$file->file_name = $filename;
			$file->description = $description;
			$prj_obj->add_screenshot($file);
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id));
}



if(isset($_REQUEST['removelink']))
{
	$index = isset($_REQUEST['removelink']) ? $_REQUEST['removelink'] : -1;
	
	if($index >= 0)
	{	
		if(isset($prj_obj->links[$index]))
		{
			$prj_obj = project_manager::get_by_id($prj_id, FALSE);
			unset($prj_obj->links[$index]);
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id));
}

if(isset($_REQUEST['removefile']))
{
	$index = isset($_REQUEST['removefile']) ? $_REQUEST['removefile'] : -1;
	
	if($index >= 0)
	{	
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		if(isset($prj_obj->files[$index]))
		{
			//delete file
			project_manager::delete_project_file($prj_obj->files[$index], $prj_obj->id);
			//update project obj
			unset($prj_obj->files[$index]);
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id));
}

if(isset($_REQUEST['removeshot']))
{
	$index = isset($_REQUEST['removeshot']) ? $_REQUEST['removeshot'] : -1;
	
	if($index >= 0)
	{	
		$prj_obj = project_manager::get_by_id($prj_id, FALSE);
		if(isset($prj_obj->screenshots[$index]))
		{
			//delete file
			project_manager::delete_project_screenshot($prj_obj->screenshots[$index], $prj_obj->id);
			//update project obj
			unset($prj_obj->screenshots[$index]);
			project_manager::update_project($prj_obj);
		}
	}
	//redirect
	header("location: /prj/".sprintf("%s", $prj_obj->id));
}

data::close_conn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Project details</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
	
		jQuery.validator.addMethod("prjurl", function(value, element) { 
		  return this.optional(element) || /^[a-zA-Z0-9\-]/.test(value); 
		}, "Please specify the correct url for your project");
		
		$(document).ready(function(){
		    $("#update").validate();
			$("#add").validate();
			$("#links").validate();
			$("#files").validate();
			$("#screenshot").validate();
			
			$("#cipher").change(function(){
				if($("#cipher").val() != "none")
					$("#span_cipher").html("key: " + $("#span_secret").html().substring(0, parseInt($("#cipher").val())/8));
				else
					$("#span_cipher").html("");
			});			
		});
		
		function revoke_perm(id)
		{
			if(confirm('are you sure?'))
				top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/revoke?u=' + escape(id);
		}
		
		function del_link(index)
		{
			if(confirm('are you sure?'))
				top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/?removelink=' + escape(index);
		}
		
		function del_file(index)
		{
			if(confirm('are you sure?'))
				top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/?removefile=' + escape(index);
		}
		
		function del_screenshot(index)
		{
			if(confirm('are you sure?'))
				top.location.href = '/prj/<?php echo sprintf("%s", $prj_obj->id); ?>/?removeshot=' + escape(index);
		}
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/prj/sub_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Project details</h1>
			<ul>
				<li>Api key: <?php echo(htmlspecialchars($prj_obj->api_key)); ?></li>
				<li>Secret key: <span id="span_secret"><?php echo(htmlspecialchars($prj_obj->api_secret)); ?></span></li>
			</ul>
			<p>&nbsp;</p>
		</div>
		<div class='clear'>&nbsp;</div>
		<div class="grid_6">
			<h2>Settings</h2>
			<form method="post" id="update">
				<div>
					Project name:
					<br />
					<input value="<?php echo(htmlspecialchars($prj_obj->name)); ?>" class="required fixed" type="text" size="60" name="name" id="name">
				</div>
				<div>
					Project url:
					<br />
					<input value="<?php echo(htmlspecialchars($prj_obj->url)); ?>" class="required prjurl fixed" type="text" size="60" name="url" id="url">
				</div>
				<div>
					Project description: <br />
					<textarea name="description" id="description" class="fixed" rows="8" cols="60"><?php echo(htmlspecialchars($prj_obj->description)); ?></textarea>
				</div>
				<div>
					<input type="hidden" name="_id" id="_id" value="<?php echo($prj_obj->id); ?>">
					<input class="button" id="action" name="action" type="submit" value="Update">
				</div>
			</form>
		</div>
		<div class="grid_6">
			<h2>Security</h2>
			<form method="post" id="update_security">
				<div>
					Cipher
					<br />
					<select name="cipher" id="cipher">
						<option value="none" <?php if ($prj_obj->cipher == "none"): ?>selected="selected"<?php endif ?>>none</option>
						<option value="128" <?php if ($prj_obj->cipher == "128"): ?>selected="selected"<?php endif ?>>AES-128-CBC</option>
						<option value="192" <?php if ($prj_obj->cipher == "192"): ?>selected="selected"<?php endif ?>>AES-192-CBC</option>
						<option value="256" <?php if ($prj_obj->cipher == "256"): ?>selected="selected"<?php endif ?>>AES-256-CBC</option>
					</select>
					<span id="span_cipher"><?php if($prj_obj->cipher != "none") { echo("key: ".substr($prj_obj->api_secret, 0, intval($prj_obj->cipher)/8)); } ?></span>
				</div>
				<div>
					<input type="hidden" name="_id" id="_id" value="<?php echo($prj_obj->id); ?>">
					<input class="button" id="action" name="action" type="submit" value="Update security">
				</div>
			</form>
			<?php
			//check if current user is the owner of the project
			if($current_user_is_admin)
			{
			?>
			<p>&nbsp;</p>
			<h2>Developers</h2>
			<ul>
			<?php
			if($prj_obj->developers)
			{
				data::open_conn();
				for ($i=0; $i < sizeof($prj_obj->developers); $i++) 
				{
					//only users different from current
					if(sprintf("%s", $prj_obj->developers[$i]) != pb_dev_id())
					{
						$usr = developer_manager::get_by_id($prj_obj->developers[$i]);
						if($usr != null)
						{
							echo("<li>");
							echo("<a class=\"action\" title=\"revoke\" href=\"javascript:revoke_perm('".sprintf("%s", $usr->id)."');\">r</a>");
							echo("email: ".htmlspecialchars($usr->email));
							echo("</li>");
						}
					}
				}
				data::close_conn();
			}
			?>
			</ul>
			<form method="post" id="add">
				<div>Email (must be a valid pebblecube user):
					<br />
					<input class="required email fixed" type="text" size="40" name="email" id="email">
				</div>
				<input type="hidden" name="_id" id="_id" value="<?php echo($prj_obj->id); ?>">
				<input type="submit" class="button" id="action" name="action" value="Add">
			</form>
			<p>&nbsp;</p>
			<h2>Remove prj</h2>
			<form method="post" id="delete">
				<input type="hidden" name="_id" id="_id" value="<?php echo($prj_obj->id); ?>">
				<input class="button" onclick="return confirm('sure?');" type="submit" id="action" name="action" value="Delete">
			</form>
			<?php
			}
			?>
	</div>		
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>	
<?php 
/*
<div style="float:left;width:50%;">
	<div style="margin-right:10px">
		<h2>Links</h2>
		<?php
		if($prj_obj->links)
		{
			echo("<ul>");
			for ($i=0; $i < sizeof($prj_obj->links); $i++) 
			{
				echo("<li>");
				echo("<a class=\"action\" title=\"delete\" href=\"javascript:del_link(".$i.")\">d</a>");
				echo(sprintf("%s - %s", $prj_obj->links[$i]->url, $prj_obj->links[$i]->description));
				echo("</li>");
			}
			echo("</ul>");
		}
		?>
		<form method="post" id="links">
			<div>url:
				<br />
				<input class="required" type="text" size="40" name="url" id="url" />
			</div>
			<div>
				description:<br />
				<input class="required" type="text" size="40" name="description" id="description" />
			</div>
			<div>
				<input type="hidden" name="_id" id="_id" value="<?php echo($prj_obj->id); ?>" />
				<input class="button" id="action" name="action" type="submit" value="add link" />
			</div>
		</form>
		<p>&nbsp;</p>
		<h2>files</h2>
		<?php
		if($prj_obj->files)
		{
			echo("<ul>");
			for ($i=0; $i < sizeof($prj_obj->files); $i++) 
			{
				echo("<li>");
				echo("<a class=\"action\" title=\"delete\" href=\"javascript:del_file(".$i.")\">d</a>");
				echo(sprintf("<a target=\"_blank\" href=\"%s\">%s</a> - %s", $prj_obj->files[$i]->url($prj_obj->id), $prj_obj->files[$i]->file_name, $prj_obj->files[$i]->description));
				echo("</li>");
			}
			echo("</ul>");
		}
		?>
		<form method="post" id="files" enctype="multipart/form-data">
			<div>
				file:<br />
				<input type="file" class="required" name="file" value="" id="file" />
			</div>
			<div>
				description:<br />
				<input type="text" size="40" name="description" id="description" />
			</div>
			<div>
				<input type="hidden" name="_id" id="_id" value="<?php echo($prj_obj->id); ?>">
				<input class="button" id="action" name="action" type="submit" value="add file">
			</div>
		</form>
		<p>&nbsp;</p>
		<h2>screenshots</h2>
		<?php
		if($prj_obj->screenshots)
		{
			echo("<ul>");
			for ($i=0; $i < sizeof($prj_obj->screenshots); $i++) 
			{
				echo("<li>");
				echo("<a class=\"action\" title=\"delete\" href=\"javascript:del_screenshot(".$i.")\">d</a>");
				echo(sprintf("<a target=\"_blank\" href=\"%s\">%s</a> - %s", $prj_obj->screenshots[$i]->url($prj_obj->id), $prj_obj->screenshots[$i]->file_name, $prj_obj->screenshots[$i]->description));
				echo("</li>");
			}
			echo("</ul>");
		}
		?>
		<form method="post" id="screenshot" enctype="multipart/form-data">
			<div>
				file:<br />
				<input type="file" class="required" name="file" value="" id="file" />
			</div>
			<div>
				description:<br />
				<input type="text" size="40" name="description" id="description" />
			</div>
			<div>
				<input type="hidden" name="_id" id="_id" value="<?php echo($prj_obj->id); ?>">
				<input class="button" id="action" name="action" type="submit" value="add screenshot">
			</div>
		</form>
		<p>&nbsp;</p>
	</div>
</div>
*/
?>