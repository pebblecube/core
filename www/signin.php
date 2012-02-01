<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/developer_manager.php");

if(pb_is_dev_logged())
	header("location: /devs/dashboard", true);

$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$remember = isset($_POST['remember']) ? $_POST['remember'] : 0;
$r = isset($_REQUEST['r']) ? $_REQUEST['r'] : '';

if(!empty($email) && !empty($password))
{
	$logged = FALSE;
	data::open_conn();
	if($user = developer_manager::authenticate($email, $password))
	{
		developer_manager::release_auth_cookie($user, $remember);
		$logged = TRUE;
	}
	data::close_conn();
	
	if($logged)
	{
		if(empty($r))
			header("location: /devs/dashboard", true);
		else
			header("location: ".pb_RIJNDAEL_decrypt($r), true);
	}
	else
	{
		$err_message = "Invalid email or password";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Signin</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#signin").validate();
		});
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Signin</h1>
			<?php
			if(isset($err_message))
				echo("<h2 class=\"error\">$err_message</h2>")
			?>
			<form method="post" id="signin">
				<div>Email:<br/><input class="required email" type="text" size="40" name="email" id="email"></div>
				<div>Password:<br/><input class="required" type="password" size="40" name="password" id="password"></div>
				<div>
					<input type="hidden" value="<?php echo(htmlspecialchars($r)); ?>" name="r" id="r">
					<input class="button" type="submit" value="Submit">
					<input type="checkbox" id="remember" name="remember" value="1">
					<label for="remember">Keep me logged in</label>
				</div>
			</form>
		</div>
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>