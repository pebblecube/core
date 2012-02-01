<div id="header">
	<div id="logo">
		<a title="pebblecube" href="/">pebblecube</a>
	</div>
	<div id="menu">
		<ul>
			<?php
			if(pb_is_dev_logged())
			{
				echo("<li><a title=\"dashboard\" href=\"/devs/dashboard\">Dashboard</a></li>");
				echo("<li><a title=\"account\" href=\"/devs/account\">Account</a></li>");
				echo("<li><a title=\"dashboard\" href=\"/docs\">Api docs</a></li>");
				echo("<li><a title=\"signout\" href=\"/signout\">Signout</a></li>");
			}
			else
			{
				echo("<li><a title=\"signin\" href=\"/signin\">Signin</a></li>");
				echo("<li><a title=\"signin\" href=\"/signup\">Signup</a></li>");
			}
			?>
		</ul>
	</div>
</div>