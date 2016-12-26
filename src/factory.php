<?php

namespace hubert\extension\db;

class factory {
    public static function get($container){
        return new \Zend\Db\Adapter\Adapter(hubert()->config()->db);
    }
    
    public function createTableByModel($modelClass){
        //check if instance of model
        if(!is_subclass_of($modelClass, model::class)){
            throw new \Exception("Class $classname not inherits class model");
        }
        
       //get Date
        $table = $modelClass::tableGateway()->getTable();
        $fields = $modelClass::fields();
        
        $this->createTable($table, $fields , $modelClass::dbAdapter());
    }
    
    public function createTable($table_name, $columns, $dbAdapter = null){
        
        if(!$dbAdapter){
            $dbAdapter = hubert()->dbAdapter;
        }
        
        //check db connection
        if(!$dbAdapter->getDriver()->getConnection()->isConnected()){
           //connnect-function throw exeption by error
           $dbAdapter->getDriver()->getConnection()->connect();  
        }
        
        $db_fields = array();
        $primary_fields = array();
        
        foreach ($columns as $field_name => $field){
            $f = array();
            $f["name"] = $field_name;
            
            if(!$field["type"]){
                throw new \Exception("Table {$table_name} Column {$field_name}: funtion createTableByModel require to each row a 'type' value like 'INT NOT NULL' OR 'VARCHAR(10)'");
            }
            
            $f["sql"] = "`{$field_name}` {$field["type"]}";
            $f["sql"] .= isset($field["sql"]) ? ' '.$field["sql"] : "";
            
            
            if(isset($field["autoincrement"]) && $field["autoincrement"]){
                $f["sql"] .= " AUTO_INCREMENT";
            }
            
            if(isset($field["null"]) && $field["null"]){
                $f["sql"] .= " NULL";
            } else {
                $f["sql"] .= " NOT NULL";
            }
            
            if(isset($field["default"])){
                if(is_string($field["default"]) && strtoupper($field["default"]) == "CURRENT_TIMESTAMP"){
                    $f["sql"] .= " DEFAULT CURRENT_TIMESTAMP";
                } else {
                    $f["sql"] .= " DEFAULT '{$field["default"]}'";
                }
            }
            
            if(isset($field["primary"]) && $field["primary"]){
                $primary_fields[] = $field_name;
            }
            
            $db_fields[$field_name] = $f;
        }
        
        if(count($db_fields) == 0){
            throw new Exception("Table {$table_name} has no columns");
        }
        
       
        //create Table if not exist
        $res = $dbAdapter->query("SHOW TABLES LIKE '{$table_name}'", []); 
        if(count($res) == 0){
            $sql = '';
        
            foreach ($db_fields as $db_field){
               if($sql) {$sql .= ",";}
               $sql .= $db_field['sql'];
            }
            
            if(count($primary_fields) > 0){
                $keys = implode(",",array_map(function($value){ return "`".$value."`";}, $primary_fields));
                $sql .= " , PRIMARY KEY ({$keys})";
            }

            $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` ({$sql})";
           
            $dbAdapter->query($sql, []);
        } else {
           
            //create missing cols
            $res = $dbAdapter->query("SHOW COLUMNS FROM `{$table_name}`", []);

            $existing_fields = array();
            foreach ($res as $field){
                $existing_fields[] = $field["Field"];
            }

            foreach ($db_fields as $db_field){
                if(in_array($db_field["name"], $existing_fields)){
                    continue;
                }
                
                
                $dbAdapter->query("ALTER TABLE {$table_name} ADD {$db_field['sql']}", []);
            }
        }
        
        
        
    }
            
    public static function getListQuery($array){
        $values = array();
        foreach ($array as $value){
            $values[] = hubert()->dbAdapter->getPlatform()->quoteValue($value);
        }
        
        return implode(",", $values);     
    }
    
}
