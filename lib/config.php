<?php
//****************************************************************
// database settings

/**
 * database name
 */
define('DB_NAME', 'pebblecube');

/**
 * database user
 */
define('DB_USER', '');

/**
 * database password
 */
define('DB_PASSWORD', '');

/**
 * database password
 */
define('DB_AUTH_REQUIRED', FALSE);

/**
 * database password
 */
define('DB_HOST', 'mongodb://localhost:27017');

//****************************************************************

//****************************************************************
// cache settings

/**
 * cache flag
 */
define('MEMCACHED_ENABLED', FALSE);

/**
 * cache host
 */
define('MEMCACHED_HOST', '127.0.0.1');

/**
 * cache port
 */
define('MEMCACHED_HOST_PORT', 11211);

/**
 * cache standard expire, 10 minutes
 */
define('MEMCACHED_DEFAULT_EXPIRE', 600);

/**
 * one hour cache
 */
define('MEMCACHED_HOUR_EXPIRE', 3600);
//****************************************************************

/**
 * Encrypt key
 */
define('GLOBAL_RIJNDAEL_KEY', 'pebblecube');

/**
 * user cookie name
 */
define('GLOBAL_DEV_COOKIE', 'pbd');

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config_paths.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config_s3.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config_urls.php';
?>