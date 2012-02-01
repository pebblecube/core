<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pb.php';

/**
 * user accounts manager
 *
 **/
class account_manager 
{
	
	/**
	 * authenticate email and password and return user object
	 *
	 * @param string $email
	 * @param string $password
	 * @return user
	 **/
	public static function authenticate($email, $password, $project_id = NULL)
	{
		//check valid email
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			throw new Exception('invalid email');
		
		if($project_id != NULL)
		{
			if(!is_object($project_id))
				$user_id = new MongoId($project_id);
		}
		
		$users = data::$database->users;
		$user_array = $users->findOne(array('email' => strtolower($email), 'status' => 1, 'project_id' => $project_id));
		
		if(!is_array($user_array))
			return NULL;
		else
			return $user_array['password'] == md5($password) ? new user($user_array) : NULL;
	}
	
	/**
	 * returns user object
	 *
	 * @param mix $user_id
	 * @return user
	 **/
	public static function get_by_id($user_id, $cache = TRUE)
	{
		if(!is_object($user_id))
			$user_id = new MongoId($user_id);
		
		$user = memcache_util::memcache_get(sprintf("user_%s", $user_id));
		
		if(!$user || !$cache)
		{
			//find user
			$users = data::$database->users;
			$user_array = $users->findOne(array('_id' => new MongoId($user_id)));
			
			//check if null
			if($user_array != null)
			{
				//build object
				$user = new user($user_array);
				//save to cache
				memcache_util::memcache_set(sprintf("user_%s", $user_id), $user);
				//return value
				return $user;
			}
			//set null obj in cache with a timeout
			memcache_util::memcache_set(sprintf("user_%s", $user_id), NULL, MEMCACHED_DEFAULT_EXPIRE);
			return NULL;
		}
		else
		{
			return $user; //return cache value
		}
	}
	
	/**
	 * checks if user exists
	 *
	 * @param string $email
	 * @param string $current
	 * @param bool $return_user
	 * @return bool|user
	 */
	public static function user_exists($field, $value, $project_id = NULL, $current = "", $return_user = FALSE)
	{
		$users = data::$database->users;
		
		if($project_id != NULL)
		{
			if(!is_object($project_id))
				$user_id = new MongoId($project_id);
		}
		
		//check if exclude current current user email
		if(empty($current))
			$user_array = $users->findOne(array($field => strtolower($value), 'status' => 1, 'project_id' => $project_id));
		else
			$user_array = $users->findOne(array($field => strtolower($value), $field => array('$ne' => strtolower($current)), 'status' => 1, 'project_id' => $project_id));
		
		//check if return bool or user object
		if($return_user && isset($user_array))
		{
			return new user($user_array);
		}
		else
		{
			return isset($user_array);
		}
	}
	
	public static function user_exists_username($username, $project_id = NULL)
	{
		if(preg_match_all('/^[a-zA-Z0-9._-]$/i', $username, $out) === FALSE)
			throw new Exception('invalid username');
		
		return account_manager::user_exists('username', $username, $project_id);
	}
	
	public static function user_exists_email($email, $project_id)
	{
		//check valid email
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			throw new Exception('invalid email');
			
		return account_manager::user_exists('email', $email, $project_id);
	}
	
	/**
	 * creates a new user
	 * 0: user already exists
	 * -1: error in insert
	 * id: mongoid of the row inserted
	 *
	 * @param user $user 
	 * @return mix
	 */
	public static function create_user($user)
	{
		//check valid email
		if(!filter_var($user->email, FILTER_VALIDATE_EMAIL))
			throw new Exception('invalid email');
			
		//check email exists
		if(!account_manager::user_exists_email($user->email, $user->project_id)) //&& !account_manager::user_exists_username($user->username, $user->project_id)
		{
			$user_array = $user->toArray();
			//create user
			$users = data::$database->users;
			return $users->insert($user_array) ? $user_array['_id'] : -1;
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * Updates an user object
	 *
	 * @param string $user 
	 * @return void
	 */
	public static function update_user($user)
	{
		$users = data::$database->users;
		$users->update(array("_id" => $user->id), $user->toArray());
		//remove cache object
		memcache_util::memcache_delete(sprintf("user_%s", $user->id));
	}

	
	/**
	 * checks if a token exists
	 *
	 * @param string $auth_token 
	 * @return array|null Returns record matching the search or NULL. 
	 */
	public static function exists_auth_token($auth_token, $project_id = NULL)
	{
		/*
			TODO add memcached
		*/
		
		if($project_id != NULL)
		{
			if(!is_object($project_id))
				$project_id = new MongoId($project_id);
		}
		
		$users = data::$database->users;
		return $users->findOne(array('auth_token' => $auth_token, 'status' => 1, 'project_id' => $project_id));
	}
	
	/**
	 * checks if the given token has access to the selected project
	 *
	 * @param string $token_id 
	 * @param string $project_id 
	 * @return array|null
	 */
	public static function check_access_token_project($token_id, $project_id)
	{
		/*
			TODO add memecached
		*/
		if(!is_object($token_id))
			$token_id = new MongoId($token_id);

		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
				
		$tokens = data::$database->tokens;
		return $tokens->findOne(array('_id' => $token_id, 'project_id' => $project_id, 'status' => 1));
	}
	
	/**
	 * creates/updates user token associated to the application
	 *
	 * @param string|MongoId $user_id 
	 * @param string|MongoId $project_id
	 * @param string $api_sig
	 * @return array
	 */
	public static function create_api_access_token($user_id, $user_name, $project_id, $api_sig)
	{
		if(!is_object($user_id))
			$user_id = new MongoId($user_id);
			
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		$tokens = data::$database->tokens;
		$obj = array('user_id' => $user_id, 'user_name' => $user_name, 'project_id' => $project_id, 'timestamp' => time(), 'api_sig' => $api_sig, 'status' => 1);
		
		$return_token = NULL;
		
		//check if exists
		if($token = $tokens->findOne(array('user_id' => $user_id, 'project_id' => $project_id, 'status' => 1)))
		{
			//update
			$tokens->update(
							array('user_id' => $user_id, 'project_id' => $project_id), 
							array('$set' => array('timestamp' => time()))
							);
			$return_token = $token;
		}
		else
		{
			//insert
			$return_token = $tokens->insert($obj) ? $obj : NULL;
		}
		
		if(isset($return_token))
		{
			$users = data::$database->users;
			//insert or update user object tokens
			$users->update(
							array('_id' => $user_id),
							array('$addToSet' => array('tokens' => array('token_id' => $return_token['_id'], 'project_id' => $return_token['project_id'])))
							);
		}
		
		//remove cache object for user
		memcache_util::memcache_delete(sprintf("user_%s", $user_id));

		//return object
		return $return_token;
	}
	
	/**
	 * revoce access token to the selected user
	 *
	 * @param string $user_id 
	 * @param string $token_id 
	 * @return void
	 */
	public static function revoke_api_access_token($user_id, $token_id)
	{
		if(!is_object($token_id))
			$token_id = new MongoId($token_id);
			
		if(!is_object($user_id))
			$user_id = new MongoId($user_id);
		
		//set token deleted
		$tokens = data::$database->tokens;
		$tokens->update(
			array('_id' => $token_id, 'user_id' => $user_id),
			array('$set' => array('status' => 0))
		);
		
		//remove token from user object
		$users = data::$database->users;
		$users->update(
			array('_id' => $user_id),
			array('$pull' => array('tokens' => array('token_id' => $token_id)))
		);
		
		//remove cache object for user
		memcache_util::memcache_delete(sprintf("user_%s", $user_id));
	}
	
} // END class
?>