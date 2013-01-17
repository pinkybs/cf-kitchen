<?php

/**
 * Mobile kitchen gacha data access layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-14
 */

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Gacha extends Mdal_Abstract
{
    /**
     * gacha table name
     *
     * @var string
     */
    protected $table_user = 'res_user_gacha';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Gacha
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function insert($info)
    {
    	$this->_wdb->insert('res_user_gacha', $info);
    }
    
    public function getGacha($gachaId)
    {
        $sql = "SELECT * FROM res_shop_gacha WHERE gacha_id=:gachaId";
        return $this->_rdb->fetchRow($sql, array('gachaId' => $gachaId));
    }
    
    public function listGacha($pageStart = 0, $pageSize = 5)
    {
    	$sql = "SELECT * FROM res_shop_gacha LIMIT $pageStart,$pageSize";
        return $this->_rdb->fetchAll($sql);
    }
    
	public function cntListGacha()
    {
    	$sql = "SELECT COUNT(1) FROM res_shop_gacha";
        return $this->_rdb->fetchOne($sql);
    }

    public function getUserGacha($uid)
    {
    	$sql = "SELECT * FROM res_user_gacha WHERE uid=:uid";
        return $this->_wdb->fetchRow($sql, array('uid' => $uid));
    }
    
    //get one detail of gacha
    public function getGachaDetail($did, $dtable)
    {
    	$picFolder = $dtable == 'goods' ? 'zakka' : $dtable;
    	$picFolder = $picFolder == 'item' ? 'yorozu' : $picFolder;
    	$table = 'res_shop_' . $dtable;
    	$sql = "SELECT " . $dtable ."_id AS id," . $dtable ."_name AS name,"
    	 . $dtable ."_introduce AS introduce," . $dtable ."_picture AS picture,type,"
    	. '"' . $picFolder . '"' ." AS picfolder FROM $table WHERE " . $dtable ."_id=:did";
        return $this->_wdb->fetchRow($sql, array('did' => $did));
    }
    
    public function updateUserGacha($info, $uid)
    {
        $where = $this->_wdb->quoteInto('uid=?', $uid);
        return $this->_wdb->update($this->table_user, $info, $where);
    }
    
    public function hasData($uid, $did, $dtable)
    {
    	$table = 'res_user_' . $dtable;
    	$sql = "SELECT COUNT(1) FROM $table WHERE " . $dtable . "_id=:did AND uid=:uid";
        
    	$result = $this->_wdb->fetchOne($sql, array('did' => $did, 'uid' => $uid));
        return $result > 0;
    }
    
    public function resultUpdate($uid, $did, $dtable, $dcnt)
    {
    	$table = 'res_user_' . $dtable;
    	$sql = "UPDATE $table SET " . $dtable . "_count = " . $dtable . "_count + :dcnt WHERE " . $dtable . "_id =:did AND uid=:uid";
        return $this->_wdb->query($sql, array('did' => $did, 'uid' => $uid, 'dcnt' => $dcnt));
    }
    
    public function resultInsert($info, $dtable)
    {
    	$table = 'res_user_' . $dtable;
        return $this->_wdb->insert($table, $info);
    }
    
    public function getFoodCnt($foodId)
    {
    	$sql = "SELECT food_count FROM res_shop_food WHERE food_id=:foodId";
        
    	$result = $this->_wdb->fetchOne($sql, array('foodId' => $foodId));
        return $result > 0 ? $result : 0;
    }
    
    public function updateUserBy($uid, $colName, $number)
    {
        $sql = "UPDATE $this->table_user SET $colName = $colName + $number WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }
    public function getItemCnt($itemId)
    {
    	$sql = "SELECT item_count FROM res_shop_item WHERE item_id=:itemId";
        
    	$result = $this->_wdb->fetchOne($sql, array('itemId' => $itemId));
        return $result > 0 ? $result : 0;
    }
    
}