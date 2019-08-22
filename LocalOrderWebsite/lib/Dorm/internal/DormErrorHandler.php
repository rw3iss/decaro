<?php
/* DormErrorHandler - overrides the PHP error handler */

namespace Dorm;

class DormErrorHandler {

	function __construct() {
		set_error_handler(array($this, "errorHandler"));
	}

	function errorHandler($errno, $errstr, $errfile, $errline) {
		global $dorm;

		$dorm->response->error_response('Error: ' . $errno . ' : ' . $errstr . ' @ ' . $errfile . ':' . $errline . '<br/>Stack trace: ' .
				$this->generateCallTrace() );
		
		exit();
	}

	function generateCallTrace()
	{
	    $e = new \Exception();
	    $trace = explode("\n", $e->getTraceAsString());
	    // reverse array to make steps line up chronologically
	    $trace = array_reverse($trace);
	    array_shift($trace); // remove {main}
	    array_pop($trace); // remove call to this method
	    $length = count($trace);
	    $result = array();
	    
	    for ($i = 0; $i < $length; $i++)
	    {
	        $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
	    }
	    
	    return " <br/>" . implode("<br/><br/>", $result);
	}
}

abstract class Errors {
	const UNAUTHORIZED = 403;
	const PAGE_NOT_FOUND = 404;
	const CLASS_NOT_FOUND = 500;
}
?>