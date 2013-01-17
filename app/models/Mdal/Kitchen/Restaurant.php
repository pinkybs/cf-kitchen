<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Restaurant extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_user_restaurant';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Restaurant
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getActiveRestaurant($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid AND in_use=1 ";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get max genre level
     *
     * @param string $uid
     * @return integer
     */
    public function getMaxLevel($uid)
    {
        $sql = "SELECT max(level) FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
 	/**
     * insert Restaurant
     *
     * @param array $info
     * @return integer
     */
    public function insertRestaurant($info)
    {
        return $this->_wdb->insert($this->table_user, $info);
    }

    /**
     * update Restaurant
     *
     * @param array $info
     * @param integer $uid
     * @param integer $genre
     * @return integer
     */
    public function updateRestaurant($info, $uid, $genre)
    {
        $where = array($this->_wdb->quoteInto('uid=?', $uid),
                       $this->_wdb->quoteInto('genre=?', $genre));
        return $this->_wdb->update($this->table_user, $info, $where);
    }

	/**
     * delete Restaurant
     *
     * @param integer $uid
     * @return integer
     */
    public function deleteRestaurant($uid)
    {
        $sql = "DELETE FROM $this->table_user WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }
    
    /**
     * set all rest to not use
     *
     * @param integer $uid
     */
    public function setAllToUnuse($uid)
    {
    	$sql = "UPDATE $this->table_user SET in_use=0 WHERE uid=:uid ";
    	$this->_wdb->query($sql, array('uid' => $uid));
    }
    
    public function getArrGenreEstate($uid)
    {
        $sql = "SELECT genre,estate,recipe_count FROM $this->table_user WHERE uid=:uid ORDER BY genre";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    
    public function getOneRestaurant($uid, $genre)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid AND genre=:genre";
        $re = $this->_rdb->fetchRow($sql, array('uid' => $uid, 'genre' => $genre));
        
        if (!$re) {
        	$sqll = "SELECT COUNT(1) FROM res_user_recipe WHERE uid=:uid AND genre=:genre";
        	$re['recipe_count'] = $this->_rdb->fetchOne($sqll, array('uid' => $uid, 'genre' => $genre));
        }
        return $re;
    }
    
    public function getGenreLv($uid, $genre)
    {
        $sql = "SELECT `level` FROM $this->table_user WHERE uid=:uid AND genre=:genre";
        $re = $this->_rdb->fetchOne($sql, array('uid' => $uid, 'genre' => $genre));
        return $re > 0 ? $re : 0;
    }
    
    /**
     * add recipe count
     *
     * @param integer $uid
     * @param integer $genre
     */
    public function addRecipeCount($uid, $genre)
    {
    	$sql = "UPDATE $this->table_user SET recipe_count=recipe_count+1 WHERE uid=:uid AND genre=:genre";
        $this->_wdb->query($sql, array('uid' => $uid, 'genre' => $genre));
    }
    
    /**
     * check user has restaurant
     *
     * @param integer $uid
     * @param integer $genre
     * @return integer
     */
    public function hasRest($uid, $genre)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid AND genre=:genre";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'genre' => $genre));
    }
    
    /**
     * get user all restaurant
     *
     * @param integer $uid
     * @return array
     */
    public  function getUserAllRestaurant($uid)
    {
    	$sql = "SELECT a.uid, a.genre, a.estate, b.name AS rest_name,b.recipe_count AS total_recipe,a.name,a.in_use,a.recipe_count,b.img_path
				FROM res_user_restaurant AS a,res_nb_restaurant AS b WHERE a.genre=b.genre AND a.uid=:uid";
    	
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    
    
    public function getNbGenreList()
    {
        $sql = "SELECT * FROM res_nb_restaurant";
        return $this->_rdb->fetchAll($sql);
    }
    
}