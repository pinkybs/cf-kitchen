<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_NbPosition extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_nb_position';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getNbPosition($positionId)
    {
        $sql = "SELECT * FROM $this->table_user WHERE position=:position";
        return $this->_rdb->fetchRow($sql, array('position' => $positionId));
    }

}