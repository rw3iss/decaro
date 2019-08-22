<?php
/* DormResponseHandler - utilities to help render specific responses to a client */

namespace Dorm;

/**
 * /dorm/lib/core/dorm_response.php - Offers means of communicating back to the client, 
 * providing a set of method to encapsulate data and set any required headers. Supports
 * rendering of views and 
 * TODO: implement as Response interfaces:
 *   ResponseHandler->respond($result)
 * ie: JsonResponseHandler->respond($result); prints json
 * ie: ViewResponseHandler->respond($view); prints the view to the browser
 * TODO: needs to be cleaned up
 */

class DormResponseHandler {
	private $final_output = '';

	function view($view, $return = false) {
		//serve the view depending on which environment we're in
		$view_path = DORM_VIEW_PATH . $view . '.php';

		if ( ! file_exists($view_path))
		{
			//try a default view
			$view_path = DORM_VIEW_PATH . 'default/' . $view;

			if (strpos($view,'.') === false) {
				$view_path = $this->_find_file_extension_path($view_path);
			}

			if ( ! file_exists($view_path)) {
				throw new DormException("View file was not found: " . $view);
			}
		}

		return $this->_load_view($view_path, $return);
	}
	
	//returns a file path of an existing file with tried extensions: php, html
	private function _find_file_extension_path($path, $extensions = null) {
		$extensions = $extensions ?: array('php', 'html');

		foreach($extensions as $e) {
			$tryPath = $path . '.' . $e;
			if(file_exists($tryPath)) {
				return $tryPath;
			}
		}

		return null;
	}

	// Add the view directly to the current buffer output, in place.
	function insert($view) {
		//serve the view depending on which environment we're in
		$viewPath = DORM_PATH . (strpos($view, '/') == 0 ? '' : '/') . $view;

		if (strpos($view,'.') === FALSE) {
			$viewPath .= '.php';
		}

		if ( ! file_exists($viewPath))
		{
			throw new DormException("Could not locate the view file for inclusion: " . $viewPath);
		}

		$this->_load_view($viewPath, false);
	}

	// includes file, passes data to the view
	function data_view($view, $dataInstance) {
		//serve the view depending on which environment we're in
		$view_path = DORM_VIEW_PATH . $view . '.php';

		if ( ! file_exists($view_path))
		{
			//try a default view
			$view_path = DORM_VIEW_PATH . 'default/' . $view;

			if (strpos($view,'.') === false) {
				$view_path = $this->_find_file_extension_path($view_path);
			}

			if ( ! file_exists($view_path)) {
				throw new DormException("View file was not found: " . $view);
			}
		}

	    global $data;
		$data = $dataInstance;

		include($view_path);
	}

	//Loads a view based on an absolute path
	function system_view($view, $return = false) {
		//serve the view depending on which environment we're in
		$view_path = DORM_VIEW_PATH . '../lib/views/';

		if(strpos($view, '/') == false) {
			//$mobile = $dorm->MobileDetect->isMobile();
			$mobile = FALSE; //TODO

			if($mobile) 
				$view_path .= 'mobile/';
			else 
				$view_path .= 'default/';
		}

		$view_path .= $view;

		if (strpos($view,'.') === FALSE) {
			$view_path = $this->_find_file_extension_path($view_path);
		}

		//try the shared folder if this one doesn't exist
		if ( ! file_exists($view_path))
		{
			$view_path = DORM_VIEW_PATH . '../lib/views/shared/' . $view;

			if (strpos($view,'.') === FALSE) {
				$view_path = $this->_find_file_extension_path($view_path);
			}
		}

		if ( ! file_exists($view_path)) {
			throw new Exception("View file was not found: " . $view);
		}

		return $this->_load_view($view_path, $return);
	}

	function responsive_view($view, $return = false) {
		//serve the view depending on which environment we're in
		$view_path = DORM_VIEW_PATH;

		//if view contains a path, user wants to load it manually, so start from the views folder,
		//otherwise detect if we're to be responsive and load the appropriate view.
		if(strpos($view, '/') == false) {
			//$mobile = $dorm->MobileDetect->isMobile();
			$mobile = false;

			if($mobile) 
				$view_path .= 'mobile/';
			else 
				$view_path .= 'default/';
		}

		$view_path .= $view;

		if (strpos($view,'.') === false) {
			$view_path = $this->_find_file_extension_path($view_path);
		}

		if ( ! file_exists($view_path)) {
			throw new DormException("View file was not found: " . $view_path);
		}

		return $this->_load_view($view_path, $return);
	}

	function json_response($o) {
		echo json_encode($o);
	}

	function set_header($header, $value) {
		header($header, $value);
	}

	// Returns a standard json error response for backend requests.
	function error_response($message, $reponse_code = 400, $o = null) {
		$msg = "";
		if(is_array($message)) {
			$delim = '';
			foreach($message as $m) {
				$msg .= $delim . $m;
				$delim = ', ';
			}
		} else {
			$msg = $message;
		}

		$data = array("success" => false, "error" => $reponse_code, "message" => $msg);
		if($o != null)
			$data['object'] = $o;

		echo json_encode($data);
		exit();
	}

	function success_response($obj = null) {
		$response = array("success" => true);

		if($obj != null)
			$response['data'] = $obj;

		echo json_encode($response);
	}

	// Adds content directly to the current output
	function append_output($output)
	{
		if ($this->final_output == '')
		{
			$this->final_output = $output;
		}
		else
		{
			$this->final_output .= $output;
		}

		return $this;
	}

	// Actually outputs the output
	private function _display($output = '') {
		global $dorm;

		//TOOD: implement caching of output

		// Does the current controller contain a function named _output()?
		// If so send the output there.  Otherwise, echo it.
		if(isset($dorm->controller)) {
			if (method_exists($dorm->controller, '_output'))
			{
				$dorm->controller->_output($output);
				return;
			}
		}

		// Send it to the browser!
		echo $output;
	}

	// Does the loading work. If return is true, it will return the view as a string instead of rendering it.
	private function _load_view($view_path, $return) {
		global $dorm;

		if ( ! file_exists($view_path))
		{
			$this->error_response("File not found: " . $view_path, 404);
		}

		include($view_path);

		if ($return === TRUE)
		{
			ob_start();
			$buffer = ob_get_contents();
			@ob_end_clean();

			if (ob_get_level() > $dorm->_dorm_ob_level + 1)
			{
				ob_end_flush();
			}

			return $buffer;
		}

		//$this->append_output(ob_get_contents());
		//@ob_end_clean();

		//$this->_display($this->final_output);
	}

}

?>