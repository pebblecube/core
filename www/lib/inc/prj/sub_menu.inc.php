<div id="sub-header">
	<div id="prj-name"><?php echo(htmlspecialchars($prj_obj->name)); ?></div>
	<ul>
		<li><a title="Details" href="/prj/<?php echo($prj_id); ?>">Details</a></li>
		<li><a title="Stats settings" href="/prj/<?php echo($prj_id); ?>/statssettings">Stats settings</a></li>
		<li><a title="Stats" href="/stats/<?php echo($prj_id); ?>?sessions">Stats</a></li>
		<li><a title="Functions" href="/prj/<?php echo($prj_id); ?>/functions">Functions</a></li>
		<li><a title="Users" href="/prj/<?php echo($prj_id); ?>/users">Users</a></li>
		<li><a title="Scoreboards" href="/prj/<?php echo($prj_id); ?>/scoreboards">Scoreboards</a></li>
		<li><a title="Achievements" href="/prj/<?php echo($prj_id); ?>/achievements">Achievements</a></li>
		<li><a title="Maps" href="/prj/<?php echo($prj_id); ?>/maps">Maps</a></li>
		<li><a title="Exports" href="/prj/<?php echo($prj_id); ?>/exports">Exports</a></li>
		<?php 
		//temporary suspended
		/* <li><a title="design" href="/prj/<?php echo($prj_id); ?>/design">design</a></li> */ ?>
	</ul>
</div>