<?php
namespace models\loader;

class SplAutoLoader {
    const NS_SEPARATOR     = '\\';
    const PREFIX_SEPARATOR = '_';
    
    private $_extension = '.class.php';
    private $_resources = array();
    private $_prepend   = false;
    private $_fallback  = true;
    
    public function __construct() {}
    
    public function setFallBack($fallback) {
        $this->_fallback = (is_bool($fallback)) ? $fallback : true; 
    }
    
    public function getExtension() {
        return $this->_extension;
    }
    
    public function setExtension($ext) {
        $this->_extension = $ext;
    }
    
    public function setPrepend($prepend) {
        $this->_prepend = (is_bool($prepend)) ? $prepend : false; 
    }
    
    public function add($resource, $paths) {
        $this->_resources[$resource] = (is_array($paths)) ? $paths : array($paths);
    }
    
    public function register() {
        spl_autoload_register(array($this, 'autoload'), true, $this->_prepend);
    }
    
    public function unregister() {
        spl_autoload_unregister(array($this, 'autoload'));
    }
    
    protected function autoload($class) {
        // Remove '/' on the left
        $class = ltrim($class, DIRECTORY_SEPARATOR);
        
        $_fallback_class = '';
        
        if ($this->isNamespaced($class)) {
            $_saparator = self::NS_SEPARATOR;
            $_fallback_class .= str_replace(self::NS_SEPARATOR, DIRECTORY_SEPARATOR, $class);
        }
        
        if ($this->isPrefixed($class)) {
            $_saparator = self::PREFIX_SEPARATOR;
            $_fallback_class .= str_replace(self::PREFIX_SEPARATOR, DIRECTORY_SEPARATOR, $class);
        }
        
        // fallback autoloading
        if ($this->_fallback) {
            $_fallback_class .= $this->_extension;
            
            if (stream_resolve_include_path($_fallback_class) !== false) {
                require_once $_fallback_class;
            }
        }
        
        // Get trimmed class name
        $class_resource = '';
        $class_name = '';
        
        if (($last_saparator_pos = strripos($class, $_saparator)) !== false) {
            $class_resource = substr($class, 0, $last_saparator_pos);
            $class_name = substr($class, $last_saparator_pos + 1);
        }
        
        // namespace or prefix autoloading
        foreach($this->_resources as $resource => $paths) {
            if (strpos($class_resource, strtolower($resource)) !== false) {
                foreach($paths as $path) {
                    $file = $path . DIRECTORY_SEPARATOR . $class_name . $this->_extension;
                    
                    if (stream_resolve_include_path($file) !== false) {
                        require_once $file;
                    }
                }
            } 
        }
    }
    
    protected function isPrefixed($class) {
        if (strpos($class, self::PREFIX_SEPARATOR) !== false) {
            return true;
        }
        
        return false;
    }
    
    protected function isNamespaced($class) {
        if (strpos($class, self::NS_SEPARATOR) !== false) {
            return true;
        }
        
        return false;
    }
}

