<?php

namespace DeCaro\Data;
use \Dorm\Data;

class UserMapper {
    protected $db; //pdo adapter
    protected $identityMap; //"cache"
    private $_table = "users";

    public function __construct(\Dorm\Data\PdoDatabaseAdapter $db, \Dorm\Data\IdentityMap $identityMap) {
        $this->db = $db;
        $this->identityMap = $identityMap;
    }

    public function find($id) {
        $obj = $this->identityMap->get($id);

        if ($obj) {
          return $obj;
        }

        //fetch from database
        $obj = $this->db->fetch($this->_table, $id, '\DeCaro\Models\User');

        if(!$obj)
            throw new \OutOfBoundsException("Could not locate that User");

        return $obj;
    }

    public function findAll() {
         $objArray = $this->db->fetch($this->_table, null, '\DeCaro\Models\User');
         return $objArray;
    }

    public function findAllRoles() {
         $objArray = $this->db->fetch("user_roles", null);
         return $objArray;
    }

    public function findBy($key, $value) {
        $args = array($key => $value);
        $objArray = $this->db->fetchWhere("users", $args, '\DeCaro\Models\User');
        return $objArray;
    }

    /**
    * @param User $user
    * @throws MapperException
    * @return integer A lastInsertId.
    */
    public function insert(\DeCaro\Models\User $obj)
    {
        $obj->id = $this->db->insert($this->_table, $obj, array('id'));
        return $obj;
    }

    public function update(\DeCaro\Models\User $obj)
    {
        $obj = $this->db->update($this->_table, $obj, array('id'));
        return $obj;
    }

    public function delete(\DeCaro\Models\User $obj)
    {
        $obj = $this->db->delete($this->_table, $obj);
        return $obj;
    }
}

?>