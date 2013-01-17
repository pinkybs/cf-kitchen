<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_NbLevel extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_nb_level';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function listNbLevel()
    {
        $sql = "SELECT * FROM $this->table_user ";
        return $this->_rdb->fetchAll($sql);
    }

	public function getNbLevelExp($level)
    {
        $sql = "SELECT * FROM $this->table_user WHERE level>:level ORDER BY level Limit 0,1";
        return $this->_rdb->fetchRow($sql, array('level' => $level));
    }

	public function getLevelUpGift($genre, $level)
    {
        $sql = "SELECT * FROM res_nb_levelup_gift WHERE genre=:genre AND level=:level ";
        return $this->_rdb->fetchRow($sql, array('genre' => $genre, 'level' => $level));
    }

	public function getVisitGift($genre, $level)
    {
        $sql = "SELECT * FROM res_nb_visit_gift WHERE genre=:genre AND level=:level ";
        return $this->_rdb->fetchRow($sql, array('genre' => $genre, 'level' => $level));
    }
}