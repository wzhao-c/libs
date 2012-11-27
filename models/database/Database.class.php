<?php

namespace models\database;

use models\database\DatabaseIterator;
use models\database\Exceptions\DBConnectionException,
    models\database\Exceptions\DBDatabaseSelectionException,
    models\database\Exceptions\DBQueryException,
    models\database\Exceptions\DBFetchFunctionException;

class Database {
    
    const MYSQL_ARRAY  = 'mysql_fetch_array';
    const MYSQL_ASSOC  = 'mysql_fetch_assoc';
    const MYSQL_ROW    = 'mysql_fetch_row';
    const MYSQL_OBJECT = 'mysql_fetch_object';
    
    private static $_instance = null;
    
    private $host     = null;
    private $user     = null;
    private $password = null;
    private $port     = null;
    private $database = null;
    
    private $_connection = false;
    
    private static $conn_params = array('host', 'user', 'password', 'port', 'database');
    
    private function __construct() {}
    
    private function __clone() {}
    
    public static function getInstance() {
        if (self::$_instance === null || !self::$_instance) {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    public function load($settings = array()) {
        if (is_array($settings)) {            
            foreach(self::$conn_params as $param) {
                if(isset($settings[$param])) {
                    $this->$param = $settings[$param];
                }
            }
        }
    }
    
    public function connect() {
        $this->_connection = @mysql_connect($this->host, $this->user, $this->password);
        
        if (!$this->isConnected()) {
            throw new DBConnectionException('DB Connection failed.');
        } elseif (!mysql_select_db($this->database, $this->_connection)) {
            throw new DBDatabaseSelectionException('Can\'t use database \'' . $this->database . '\'');
        }
    }
    
    public function disconnect() {
        if ($this->_connection !== false) {
            @mysql_close($this->_connection);
            $this->_connection = false;
        }
    }
    
    public function isConnected() {
        if (@mysql_ping($this->_connection)) {
            return true;
        }
        
        return false;
    }
    
    public function fetch($query, $type = self::MYSQL_ASSOC, $class = null) {
        // Try to reconnect the DB.
        if (!$this->_connection) {
            $this->connect();
        }
        
        // Get the database cursor
        $result = @mysql_query($query, $this->_connection);
        if (!$result) {
            throw new DBQueryException(mysql_error());
        }
        
        if ($type != self::MYSQL_ARRAY && $type != self::MYSQL_ASSOC && $type != self::MYSQL_ROW && $type != self::MYSQL_OBJECT) {            
            throw new DBFetchFunctionException('Function doesn\'t exist.');
        }
        
        $data = ($class) ? new DatabaseIterator($result, $type, $class) : new DatabaseIterator($result, $type);
        
        return ($data) ? $data : false;
    }
    
    public function import($filename) {}
    
    public function strPrep($value = null, $quotes = true) {
        if (is_string($value)) {
            if ($quotes) {
                return '"' . $value . '"';
            } else {
                return mysql_real_escape_string($value);
            }
        }
        
        if (is_numeric($value)) {
            return $value;
        }
        
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        
        if ($value === null) {
            return 'NULL';
        }
        
        return false;
    }
}