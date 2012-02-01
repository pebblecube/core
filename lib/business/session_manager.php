<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pb.php';

/**
 * api sessions manager
 *
 **/
class session_manager 
{
	/**
	 * Starts a new API session
	 *
	 * @param session $session
	 * @return MongoId|Null
	 */
	public static function start($session)
	{
		$sessions = data::$database->sessions;
		$session_array = $session->toArray();
		
		if($sessions->insert($session_array))
		{
			$session->id = $session_array['_id'];
			memcache_util::memcache_set(sprintf("session_%s", $session->id), $session, MEMCACHED_HOUR_EXPIRE);
			return $session->id;
		}
		else
		{
			return NULL;
		}
	}
	
	/**
	 * Stops an API session
	 *
	 * @param string|MongoId $session_id 
	 * @return int delta between start and stop
	 */
	public static function stop($session_id, $time_stop)
	{
		if(!is_object($session_id))
			$session_id = new MongoId($session_id);

		//update
		$sessions = data::$database->sessions;
		$sessions->update(
			array('_id' => $session_id),
			array('$set' => array('time_stop' => (int)$time_stop))
		);
		
		//remove from cache
		memcache_util::memcache_delete(sprintf("session_%s", $session_id));
		
		//return time
		return $time_stop;
	}
	
	/**
	 * saves a event into the collection
	 *
	 * @param session_event $event
	 * @return mongoId
	 */
	public static function send_event($event)
	{
		$sessions_events = data::$database->sessions_events;
		$event_array = $event->toArray();
		$sessions_events->insert($event_array);
		return $event_array['_id'];
	}
	
	/**
	 * returns and active user session
	 *
	 * @param string $session_id 
	 * @param string $project_id 
	 * @return session|null
	 */
	public static function get_active_session($session_id, $project_id = NULL)
	{	
		if(!is_object($session_id))
			$session_id = new MongoId($session_id);
		
		//search in cache... if it's in cache means that is active
		$session = memcache_util::memcache_get(sprintf("session_%s", $session_id));	
		if(!$session)
		{
			$sessions = data::$database->sessions;
			//search in db, maybe cache key is expired
			$session_array = $sessions->findOne(array('_id' => $session_id, 'time_stop' => NULL));
			$session = isset($session_array) ? new session($session_array) : NULL;
		}
		
		//check project id
		if(isset($project_id) && isset($session))
		{
			if(!is_object($project_id))
				$project_id = new MongoId($project_id);
				
			return (sprintf("%s", $session->project_id) == sprintf("%s", $project_id)) ? $session : NULL;
		}
		
		//return session
		return $session;
	}
	
	public static function track_map_segment($map_segment) {
		$sessions_map_segments = data::$database->sessions_map_segments;
		$map_segment_array = $map_segment->toArray();
		$sessions_map_segments->insert($map_segment_array);
		return $map_segment_array['_id'];
	}
	
} // END class
?>