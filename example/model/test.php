<?php

namespace model;

class test extends \hubert\extension\db\model {
    
     protected static $table = "db_test";
     
     public static function fields(){
        return array(
            "id" => array('type' => 'int(11)', 'primary' => true, 'autoincrement' => true),
            "name" => array('type' => 'varchar(30)', "default" => ""),
            "comment" => array('type' => 'text', "null" => true),
        );
    }
    
}
