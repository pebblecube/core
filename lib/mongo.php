<?php
/**
 * static class mongodb
 *
 **/
class data
{
	/**
	 * mongodb connection
	 *
	 * @var Mongo
	 **/
	public static $connection;
	
	/**
	 * mongodb database
	 *
	 * @var MongoDB
	 **/
	public static $database;
	
	
	/**
	 * returns mongodb connection
	 *
	 * @return MongoDB
	 **/
	public static function open_conn()
	{
		try
		{
			data::$connection = new Mongo(DB_HOST);
			data::$database = data::$connection->selectDB(DB_NAME);
			if(DB_AUTH_REQUIRED)
			{
				$result = data::$database->authenticate(DB_USER, DB_PASSWORD);
				if($result['ok'] == 0)
				{
					 throw new Exception('authenticate failed');
				}
			}
			return data::$database;
		}
		catch(Exception $e)
		{
			die("Could not connect: ".$e);
		}
	}
	
	/**
	 * close mongodb connection
	 *
	 * @return void
	 **/
	public static function close_conn()
	{
		data::$connection->close();
	}
	
} // END class 
?>