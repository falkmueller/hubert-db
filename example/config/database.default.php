<?php

return array(
    "factories" => array(
         "dbAdapter" => array(hubert\extension\db\factory::class, 'get')
        ),
    
   "config" => array(
        "db" => array(
            'driver'   => 'Pdo_Mysql',
            'database' => 'db_test',
            'username' => 'root',
            'password' => '',
        )
    )
);
