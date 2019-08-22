<?php

class UserController extends \Dorm\Models\DormController {
	private $_repository;

	function __construct() {
		global $dorm;
		//locate the repository
		$this->_repository = $dorm->di->get('UserRepository');
	}

	function loginUser() {
		global $dorm;

		$username = $dorm->input->post('username');
		$password = $dorm->input->post('password');

		$users = $this->_repository->findBy('username', $username);

		if(isset($users[0])) {
			$user = $users[0];

			if($user->password == $password) {
				//login, set cookie
				$expires = time() + 60 * 60 * 24 * 30; //expires in 30 days
				$data = array("username" => $username);
				$data['role'] = $user->role;

				setcookie("userlogin", json_encode($data), $expires, '/', null, false, false);

				set_current_user($user);

				$dorm->response->success_response($user);
			} else {
				$dorm->response->error_response('Invalid password.');
			}
		} else {
			$dorm->response->error_response('Username "' . $username . '" not found.');
		}
	}

	function logoutUser() {
		unset($_SESSION["current_user"]);
		setcookie("userlogin", "", time()-3600, '/', null, false, false);
	}

	function getCurrentUser() {
		echo json_encode(array("username" => "rw3iss", "fistname" => "Ryan", "lastname" => "Weiss", "role" => "ADMINISTRATOR"));
	}

	function getUser($id) {
		try {
			$user = $this->_repository->find($id);

			if($user == null) {
				//TODO: throw not found 
			}

			echo json_encode($user);
		} catch(\OutOfBoundsException $ex) {
			\Dorm\Util\Header::notFoundError();

			echo json_encode(array('status'=>'error', 'message'=>'Could not locate that user.'));
		}
	}

	function getAllUsers() {
		$users = $this->_repository->findAll();

		echo json_encode($users);
	}

	//fulfill a service request
	function startNewUser() {
		$user = $this->_repository->newUser();

		echo json_encode($user);
	}

	//fulfill a service request
	function saveUser($id) {
		//grab from request
		$user = new \DeCaro\Models\User();
		$user = fill_object($user);

		//save the instance
		$user = $this->_repository->save($user);

		echo json_encode($user);
	}

	//fulfill a service request
	function removeClient($id) {
		echo "REMOVE CLIENT " . $id;
	}

	function getOrdersForClient($clientId) {
		echo "get users for client";
	}

	function getAllUserRoles() {
		$users = $this->_repository->findAllRoles();

		echo json_encode($users);
	}
}

?>