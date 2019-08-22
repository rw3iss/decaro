<?php

namespace DeCaro\Data;
use \Dorm\Data;

class SettingMapper {
    protected $db; //pdo adapter
    protected $identityMap; //"cache"
    private $_table = "settings";

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
        $obj = $this->db->fetch($this->_table, $id, '\DeCaro\Models\Setting');

        if(!$obj)
            throw new \OutOfBoundsException("Could not locate that Setting");

        return $obj;
    }

    public function findAll() {
         $objArray = $this->db->fetch($this->_table, null, '\DeCaro\Models\Setting');
         return $objArray;
    }
    
    /**
    * @param Setting $setting
    * @throws MapperException
    * @return integer A lastInsertId.
    */
    public function insert(\DeCaro\Models\Setting $obj)
    {
        $obj->id = $this->db->insert($this->_table, $obj, array('id', 'editing'));
        return $obj;
    }

    public function update(\DeCaro\Models\Setting $obj)
    {
        $obj = $this->db->update($this->_table, $obj, array('id', 'editing'));
        return $obj;
    }

    public function delete(\DeCaro\Models\Setting $obj)
    {
        $obj = $this->db->delete($this->_table, $obj);
    }
}

?>