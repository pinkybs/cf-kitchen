<?php

/**
 * Mobile kitchen amazon save/get file logic
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  hch  2010-2-3
 */

/** @see Zend_Service_Amazon_S3.php */
require_once 'Zend/Service/Amazon/S3.php';  

class Mbll_Kitchen_Amazon
{
    /**
     * Zend_Service_Amazon_S3 object
     *
     * @var Zend_Service_Amazon_S3
     */
    private static $_s3;
    
    /**
     * amazon bucket
     *
     * @var string
     */
    private static $_bucket;
    
    /**
     * save path
     *
     * @var string
     */
    private static $_path = 'KitchenFlash';
    
    /**
     * get amazon s3 object
     *
     */
    private static function _getS3()
    {
        //load configration
        require_once 'Bll/Config.php';
        $amazon = Bll_Config::get(CONFIG_DIR . '/amazon.xml');
    
        self::$_bucket = $amazon->bucket;
        self::$_s3 = new Zend_Service_Amazon_S3($amazon->key, $amazon->secret);
    }
    
    /**
     * get amazon base url
     *
     * @return string
     */
    public static function getBaseUrl()
    {
        if (self::$_bucket == null) {
            require_once 'Bll/Config.php';
            $amazon = Bll_Config::get(CONFIG_DIR . '/amazon.xml');        
            self::$_bucket = $amazon->bucket;
        }
        
        return 'http://s3.amazonaws.com/' . self::$_bucket . '/' . self::$_path . '/';
    }
    
    /**
     * check if a given object exists
     *
     * @param string $name
     * @return boolean
     */
    public static function hasObject($name)
    {
        if (self::$_s3 == null) {
            self::_getS3();
        }
        
        return self::$_s3->isObjectAvailable(self::$_bucket . '/' . self::$_path . '/' . $name);
    }
    
    /**
     * save flash object
     *
     * @param string $name
     * @param object $object
     * @return boolean
     */
    public static function saveObject($name, $object)
    {
        if (self::$_s3 == null) {
            self::_getS3();
        }

        $result = self::$_s3->putObject(self::$_bucket . '/' . self::$_path . '/' . $name, $object,
                        array(Zend_Service_Amazon_S3::S3_ACL_HEADER =>Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ));
                        
        return $result;
    }
    
    /**
     * get flash object
     *
     * @param string $name
     * @return object
     */
    public static function getObject($name)
    {
        if (self::$_s3 == null) {
            self::_getS3();
        }
        
        return self::$_s3->getObject(self::$_bucket . '/' . self::$_path . '/' . $name);
    }
    
    /**
     * remove flash object
     *
     * @param string $name
     */
    public static function removeObject($name)
    {
        if (self::$_s3 == null) {
            self::_getS3();
        }
        
        self::$_s3->removeObject(self::$_bucket . '/' . self::$_path . '/' . $name);
    }
}