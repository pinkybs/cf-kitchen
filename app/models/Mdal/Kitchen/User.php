<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_User extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_user_profile';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_User
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getUser($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

 	/**
     * insert User
     *
     * @param array $info
     * @return integer
     */
    public function insertUser($info)
    {
        return $this->_wdb->insert($this->table_user, $info);
    }

    /**
     * check is join app
     *
     * @param integer $uid
     * @return boolean
     */
    public function isJoin($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid";
        $result = $this->_rdb->fetchOne($sql, array('uid' => $uid));
        return $result>0 ? true : false;
    }
    
    /**
     * update User
     *
     * @param array $info
     * @param integer $uid
     * @return integer
     */
    public function updateUser($info, $uid)
    {
        $where = $this->_wdb->quoteInto('uid=?', $uid);
        return $this->_wdb->update($this->table_user, $info, $where);
    }
    
    /**
     * update user table by a certain colname and $number
     *
     * @param string $uid
     * @param string $colName
     * @param string $number
     * @return integer
     */
    public function updateUserBy($uid, $colName, $number)
    {
        $sql = "UPDATE $this->table_user SET $colName=$colName + $number WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

	/**
     * get neighber friend uid
     * @param integer $uid
     * @param integer $profileUid
     * @param string $nextOrPrev[prev/next/first/last]
     * @return integer
     */
    public function getNeighberFriendUid($uid, $profileUid, $nextOrPrev, $fids)
    {
        $fids = $this->_rdb->quote($fids);

        $aryParm = array();
        $aryParm['uid'] = $uid;
        if ('prev' == $nextOrPrev) {
            $sql = "SELECT a.uid FROM
                           (SELECT uid FROM res_user_profile WHERE uid IN ($fids,:uid)) a
                    WHERE a.uid<:profile_uid ORDER BY a.uid DESC LIMIT 0,1 ";
            $aryParm['profile_uid'] = $profileUid;
        }
        else if ('next' == $nextOrPrev){
            $sql = "SELECT a.uid FROM
                           (SELECT uid FROM res_user_profile WHERE uid IN ($fids,:uid)) a
                    WHERE a.uid>:profile_uid ORDER BY a.uid LIMIT 0,1 ";
            $aryParm['profile_uid'] = $profileUid;
        }
        else if ('first' == $nextOrPrev) {
            $sql = "SELECT a.uid FROM
                           (SELECT uid FROM res_user_profile WHERE uid IN ($fids,:uid)) a
                    ORDER BY a.uid LIMIT 0,1 ";
        }
        else if ('last' == $nextOrPrev) {

            $sql = "SELECT a.uid FROM
                           (SELECT uid FROM res_user_profile WHERE uid IN ($fids,:uid)) a
                    ORDER BY a.uid DESC LIMIT 0,1 ";
        }
        return $this->_rdb->fetchOne($sql, $aryParm);
    }
    
    public function getFriendUids($fids, $pageStart, $pageSize, $rankBy)
    {
        if (empty($fids)) {
            $fids = '0';
        }
        else {
            $fids = $this->_rdb->quote($fids);
        }
        $sql = "SELECT uid FROM $this->table_user WHERE uid IN ($fids) ORDER BY total_exp $rankBy LIMIT $pageStart,$pageSize";
        return $this->_rdb->fetchAll($sql);
    }
    
    public function getFriendUids2($fids, $pageStart, $pageSize, $rankBy)
    {
        if (empty($fids)) {
            $fids = '0';
        }
        else {
            $fids = $this->_rdb->quote($fids);
        }
        $sql = "SELECT uid FROM $this->table_user WHERE uid IN ($fids) ORDER BY $rankBy LIMIT $pageStart,$pageSize";
        return $this->_rdb->fetchAll($sql);
    }
    
    public function cntFriendInKitchen($fids)
    {
        if (empty($fids)) {
            $fids = '0';
        }
        else {
            $fids = $this->_rdb->quote($fids);
        }
        $sql = "SELECT count(1) FROM $this->table_user WHERE uid IN ($fids)";
        return $this->_rdb->fetchOne($sql);
    }
}