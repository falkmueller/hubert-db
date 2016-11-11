<?php

namespace hubert\extension\db;

class factory {
    public static function get($container){
        return new \Zend\Db\Adapter\Adapter(hubert()->config()->db);
    }
}
