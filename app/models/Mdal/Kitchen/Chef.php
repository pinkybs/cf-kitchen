<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Chef extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_user_chef';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Chef
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getChef($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }


 	/**
     * insert Chef
     *
     * @param array $info
     * @return integer
     */
    public function insertChef($info)
    {
        $this->_wdb->insert($this->table_user, $info);
    }

    /**
     * update Chef
     *
     * @param array $info
     * @param string $uid
     * @return integer
     */
    public function updateChef($info, $uid)
    {
        $where = $this->_wdb->quoteInto('uid=?', $uid);
        return $this->_wdb->update($this->table_user, $info, $where);
    }

	/**
     * delete Chef
     *
     * @param integer $uid
     * @return integer
     */
    public function deleteChef($uid)
    {
        $sql = "DELETE FROM $this->table_user WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

    public function getCfChef($uid)
    {
        $sql = "SELECT face AS CF_face,rabbit AS CF_rabbit,ear AS CF_ear,head_m AS CF_head_m,eye AS CF_eye,eyemask AS CF_eyemask FROM res_user_chef WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }
}