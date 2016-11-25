<?php

namespace model;

class test extends \hubert\extension\db\model {
    
     protected static $table = "db_test";
     
     public static function fields(){
        return array(
            "id" => array('type' => 'integer', 'primary' => true, 'autoincrement' => true, "sql" => "INT NOT NULL AUTO_INCREMENT PRIMARY KEY"),
            "name" => array('type' => 'string', "default" => "", "sql" => "VARCHAR(11) NOT NULL DEFAULT ''")
        );
    }
    
}
