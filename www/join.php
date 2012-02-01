<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/business/developer_manager.php");

if(pb_is_dev_logged())
	header("location: /devs/dashboard", true);

$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$username = isset($_POST['username']) ? $_POST['username'] : '';

if(!empty($email) && !empty($password) && !empty($username)) {
	data::open_conn();
	
	//build developer
	$developer = new developer();
	$developer->email = $email;
	$developer->password = md5($password);
	$developer->status = 1;
	$developer->username = $username;

	//add developer
	$res = developer_manager::create_developer($developer);
	if(is_object($res)) {
		$developer->id = $res;
		//create and store avatar
		developer_manager::create_default_avatar($developer);
		//release cookie
		developer_manager::release_auth_cookie($developer, 0);
		
		//redirect to dashboard
		header("location: /devs/dashboard");
		exit();
	}
	else {
		switch($res) {
			case 0:
				$err_message = "email already exists";
				break;
			default:
				$err_message = "error in insert";
				break;
		}
	}
		
	data::close_conn();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pebblecube - Take back control of your game</title>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header.inc.php");?>
	<script type="text/javascript">
		$(document).ready(function(){
		    $("#join").validate();
		});
	</script>
</head>
<body>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/header_menu.inc.php");?>
	<div class='clear'>&nbsp;</div>
	<div class="container container_12 standardcontent">
		<div class="grid_12">
			<h1>Signup for a beta account</h1>
			<?php
			if(isset($err_message))
				echo("<h2 class=\"error\">$err_message</h2>")
			?>
			<form method="post" id="join">
				<div>username:<br /><input class="required" type="text" size="40" name="username" id="username"></div>
				<div>email:<br /><input class="required email" type="text" size="40" name="email" id="email"></div>
				<div>password:<br /><input class="required" type="password" size="40" name="password" id="password"></div>
				<div><input class="button" type="submit" value="Join"></div>
			</form>
		</div>
	</div>
	<div class='clear'>&nbsp;</div>
	<?php include("{$_SERVER['DOCUMENT_ROOT']}/lib/inc/footer.inc.php");?>
</body>
</html>