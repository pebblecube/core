<?php
include_once("{$_SERVER['DOCUMENT_ROOT']}/../lib/pb.php");

//remove cookies
pb_clear_dev_cookies();

//redirect
header("location: /");
?>