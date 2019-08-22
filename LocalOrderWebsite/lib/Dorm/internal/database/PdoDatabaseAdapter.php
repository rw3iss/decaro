<?php
namespace Dorm\Data;
use \PDO;


/* wraps the pdo data object */
class PdoDatabaseAdapter {
	public $pdo;

	/**
	 * Initializes the connection handle and sets up basic configuration.
	 * @param string $connectionString The PDO connection string.
	 */
	public function __construct($connectionString, $user, $password) {
		try {
			$this->pdo = new \PDO($connectionString, $user, $password);
			$this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		} catch(PDOException $ex) {
			echo "PDO Exception: " . $ex->getMessage();
			//TODO emit error
		}
	}

	public function fetch($table, $id = null, $class = null, $sortBy = null, $sortDir = null) {
		try {
			$sql = "SELECT * FROM " . $table;

			if($id != null) 
				$sql .= " WHERE id=" . $id;

			if($sortBy != null) {
				$sql .= " ORDER BY " . $sortBy;
			}
			
			if($sortDir != null) {
				$sql .= " " . $sortDir;
			}

			$stm = $this->pdo->prepare($sql);

			if($class != null) {
				//fetch into a class instantiation
				$stm->setFetchMode(PDO::FETCH_CLASS, $class);
			} else {
				//otherwise use associative array
				$stm->setFetchMode(PDO::FETCH_ASSOC);
			}

			$stm->execute();

			if($id != null) {
				$obj = $stm->fetch();
			} else {
				$obj = $stm->fetchAll();
			}

			return $obj;
		} catch(PDOException $ex) {
			echo "PDO EXCEPTION on fetch";
			throw $ex;
		}
	}

	public function fetchWhere($table, $args, $class = null) {
		try {
			$sql = "SELECT * FROM " . $table;

			$sql .= $this->_whereString($args);

			$stm = $this->pdo->prepare($sql);

			if($class != null) {
				//fetch into a class instantiation
				$stm->setFetchMode(PDO::FETCH_CLASS, $class);
			} else {
				//otherwise use associative array
				$stm->setFetchMode(PDO::FETCH_ASSOC);
			}

			$stm->execute($args);

			$objArray = $stm->fetchAll();

			return $objArray;
		} catch(PDOException $ex) {
			echo "PDO EXCEPTION on fetchWhere";
			throw $ex;
		}
	}

	/** Creates an insert query based on the object class */
	public function insert($table, $object, $filter = null) {
		try {
			$fields = null;

			if(is_object($object)) {
				$fields = get_object_vars($object);
			} else if(is_array($object)) {
				$fields = $object;
			} else {
				throw new InvalidArgumentException("PdoDatabaseAdapter::insert() expect an object or an array as a second argument. " .
					get_class($object) . " was passed.");
			}

			if($filter != null) {
				$fields = $this->_filter($fields, $filter);
			}
			
			$sql = "INSERT INTO " . $table . " (" . implode(array_keys($fields), ", ") . ") VALUES " . 
				$this->_valueString($fields) . ";";

			$stm = $this->pdo->prepare($sql);
			$stm->execute($fields);

			return intval($this->pdo->lastInsertId());
		} catch(PDOException $ex) {
			echo "PDO EXCEPTION on insert";
			throw $ex;
		}
	}

	public function update($table, $object, $filter = null) {
		try {
			$fields = null;

			if(is_object($object)) {
				$fields = get_object_vars($object);
			} else if(is_array($object)) {
				$fields = $object;
			} else {
				throw new InvalidArgumentException("PdoDatabaseAdapter::insert() expect an object or an array as a second argument. " .
					get_class($object) . " was passed.");
			}

			if($filter != null) {
				$fields = $this->_filter($fields, $filter);
			}
			
			$sql = "UPDATE " . $table . " SET " . $this->_updateString($fields) . 
					" WHERE id=" . $object->id;

			$stm = $this->pdo->prepare($sql);
			$stm->execute($fields);

			return $object;
		} catch(PDOException $ex) {
			echo "PDO EXCEPTION on insert";
			throw $ex;
		}
	}

	public function delete($table, $object) {
		try {
			$sql = "DELETE FROM " . $table . " WHERE id=" . $object->id;

			$stm = $this->pdo->prepare($sql);

			$stm->execute();//array(':table' => $table));
		} catch(PDOException $ex) {
			echo "PDO EXCEPTION on delete";
			throw $ex;
		}
	}

	private function _whereString($args) {
		$value = "";
		if(sizeof($args) > 0) {
			$value = " WHERE ";
		}

		$delim = "";
		foreach($args as $k => $v) {
			$value .= $delim . "$k=:$k";
			$delim = " AND ";
		}

		return $value;
	}

	//return a string of field=:field, ...
	private function _updateString($fields) {
		$values = "";

		$delim = "";
		foreach($fields as $k => $v) {
			if(is_array($v)) {
				$av = "";
				foreach($v as $x) {
					$d = "";
					$av .= $d . $x;
					$d = ",";
				}
				$values .= $delim . "$k=$av";
			} else {
				$values .= $delim . "$k=:$k";
			}
			$delim = ", ";
		}

		return $values;
	}

	//returns a string of :field, ...
	private function _valueString($fields) {
		$values = "(";

		$delim = "";
		foreach($fields as $k => $v) {
			$values .= $delim . ":$k";
			$delim = ", ";
		}

		$values .= ")";

		return $values;
	}


	private function _filter($fields, $filter) {
		foreach($filter as $i=>$k) {
			if(is_array($fields)) {
				if(array_key_exists($k, $fields)) {
					unset($fields[$k]);
				}
			} else if(is_object($fields)) {
				if(isset($fields->{$k})) {
					unset($fields->{$k});
				}
			}
		}

		return $fields;
	}

	//executes raw sql
	public function query($sql, $data = null) {
		$stm = $this->pdo->prepare($sql);
		$stm->execute($data);
		return $stm->fetchAll();
	}

	public function __destruct() {
		//close any connections
		$this->dbh = null;
	}
}

?>
