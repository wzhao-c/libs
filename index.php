<?php

use models\database\Database,
    models\loader\SplAutoLoader;
    
require_once 'models/loader/SplAutoLoader.class.php';

$loader = new SplAutoLoader;
$loader->register();


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

try {
    $qry_str = 'SELECT * FROM users ORDER BY username';
    
    $i = $db->fetch($qry_str, Database::MYSQL_OBJECT);
    
    for($i; $i->valid(); $i->next()) {
        var_dump($i->current(), $i->key());
        echo '<br>';
    }
} catch (Exception $e) {
    $_errors['database'] = $e->getMessage();
}

if (!empty($_errors)) {    
    $_error_obj = new ArrayObject($_errors);
    
    for ($_error_itr = $_error_obj->getIterator(); $_error_itr->valid(); $_error_itr->next()) {
        echo $_error_itr->current() . '<br />';
    }
}

