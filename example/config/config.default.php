<?php
return array(
    
   "config" => array(
        "display_errors" => true, 
    ),
   
    "namespace" => array(
        "model" => dirname(__dir__).'/model/'
    ),
    
    "routes" => array(
            "home" => array(
                "route" => "/", 
                "method" => "GET|POST", 
                "target" => function($request, $response, $args){
                    $dbAdapter = hubert()->dbAdapter;
                    $result = $dbAdapter->query('SELECT * FROM `db_test` WHERE `id` = ?', [1]);
                   print_r($result->current());
                    
                    print_r(json_encode(\model\test::selectOne(["id" => 1])));
                    
                    print_r(json_encode(\model\test::selectAll()));
                }
            ),
            "install" => array(
                "route" => "/install", 
                "method" => "GET|POST", 
                "target" => function($request, $response, $args){
                    $factory = new \hubert\extension\db\factory();
                   $factory->createTableByModel(\model\test::class);
                }
            ),
        )
);
