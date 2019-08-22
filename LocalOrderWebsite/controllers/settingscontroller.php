<?php

class SettingsController extends \Dorm\Models\DormController {
	private $_repository;

	function __construct() {
		global $dorm;
		//locate the repository
		$this->_repository = $dorm->di->get('SettingRepository');
	}

	function getSetting($id) {
		try {
			$setting = $this->_repository->find($id);

			if($setting == null) {
				//TODO: throw not found 
			}

			echo json_encode($setting);
		} catch(\OutOfBoundsException $ex) {
			\Dorm\Util\Header::notFoundError();

			echo json_encode(array('status'=>'error', 'message'=>'Could not locate that setting.'));
		}
	}

	function getAllSettings() {
		$settings = $this->_repository->findAll();

		$result = array();

		//reorganize into dictionary
		foreach($settings as $s) {
			$result[$s->name] = $s;
		}

		echo json_encode($result);
	}

	//fulfill a service request
	function saveSetting($id) {
		//grab from request
		$setting = new \DeCaro\Models\Setting();
		$setting = fill_object($setting);

		//TODO: validate based on key

		//save the instance
		$setting = $this->_repository->save($setting);

		echo json_encode($setting);
	}

	//fulfill a service request
	function removeSetting($id) {
		$setting = $this->_repository->find($id);
		if(!$setting) {
			throw new Exception("Setting does not exist: " . $id);
		}

		$this->_repository->remove($setting);

		return dorm()->response->success_response();
	}
}

?>