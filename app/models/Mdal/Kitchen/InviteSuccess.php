<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_InviteSuccess extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_invite_success';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getInviteSuccessCount($uid)
    {
        $sql = "SELECT COUNT(*) FROM $this->table_user WHERE uid=:uid AND is_fortune_used=0 ";
        $rst = $this->_rdb->fetchOne($sql, array('uid' => $uid));
        return $rst;
    }

	public function getInviteSuccess($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid AND is_fortune_used=0 ORDER BY create_time";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

	public function updateInviteSuccess($info, $uid, $target_uid)
    {
        $where = array($this->_wdb->quoteInto('uid=?', $uid),
                       $this->_wdb->quoteInto('target_uid=?', $target_uid));
        return $this->_wdb->update($this->table_user, $info, $where);
    }

 	/**
     * insert InviteSuccess
     *
     * @param array $info
     * @return integer
     */
    public function insertInviteSuccess($info)
    {
        return $this->_wdb->insert($this->table_user, $info);
    }
    
    public function getTargetInviteSuccess($target)
    {
    	$sql = "SELECT * FROM res_invite_success WHERE target_uid = :target ORDER BY create_time LIMIT 0,1";
        $result = $this->_rdb->fetchRow($sql, array('target' => $target));
        
        return $result;
    }
    
    public function getInviteSuccessCountLimitTime($uid, $start, $end)
    {
    	$sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid and create_time between $start and $end";
        $rst = $this->_rdb->fetchOne($sql, array('uid' => $uid));
        return $rst;
    }

}