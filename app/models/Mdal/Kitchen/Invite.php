<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Invite extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_invite';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getInviteByUser($actor, $target, $status = 1)
    {
        $where = "";
        if ($status != 1) {
            $where = " AND status = $status";
        }
        $sql = "SELECT COUNT(*) FROM $this->table_user WHERE actor = :actor AND target = :target " . $where;
        $result = $this->_rdb->fetchOne($sql, array('actor' => $actor, 'target' => $target));
        
        return $result;
    }

	public function updateInvite($info, $uid, $target_uid)
    {
        $where = array($this->_wdb->quoteInto('actor=?', $uid),
                       $this->_wdb->quoteInto('target=?', $target_uid));
        return $this->_wdb->update($this->table_user, $info, $where);
    }

 	/**
     * insert invite
     *
     * @param array $info
     * @return integer
     */
    public function insertInvite($info)
    {
        return $this->_wdb->insert($this->table_user, $info);
    }

}