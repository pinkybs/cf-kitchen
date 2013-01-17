<?php

/**
 * Mobile kitchen tt save/get file logic
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  hch  2010-3-2
 */

class Mbll_Kitchen_Tt
{    
    private $_tt;
    
    private $_path = array('10.192.183.240', '10.211.19.175');    
    
    private $_port = 1978;
    
    private $_uid;
    
    /**
     * init the user's variables
     *
     * @param array $config ( config info )
     */
    public function __construct($uid)
    {
        $this->_uid = $uid;
        $serverId = $uid % 2;
        $this->_tt = new TokyoTyrant($this->_path[$serverId], $this->_port);
    }    
    
    /**
     * get amazon base url
     *
     * @return string
     */
    public function getFlashUrl($key)
    {
        return Zend_Registry::get('host') . '/mobile/flash/index/CF_uid/' . $this->_uid . '/CF_key/' . $key;
    }
    
    /**
     * check if a given object exists
     *
     * @param string $key
     * @return boolean
     */
    public function hasObject($key)
    {        
        $result = $this->getObject($key);
        
        return empty($result) ? false : true;
    }
    
    /**
     * save flash object
     *
     * @param string $key
     * @param object $value
     * @return boolean
     */
    public function saveObject($key, $value)
    {
        try {
            $this->_tt->put($key, $value);
        }
        catch (Exception $e) {
            info_log('Server ' . $this->_uid . ' error. ' . PHP_EOL . $e->getTrace(), 'Tt_Error');
            return false;
        }
        
        return true;
    }
    
    /**
     * get flash object
     *
     * @param string $key
     * @return object
     */
    public function getObject($key)
    {        
        return $this->_tt->get($key);
    }
    
    /**
     * remove flash object
     *
     * @param string $key
     */
    public function removeObject($key)
    {        
        $this->_tt->out($key);
    }
}