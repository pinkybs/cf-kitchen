<?php

/**
 * Mobile kitchen estate data access layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-7
 */

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Estate extends Mdal_Abstract
{
    /**
     * estate table name
     *
     * @var string
     */
    protected $table_user = 'res_shop_estate';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
 
        return self::$_instance;
    }

    public function getEstate($estateId, $genre)
    {
        $sql = "SELECT * FROM res_shop_estate WHERE estate_id=:estateId AND genre=:genre";
        return $this->_wdb->fetchRow($sql, array('estateId' => $estateId, 'genre' => $genre));
    }
    
    public function listEstate($genre = 0, $pageStart = 0, $pageSize = 5)
    {
    	if ($genre == 0) {
    		$sql = "SELECT * FROM res_shop_estate ORDER BY estate_price_point ASC LIMIT $pageStart,$pageSize";
        	return $this->_rdb->fetchAll($sql);
    	}
    	else {
    		$sql = "SELECT * FROM res_shop_estate WHERE genre=:genre ORDER BY estate_price_point ASC LIMIT $pageStart,$pageSize";
    		return $this->_rdb->fetchAll($sql, array('genre' => $genre));
    	}
    	/*
    	if ($genre == 0) {
    		$sql = "SELECT * FROM res_shop_estate WHERE genre = $inUseGenre AND level<=:level
    		UNION ALL SELECT * FROM res_shop_estate WHERE genre = $inUseGenre AND level>:level
    		UNION ALL SELECT * FROM res_shop_estate WHERE genre != $inUseGenre
    		LIMIT $pageStart,$pageSize";
        	return $this->_wdb->fetchAll($sql, array('level' => $level));
    	}
    	else {
    		$sql = "SELECT * FROM res_shop_estate WHERE genre=:genre ORDER BY `level` ASC LIMIT $pageStart,$pageSize";
    		return $this->_wdb->fetchAll($sql, array('genre' => $genre));
    	}
		*/
    	
    }
    
    
	public function cntListEstate($genre)
    {
    	
    	if ($genre == 0) {
    		$sql = "SELECT COUNT(1) FROM res_shop_estate";
        	return $this->_rdb->fetchOne($sql);
    	}
    	else {
    		$sql = "SELECT COUNT(1) FROM res_shop_estate WHERE genre=:genre";
    		return $this->_rdb->fetchOne($sql, array('genre' => $genre));
    	}
		/*
    	if ($genre == 0) {
    		$sql = "SELECT COUNT(1) FROM res_shop_estate";
        	return $this->_wdb->fetchOne($sql);
    	}
    	else {
    		$sql = "SELECT COUNT(1) FROM res_shop_estate WHERE genre=:genre";
    		return $this->_wdb->fetchOne($sql, array( 'genre' => $genre));
    	}
		*/
    }
    
    
    //code before this line affect the res_shop_estate table ,code after this line affect the res_user_estate table
    
    public function hasEstate($uid, $estateId)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE estate_id=:estateId AND uid=:uid";
        return $this->_wdb->fetchOne($sql, array('estateId' => $estateId, 'uid' => $uid));
    }

 	/**
     * insert estate
     *
     * @param array $info
     * @return integer
     */
    public function insertEstate($info)
    {
        return $this->_wdb->insert($this->table_user, $info);
    }
    
    public function addEstateNum($uid, $estateId, $estateCountAdd)
    {
    	$sql = "UPDATE $this->table_user SET estate_count = estate_count + :estateCountAdd WHERE estate_id=:estateId AND uid=:uid";
        return $this->_wdb->query($sql, array('estateId' => $estateId, 'uid' => $uid, 'estateCountAdd' => $estateCountAdd));
    }

}