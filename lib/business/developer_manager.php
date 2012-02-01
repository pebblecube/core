<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pb.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'util' . DIRECTORY_SEPARATOR . 'avatar_generator.php';

/**
 * developer accounts manager
 *
 **/
class developer_manager 
{
	
	/**
	 * authenticate email and password and return developer object
	 *
	 * @param string $email
	 * @param string $password
	 * @return developer
	 **/
	public static function authenticate($email, $password)
	{
		//check valid email
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			throw new Exception('invalid email');
		
		$developers = data::$database->developers;
		$developer_array = $developers->findOne(array('email' => strtolower($email), 'status' => 1));
		return $developer_array['password'] == md5($password) ? new developer($developer_array) : NULL;
	}
	
	/**
	 * returns developer object
	 *
	 * @param mix $developer_id
	 * @return developer
	 **/
	public static function get_by_id($developer_id, $cache = TRUE)
	{
		if(!is_object($developer_id))
			$developer_id = new MongoId($developer_id);
		
		$developer = memcache_util::memcache_get(sprintf("developer_%s", $developer_id));
		
		if(!$developer || !$cache)
		{
			//find developer
			$developers = data::$database->developers;
			$developer_array = $developers->findOne(array('_id' => new MongoId($developer_id)));
			
			//check if null
			if($developer_array != null)
			{
				//build object
				$developer = new developer($developer_array);
				//save to cache
				memcache_util::memcache_set(sprintf("developer_%s", $developer_id), $developer);
				//return value
				return $developer;
			}
			//set null obj in cache with a timeout
			memcache_util::memcache_set(sprintf("developer_%s", $developer_id), NULL, MEMCACHED_DEFAULT_EXPIRE);
			return NULL;
		}
		else
		{
			return $developer; //return cache value
		}
	}
	
	/**
	 * releases a developer encrypted cookie
	 *
	 * @param string $developer 
	 * @param int $remember 
	 * @return void
	 */
	public static function release_auth_cookie($developer, $remember)
	{
		//check if keep logged in
		if(is_numeric($remember))
			$expire = $remember > 0 ? time() + 1296000 : 0; //2 weeks or only the session
		//release cookie
		setcookie(GLOBAL_DEV_COOKIE, pb_RIJNDAEL_encrypt(sprintf("OK:%s:%s", $developer->id, $developer->email)), $expire, "/", "", false, true);
	}
	
	/**
	 * checks if developer exists
	 *
	 * @param string $email 
	 * @param string $current 
	 * @param bool $return_developer 
	 * @return bool|developer
	 */
	public static function developer_exists($email, $current = "", $return_developer = FALSE)
	{
		$developers = data::$database->developers;
		
		//check if exclude current current developer email
		if(empty($current))
			$developer_array = $developers->findOne(array('email' => strtolower($email), 'status' => 1));
		else
			$developer_array = $developers->findOne(array('email' => strtolower($email), 'email' => array('$ne' => $current), 'status' => 1));
		
		//check if return bool or developer object
		if($return_developer && isset($developer_array))
		{
			return new developer($developer_array);
		}
		else
		{
			return isset($developer_array);
		}
	}
	
	/**
	 * creates a new developer
	 * 0: developer already exists
	 * -1: error in insert
	 * id: mongoid of the row inserted
	 *
	 * @param developer $developer 
	 * @return mix
	 */
	public static function create_developer($developer)
	{
		//check valid email
		if(!filter_var($developer->email, FILTER_VALIDATE_EMAIL))
			throw new Exception('invalid email');
			
		//check email exists
		if(!developer_manager::developer_exists($developer->email))
		{
			$developer_array = $developer->toArray();
			//create developer
			$developers = data::$database->developers;
			return $developers->insert($developer_array) ? $developer_array['_id'] : -1;
		}
		else
		{
			return 0;
		}
	}
	
	public static function beta_developer_exists($email)
	{
		$developers = data::$database->developers_beta;
		$developer_array = $developers->findOne(array('email' => strtolower($email)));
		return isset($developer_array);
	}
	
	public static function beta_developer_token_exists($token)
	{
		$developer_id = new MongoId($token);
		$developers = data::$database->developers_beta;
		$developer_array = $developers->findOne(array('_id' => $developer_id));
		return isset($developer_array);
	}
	
	public static function create_beta_developer($developer)
	{
		//check valid email
		if(!filter_var($developer->email, FILTER_VALIDATE_EMAIL))
			throw new Exception('invalid email');
		
		//check email exists
		if(!developer_manager::beta_developer_exists($developer->email))
		{
			$developer_array = $developer->toArray();
			//create developer
			$developers = data::$database->developers_beta;
			return $developers->insert($developer_array) ? $developer_array['_id'] : -1;
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * Updates an developer object
	 *
	 * @param string $developer 
	 * @return void
	 */
	public static function update_developer($developer)
	{
		$developers = data::$database->developers;
		$developers->update(array("_id" => $developer->id), $developer->toArray());
		//remove cache object
		memcache_util::memcache_delete(sprintf("developer_%s", $developer->id));
	}
	
	/**
	 * creates a new random developer avatar
	 *
	 * @param developer $developer 
	 * @return void
	 */
	public static function create_default_avatar($developer)
	{
		//generates and save avatar
		avatar_generator::generate_random(sprintf("%s", $developer->id), 7, array(98, 49));
		if(S3_ENABLED)
		{
			/*
				TODO upload file to S3
			*/
		}
	}

} // END class
?>