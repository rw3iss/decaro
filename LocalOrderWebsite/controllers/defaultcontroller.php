<?php

class DefaultController extends \Dorm\Models\DormController {

	function __construct() {
		global $dorm;
		$this->_userRepository = $dorm->di->get('UserRepository');
	}

	function testgoogle() {
		global $dorm;
		$dorm->response->view('partials/google');
	}

	function index() {
		global $dorm;

		//check if logged in
		$cookie = $dorm->input->cookie('userlogin');
		//echo print_r($_COOKIE["userlogin"]);

		if($cookie) {
			$username = json_decode($cookie)->username;

			$users = $this->_userRepository->findBy('username', $username);
			if(isset($users[0])) {
				$user = $users[0];

				set_current_user($user);

				$dorm->response->responsive_view('index');
				return;
			}
		}

		if(false) {
			$dorm->response->responsive_view('index');
		} else {
			$dorm->response->responsive_view('loggedout');
		}
	}

	function manage() {
		global $dorm;

		//check if logged in
		if(true) {
			$dorm->response->responsive_view('index');
		} else {
			$dorm->response->responsive_view('loggedout');
		}
	}

	function login() {
		global $dorm;

		//check if logged in
		$dorm->response->responsive_view('loggedout');
	}

	function page_not_found() {
		echo "PAGE NOT FOUND";
	}

	/* responds with a partial view  */
	function partial($view) {
		global $dorm;
		
		$dorm->response->view('partials/'. $view);
	}

}

?>