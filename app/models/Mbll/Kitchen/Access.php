<?php

/**
 * Mobile kitchen access logic
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-3-5
 */
require_once 'Bll/Cache.php';

class Mbll_Kitchen_Access
{
    private static $_mdalAccess = null;

    private static $_prefix = 'Mbll_Kitchen_Access';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    public static function getMdalAccess()
    {
        if (self::$_mdalAccess === null) {
            require_once 'Mdal/Kitchen/Access.php';
            self::$_mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
        }

        return self::$_mdalAccess;
    }

    /**
     * daily insert uu
     *
     * @param string $uid
     * @param string $type
     * @return boolean   if inserted, return true. if didnot insert,return false;
     */
	public static function tryInsert($uid, $type)
    {
    	if ($type == 1 || $type == 2) {
    		//chef edit || edit name
	        $key = self::getCacheKey('getAccess100318', $uid .'_'. $type);

	        if (!$result = Bll_Cache::get($key)) {

	            $mdalAccess = self::getMdalAccess();
	        	try {
	        		$result = $mdalAccess->insertUu(array('type' => $type,
	            								  		  'create_time' => time()));
	        	}
	        	catch (Exception $e){
		        }

	            if ($result) {
	                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_MAX);
	                return true;
	            }
	        }
    	}//xial2010-04-15: 13:gift uu; 15:friend味付UU; 17:friend味見UU; 20:free gift送信数; 22:カキコミUU;
    	//23:communication feed配信UU;25:調理開始UU
    	else if (in_array($type, array(10, 13, 15, 17, 20, 22, 23, 25))) {
    		//friend home daily uu
    		$key = self::getCacheKey('getAccess100318', $uid .'_'. $type);

	        if (!$result = Bll_Cache::get($key)) {

	            $mdalAccess = self::getMdalAccess();
	        	try {
	        		$result = $mdalAccess->insertUu(array('type' => $type,
	            								  		  'create_time' => time()));
	        	}
	        	catch (Exception $e){
		        }

	            if ($result) {
	                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_ONE_DAY);
	                return true;
	            }
	        }
    	}
    	else {
    		$key = self::getCacheKey('getAccess100318', $uid .'_'. '1');

    		$result = Bll_Cache::get($key);
    		if (!empty($result)) {
    			//new login user DO uu 3-8
    			$key = self::getCacheKey('getAccess100318', $uid .'_'. $type);

	    		if (!$result = Bll_Cache::get($key)) {

		            $mdalAccess = self::getMdalAccess();
		        	try {
		        		$result = $mdalAccess->insertUu(array('type' => $type,
		            								  		  'create_time' => time()));
		        	}
		        	catch (Exception $e){
			        }

		            if ($result) {
		                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_MAX);
		                return true;
		            }
		        }
    		}
    	}

        return false;
    }

   	public static function clearUuCache($uid, $type)
    {
        Bll_Cache::delete(self::getCacheKey('getAccess100318', $uid .'_'. $type));
    }

	// add by xial
	/**
	 * write activity access
	 *
	 * @param integer $uid
	 * @param integer $typeUu
	 * @param integer $typePv
	 */
	public static function insertAccess($uid, $typeUu, $typePv)
	{
	    //xial 2010-04-21 UU,pv
        try {
            $mdalKitchenAccess = Mdal_Kitchen_Access::getDefaultInstance();
            //pv
            $mdalKitchenAccess->insertUu(array('type' => $typePv, 'create_time' => time()));
        }
        catch (Exception $e){
        }
        //UU
        self::tryInsert($uid, $typeUu);
	}
}