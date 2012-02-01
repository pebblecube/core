<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pb.php';

/**
 * project manager functions
 *
 **/
class project_manager
{	
	/**
	 * undocumented function
	 *
	 * @param project $project
	 * @return MongoId
	 **/
	public static function add($project)
	{	
		//check if url exists
		if(!project_manager::url_exists($project->url))
		{
			$prj_array = $project->toArray();
			//create project
			$prjs = data::$database->projects;
			return $prjs->insert($prj_array) ? $prj_array['_id'] : -1;
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * returns a project object
	 *
	 * @param mix $project_id 
	 * @return void
	 */
	public static function get_by_id($project_id, $cache = TRUE)
	{
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
			
		$prj = memcache_util::memcache_get(sprintf("project_%s", $project_id));
		
		if(!$prj || !$cache)
		{
			//find project
			$prjs = data::$database->projects;
			$prj_array = $prjs->findOne(array('_id' => $project_id));
			//check if null
			if($prj_array != null)
			{
				//build object
				$prj = new project($prj_array);
				//save to cache
				memcache_util::memcache_set(sprintf("project_%s", $project_id), $prj);
				//return value
				return $prj;
			}
			//set null obj in cache with a timeout
			memcache_util::memcache_set(sprintf("project_%s", $project_id), NULL, MEMCACHED_DEFAULT_EXPIRE);
			return NULL;
		}
		else
		{
			return $prj; //return cache value
		}
	}
	
	/**
	 * updates a project object
	 *
	 * @param project $prj
	 */
	public static function update_project($prj)
	{
		$prjs = data::$database->projects;
		$prjs->update(array("_id" => $prj->id), $prj->toArray());
		
		//remove cache object
		project_manager::delete_cache_keys($prj->id, $prj);
	}
	
	/**
	 * removes a project
	 *
	 * @param string|MongoId $project_id
	 */
	public static function delete_project($project_id)
	{
		//check id
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		//update project status
		$prjs = data::$database->projects;
		$prjs->update(array('_id' => $project_id), array('$set' => array('status' => 0)));
		
		//remove cache object
		project_manager::delete_cache_keys($project_id);
		
		//update project status in all dev associated to this project
		$developers = data::$database->developers;
		$res = $developers->update(array("projects.project_id" => $project_id), array('$set' => array('projects.$.status' => 0)));

		//empty cache
		$query = array("projects.project_id" => $project_id);
		$cursor = $developers->find($query);
		foreach ($cursor as $id => $value) {
			memcache_util::memcache_delete(sprintf("user_%s", $value["_id"]));
		}
	}
	
	/**
	 * checks if url exists
	 *
	 * @return void
	 **/
	public static function url_exists($url)
	{
		$prjs = data::$database->projects;
		$prj_array = $prjs->findOne(array('url' => $url, 'status' => 1), array('_id' => 1));
		return isset($prj_array);
	}
	
	/**
	 * generates random project keys
	 *
	 * @param bool $unique
	 * @return string
	 **/
	public static function gen_random_key($unique = TRUE)
	{
		$key = md5(uniqid(rand(), true));
		if ($unique)
		{
			list($usec,$sec) = explode(' ',microtime());
			$key .= dechex($usec).dechex($sec);
		}
		return $key;
	}
	
	/**
	 * returns a project object with given api_key and api_sig.
	 *
	 * @param string $api_key 
	 * @param string $api_sig 
	 * @return project|null
	 */
	public static function get_by_api_sig($api_key, $api_sig)
	{	
		$prj = memcache_util::memcache_get(sprintf("project_key_%s_sig_%s", $api_key, $api_sig));
		
		if(!$prj)
		{
			//find project
			$projects_coll = data::$database->projects;
			$prj_array = $projects_coll->findOne(array('api_key' => $api_key, 'api_sig' => $api_sig, 'status' => 1), array('api_key' => 1, 'api_sig' => 1, 'api_secret' => 1, 'status' => 1, 'events' => 1, 'scoreboards' => 1, 'achievements' => 1, 'cipher' => 1, 'constants' => 1, 'functions' => 1, 'maps' => 1));
			//check if null
			if($prj_array != null)
			{
				//build object
				$prj = new project($prj_array);
				//save to cache
				memcache_util::memcache_set(sprintf("project_key_%s_sig_%s", $api_key, $api_sig), $prj);
				//return value
				return $prj;
			}
			//set null obj in cache with a timeout
			memcache_util::memcache_set(sprintf("project_key_%s_sig_%s", $api_key, $api_sig), NULL, MEMCACHED_DEFAULT_EXPIRE);
			return NULL;
		}
		else
		{
			return $prj; //return cache value
		}
	}
	
	/**
	 * revoke access to a specific developer
	 *
	 * @param string|MongoId $project_id 
	 * @param string|MongoId $user_id 
	 * @return void
	 */
	public static function revoke_dev_access($project_id, $user_id)
	{
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
			
		if(!is_object($user_id))
			$user_id = new MongoId($user_id);
		
		//remove user id
		$projects = data::$database->projects;
		$projects->update(
			array('_id' => $project_id),
			array('$pull' => array('developers' => $user_id))
		);
		
		//delete cache object
		project_manager::delete_cache_keys($project_id, NULL);
		
		$developers = data::$database->developers;
		$developers->update(
			array('_id' => $user_id, 'projects.project_id' => $project_id), 
			array('$set' => array('projects.$.status' => 0))
		);
		//delete cache object
		memcache_util::memcache_delete(sprintf("user_%s", $user_id));
	}
	
	/**
	 * returns a list of users that has an access token with the project
	 *
	 * @param string $project_id 
	 * @param int $page 
	 * @param int $page_size 
	 * @return cursor returns a cursor for the search results
	 */
	public static function get_access_tokens($project_id, $page, $page_size)
	{
		if(!is_object($project_id))
			$project_id = new MongoId($project_id);
		
		$skip = (int)($page_size * ($page - 1));
		
		$users = data::$database->users;
		return $users->find(array('tokens.project_id' => $project_id), array('username' => 1, 'tokens.token_id' => 1))->skip($skip)->limit($page_size);
	}
	
	/**
	 * saves and associate an header to the project page
	 *
	 * @param string $project_id 
	 * @param string $tmp_name 
	 * @param string $ext 
	 * @return string
	 */
	public static function design_set_custom_header($project_id, $tmp_name, $ext)
	{
		$file_name = "";
		
		//save header
		if (is_uploaded_file($tmp_name))
		{
			$file_name = strtolower($project_id.".".$ext);
			move_uploaded_file($tmp_name, GLOBAL_WWW_FILE_PATH.GLOBAL_HEADER_FOLDER.DIRECTORY_SEPARATOR.$file_name);
			if(S3_ENABLED)
			{
				/*
					TODO upload file to S3
				*/
			}
		}
		
		//return file name
		return $file_name;
	}
	
	public static function save_project_file($project_id, $file)
	{
		$file_name = "";
		//save header
		if (is_uploaded_file($file['tmp_name']))
		{
			$file_name = stripslashes($file['name']);
			if(S3_ENABLED)
			{
				/*
					TODO upload file to S3
				*/
			}
			
			$dir = GLOBAL_WWW_FILE_PATH.GLOBAL_DOWNLOADS_FOLDER.DIRECTORY_SEPARATOR.$project_id;
			if(!is_dir($dir))
				mkdir($dir);

			return move_uploaded_file($file['tmp_name'], GLOBAL_WWW_FILE_PATH.GLOBAL_DOWNLOADS_FOLDER.DIRECTORY_SEPARATOR.$project_id.DIRECTORY_SEPARATOR.$file_name);
		}
		
		return false;
	}
	
	public static function save_project_screenshot($project_id, $file)
	{
		$file_name = "";
		//save header
		if (is_uploaded_file($file['tmp_name']))
		{
			$file_name = stripslashes($file['name']);
			if(S3_ENABLED)
			{
				/*
					TODO upload file to S3
				*/
			}
			
			$dir = GLOBAL_WWW_FILE_PATH.GLOBAL_SCREENSHOTS_FOLDER.DIRECTORY_SEPARATOR.$project_id;
			if(!is_dir($dir))
				mkdir($dir);

			return move_uploaded_file($file['tmp_name'], GLOBAL_WWW_FILE_PATH.GLOBAL_SCREENSHOTS_FOLDER.DIRECTORY_SEPARATOR.$project_id.DIRECTORY_SEPARATOR.$file_name);
		}
		
		return false;
	}
	
	public static function delete_project_file($project_file, $projet_id)
	{
		if(S3_ENABLED)
		{
			/*
				TODO delete from s3
			*/
		}
		if(file_exists($project_file->path($projet_id)))
			unlink($project_file->path($projet_id));
	}
	
	public static function delete_project_screenshot($project_shot, $projet_id)
	{
		if(S3_ENABLED)
		{
			/*
				TODO delete from s3
			*/
		}
		if(file_exists($project_shot->path($projet_id)))
			unlink($project_shot->path($projet_id));
	}
	
	/**
	 * removed project cache keys
	 *
	 * @param MongoId $project_id
	 * @param string|NULL $project 
	 * @return void
	 */
	public static function delete_cache_keys($project_id, $project = NULL)
	{
		memcache_util::memcache_delete(sprintf("project_%s", $project_id));
		if($project != null)
		{
			memcache_util::memcache_delete(sprintf("project_key_%s_sig_%s", $project->api_key, $project->api_sig));
		}
	}
	
} // END class
?>