<?php 
/**
 * checks if upload on S3 or keep locally
 */
define('S3_ENABLED', FALSE);

define('AWS_KEY', '');

define('AWS_SECRET_KEY', '');

/**
 * path for avatars on S3
 */
define('GLOBAL_AVATAR_S3_PATH', '');

/**
 * Url for headers on S3
 */
define('GLOBAL_HEADER_S3_PATH', '');

/**
 * Url for screenshots on S3
 */
define('GLOBAL_SCREENSHOTS_S3_PATH', '');

/**
 * Url for downloads on S3
 */
define('GLOBAL_DOWNLOADS_S3_PATH', '');

/**
 * Url for stats on S3
 */
define('GLOBAL_STATS_S3_PATH', '');

/**
 * Url for saved games on S3
 */
define('GLOBAL_GAMES_S3_BUCKET', 'bucketname');

/**
 * Url for achievements
 */
define('GLOBAL_ACHIEVEMENTS_S3_BUCKET', 'bucketname');
?>