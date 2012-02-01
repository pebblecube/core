<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/developer_manager.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/project_manager.php");
pb_redirect_not_logged("/devs/account");

data::open_conn();
$dev_id = pb_dev_id();
$dev_obj = developer_manager::get_by_id($dev_id);
data::close_conn();

$action = isset($_POST['action']) ? $_POST['action'] : '';
if(strtolower($action) == "update")
{
	$username = isset($_POST['username']) ? $_POST['username'] : '';
	if(!empty($username))
	{
		//update commmon user data
		$dev_obj->username = $username;
		developer_manager::update_developer($dev_obj);
		header("location: /devs/account");
	}
}

if(strtolower($action) == "change")
{
	//change email if new one doesn't exists
	$email = isset($_POST['email']) ? $_POST['email'] : '';
	if(!empty($email))
	{
		if(!developer_manager::developer_exists($email))
		{
			$dev_obj->email = $email;
			developer_manager::update_developer($dev_obj);
			header("location: /devs/account");
		}
		else
		{	
			$err_message = "email already in db";
		}
	}
}

if(strtolower($action) == "reset")
{
	$old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
	$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
	//check old_password
	if($dev_obj->password == md5($old_password))
	{
		$dev_obj->password = md5($new_password);
		developer_manager::update_developer($dev_obj);
		header("location: /devs/account");
	}
	else
	{	
		$err_message = "wrong password";
	}
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Account settings</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">	
		$(document).ready(function(){
		    $("#change_details").validate({
				submitHandler: function(form) {
					if(confirm('are you sure?'))
						form.submit();
				}
			});
			
		    $("#change_password").validate({
				submitHandler: function(form) {
					if(confirm('are you sure?'))
						form.submit();
				}
			});
			
		    $("#change_email").validate({
				submitHandler: function(form) {
					if(confirm('are you sure?'))
						form.submit();
				}
			});
		});
		
		function revoke_perm(conn)
		{
			if(confirm('are you sure?'))
				top.location.href = '/devs/revoke?t=' + escape(conn);
		}
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Account settings</h1>	
			<?php
			if(isset($err_message))
				echo("<h2 class=\"error\">$err_message</h2>")
			?>	
		</div>
		<div class="grid_6">
			<form method="post" id="change_details">
				<div>
					Username / Full name:
					<br />
					<input class="required fixed" value="<?php echo $dev_obj->username; ?>" type="text" size="40" name="username" id="username">
				</div>
				<div><input class="button" type="submit" id="action" name="action" value="Update"></div>
			</form>
			<p>&nbsp;</p>
			<h2>Change password</h2>
			<form method="post" id="change_password">
				<div>
					Old password:
					<br /><input class="required fixed" type="password" size="40" name="old_password" id="old_password">
				</div>
				<div>
					New password:
					<br /><input class="required fixed" type="password" size="40" name="new_password" id="new_password">
				</div>
				<div><input class="button" type="submit" id="action" name="action" value="Reset"></div>
			</form>
		</div>
		<div class="grid_6">	
			<h2>Change email</h2>
			<form method="post" id="change_email">
				<div>
					Email:
					<br />
					<input class="required email fixed" value="<?php echo $dev_obj->email; ?>" type="text" size="40" name="email" id="email">
				</div>
				<div><input class="button" type="submit" id="action" name="action" value="Change"></div>
			</form>
			</div>
		</div>
		<div class='clear'>&nbsp;</div>
		<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
	</body>
	</html>