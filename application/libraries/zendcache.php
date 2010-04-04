<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MyCMS
 *
 * LICENSE: Some license information
 * 
 * @package	   MyCMS
 * @author	   Ladygin 
 * @copyright  Ladygin 2008
 * @version    0.1
*/


/**
 * Cache library
 *
 * Cache based on Zend_Cache
 * http://framework.zend.com/manual/ru/zend.cache.html
 *
 * @package	   MyCMS
 * @subpackage Cache
 * @category   Libraries
 * @version    0.5
 */
/**
 * Modified by Artyuh Anton
 *
 * private $sLng -- alias for language support
 * load & save added jnt new parametr -- NeedNameCreate. if TRUE -- $id = $id.$sLng
 * sorry about bad english (:
 */

class zendcache {
    
    /**
     * Object Zend Framework
     */
    private $Zend;
    
    /**
     * Fronted
     * 
     * @var string
     */
    private $frontend = 'Core';
    /**
     * Backend
     * 
     * @var string
     */
    private $backend  = 'File';
    /**
     * Frontend options
     * 
     * @var array
     */
    private $frontendOption = array();
    /**
     * Backend options
     * 
     * @var array
     */
    private $backendOption  = array();
    
    /**
     * Contaner for stored settings
     * 
     * @var array
     */
    private $store_settings = array();
    /**
     * Contaner for blockers settings
     * 
     * @var array
     */
    private $block = array();

    function __construct($params = array()) {
        
        ini_set('include_path',
        ini_get('include_path') . PATH_SEPARATOR . APPPATH.'/libraries/' );
		require_once (string) 'Zend/Cache' . EXT;
		
    	if (count($params) > 0) {
    	    
			$this->initialize($params);		
		}
	
		log_message('debug', "Cache Class Initialized");
    }
    
    /**
     * Initialize option and object
     * 
     * @param array $params settings
     */
	function initialize($params = array()) {
		if (count($params) > 0)	{
			foreach ($params as $key => $val) {
				if (isset($this->$key))	{
					$this->$key = $val;
				}
			}		
		}
	    
		$this->Zend = Zend_Cache::factory($this->frontend, $this->backend, $this->frontendOption, $this->backendOption);
	}    
    
	/**
	 * Change settings
	 * 
	 * @param string $name name of the setting
	 * @param string $value value of the setting
	 * @param boolean $store if == false do not bakup this setting
	 * @return boolean 
	 */
	public function set_settings($name, $value, $store = true) {
	    
	    if ( in_array($name, $this->block) ) {
	        
	        return false;
	    }
	    
	    $this->Zend->setOption($name, $value);
	    
	    if ( $store ) {
	        
	        $this->store_settings[$name] = $value;
	    }
	    return TRUE;
	}
    
	/**
	 * Change settings by defult
	 * 
	 * @return boolean
	 */
	public function restore_settings() {
	    
	    if ( count($this->store_settings) > 0 ) {
	        
	        foreach ($this->store_settings as $key => $val) {
	            
	            $this->set_settings($key, $val, false);
	        }
	    }
	    return TRUE;
	}
	
	/**
	 * Set block for settings name
	 * 
	 * @param string $name name of setting
	 * @return boolean
	 */
	public function set_block($name = null) {
	    
	    if ( $name == null) {
	        return false;
	    }
	    
	    if ( !in_array($name, $this->block)) {
	        
	        $this->block[] = $name;
	        return true;
	    }
	    return false;
	}
	
	/**
	 * Load data from cache
	 * 
	 * @param string $id cache id
	 * @param boolean $nameCreate is needto create id with language orientation
	 * @return mixed cached datas
	 */
    public function load($id) {
        $id = preg_replace('@[^a-zA-Z0-9_]@' , '_', $id);
        return $this->Zend->load($id, false, false);
    }
    
    /**
     * Save some data in a cache
     * 
     * @param mixed $data data to put in cache (can be another type than string if automatic_serialization is on)
     * @param cache $id cache id (if not set, the last cache id will be used)
     * @param array $tags cache tags
     * @param int $specificLifetime if != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean true if no problem
     */
    public function save($data, $id, $tags = array(), $time = false) {
        $id = preg_replace('@[^a-zA-Z0-9_]@' , '_', $id);
        if(strpos($time, 'x') === 0){
            $time = $this->frontendOption['lifetime'] * (int)substr($time, 1);
        }
        return $this->Zend->save($data, $id, $tags, $time);
    }
    
    /**
     * Remove some data from the cache
     * 
     * @param string $id cache id to remove
     * @return boolean true if ok
     */
    public function remove($id) {
        
        $this->Zend->remove($id);
    }
    
    /**
     * Clean cache entries
     * 
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => remove too old cache entries ($tags is not used)
     * 'matchingTag'    => remove cache entries matching all given tags
     *                     ($tags can be an array of strings or a single string)
     * 'notMatchingTag' => remove cache entries not matching one of the given tags
     *                     ($tags can be an array of strings or a single string)
     *
     * @param mixed $tags
     * @param string $mode
     * @return boolean true if ok
     */
    public function clean($tags = array(), $mode = 'matchingTag') {
        
        $this->Zend->clean($mode, $tags);
    }
}
?>