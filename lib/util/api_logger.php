<?php 
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pb.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'api_log.php';

class api_logger
{	
	public static function add($apilog)
	{
		$apilogs = data::$database->apilogs;
		$apilogs->insert($apilog);
	}

} // END class
?>