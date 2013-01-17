<?php

/**
 * Mobile kitchen goods data access layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-5
 */

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Goods extends Mdal_Abstract
{
    /**
     * goods table name
     *
     * @var string
     */
    protected $table_user = 'res_user_goods';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Goods
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getAllGoods()
    {
    	$sql = "SELECT * FROM res_shop_goods";
        return $this->_rdb->fetchAll($sql);
    }

    public function getGoods($goodsId)
    {
        $sql = "SELECT * FROM res_shop_goods WHERE goods_id=:goodsId";
        return $this->_rdb->fetchRow($sql, array('goodsId' => $goodsId));
    }

    /**
     * get no position goods gid
     *
     * @param integer $uid
     * @param integer $goodsId
     * @return integer
     */
    public function getNoPositionGoodsGid($uid, $goodsId)
    {
    	$sql = "SELECT gid FROM res_user_goods WHERE
				gid NOT IN (SELECT gid FROM res_user_goods_position WHERE uid=:uid) AND
				uid=:uid AND goods_id=:goodsId LIMIT 1";
    	return $this->_wdb->fetchOne($sql, array('uid'=>$uid, 'goodsId'=>$goodsId));
    }

    public function getGoodsUnit($goodsId)
    {
        $sql = 'SELECT "zakka" as belong,goods_id as unit_id,goods_name as unit_name,goods_introduce as unit_introduce,
                goods_price_point as unit_price_point,goods_price_gold as unit_price_gold,type,goods_picture as unit_pictrue
                FROM res_shop_goods WHERE goods_id=:goodsId';
        return $this->_rdb->fetchRow($sql, array('goodsId' => $goodsId));
    }

    public function listGoods($genre = 0, $pageStart = 0, $pageSize = 5, $type = 1)
    {
    	if ($genre == 0) {
    		$sql = "SELECT * FROM res_shop_goods WHERE type=:type ORDER BY level ASC,goods_id ASC LIMIT $pageStart,$pageSize";
        	return $this->_rdb->fetchAll($sql, array('type' => $type));
    	}
    	else {
    		$sql = "SELECT * FROM res_shop_goods WHERE type=:type AND genre=:genre ORDER BY level ASC,goods_id ASC LIMIT $pageStart,$pageSize";
    		return $this->_rdb->fetchAll($sql, array('genre' => $genre, 'type' => $type));
    	}
    }


	public function cntListGoods($genre, $type = 1)
    {

    	if ($genre == 0) {
    		$sql = "SELECT COUNT(1) FROM res_shop_goods WHERE type=:type";
        	return $this->_rdb->fetchOne($sql, array('type' => $type));
    	}
    	else {
    		$sql = "SELECT COUNT(1) FROM res_shop_goods WHERE type=:type AND genre=:genre";
    		return $this->_rdb->fetchOne($sql, array('genre' => $genre, 'type' => $type));
    	}
    }



    //code before this line affect the res_shop_goods table ,code after this line affect the res_user_goods table

    public function hasGoods($uid, $goodsId)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE goods_id=:goodsId AND uid=:uid";
        return $this->_wdb->fetchOne($sql, array('goodsId' => $goodsId, 'uid' => $uid));
    }

    /**
     * check good is user and not used
     *
     * @param integer $uid
     * @param integer $goodsId
     * @return boolean
     */
    public function checkUserGoods($uid, $goodsId)
    {
    	$sql = "SELECT IFNULL(b.position,0) FROM res_user_goods AS a LEFT JOIN res_user_goods_position AS b
				ON a.gid=b.gid WHERE a.uid=:uid AND a.goods_id=:goodsId AND b.position IS NULL ";

    	$result = $this->_wdb->fetchOne($sql, array('uid' => $uid, 'goodsId'=>$goodsId));

    	return $result === false ? true : false;
    }

    /**
     * match genre and goods
     *
     * @param integer $uid
     * @param string $goodsId
     * @return boolean
     */
    public function matchGenreGoods($uid, $goodsId)
    {
    	$sql = "SELECT COUNT(1) FROM res_shop_goods WHERE goods_id=:goodsId AND
    			genre=(SELECT genre FROM res_user_restaurant WHERE uid=:uid AND in_use=1)";

    	$result = $this->_wdb->fetchOne($sql, array('uid' => $uid, 'goodsId'=>$goodsId));

    	return $result > 0 ? true : false;
    }

    public function getGoodsPosition($uid, $genre)
    {
        $sql = "SELECT p.*,g.goods_id FROM res_user_goods_position AS p,res_user_goods AS g
                WHERE p.gid=g.gid AND p.genre=:genre AND p.uid=:uid ORDER BY p.position";
        return $this->_wdb->fetchAll($sql, array('uid' => $uid, 'genre' => $genre));
    }

 	/**
     * insert goods
     *
     * @param array $info
     * @return integer
     */
    public function insertGoods($info)
    {
    	$this->_wdb->insert($this->table_user, $info);
        return $this->_wdb->lastInsertId();
    }

    public function addGoodsNum($uid, $goodsId, $goodsCountAdd)
    {
    	$sql = "UPDATE $this->table_user SET goods_count = goods_count + :goodsCountAdd WHERE goods_id=:goodsId AND uid=:uid";
        return $this->_wdb->query($sql, array('goodsId' => $goodsId, 'uid' => $uid, 'goodsCountAdd' => $goodsCountAdd));
    }

    /**
     * get user goods
     *
     * @param integer $uid
     * @return array
     */
    public function getUserGoods($uid, $genre, $pageIndex, $pageSize)
    {
    	$start = ($pageIndex - 1) * $pageSize;

    	$sql = "SELECT IFNULL(c.position,0) AS `position`,d.* FROM res_user_goods_position AS c RIGHT JOIN
				(SELECT a.uid,a.gid,b.genre,b.goods_id,b.goods_name,b.goods_introduce,b.goods_picture,a.create_time
				FROM res_user_goods AS a,res_shop_goods AS b WHERE a.goods_id=b.goods_id AND a.uid=:uid ORDER BY FIELD(b.genre, $genre) DESC
				LIMIT $start, $pageSize) AS d ON c.gid=d.gid";

    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get user goods count
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserGoodsCount($uid)
    {
    	$sql = "SELECT COUNT(1) FROM res_user_goods WHERE uid=:uid";
    	return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }

    /**
     * insert into res_user_goods_position
     *
     * @param array $info
     */
    public function insertGoodsPosition($info)
    {
    	$this->_wdb->insert('res_user_goods_position', $info);
    }

    /**
     * delete res_user_goods_position
     *
     * @param integer $uid
     * @param string $gid
     */
    public function delGoodsPosition($uid, $gid)
    {
    	$sql = "DELETE FROM res_user_goods_position WHERE uid=:uid AND gid=:gid";
    	$this->_wdb->query($sql, array('uid'=>$uid, 'gid'=>$gid));
    }

    /**
     * check is null position
     *
     * @param integer $uid
     * @param integer $position
     * @return boolean
     */
    public function isNullPosition($uid, $position, $genre)
    {
    	$sql = "SELECT COUNT(1) FROM res_user_goods_position  WHERE genre=:genre AND `position`=:position AND uid=:uid";
    	$result = $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'position'=>$position, 'genre' => $genre));
    	return $result == 0 ? true : false;
    }


     //**************** xial add **********************
    /**
     * get goods count
     *
     * @param integer $uid
     * @param integer $goodsId
     * @return integer
     */
    public function getGoodsCount($uid, $goodsId)
    {
        $sql = "SELECT COUNT(*) FROM $this->table_user WHERE goods_id=:goodsId AND uid=:uid";
        return $this->_wdb->fetchOne($sql, array('goodsId' => $goodsId, 'uid' => $uid));
    }
}