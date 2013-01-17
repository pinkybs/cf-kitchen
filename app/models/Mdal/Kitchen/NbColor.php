<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_NbColor extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_nb_color';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function listNbColor()
    {
        $sql = "SELECT * FROM $this->table_user ";
        return $this->_rdb->fetchAll($sql);
    }

}