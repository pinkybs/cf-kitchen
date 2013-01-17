<?php

/**
 * Admin Dal Mykitchen
 * Mixi Admin Mykitchen Data Access Layer
 *
 * @package    Admin/Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2010/04/02    xial
 */
class Admin_Dal_Mykitchen extends Admin_Dal_Abstract
{

    /**
     * class default instance
     * @var self instance
     */
    protected static $_instance;

    protected $table_res_user_profile = 'res_user_profile';

    protected $table_res_invite = 'res_invite';

    protected $table_res_invite_success = 'res_invite_success';


    /**
     * return self's default instance
     *
     * @return self instance
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * search user info
     *
     * @param integer $uid
     * @param array $uids
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function getUserSearch($uid, $uids, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;

        $sql = "SELECT uid,gold,point,last_login_time FROM $this->table_res_user_profile";

        if ($uids) {
        	$uids = $this->_rdb->quote($uids);;
        }

        if ($uid && $uids) {
            $uids .= "," .$uid;
        }
        elseif ($uid && empty($uids)) {
            $uids = $uid;
        }

        if ($uids && $uids != "''") {
            $sql .= " WHERE uid IN ($uids) ";
        }

        $sql .= " LIMIT $start,$pagesize ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * search user count
     *
     * @param integer $uid
     * @param array $uids
     * @return array
     */
    public function getUserSearchCount($uid, $uids)
    {
        $sql = "SELECT COUNT(*) FROM $this->table_res_user_profile ";

        if ($uids) {
        	$uids = $this->_rdb->quote($uids);;
        }

        if ($uid && $uids) {
            $uids .= "," .$uid;
        }
        elseif ($uid && empty($uids)) {
            $uids = $uid;
        }

        if ($uids && $uids != "''") {
            $sql .= " WHERE uid IN ($uids) ";
        }

        return $this->_rdb->fetchOne($sql);
    }

    /**
     * search name's id
     *
     * @param string $name
     * @return array
     */
    public function getUidByLikeName($name)
    {
        $uids = array();
        $name = "'%" . $name . "%'";
        for ( $i = 0; $i < 10; $i ++ )
        {
            $uidAry = array();

            $sql = "SELECT id FROM mixi_user_$i WHERE displayName LIKE $name ";
            $uidAry = $this->_rdb->fetchAll($sql);

            $uids = array_merge($uids, $uidAry);
        }

        return $uids;
    }

    /**
     * check user is remove app
     *
     * @param integer $appId
     * @param integer $uid
     * @return array
     */
    public function isRemoveApp($appId = 16235, $uid)
    {
        $sql = "SELECT id FROM app_remove_log WHERE app_id = :appId AND uid = :uid";

        return $this->_rdb->fetchRow($sql, array('appId' => $appId, 'uid' => $uid));
    }

    /**
     * get user info by id
     *
     * @param integer $uid
     * @return array
     */
    public function getUserInfoByUid($uid)
    {
        $sql = "SELECT uid,FORMAT(gold, 0) as gold,FORMAT(point, 0) as point,last_login_time FROM $this->table_res_user_profile WHERE uid = :uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get in app friend
     *
     * @param array $fids
     * @return array
     */
    public function getUserFriends($fids)
    {
        //$fids = $this->_rdb->quote($fids);

        $sql = "SELECT uid FROM $this->table_res_user_profile WHERE uid IN ($fids)";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get
     *
     * @param integer $uid
     * @return array
     */
    public function getSendInviteInfo($uid)
    {
        $sql = "SELECT * FROM $this->table_res_invite WHERE actor = :uid";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get
     *
     * @param integer $uid
     * @return array
     */
    public function getUsedInviteInfo($uid)
    {
        $sql = "SELECT * FROM $this->table_res_invite_success WHERE uid = :uid";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get active restaurant
     *
     * @param integer $uid
     * @return array
     */
    public function getActiveRestaurant($uid)
    {
        $sql = "SELECT * FROM res_user_restaurant WHERE uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user already used Restaurant
     *
     * @param integer $uid
     * @return integer
     */
    public function getUsedRestaurant($uid)
    {
        $sql = " SELECT genre FROM res_user_restaurant WHERE uid=:uid AND in_use = 1";
        return $this->_rdb->fetchone($sql, array('uid' => $uid));
    }

    /**
     * get user gacha count
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserGachaCount($uid)
    {
        $sql = "SELECT gacha_count FROM res_user_gacha WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

 	/**
     * get user invite
     *
     * @param integer $targetUid
     * @return array
     */
    public function getInviteUserById($targetUid)
    {
        $sql = "SELECT uid FROM res_invite_success WHERE target_uid = :uid AND is_fortune_used = 1";
        return $this->_rdb->fetchRow($sql, array('uid' => $targetUid));
    }

 	/**
     * get user invite
     *
     * @param integer $targetUid
     * @return array
     */
    public function getUserInviteSuccess($uid)
    {
        $sql = "SELECT target_uid FROM res_invite_success WHERE uid = :uid AND is_fortune_used = 1";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user shopping visit
     *
     * @param integer $uid
     * @param string $tableName
     * @param string $startTime
     * @param string $endTime
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function getUserBuyInfoById($uid, $buyType, $startTime, $endTime, $pageindex = 1, $pagesize = 20)
    {
        $start = ($pageindex - 1) * $pagesize;

        $tableName = "res_access_" . $buyType;
        $sql = "SELECT * FROM $tableName WHERE uid = :uid ";

        if ($buyType != 'gacha') {
        	$sql .= " AND create_type = '購入' " ;
        }

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);

        	$sql .= " AND buy_time >= $startTime and buy_time <= $endTime ";
        }
        else if ($startTime && empty($endTime)){
            $startTime = strtotime($startTime);
            $sql .= " AND buy_time >= $startTime" ;
        }
        /*else {
            $defaultTime = strtotime($startTime);
            $sql .= " AND buy_time >= $defaultTime" ;
        }*/
        $sql .= " LIMIT $start,$pagesize ";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * count user shopping
     *
     * @param integer $uid
     * @param string $buyType
     * @return integer
     */
    public function getUserBuyInfoCntById($uid, $buyType, $startTime, $endTime)
    {
        $tableName = "res_access_" . $buyType;
        $sql = "SELECT COUNT(*) FROM $tableName WHERE uid = :uid ";

        if ($buyType != 'gacha') {
        	$sql .= " AND create_type = '購入' " ;
        }

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);

        	$sql .= " AND buy_time >= $startTime and buy_time <= $endTime ";
        }
        else if ($startTime && empty($endTime)){
            $startTime = strtotime($startTime);
            $sql .= " AND buy_time >= $startTime" ;
        }

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * insert buy info
     *
     * @param string $buyType
     * @param array $info
     * @return integer
     */
    public function insert($buyType, $info)
    {
        $tableName = "res_access_" . $buyType;
        return $this->_wdb->insert($tableName, $info);
    }

    /**
     * select nb list
     *
     * @param string $buyType
     * @param string $colName
     * @param string $colId
     *
     * @return array
     */
    public function selectNbListByType($tableName, $colName, $colId)
    {
        $sql = "SELECT $colName AS name, $colId AS id FROM $tableName";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get action list by id
     *
     * @param unknown_type $uid
     * @param unknown_type $tableName
     * @param unknown_type $startTime
     * @param unknown_type $endTime
     * @param unknown_type $pageindex
     * @param unknown_type $pagesize
     * @param unknown_type $typeId
     * @return unknown
     */
    public function getActionListById($uid, $tableName, $startTime, $endTime, $pageindex = 1, $pagesize = 20, $typeId = '')
    {
        $start = ($pageindex - 1) * $pagesize;

        $sql = "SELECT * FROM $tableName WHERE uid = :uid ";

        $aryParm['uid'] = $uid;
        if (!empty($typeId)) {
            $sql .= " AND shop_id = :typeId ";
            $aryParm['typeId'] = $typeId;
        }

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);

        	$sql .= " AND buy_time >= $startTime and buy_time <= $endTime ";
        }
        else if ($startTime && empty($endTime)){
            $startTime = strtotime($startTime);
            $sql .= " AND buy_time >= $startTime" ;
        }

        $sql .= " LIMIT $start,$pagesize ";

        return $this->_rdb->fetchAll($sql, $aryParm);
    }

    /**
     * get action count by id
     *
     * @param integer $uid
     * @param string $tableName
     * @param string $startTime
     * @param string $endTime
     * @param string $typeId
     * @return integer
     */
    public function getActionCntById($uid, $tableName, $startTime, $endTime, $typeId)
    {
        $sql = "SELECT COUNT(*) FROM $tableName WHERE uid = :uid ";

        $aryParm['uid'] = $uid;
        if (!empty($typeId)) {
            $sql .= " AND shop_id = :typeId ";
            $aryParm['typeId'] = $typeId;
        }

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);

        	$sql .= " AND buy_time >= $startTime and buy_time <= $endTime ";
        }
        else if ($startTime && empty($endTime)){
            $startTime = strtotime($startTime);
            $sql .= " AND buy_time >= $startTime" ;
        }

        return $this->_rdb->fetchOne($sql, $aryParm);
    }
}