<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");

data::open_conn();

var_dump(data::$database->execute(file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/../lib/mrs/sessions_stats_last_x_days.js")));
var_dump(data::$database->execute(file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/../lib/mrs/events_stats_last__x_days.js")));

data::open_conn();
?>