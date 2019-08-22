<?php
namespace DeCaro\Repositories;

class SettingRepository {
	protected $_mapper;

	/**
     * @param \Dorm\Data\SqlDatabase $database
     */
	public function __construct(\Decaro\Data\SettingMapper $mapper) {
		$this->_mapper = $mapper;
	}

	//acts as a factory and returns a basic representation
	public function newSetting() {
		$obj = new \DeCaro\Models\Setting();
		$obj->id = 0;
		$obj->state = 0;

		return $obj;
	}

	public function find($id) {
		$obj = $this->_mapper->find($id);
		return $obj;
	}

	public function findAll() {
		$objArray = $this->_mapper->findAll();
		return $objArray;
	}

	public function save(\Decaro\Models\Setting $obj) {
		if($obj->id == 0)
			$obj = $this->_mapper->insert($obj);
		else 
			$obj = $this->_mapper->update($obj);

		return $obj;
	}

	public function remove(\Decaro\Models\Setting $obj) {
		$obj = $this->_mapper->delete($obj);
		return $obj;
	}
}

?>