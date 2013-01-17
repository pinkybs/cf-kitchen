<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Kitchen
 * MixiApp Kitchen Visit Data Access Layer
 *
 * @package    Mdal/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class Mdal_Kitchen_Visit extends Mdal_Abstract
{

    /**
     * class default instance
     * @var self instance
     */
    protected static $_instance;

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
     * list visit foot by uid (no repeat user)
     *
     * @param integer $visit_uid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function listVisitFoot($visit_uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM res_visit WHERE uid=:uid
                ORDER BY update_time DESC LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql, array('uid' => $visit_uid));
    }

    /**
     * get visit foot count (no repeat user)
     *
     * @param integer $visit_uid
     * @return integer
     */
    public function getVisitFootCount($visit_uid)
    {
        $sql = 'SELECT COUNT(*) FROM res_visit WHERE uid=:uid ';
        return $this->_rdb->fetchOne($sql, array('uid' => $visit_uid));
    }

    /**
     * get visit foot count all (has repeat user)
     *
     * @param integer $visit_uid
     * @return integer
     */
    public function getVisitFootCountAll($visit_uid)
    {
        $sql = 'SELECT IFNULL(SUM(visit_count),0) AS count_all FROM res_visit WHERE uid=:uid ';
        return $this->_rdb->fetchOne($sql, array('uid' => $visit_uid));
    }

	/**
     * get visit foot count by date
     *
     * @param integer $visit_uid
     * @param string $date
     * @return integer
     */
    public function getVisitFootCountByDate($visit_uid, $date)
    {
        $sql = 'SELECT IFNULL(SUM(visit_count),0) AS cnt FROM res_visit WHERE uid=:uid AND action_date=:action_date';
        return $this->_rdb->fetchOne($sql, array('uid' => $visit_uid, 'action_date' => $date));
    }

    /**
     * get visit foot by key
     *
     * @param integer $uid
     * @param integer $visit_uid
     * @param string $date
     * @return integer
     */
    public function getVisitFoot($uid, $visit_uid, $date)
    {
        $sql = "SELECT * FROM res_visit WHERE uid=:uid AND visit_uid=:visit_uid AND action_date=:action_date ";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'visit_uid' => $visit_uid, 'action_date' => $date));
    }

    /**
     * insert visit foot
     *
     * @param array $info
     * @return integer
     */
    public function insertVisitFoot($info)
    {
        return $this->_wdb->insert('res_visit', $info);
    }

    /**
     * update visit foot
     *
     * @param integer $uid
     * @param integer $visit_uid
     * @param string $date
     * @param integer $updateTime
     * @param integer $action
     * @return integer
     */
    public function updateVisitFoot($uid, $visit_uid, $date, $updateTime, $action=2)
    {
        $sql = "UPDATE res_visit SET visit_count=visit_count+1,update_time=:update_time,action=:action
                WHERE uid=:uid AND visit_uid=:visit_uid AND action_date=:action_date ";
        return $this->_wdb->query($sql, array('update_time' =>$updateTime, 'action' => $action, 'uid' => $uid, 'visit_uid' => $visit_uid, 'action_date' => $date));
    }



    /**************************** add by shenhw **************************************************/

    /**
     * get visit list by current uid
     *
     * @param integer $uid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function getVisitList($uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM res_visit WHERE uid=:uid
                ORDER BY update_time DESC, visit_uid ASC LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get visit count by current uid
     *
     * @param integer $uid
     * @return integer
     */
    public function getVisitCount($uid)
    {
        $sql = 'SELECT COUNT(*) FROM res_visit WHERE uid=:uid ';
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    /*******************add by zhaoxh ***********************/
    public function insertOpenAlert($friendArr, $sendUid, $shopName)
    {
    	$nowTime = time();
    	$sql = "";
    	foreach ($friendArr as $var) {
    		$sql .= "INSERT res_open_alert SET uid=$var,send_uid=:sendUid,shop_name=:shopName,create_time=:nowTime ON DUPLICATE KEY UPDATE create_time=:nowTime;";
    	}
    	
    	return $this->_wdb->query($sql, array('sendUid' => $sendUid, 'shopName'=>$shopName, 'nowTime' => $nowTime));
    }
    
    public function getOpenAlertAll($uid)
    {
        $sql = "SELECT * FROM res_open_alert WHERE uid=:uid";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    public function deleteOpenAlert($uid , $sendUid)
    {
        $sql = "DELETE FROM res_open_alert WHERE uid=:uid AND send_uid=:sendUid";

        $this->_wdb->query($sql, array('uid' => $uid, 'sendUid' => $sendUid));
    }
    

}