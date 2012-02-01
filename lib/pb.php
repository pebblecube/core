<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'mongo.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'memcache.php';

function __autoload($class_name) {
	require_once dirname(__FILE__) . '/common' . DIRECTORY_SEPARATOR . $class_name . '.php';
}

//****************************************************************************************************
//apis encrytion uses openssl library for more compability with other languages

function pb_api_RIJNDAEL_encrypt($text, $key, $cipher = "none", $iv = NULL) {
	if($cipher != "none") {
		switch ($cipher) {
			case '256':
				$key = substr($key, 0, 32);
				break;
			case '192':
				$key = substr($key, 0, 24);
				break;
			case '128':
				$key = substr($key, 0, 16);
				break;
		}
		if($iv == NULL) {
			$iv = openssl_random_pseudo_bytes(16);
		}
		if (version_compare(PHP_VERSION, '5.3.3') >= 0) {
			header(sprintf("PC_IV: %s", base64_encode($iv)));
			return openssl_encrypt($text, 'AES-'.$cipher.'-CBC', $key, FALSE, $iv);
		} else {
			header(sprintf("PC_IV: %s", ""));
			return openssl_encrypt($text, 'AES-'.$cipher.'-CBC', $key, FALSE);
		}
	}
	else
		return $text;
}

function pb_api_RIJNDAEL_decrypt($text, $key, $cipher = "none", $iv = NULL) {
	if($cipher != "none") {
		switch ($cipher) {
			case '256':
				$key = substr($key, 0, 32);
				break;
			case '192':
				$key = substr($key, 0, 24);
				break;
			case '128':
				$key = substr($key, 0, 16);
				break;
		}
		if (version_compare(PHP_VERSION, '5.3.3') >= 0) {
			if($iv == NULL && array_key_exists("HTTP_PC_IV", $_SERVER)) {
				$iv = $_SERVER['HTTP_PC_IV'];
			}
			return trim(openssl_decrypt($text, 'AES-'.$cipher.'-CBC', $key, FALSE, base64_decode($iv)));
		} else {
			return trim(openssl_decrypt($text, 'AES-'.$cipher.'-CBC', $key, FALSE));
		}
	}
	else
		return $text;
}
//****************************************************************************************************

/**
 * @param    string $text
 * @return   string
 */
function pb_RIJNDAEL_encrypt($text)
{
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
   	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
   	return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, GLOBAL_RIJNDAEL_KEY, $text, MCRYPT_MODE_ECB, $iv));
}

/**
 * @param    string $text
 * @return   string
 */
function pb_RIJNDAEL_decrypt($text)
{
   	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
   	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
   	return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, GLOBAL_RIJNDAEL_KEY, base64_decode($text), MCRYPT_MODE_ECB, $iv));
}

/**
 * return the dev encrypted cookie
 *
 * @return void
 **/
function pb_get_dev_cookie_value()
{
	return isset($_COOKIE[GLOBAL_DEV_COOKIE]) ? pb_RIJNDAEL_decrypt($_COOKIE[GLOBAL_DEV_COOKIE]) : NULL;
}

/**
 * remove all cookies released
 *
 * @return void
 **/
function pb_clear_dev_cookies()
{
	setcookie(GLOBAL_DEV_COOKIE, "", time() - 3600);
}

/**
 * checks if a dev is logged in checking the dev cookie
 *
 * @return bool
 */
function pb_is_dev_logged()
{
	if($cookie_value = pb_get_dev_cookie_value())
	{
		$cookie_array = explode(":", $cookie_value);
		return $cookie_array[0] == "OK";
	}
	else
		return FALSE;
}

/**
 * returns the dev id sved in the cookie
 *
 * @return int
 */
function pb_dev_id()
{
	if($cookie_value = pb_get_dev_cookie_value())
	{
		$cookie_array = explode(":", $cookie_value);
		return $cookie_array[1];
	}
	return NULL;
}

/**
 * returns an HTML image tag with the avatar of the selected dev
 *
 * @param string $dev_id 
 * @param string $size 
 * @return string
 */
function pb_get_dev_avatar_img($dev_id, $size = 98)
{
	return "<img class=\"avatar\" src=\"".pb_get_dev_avatar_url($dev_id, $size)."\" width=\"".$size."\" height=\"".$size."\" />";
}

/**
 * returns the url of an dev avatar
 *
 * @param string $dev_id 
 * @param string $size 
 * @return string
 */
function pb_get_dev_avatar_url($dev_id, $size = 98)
{
	return GLOBAL_AVATAR_URL."/".$dev_id."-".$size.".png";
}

/**
 * checks if a dev is logged and redirects to login page encrypting the return url
 *
 * @param string $return_url 
 * @return void
 */
function pb_redirect_not_logged($return_url = "")
{
	if(!pb_is_dev_logged())
		header("location: /signin?r=".urlencode(pb_RIJNDAEL_encrypt($return_url)), true);
}

/**
 * returns an HTTP header
 *
 * @param int $code 
 * @param string $message 
 * @return void
 */
function pb_api_error_message($code, $message)
{
	switch($code)
	{
		case 401:
			header('HTTP/1.0 401 Unauthorized');
			break;
		case 403:
			header('HTTP/1.0 403 Forbidden');
			break;
		case 404:
			header('HTTP/1.0 404 Not Found');
			break;
		default:
			header('HTTP/1.0 400 Bad Request');
			break;
	}
	$err_array = array("e" => $message);
	die(json_encode($err_array));
}

/**
 * search an array in an array and returns true or false
 *
 * @param array $needle 
 * @param array $haystack 
 * @return bool
 */
function pb_array_in_array_search($needle, $haystack) 
{
	if (empty($needle) || empty($haystack)) {
        return false;
    }

    foreach ($haystack as $key => $value) {
        $exists = 0;
        foreach ($needle as $nkey => $nvalue) {
            if (!empty($value[$nkey]) && $value[$nkey] == $nvalue) {
                $exists = 1;
            } else {
                $exists = 0;
            }
        }
        if ($exists) return $key;
    }

    return false;
}

/**
 * returns the extension of a file
 *
 * @param string $str 
 * @return string
 */
function pb_get_file_extension($str) {
	$i = strrpos($str,".");
	if (!$i) { return ""; }
	$l = strlen($str) - $i;
	$ext = substr($str,$i+1,$l);
	return $ext;
}

function pb_get_timezone_by_offset($offset)
{
	$offset = $offset*60*60;
	$abbrarray = timezone_abbreviations_list();
	foreach ($abbrarray as $abbr) {
			foreach ($abbr as $city) {
					if ($city['offset'] == $offset) {
						return $city['timezone_id'];
					}
			}
	}
    return "GMT"; 
}

function array2object($data, $class) 
{
    if(!is_array($data)) return $data;

    $object = new stdClass();
    if (is_array($data) && count($data) > 0) {
        foreach ($data as $name=>$value) {
            $name = strtolower(trim($name));
            if (!empty($name)) {
                $object->$name = array2object($value);
            }
        }
    }
    return $object;
}

/**
 * redirects to the standard error page
 *
 * @return void
 */
function error_redirect()
{
	if (!headers_sent()) 
	{
		header("Location: /500", true);
	}
	else
	{
		echo "<script language=\"javascript\" type=\"text/javascript\">";
		echo "top.location.href = '/500'";
		echo "</script>";
		exit();
	}
}

/**
 * generic error handler
 *
 * @param string $level 
 * @param string $message 
 * @param string $file 
 * @param string $line 
 * @param string $context 
 * @return void
 */
function error_handler($level, $message, $file, $line, $context) 
{
	echo("{$level}\n{$message}\n{$file}\n{$line}\n{$context}");
	//error_redirect();
}

/**
 * exceptions handler
 *
 * @param string $exception 
 * @return void
 */
function exception_handler($exception) 
{
	var_dump($exception);
	//error_redirect();
}

set_error_handler("error_handler");
set_exception_handler("exception_handler");
?>