<?php
/**
 * /dorm/lib/core/dorm_helper.php - Contains auxiliary methods for general functionality not related specifically
 *
 */

/* Shorthand helper to retrieve the global Dorm instance */
function dorm() {
	global $dorm;
	return $dorm;
}

function dorm_is_installed() {
	return false;
}

function dorm_redirect($uri) {
	header('Location: ' . $uri);
}

/* log to a file */
function log_message($type, $msg) {
}

function debug() {
	$args = func_get_args();

	foreach($args as $a) {
		if(is_object($a) || is_array($a)) {
			echo print_r($a) . ' ';
		} else {
			echo $a . ' ';
		}
	}

	//add a line break
	if(php_sapi_name() == "cli") {
		echo "\n";
	} else {
		echo "<br/>";
	}
}

function now() {
	return date('m/d/Y h:i:s a', time());
}

function fill_input_object($obj) {
	$className = "";
	$result = null;

	if(is_string($obj)) {
		$className = $obj;
		$result = new $className;
	} else if (is_object($obj)) {
		$className = get_class($obj);
		$result = $obj;
	}

	$props = get_class_vars($className);
	$input = json_decode(file_get_contents('php://input'), true);

	foreach($props as $p=>$v) {
		$result->$p = $input[$p];
	}

	return $result;
}

function fill_object($obj) {
	global $dorm;
	$className = "";
	$result = null;

	if(is_string($obj)) {
		$className = $obj;
		$result = new $className;
	} else if (is_object($obj)) {
		$className = get_class($obj);
		$result = $obj;
	}

	$props = get_class_vars($className);
	//$input = json_decode(file_get_contents('php://input'), true);
	
	foreach($props as $p=>$v) {
		$result->$p = $dorm->input->post($p); //input[$p];
	}

	return $result;
}


function idx($array, $key, $default = null) {
	return isset($array[$key]) ? $array[$key] : $default;
}

function dateFromFormat($format, $date)
{       
    //$is_pm  = (stripos($date, 'PM') !== false);
    //$date   = str_replace(array('AM', 'PM'), '', $date);
    //$format = str_replace('A', '', $format);
    $date   = DateTime::createFromFormat(trim($format), trim($date));
/*
    if ($is_pm)
    {
        $date->modify('+12 hours');
    }
*/
    
    return $date;
}

//"Logs" the user in for the current session
function set_current_user($user) {
	$_SESSION["current_user"] = $user;
}

?>