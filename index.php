<?php

use models\database\Database,
    models\loader\SplAutoLoader;

// Autoloading PHP classes
require_once 'models/loader/SplAutoLoader.class.php';

$loader = new SplAutoLoader;
$loader->register();

// Database connection
$_errors = array();
$config['database'] = array('host'     => 'localhost',
                            'user'     => 'root',
                            'password' => 'root',
                            'database' => 'practice');
 
try {
    $db = Database::getInstance();
    
    $db->load($config['database']);
    $db->connect();
} catch (Exception $e) {
    $_errors['database'] = $e->getMessage();
}

