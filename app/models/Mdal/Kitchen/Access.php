<?php

/**
 * Mobile kitchen access analyse data access layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-3-4
 */

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Access extends Mdal_Abstract
{
    protected $table_money = 'res_access_money';
    protected $table_uu = 'res_access_uu';
    
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function insertMoney($info)
    {
        $this->_wdb->insert($this->table_money, $info);
    }
    
    public function insertUu($info)
    {
        $this->_wdb->insert($this->table_uu, $info);
        return $this->_wdb->lastInsertId();
    }
}