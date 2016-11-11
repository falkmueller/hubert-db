Hubert Database Extension
======

## Installation

Hubert is available via Composer:

```json
{
    "require": {
        "falkm/hubert-db": "1.*"
    }
}
```

## Usage

Create an index.php file with the following contents:

```php
<?php

require 'vendor/autoload.php';

$app = new hubert\app();

$config = array(
    "factories" => array(
         "dbAdapter" => array(hubert\extension\db\factory::class, 'get')
        ),
    "config" => array(
        "display_errors" => true,
        "db" => array(
            'driver'   => 'Pdo_Mysql',
            'database' => 'db_test',
            'username' => 'user',
            'password' => 'pass',
        ),
    "routes" => array(
        "home" => array(
            "route" => "/", 
            "method" => "GET|POST", 
            "target" => function($request, $response, $args){
                $result = hubert()->container()->dbAdapter->query('SELECT * FROM `db_test` WHERE `id` = :id', ['id' => 1]);
                print_r($result->current());
            })
        ),
);

hubert($config);
hubert()->emit(hubert()->run());
```

For more see the example in this repository.

### components

- Zend Database engine [zendframework/zend-db](https://docs.zendframework.com/zend-db/)

## License

The MIT License (MIT). Please see [License File](https://github.com/falkmueller/hubert/blob/master/LICENSE) for more information.