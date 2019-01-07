<?php

namespace hubert\extension\db;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

abstract class model implements \JsonSerializable {
    
    /*configuration*/
    protected static $table = "";
    
    protected $_data;
    protected $_dirty_fields = array();

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
            $value = null;
            if(isset($data[$name])){
                $value = $data[$name];
            }
            elseif (isset($this->$name)){
                continue;
            }
            elseif (isset($config["default"])){
                $value = $config["default"];
                if($value && $value === "CURRENT_TIMESTAMP"){
                    $value = date('Y-m-d H:i:s',time());
                }
            }
            
            $this->$name = $value;
        }
    }
    
    public function jsonSerialize() {
        return $this->_data;
    }
    
     public function isDirty(){
        return count($this->_dirty_fields) > 0;  
    }
    
    public function __get($name){
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }
    
    public function __set($name, $value) 
    {
        if(isset($this->$name) && $this->$name !== $value && !in_array($name, $this->_dirty_fields)){
            $this->_dirty_fields[] = $name;
        }
        $this->_data[$name] = $value;
    }
    
     public function __isset($name) 
    {
        return isset($this->_data[$name]);
    }

    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
    
    public function __clone()
   {
       $object_vars = get_object_vars($this);

       foreach ($object_vars as $attr_name => $attr_value)
       {
           if (is_object($this->$attr_name))
           {
               $this->$attr_name = clone $this->$attr_name;
           }
       }
   }
    
    public function toArray(){
        return $this;
    }

    /*Static methods*/
    public static function dbAdapter(){
        return hubert()->dbAdapter;
    }
    
    public static function tableGateway()
    {
        static $tableGateway;
        if(!$tableGateway){
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new static());
            $tableGateway = new TableGateway(static::$table, static::dbAdapter(), null, $resultSetPrototype);
        }
        
        return $tableGateway;
        
    }

    public static function selectAll($where = array(), $limit = 0, $offset = 0, $order = null, $join = null){
        return static::tableGateway()->select(function(Select $select) use ($where, $limit, $offset, $order, $join){
                if($limit){$select->limit($limit);}
                if($offset){$select->offset($offset);}
                if($where){$select->where($where);}
                if($order){$select->order($order);}
                if($join){$join($select);}
            })->toArray();
    }
    
    public static function count($where = array(), $join = null){
        $sql = static::tableGateway()->getSql();
        $select = $sql->select();
        if($where){$select->where($where);}
        if($join){$join($select);}
        $select->columns(array(
                    'count' => new Expression('COUNT(0)')
                ));
        
       $statement = $sql->prepareStatementForSqlObject($select);
       $result = $statement->execute()->current();
       return $result["count"];    
    }
    
    public static function selectOne($where = array()){        
        return static::tableGateway()->select($where)->current();
    }
    
    public function insert(){
       $data = array();
       $auto_id_field = null;
       foreach (static::fields() as $name => $config){
            $data[$name] = isset($this->$name) ? $this->$name : (isset($config["default"]) ? $config["default"] : null);
           
            if(!empty($config["autoincrement"])){
                $auto_id_field = $name;
            }
       }
       static::tableGateway()->insert($data);
       
        if($auto_id_field){
            $this->$auto_id_field = static::tableGateway()->getLastInsertValue();
        }
    }
    
    public function update($dirty_rows = array(), $update_all = false){
        $update = array();
        $primary = array();
        
        if($update_all || (count($dirty_rows) == 0 && count($this->_dirty_fields) > 0)){
            $dirty_rows = $this->_dirty_fields;
            $this->_dirty_fields = array();
        }
        
        foreach (static::fields() as $name => $config){
            if (!empty($config["primary"])){
                $primary[$name] = $this->$name;
                if(!empty($config["autoincrement"])){
                    continue;
                }
            }
            
            if(!empty($dirty_rows) && !in_array($name, $dirty_rows) ){
                continue;
            }
            
            $update[$name] = $this->$name;
        }
        
        if(empty($primary)){
            throw new \Exception('no primary Row in entity');
        }
        elseif(empty($update)){
            return false;
        }
        
        return static::tableGateway()->update($update, $primary);
    }
    
    public static function deleteBy($where = array()){
        return static::tableGateway()->delete($where);
    }
    
    public function delete(){
        $primary = array();
        
        foreach (static::fields() as $name => $config){
            if (!empty($config["primary"])){
                $primary[$name] = $this->$name;
            }
        }
        
        if(empty($primary)){
            throw new \Exception('no primary Row in entity');
        }
        return static::tableGateway()->delete($primary);
    }
    
}
