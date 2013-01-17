<?php

/**
 * Mobile kitchen food data access layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-4
 */

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Food extends Mdal_Abstract
{
    /**
     * food table name
     *
     * @var string
     */
    protected $table_user = 'res_user_food';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Food
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getFood($foodId)
    {
        $sql = "SELECT *, LEFT(food_id,2) AS f, RIGHT(food_id,2) AS n FROM res_shop_food WHERE food_id=:foodId";
        return $this->_rdb->fetchRow($sql, array('foodId' => $foodId));
    }

    public function getFoodUnit($foodId)
    {
        $sql = 'SELECT "food" as belong,food_id as unit_id,food_name as unit_name,food_introduce as unit_introduce,
                food_price_point as unit_price_point,food_price_gold as unit_price_gold,type,food_picture as unit_pictrue
                FROM res_shop_food WHERE food_id=:foodId';
        return $this->_rdb->fetchRow($sql, array('foodId' => $foodId));
    }

    public function listFood($category = 0, $pageStart = 0, $pageSize = 5, $genre, $level, $type = 1)
    {
    	/*   level limit on
    	if ($category == 0) {
    		$sql = "SELECT * FROM res_shop_food WHERE type=:type AND genre_" . $genre .  "=1 AND level<=:level LIMIT $pageStart,$pageSize";
        	return $this->_wdb->fetchAll($sql, array('type' => $type, 'level' => $level));
    	}
    	else {
    		$sql = "SELECT * FROM res_shop_food WHERE type=:type AND genre_" . $genre .  "=1 AND level<=:level AND food_category=:category LIMIT $pageStart,$pageSize";
    		return $this->_wdb->fetchAll($sql, array('type' => $type, 'level' => $level, 'category' => $category));
    	}
    	*/
    	// level limit off
    	if ($category == 0) {
    		$sql = "SELECT * FROM res_shop_food WHERE type=:type AND genre_" . $genre .  "=1 ORDER BY level ASC LIMIT $pageStart,$pageSize";
        	return $this->_rdb->fetchAll($sql, array('type' => $type));
    	}
    	else {
    		$sql = "SELECT * FROM res_shop_food WHERE type=:type AND genre_" . $genre .  "=1 AND food_category=:category ORDER BY level ASC LIMIT $pageStart,$pageSize";
    		return $this->_rdb->fetchAll($sql, array('type' => $type, 'category' => $category));
    	}
		/*
    	//level limit off  + type=0 visible
   	    if ($category == 0) {
    		$sql = "SELECT * FROM res_shop_food WHERE genre_" . $genre .  "=1 ORDER BY level ASC LIMIT $pageStart,$pageSize";
        	return $this->_wdb->fetchAll($sql);
    	}
    	else {
    		$sql = "SELECT * FROM res_shop_food WHERE genre_" . $genre .  "=1 AND food_category=:category ORDER BY level ASC LIMIT $pageStart,$pageSize";
    		return $this->_wdb->fetchAll($sql, array('category' => $category));
    	}
    	*/

    }


	public function cntListFood($category = 0, $genre, $level, $type = 1)
    {
    	/*   level limit on
    	if ($category == 0) {
    		$sql = "SELECT COUNT(1) FROM res_shop_food WHERE type=:type AND genre_" . $genre .  "=1 AND level<=:level";
        	return $this->_wdb->fetchOne($sql, array('type' => $type, 'level' => $level));
    	}
    	else {
    		$sql = "SELECT COUNT(1) FROM res_shop_food WHERE type=:type AND genre_" . $genre .  "=1 AND level<=:level AND food_category=:category";
    		return $this->_wdb->fetchOne($sql, array('type' => $type, 'level' => $level, 'category' => $category));
    	}
		*/
    	//level limit off
    	if ($category == 0) {
    		$sql = "SELECT COUNT(1) FROM res_shop_food WHERE type=:type AND genre_" . $genre .  "=1";
        	return $this->_rdb->fetchOne($sql, array('type' => $type));
    	}
    	else {
    		$sql = "SELECT COUNT(1) FROM res_shop_food WHERE type=:type AND genre_" . $genre .  "=1 AND food_category=:category";
    		return $this->_rdb->fetchOne($sql, array('type' => $type,'category' => $category));
    	}
    	/*
    	//level limit off  + type=0 visible
    	if ($category == 0) {
    		$sql = "SELECT COUNT(1) FROM res_shop_food WHERE genre_" . $genre .  "=1";
        	return $this->_wdb->fetchOne($sql);
    	}
    	else {
    		$sql = "SELECT COUNT(1) FROM res_shop_food WHERE genre_" . $genre .  "=1 AND food_category=:category";
    		return $this->_wdb->fetchOne($sql, array('category' => $category));
    	}
    	*/
    }


    //code before this line affect the res_shop_food table ,code after this line affect the res_user_food table

    public function hasFood($uid, $foodId)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE food_id=:foodId AND uid=:uid";
        return $this->_wdb->fetchOne($sql, array('foodId' => $foodId, 'uid' => $uid));
    }

 	/**
     * insert food
     *
     * @param array $info
     * @return integer
     */
    public function insertFood($info)
    {
        return $this->_wdb->insert($this->table_user, $info);
    }

    public function addFoodNum($uid, $foodId, $foodCountAdd)
    {
    	$sql = "UPDATE $this->table_user SET food_count = food_count + :foodCountAdd WHERE food_id=:foodId AND uid=:uid";
        return $this->_wdb->query($sql, array('foodId' => $foodId, 'uid' => $uid, 'foodCountAdd' => $foodCountAdd));
    }

    public function checkUserHasFood($uid, $foodId)
    {
    	$sql = "SELECT food_count FROM $this->table_user WHERE food_id=:foodId AND uid=:uid";
        $count = $this->_wdb->fetchOne($sql, array('foodId' => $foodId, 'uid' => $uid));
        return $count > 0 ? true : false;
    }

    /**
     * get user food
     *
     * @param integer $uid
     * @param integer $category
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getUserFood($uid, $category, $pageIndex=1, $pageSize=5)
    {
    	$start = ($pageIndex - 1) * $pageSize;

    	if ($category != 0) {
    		$sql = "SELECT b.*,a.food_name,a.food_introduce,a.food_picture FROM res_shop_food AS a,
    				(SELECT * FROM res_user_food WHERE food_category=:category AND food_count>0 AND uid=:uid LIMIT $start, $pageSize) AS b
    				WHERE a.food_id=b.food_id";

    		return $this->_wdb->fetchAll($sql, array('category'=>$category ,'uid'=>$uid));
    	}
    	else {
    		$sql = "SELECT b.*,a.food_name,a.food_introduce,a.food_picture FROM res_shop_food AS a,
    				(SELECT * FROM res_user_food WHERE food_count>0 AND uid=:uid LIMIT $start, $pageSize) AS b
    				WHERE a.food_id=b.food_id";

    		return $this->_wdb->fetchAll($sql, array('uid'=>$uid));
    	}
    }

    /**
     * get user food count
     *
     * @param integer $uid
     * @param string $category
     * @return integer
     */
    public function getUserFoodCount($uid, $category=0)
    {
    	if ($category != 0) {
    		$sql = "SELECT COUNT(1) FROM res_user_food WHERE food_category=:category AND food_count>0 AND uid=:uid";
    		return $this->_rdb->fetchOne($sql, array('category'=>$category ,'uid'=>$uid));
    	}
    	else {
    		$sql = "SELECT COUNT(1) FROM res_user_food WHERE food_count>0 AND uid=:uid";
    		return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    	}
    }

	public function listUserFoodByCategory($uid, $categoryId) {
        $sql = "SELECT * FROM res_user_food WHERE uid=:uid AND food_category=:food_category AND food_count>0 ORDER BY food_id";
        return $this->_wdb->fetchAll($sql, array('uid' => $uid, 'food_category' => $categoryId));
    }

	public function getUserFoodByCategoryCount($uid, $categoryId) {
        $sql = "SELECT COUNT(food_id) FROM res_user_food WHERE uid=:uid AND food_category=:food_category AND food_count>0 ";
        return $this->_wdb->fetchOne($sql, array('uid' => $uid, 'food_category' => $categoryId));
    }

    public function getUserFoodInfo($uid, $foodId) {
        $sql = "SELECT * FROM res_user_food WHERE uid=:uid AND food_id=:food_id ";
        return $this->_wdb->fetchRow($sql, array('uid' => $uid, 'food_id' => $foodId));
    }

    /************* add by shenhw ****************/
    public function getAllFood() {
        $sql = "SELECT * FROM res_shop_food";
        return $this->_rdb->fetchAll($sql);
    }

    public function insertFoodFromGift($uid, $food) {
        $sql = "INSERT INTO $this->table_user values (:uid, :food_id, :food_count, :food_category)
                ON DUPLICATE KEY
                UPDATE food_count = food_count + :food_count";
        return $this->_wdb->query($sql, array('uid' => $uid, 'food_id' => $food['food_id'], 'food_count' => $food['food_count'], 'food_category' => $food['food_category']));
    }

	public function getGachaFoods() {
        $sql = "SELECT food_id FROM res_shop_food where `type`=0";
        return $this->_rdb->fetchCol($sql);
    }


    //****************** xial add *********************
    /**
     * get food count
     *
     * @param integer $uid
     * @param string $foodId
     * @return integer
     */
    public function getFoodCount($uid, $foodId)
    {
        $sql = "SELECT food_count FROM $this->table_user WHERE food_id = :foodId AND uid = :uid";
        return $this->_wdb->fetchOne($sql, array('foodId' => $foodId, 'uid' => $uid));
    }
}