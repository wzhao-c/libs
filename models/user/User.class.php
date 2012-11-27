<?php

namespace models\user;

class User {
    protected $username;
    protected $email;
    
    public function __construct() {
        
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public static function getUsers() {
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
    }
}