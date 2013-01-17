<?php

/**
 * Mobile kitchen gold data access layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-3-1
 */

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Gold extends Mdal_Abstract
{
    /**
     * gold_log table name
     *
     * @var string
     */
    protected $table_user = 'res_user_gold_log';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Goods
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
 
        return self::$_instance;
    }

    public function getAllGold()
    {
    	$sql = "SELECT * FROM res_shop_gold";
        return $this->_rdb->fetchAll($sql);
    }
    
    public function getGold($goldId)
    {
        $sql = "SELECT * FROM res_shop_gold WHERE id=:goldId";
        return $this->_rdb->fetchRow($sql, array('goldId' => $goldId));
    }

    public function insertGoldLog($info)
    {
    	$this->_wdb->insert($this->table_user, $info);
        return $this->_wdb->lastInsertId();
    }

    public function updateGoldLogStatus($point_code, $status, $finishTime)
    {
        $sql = "UPDATE $this->table_user SET status=:status,finish_time=:finish_time WHERE point_code=:point_code";

        $this->_wdb->query($sql, array('status' => $status, 'finish_time' => $finishTime, 'point_code' => $point_code));
    }
    
    public function getGoldLogByCode($code, $status = 0)
    {
        $sql = "SELECT * FROM $this->table_user WHERE point_code=:code AND status=:status";

        return $this->_rdb->fetchRow($sql, array('code' => $code, 'status' => $status));
    }
    
    public function insertGoldLogSuccess($info)
    {
    	$this->_wdb->insert('res_user_gold_log_success', $info);
        return $this->_wdb->lastInsertId();
    }
    
    
    
    
}