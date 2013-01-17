<?php

/**
 * Mobile kitchen access analyse data access layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-4-21
 */

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Activity extends Mdal_Abstract
{
    protected $table_minifeed = 'res_minifeed';
    
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function insertMiniFeed($info)
    {
        $this->_wdb->insert($this->table_minifeed, $info);
    }
    
    public function getActivity($uid, $pageSize)
    {
    	$sql = "SELECT * FROM res_minifeed WHERE rcv_uid=:uid ORDER BY create_time DESC Limit $pageSize";
    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    public function getMiniFeedList($uid, $pageIndex, $pageSize) 
    {
    	$start = ($pageIndex - 1) * $pageSize;
        $sql = "SELECT * FROM res_minifeed WHERE rcv_uid=:uid
                ORDER BY create_time DESC LIMIT $start, $pageSize";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    public function getMiniFeedCount($uid)
    {
        $sql = 'SELECT COUNT(1) FROM res_minifeed WHERE rcv_uid=:uid ';
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
}