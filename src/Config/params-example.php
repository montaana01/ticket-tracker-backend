<?php
// rename this file to params.php
return [
    'db' => [
        'hostname' => 'hostname',
        'port' => '3306',
        'dbname' => 'dbname',
        'username' => 'username',
        'password' => 'password',
        'options' => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]
    ],
    'JWT' => 'example key',
    'FE_DOMAIN' => 'localhost',
];