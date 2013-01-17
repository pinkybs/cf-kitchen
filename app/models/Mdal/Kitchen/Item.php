<?php

/**
 * Mobile kitchen item data access layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-9
 */

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Item extends Mdal_Abstract
{
    /**
     * item table name
     *
     * @var string
     */
    protected $table_user = 'res_user_item';

    protected static $_instance;

    /**
     * get DefaultInstance
     *
     * @return Mdal_Kitchen_Item
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getAllItem()
    {
    	$sql = "SELECT * FROM res_shop_item";
        return $this->_rdb->fetchAll($sql);
    }

    public function getItem($itemId)
    {
        $sql = "SELECT * FROM res_shop_item WHERE item_id=:itemId";
        return $this->_rdb->fetchRow($sql, array('itemId' => $itemId));
    }

    public function listItem($pageStart = 0, $pageSize = 5)
    {
    	$sql = "SELECT * FROM res_shop_item WHERE `type`=1 OR `type`=3 ORDER BY sort_id ASC LIMIT $pageStart,$pageSize";
        return $this->_rdb->fetchAll($sql);
    }


	public function cntListItem()
    {
    	$sql = "SELECT COUNT(1) FROM res_shop_item WHERE `type`=1 OR `type`=3";
        return $this->_rdb->fetchOne($sql);
    }


    //code before this line affect the res_shop_item table ,code after this line affect the res_user_item table

    public function hasItem($uid, $itemId)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE item_id=:itemId AND uid=:uid";
        $result = $this->_wdb->fetchOne($sql, array('itemId' => $itemId, 'uid' => $uid));
        return $result > 0;
    }

    public function hasItemCnt($uid, $itemId)
    {
        $sql = "SELECT item_count FROM $this->table_user WHERE item_id=:itemId AND uid=:uid";
        $result = $this->_wdb->fetchOne($sql, array('itemId' => $itemId, 'uid' => $uid));
        return $result;
    }

 	/**
     * insert item
     *
     * @param array $info
     * @return integer
     */
    public function insertItem($info)
    {
        return $this->_wdb->insert($this->table_user, $info);
    }

    public function addItemNum($uid, $itemId, $itemCountAdd)
    {
    	$sql = "UPDATE $this->table_user SET item_count = item_count + :itemCountAdd WHERE item_id=:itemId AND uid=:uid";
        return $this->_wdb->query($sql, array('itemId' => $itemId, 'uid' => $uid, 'itemCountAdd' => $itemCountAdd));
    }

    public function getUserItem($uid, $kitchenId, $pageIndex, $pageSize)
    {
    	$start = ($pageIndex - 1) * $pageSize;

    	if ($kitchenId == 0) {
	    	$sql = "SELECT b.*,a.item_name,a.item_introduce,a.item_picture FROM res_shop_item AS a,
					(SELECT * FROM res_user_item WHERE item_count>0 AND uid=:uid LIMIT $start, $pageSize) AS b
					WHERE a.item_id=b.item_id";
    	}
    	else {
    		$sql = "SELECT b.*,a.item_name,a.item_introduce,a.item_picture FROM res_shop_item AS a,
					(SELECT * FROM res_user_item WHERE item_count>0 AND kitchen_only=1 AND uid=:uid AND item_id<>20 AND item_id<>21 LIMIT $start, $pageSize) AS b
					WHERE a.item_id=b.item_id";
    	}

		return $this->_wdb->fetchAll($sql, array('uid'=>$uid));
    }

    public function getUserItemCount($uid, $kitchenId)
    {
    	if ($kitchenId == 0) {
	    	$sql = "SELECT COUNT(1) FROM res_user_item WHERE  item_count>0 AND uid=:uid";
    	}
    	else {
    		$sql = "SELECT COUNT(1) FROM res_user_item WHERE  item_count>0 AND kitchen_only=1 AND uid=:uid AND item_id<>20 AND item_id<>21";
    	}

    	return $this->_wdb->fetchOne($sql, array('uid'=>$uid));
    }

    /************* add by shenhw ****************/
    public function insertItemFromGift($uid, $item) {
        $sql = "INSERT INTO $this->table_user values (:uid, :item_id, :item_count, :kitchen_only)
                ON DUPLICATE KEY
                UPDATE item_count = item_count + :item_count";
        return $this->_wdb->query($sql, array('uid' => $uid, 'item_id' => $item['item_id'], 'item_count' => $item['item_count'], 'kitchen_only' => $item['kitchen_only']));
    }

    //add by zhaoxh
	public function getUserSpoon($uid)
    {
    	$sql = "SELECT s.item_id,s.item_name,s.item_introduce,s.item_picture,u.item_count FROM res_user_item AS u,res_shop_item AS s WHERE s.item_category=4 AND u.item_id=s.item_id AND u.uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }


    //**************** xial add **********************
    /**
     * get item count
     *
     * @param integer $uid
     * @param integer $itemId
     * @return integer
     */
    public function getItemCount($uid, $itemId)
    {
        $sql = "SELECT item_count FROM $this->table_user WHERE item_id=:itemId AND uid=:uid";
        return $this->_wdb->fetchOne($sql, array('itemId' => $itemId, 'uid' => $uid));
    }
}