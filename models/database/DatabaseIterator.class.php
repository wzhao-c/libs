<?php

namespace models\database;

use models\database\Database,
    models\database\Exceptions\DBFetchException;

class DatabaseIterator implements \Iterator, \Countable {
    
    private $type   = null;
    private $cursor = null;
    private $class  = null; 
    
    private $_current = null;
    private $_key     = 0;
    
    public function __construct($cursor, $type, $class = 'stdClass') {
        // Check the class exsits. 
        try {
            $ref_cls = new \ReflectionClass($class);
            $this->class = $ref_cls->getName();
        } catch (ReflectionException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
        
        if (!empty($cursor) && !empty($type)) {
            $this->cursor = $cursor;
            $this->type = $type;    
            
            $this->next();
        } else {
            throw new \InvalidArgumentException('Cursor or Type can not be empty.');
        }
        
        return false;
    }
    
    public function __destruct() {
        if ($this->cursor) {
            mysql_free_result($this->cursor);
        }
    }
    
    public function count() {
        return mysql_num_rows($this->cursor);
    }
    
    public function current() {
        return $this->_current;
    }
    
    public function key() {
        return $this->_key;
    }
    
    public function next() {
        if ($this->count()) {
            $func = $this->type;
            
            if ($func == Database::MYSQL_OBJECT) {
                $this->_current = $func($this->cursor, $this->class);
            } else {
                $this->_current = $func($this->cursor);
            }
            
            if ($this->_current !== false) {
                $this->_key++;
            }
        }
    }
    
    public function rewind() {
        $this->_key = 0;
        mysql_data_seek($this->cursor, 0);
        
        $this->next();
    }
    
    public function valid() {
        return ($this->_current === false) ? false : true;
    }
}

