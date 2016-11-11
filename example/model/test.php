<?php

namespace model;

class test extends \hubert\extension\db\model {
    
     protected static $table = "db_test";
     
     public static function fields(){
        return array(
            "id" => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
            "name" => array('type' => 'string', "default" => ""),
        );
    }
    
}
