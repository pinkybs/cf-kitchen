<?php

/**
 * Mobile kitchen gift data access layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-9
 */

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Gift extends Mdal_Abstract
{
    /**
     * gift table name
     *
     * @var string
     */
    protected $table_user = 'res_user_gift';

    protected static $_instance;

    /**
     * Enter description here...
     *
     * @return Mdal_Kitchen_Gift
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getGift($giftId)
    {
        $sql = "SELECT * FROM res_nb_gift WHERE gift_id=:giftId";
        return $this->_rdb->fetchRow($sql, array('giftId' => $giftId));
    }

    public function getFreeGift($giftId)
    {
        $sql = "SELECT * FROM res_nb_free_gift WHERE gift_id=:giftId";
        return $this->_rdb->fetchRow($sql, array('giftId' => $giftId));
    }
    
    public function listGift($pageStart = 0, $pageSize = 5, $type = 1)
    {
    	if ($type == 1) {
    		$sql = "SELECT * FROM res_nb_gift ORDER BY gift_id ASC LIMIT $pageStart,$pageSize";

    	}
    	else if ($type == 2) {
    		$sql = "SELECT * FROM res_nb_levelup_gift ORDER BY gift_id ASC LIMIT $pageStart,$pageSize";

    	}
    	else if ($type == 3) {
    		$sql = "SELECT * FROM res_nb_visit_gift ORDER BY gift_id ASC LIMIT $pageStart,$pageSize";

    	}
    	else if ($type == 4) {
            $sql = "SELECT * FROM res_nb_free_gift ORDER BY gift_id ASC LIMIT $pageStart,$pageSize";

        }
   	    else if ($type == 5) {
            $sql = "SELECT * FROM res_nb_campain_gift ORDER BY gift_id ASC LIMIT $pageStart,$pageSize";

        }
        
        return $this->_rdb->fetchAll($sql);

    }


	public function cntListGift($type = 1)
    {

    	if ($type == 1) {
    		$sql = "SELECT COUNT(1) FROM res_nb_gift";

    	}
    	else if ($type == 2) {
    		$sql = "SELECT COUNT(1) FROM res_nb_levelup_gift";

    	}
    	else if ($type == 3) {
    		$sql = "SELECT COUNT(1) FROM res_nb_visit_gift";
    	}
        else if ($type == 4) {
            $sql = "SELECT COUNT(1) FROM res_nb_free_gift";

        }
    	else if ($type == 5) {
            $sql = "SELECT COUNT(1) FROM res_nb_campain_gift";

        }
        
    	return $this->_rdb->fetchOne($sql);
    }

    //for send gift actions
    public function listMyGift($uid, $pageStart = 0, $pageSize = 5, $type = 1)
    {
    	if ($type == 1) {
    		$sql = "SELECT g.* FROM res_nb_gift AS g,res_user_gift AS u
    		        WHERE u.gift_id = g.gift_id AND u.uid=:uid
    		        GROUP BY g.gift_id ORDER BY g.gift_id LIMIT $pageStart,$pageSize";
    	}
    	/*
    	else if ($type == 2) {
    		$sql = "SELECT * FROM res_nb_levelup_gift ORDER BY gift_id ASC LIMIT $pageStart,$pageSize";

    	}
    	else if ($type == 3) {
    		$sql = "SELECT * FROM res_nb_visit_gift ORDER BY gift_id ASC LIMIT $pageStart,$pageSize";

    	}
    	*/
    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));

    }


	public function cntListMyGift($uid, $type = 1)
    {

    	if ($type == 1) {
    		$sql = "SELECT COUNT(1) FROM (SELECT g.* FROM res_nb_gift AS g,res_user_gift AS u
    		        WHERE u.gift_id = g.gift_id AND u.uid=:uid
    		        GROUP BY g.gift_id ORDER BY g.gift_id) AS a";
    	}
    	/*
    	else if ($type == 2) {
    		$sql = "SELECT COUNT(1) FROM res_nb_levelup_gift";

    	}
    	else if ($type == 3) {
    		$sql = "SELECT COUNT(1) FROM res_nb_visit_gift";

    	}
    	*/
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

	public function cntMyGift($uid, $pageStart = 0, $pageSize = 5, $type = 1)
    {

    	if ($type == 1) {
    		//$sql = "SELECT COUNT(1) FROM res_user_gift WHERE gift_id=:giftId AND uid=:uid";
        	$sql = "SELECT COUNT(1) AS sum FROM res_nb_gift AS g,res_user_gift AS u
    		        WHERE u.gift_id = g.gift_id AND u.uid=:uid
    		        GROUP BY g.gift_id ORDER BY g.gift_id LIMIT $pageStart,$pageSize;";
    	}
    	/*
    	else if ($type == 2) {
    		$sql = "SELECT COUNT(1) FROM res_nb_levelup_gift";

    	}
    	else if ($type == 3) {
    		$sql = "SELECT COUNT(1) FROM res_nb_visit_gift";

    	}
        */
    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    public function hasGiftByUid($uid)
    {
        $sql = "SELECT COUNT(gid) FROM $this->table_user WHERE uid=:uid";
        $result = $this->_wdb->fetchOne($sql, array('uid' => $uid));
        return $result > 0;
    }

    public function hasGift($uid, $giftId)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE gift_id=:giftId AND uid=:uid";
        $result = $this->_wdb->fetchOne($sql, array('giftId' => $giftId, 'uid' => $uid));
        return $result > 0;
    }

 	/**
     * insert gift
     *
     * @param array $info
     * @return integer
     */
    public function insertGift($info)
    {
        return $this->_wdb->insert($this->table_user, $info);
    }

    public function updateGift($info, $id)
    {
        $where = array($this->_wdb->quoteInto('id=?', $id));

        $this->_wdb->update('res_send_gift', $info, $where);
    }
    
    public function addGiftNum($uid, $giftId, $giftCountAdd)
    {
    	$sql = "UPDATE $this->table_user SET gift_count = gift_count + :giftCountAdd WHERE gift_id=:giftId AND uid=:uid";
        return $this->_wdb->query($sql, array('giftId' => $giftId, 'uid' => $uid, 'giftCountAdd' => $giftCountAdd));
    }

    //send gift
	public function insertSendGift($info)
    {
        $this->_wdb->insert('res_send_gift', $info);
        return $this->_wdb->lastInsertId();
    }

	public function deleteSendGift($id)
    {
        $sql = "DELETE FROM res_send_gift WHERE id=:id ";
        return $this->_wdb->query($sql, array('id' => $id));
    }

	public function deleteUserGift($gid)
    {
        $sql = "DELETE FROM res_user_gift WHERE gid=:gid ";
        return $this->_wdb->query($sql, array('gid' => $gid));
    }

    public function deleteUserGiftByType($uid, $type)
    {
        $sql = "DELETE FROM res_send_gift WHERE target_uid=:target_uid AND type = :type";
        return $this->_wdb->query($sql, array('target_uid' => $uid, 'type' => $type));
    }
    
    public function getOneUserGift($uid, $giftId)
    {
        $sql = "SELECT * FROM $this->table_user WHERE gift_id=:giftId AND uid=:uid LIMIT 0,1";
        $result = $this->_rdb->fetchRow($sql, array('giftId' => $giftId, 'uid' => $uid));
        return $result;
    }

    public function getOneUserGiftByGid($gid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE gid=:gid";
        $result = $this->_rdb->fetchRow($sql, array('gid' => $gid));
        return $result;
    }
    
    public function getUserGiftByType($uid, $giftType)
    {
        $sql = "SELECT * FROM res_send_gift WHERE target_uid=:target_uid AND type = :type LIMIT 0,1";
        return $this->_rdb->fetchRow($sql, array('target_uid' => $uid, 'type' => $giftType));
    }

    //add by zx
	public function hasGiftFromFriend($target_uid)
    {
        $sql = "SELECT uid FROM res_send_gift WHERE target_uid=:target_uid AND uid>0 AND type=1 ORDER BY create_time LIMIT 0,1";
        return $this->_wdb->fetchOne($sql, array('target_uid' => $target_uid));
    }

	public function hasGiftFromLevelUp($target_uid)
    {
        $sql = "SELECT gift_id FROM res_send_gift WHERE target_uid=:target_uid AND type=2 ORDER BY create_time LIMIT 0,1";
        return $this->_wdb->fetchOne($sql, array('target_uid' => $target_uid));
    }

	public function hasGiftFromVisit($target_uid)
    {
        $sql = "SELECT gift_id FROM res_send_gift WHERE target_uid=:target_uid AND type=3 ORDER BY create_time LIMIT 0,1";
        return $this->_wdb->fetchOne($sql, array('target_uid' => $target_uid));
    }

    public function hasGiftFromAll($target_uid)
    {
        $sql = "SELECT gift_id FROM res_send_gift WHERE target_uid=:target_uid ORDER BY create_time LIMIT 0,1";
        return $this->_rdb->fetchOne($sql, array('target_uid' => $target_uid));
    }
    
    /***************** add by shenhw**********************/
    /**
     * get my gift list
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @param string $orderType
     * @param string $type
     * @return array
     */
    public function getMyGiftList($uid, $pageIndex = 1, $pageSize = 10, $orderType, $order)
    {
        $start = ($pageIndex - 1) * $pageSize;

        $sql = "SELECT * FROM res_send_gift WHERE target_uid = :uid
                ORDER BY  $orderType $order, id ASC
                LIMIT $start, $pageSize";

        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));

        return $result;
    }

    /**
     * get my gift count
     * @param integer $uid
     * @return integer
     */
    public function getMyGiftCount($uid)
    {
        $sql = "SELECT COUNT(1) FROM res_send_gift WHERE target_uid = :uid";

        $result = $this->_rdb->fetchOne($sql, array('uid' => $uid));

        return $result;
    }

    /**
     * get my gift count
     * @param integer $uid
     * @return integer
     */
    public function getSendGiftById($id)
    {
        $sql = "SELECT * FROM res_send_gift WHERE id=:id ";
        return $this->_wdb->fetchRow($sql, array('id' => $id));
    }

    /************** add by shenhw ***********************/

    //send gift
    public function insertFreeSendGift($info)
    {
        return $this->_wdb->insert('res_send_free_gift_history', $info);
    }
    
    public function updateFreeGift($date, $actor, $target)
    {
        $sql = "UPDATE res_send_free_gift_history SET action_date = :action_date WHERE actor=:actor AND target=:target";
        return $this->_wdb->query($sql, array('action_date' => $date, 'actor' => $actor, 'target' => $target));
    }
    
    //for send gift actions
    public function getSendFriends($date, $uid)
    {
        $sql = "SELECT target FROM res_send_free_gift_history
            WHERE action_date = :date AND actor = :actor";

        return $this->_rdb->fetchAll($sql, array('date' => $date, 'actor' => $uid));
    }
    
    //for send gift actions
    public function getFreeSendByFid($uid, $fid)
    {
        $sql = "SELECT * FROM res_send_free_gift_history
            WHERE actor = :actor AND target = :target";

        return $this->_rdb->fetchOne($sql, array('actor' => $uid, 'target' => $fid));
    }
    
    public function getCampain($uid, $campainId)
    {
        $sql = "SELECT * FROM res_user_campain_result
            WHERE uid = :uid AND campain_id = :campainId";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'campainId' => $campainId));
    }
    
    public function addCampain($uid, $campainId, $result)
    {
        $sql = "INSERT res_user_campain_result
            SET uid = :uid,campain_id = :campainId,result=:result";

        return $this->_wdb->query($sql, array('uid' => $uid, 'campainId' => $campainId, 'result' => $result));
    }

    //send gift
    public function insertFreeGiftTemp($info)
    {
        $sql = "INSERT INTO res_free_gift_temp values (:uid, :target_uids, :gid, :create_time)
                ON DUPLICATE KEY
                UPDATE target_uids = :target_uids ,gid = :gid, create_time = :create_time";
                
        return $this->_wdb->query($sql, array('uid' => $info['uid'], 'target_uids' => $info['target_uids'], 'gid' => $info['gid'], 'create_time' => $info['create_time']));
    }
    //send gift
    public function updateFreeGiftTemp($uid)
    {
        $sql = "UPDATE res_free_gift_temp SET target_uids = null, gid = null, create_time = null WHERE uid = :uid";
                
        return $this->_wdb->query($sql, array('uid' => $uid));
    }
    
    
    public function getFreeGiftTempByUid($uid)
    {
        $sql = "SELECT * FROM res_free_gift_temp WHERE uid = :uid";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }
}