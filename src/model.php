<?php

namespace hubert\extension\db;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

abstract class model implements \JsonSerializable {
    
    /*configuration*/
    protected static $table = "";
    
    protected $_data;

    public static function fields(){
        return array();
    }

    /*model functions*/
    public function __construct(array $data = array()){
        if(!empty($data)){
            $this->exchangeArray($data);
        }
    }
    
    public function exchangeArray(array $data)
    {   
        foreach (static::fields() as $name => $config){
            $this->_data[$name] = !empty($data[$name]) ? $data[$name] : (isset($config["default"]) ? $config["default"] : null);
        }
    }
    
    public function jsonSerialize() {
        return $this->_data;
    }
    
    public function __get($name){
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }
    
    public function __set($name, $value) 
    {
        if (array_key_exists($name, static::fields())) {
            $this->_data[$name] = $value;
        }
    }
    
     public function __isset($name) 
    {
        return isset($this->_data[$name]);
    }

    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
    
    public function toArray(){
        return $this;
    }


    /*Static methods*/
    public static function tableGateway()
    {
        static $tableGateway;
        if(!$tableGateway){
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new static());
            $tableGateway = new TableGateway(static::$table, hubert()->dbAdapter, null, $resultSetPrototype);
        }
        
        return $tableGateway;
        
    }

    public static function selectAll($where = array(), $limit = 0, $offset = 0){
        return static::tableGateway()->select(function(Select $select) use ($where, $limit, $offset){
                if($limit){$select->limit($limit);}
                if($offset){$select->offset($offset);}
                if($where){$select->where($where);}
            })->toArray();
    }
    
    public static function selectOne($where){
        return static::tableGateway()->select($where)->current();
    }
    
    protected static function getListQuery($array){
        $values = array();
        foreach ($array as $value){
            $values[] = static::tableGateway()->getAdapter()->getPlatform()->quoteValue($value);
        }
        
        return implode(",", $values);     
    }
    
}
