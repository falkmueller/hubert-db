<?php
return array(
    
   "config" => array(
        "display_errors" => true, 
    ),
    "routes" => array(
            "home" => array(
                "route" => "/", 
                "method" => "GET|POST", 
                "target" => function($request, $response, $args){
                    $container = $this->getContainer();
                    
                    $result = $container["dbAdapter"]->query('SELECT * FROM `db_test` WHERE `id` = ?', [1]);
                    
                    print_r($result->current());
                }
            ),
        )
);
