<?php
/**
 * static functions for memcached 
 *
 **/
class memcache_util
{
	/**
	 * add/replace an object in memcached
	 *
	 * @param string $key 
	 * @param string $value
	 * @param string $expire 
	 * @return bool
	 */
	public static function memcache_set($key, $value, $expire = 0) 
	{ 
		if(MEMCACHED_ENABLED)
		{
			$memcache_obj = memcache_connect(MEMCACHED_HOST, MEMCACHED_HOST_PORT);
	    	return memcache_set($memcache_obj, $key, $value, MEMCACHE_COMPRESSED, $expire);
		}
		//if not implemented always true
		return true;
	}
	
	/**
	 * returns a cache object
	 *
	 * @param string $key 
	 * @return object|bool returns false if memcache is not enabled
	 */
	public static function memcache_get($key)
	{
		if(MEMCACHED_ENABLED)
		{
			$memcache_obj = memcache_connect(MEMCACHED_HOST, MEMCACHED_HOST_PORT);
	    	return memcache_get($memcache_obj, $key);
		}
		//not implemented false so hits the database
		return false;
	}
	
	/**
	 * deletes an ojbect in cache
	 *
	 * @param string $key 
	 * @return bool
	 */
	public static function memcache_delete($key)
	{
		if(MEMCACHED_ENABLED)
		{
			$memcache_obj = memcache_connect(MEMCACHED_HOST, MEMCACHED_HOST_PORT);
			memcache_delete($memcache_obj, $key);
		}
	}
	
} // END class
?>