<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Memcache {
    const TAGS_CACHE_CLOUD = '__tags_cache_cloud';
    const TAGS_CACHE_CLOUD_LT = 86400; //60 * 60 * 24== сутки
    private $memcache;
    private $memcache_host = '127.0.0.1';
    private $memcache_port = 11211;
    private $memcache_compress = false;
    private $timelife = 300;

    private $sLng = '';

    public function __construct($params = array()){
        if(count($params) > 0){
            $this->initialize($params);
        }

        if($this->memcache_host && $this->memcache_port > 0){
            $this->memcache = memcache_connect($this->memcache_host, $this->memcache_port);

            if($this->memcache_compress){
                $this->memcache_compress = 'MEMCACHE_COMPRESSED';
            }
        }
        log_message('debug', 'MemCache Class Started');
    }
    /**
     * Initialize option and object
     *
     * @param array $params settings
     */
    function initialize($params = array()){
        if (count($params) > 0)	{
			foreach ($params as $key => $val){
				if (isset($this->$key))	{
					$this->$key = $val;
				}
			}
		}
        log_message('debug', 'MemCache Class Initialized');
	}

    /**
	 * Load data from cache
	 *
	 * @param string $id cache id
	 * @param boolean $nameCreate is needto create id with language orientation
	 * @return mixed cached datas
	 */
    public function load($valueID = '', $nameCreate = true){
        if($nameCreate){
            $valieID = $valueID . $this->sLng;
        }
        return memcache_get($this->memcache, $valueID);
    }

    public function save($data, $id, $tags = array(), $nameCreate = true, $time = null){
        if(is_null($time)){
            $time = $this->timelife;
        } else {
            if($time > 0) {
                $time = intval($time/1000);
            } else {
                $time = 0;
            }
        }


        if($nameCreate){
            $id = $id . $this->sLng;
        }
        if(!empty($tags)){
            $t = $this->load(self::TAGS_CACHE_CLOUD, false);
            if(!$t){
                $t = array();
            }

            $l = count($tags);
            for($i = 0; $i < $l; $i++){
                $t[$tags[$i]][] = $id;
            }

            memcache_set($this->memcache, self::TAGS_CACHE_CLOUD, $t, 
                         $this->memcache_compress, self::TAGS_CACHE_CLOUD_LT);
        }

        return memcache_set($this->memcache, $id, $data, $this->memcache_compress, $time);
    }

    /**
     * Remove one or more items from cache by key
     *
     * @param $valueID mixed one key or array
     * @return bool
     */
    public function remove($valueID){
        if($valueID == ''){
            return $this->clean(array(), 'all');
        }
        if(is_array($valueID)){
            $l = count($valueID);
            for($i = 0; $i < $l; $i++){
                memcache_delete($this->memcache, $valueID);
            }
        } else {
            return memcache_delete($this->memcache, $valueID);
        }
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
        if($mode == 'all'){
            return memcache_flush($this->memcache);
        }
        if($mode == 'old'){
            return true;
        }
        if(!empty($tags)){
            $t = $this->load(self::TAGS_CACHE_CLOUD, false);
            if(!$t){
                return true; //тегов нет
            } else {
                switch ($mode){
                    case 'matchingTag':{
                        foreach($t as $k => $v){
                            if(array_key_exists($k, $tags)){
                                $ret = $this->remove($v);
                            }
                        }
                        return true;
                        break;
                    }
                    case 'notMatchingTag';{
                        foreach($t as $k => $v){
                            if(!array_key_exists($k, $tags)){
                                $ret = $this->remove($v);
                            }
                        }
                        return true;
                    }
                }
            }
        } else {
            return false;
        }
    }

   /**
    * Закрываем соединение с Memcache
    *
    * @access   public
    * @return   boolean
    */
    public function __destruct(){
        return memcache_close($this->memcache);
    }
}

?>
