<?php

/**
 * Mobile kitchen Board link data access layer
 *
 * @copyright  Copyright (c) 2010 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-4-8
 */


require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Board extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_user_board';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Board
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getBoard($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }
    
    
    public function getNewestBoard($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE target_uid=:uid ORDER BY create_time DESC LIMIT 1";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }
    
    public function getNewestBoardTwo($uid, $num)
    {
        $sql = "SELECT * FROM $this->table_user WHERE target_uid=:uid ORDER BY create_time DESC LIMIT 0,$num";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
	public function getBoardList($uid, $pageIndex, $pageSize)
    {
    	$start = ($pageIndex - 1) * $pageSize;
        $sql = "SELECT * FROM $this->table_user WHERE target_uid=:uid
                ORDER BY create_time DESC LIMIT $start, $pageSize";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
	public function getBoardCount($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE target_uid=:uid";

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
 	/**
     * insert Board
     *
     * @param array $info
     * @return integer
     */
    public function insertBoard($info)
    {
        $this->_wdb->insert($this->table_user, $info);
    }

    /**
     * update Board
     *
     * @param array $info
     * @param string $uid
     * @return integer
     */
    public function updateBoard($info, $uid)
    {
        $where = $this->_wdb->quoteInto('uid=?', $uid);
        return $this->_wdb->update($this->table_user, $info, $where);
    }
    
    /**
     * write txt to tmp table 
     *
     * @param string $uid
     * @param string $fid
     * @param string $txt
     * @return unknown
     */
	public function writeTmp($uid, $fid, $txt)
    {
    	$time = time();
        $sql = "INSERT INTO res_board_tmp values (:uid, :fid, :txt, :timeNow)
                ON DUPLICATE KEY
                UPDATE fid = :fid ,txt = :txt, create_time = :timeNow";
                
        return $this->_wdb->query($sql, array('uid' => $uid, 'fid' => $fid, 'txt' => $txt, 'timeNow' => $time));
    }
    
	public function readTmp($uid)
    {
    	$sql = "SELECT * FROM res_board_tmp WHERE uid = :uid";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }
}