<?php 
/**
 * url path for avatars
 */
define('GLOBAL_AVATAR_URL', '/files/avatar');

/**
 * url for headers
 */
define('GLOBAL_HEADER_URL', '/files/header');

/**
 * url for screenshots
 */
define('GLOBAL_SCREENSHOTS_URL', '/files/screenshots');

/**
 * url for downloads
 */
define('GLOBAL_DOWNLOADS_URL', '/files/downloads');

/**
 * url for stats reports
 */
define('GLOBAL_STATS_URL', '/files/stats');


if(S3_ENABLED)
{
	define('GLOBAL_GAMES_URL', 'https://s3.amazonaws.com/bucketname');
	
	define('GLOBAL_ACHIEVEMENTS_URL', 'https://s3.amazonaws.com/bucketname');
}
else
{
	
	/**
	 * url for saved games
	 */
	define('GLOBAL_GAMES_URL', '/files/games');
	
	define('GLOBAL_ACHIEVEMENTS_URL', '/files/achievements');
}
?>