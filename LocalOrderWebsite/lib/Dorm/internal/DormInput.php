<?php
/* DormInput - utility class to retrieve submitted inputs to the system */

namespace Dorm;

/**
 * /dorm/lib/core/DormInput.php - Encapsulates post/get input
 */

class DormInput {
	public function get($param) {
		if(isset($_GET[$param]))
			return $_GET[$param];

		return null;
	}

	public function post($param) {
		if(isset($_POST[$param]))
			return $_POST[$param];

		return null;
	}

	public function cookie($name) {
		if(isset($_COOKIE[$name]))
			return $_COOKIE[$name];

		return null;
	}
}

?>