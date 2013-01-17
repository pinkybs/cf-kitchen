<?php

/**
 * Mobile kitchen daily link data access layer
 *
 * @copyright  Copyright (c) 2010 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-4-8
 */


require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Daily extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_user_daily';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Daily
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getDaily($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }
    
 	/**
     * insert Daily
     *
     * @param array $info
     * @return integer
     */
    public function insertDaily($info)
    {
        $this->_wdb->insert($this->table_user, $info);
    }

    /**
     * update Daily
     *
     * @param array $info
     * @param string $uid
     * @return integer
     */
    public function updateDaily($info, $uid)
    {
        $where = $this->_wdb->quoteInto('uid=?', $uid);
        return $this->_wdb->update($this->table_user, $info, $where);
    }
}