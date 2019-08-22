<?php

namespace Dorm\Data;

class IdentityMap {
	private $_store = null;

	function __construct() {
		$this->_store = array();
	}

	public function set($id, $object) {
		$key = get_class($object) . '.' . $id;
		$this->_store[$key] = $object;
	}

	public function get($id) {
		return idx($this->_store, $id, null);
	}
}

?>