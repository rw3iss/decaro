<?php
namespace DeCaro\Repositories;

class UserRepository {
	protected $_mapper;

	/**
     * @param \Dorm\Data\SqlDatabase $database
     */
	public function __construct(\Decaro\Data\UserMapper $mapper) {
		$this->_mapper = $mapper;
	}

	//acts as a factory and returns a basic representation
	public function newUser() {
		$obj = new \DeCaro\Models\User();
		$obj->id = 0;
		$obj->role = 
		$obj->state = 0;

		return $obj;
	}

	public function findBy($key, $value) {
		$obj = $this->_mapper->findBy($key, $value);
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

	public function findAllRoles() {
		//load from database:
		//$objArray = $this->_mapper->findAllRoles();

		//load from class:
		$refl = new \ReflectionClass('\DeCaro\Models\UserRole');
		$consts = $refl->getConstants();

		return $consts;
	}

	public function save(\Decaro\Models\User $obj) {
		if($obj->id == 0)
			$obj = $this->_mapper->insert($obj);
		else 
			$obj = $this->_mapper->update($obj);

		return $obj;
	}

	public function remove(\Decaro\Models\User $obj) {
		$obj = $this->_mapper->delete($obj);
		return $obj;
	}
}

?>