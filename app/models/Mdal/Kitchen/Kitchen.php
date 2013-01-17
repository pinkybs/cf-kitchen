<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Kitchen extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_kitchen = 'res_kitchen';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Kitchen
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert into kitchen
     *
     * @param array $info
     * @return integer
     */
    public function insert($info)
    {
        $this->_wdb->insert($this->table_kitchen, $info);
    }

    /**
     * update kitchen
     *
     * @param array $info
     * @param integer $uid
     */
    public function update($info, $uid, $kitchen_id)
    {
        $where = array($this->_wdb->quoteInto('uid=?', $uid),
                       $this->_wdb->quoteInto('kitchen_id=?', $kitchen_id));

        $this->_wdb->update($this->table_kitchen, $info, $where);
    }

    /**
     * update user kitchen failure count
     *
     * @param integer $uid
     * @param integer $kitchen_id
     * @param integer $count
     */
    public function updateFailureCount($uid, $kitchen_id, $count)
    {
    	$sql = "UPDATE res_kitchen SET failure_count=failure_count+:c WHERE uid=:uid AND kitchen_id=:kitchen_id";
    	$this->_wdb->query($sql, array('c' => $count, 'uid' => $uid, 'kitchen_id' => $kitchen_id));
    }
    
	/**
     * delete kitchen
     *
     * @param integer $uid
     * @param integer $kitchen_id
     * @return integer
     */
    public function delete($uid, $kitchen_id)
    {
        $sql = "DELETE FROM $this->table_kitchen WHERE uid=:uid AND kitchen_id=:kitchen_id";
        return $this->_wdb->query($sql, array('uid' => $uid, 'kitchen_id' => $kitchen_id));
    }

    public function getUserKitchenFailCount($uid, $kitchen_id)
    {
        $sql = "SELECT failure_count FROM res_kitchen WHERE uid=:uid AND kitchen_id=:kitchen_id";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'kitchen_id'=>$kitchen_id));
    }


	public function getUserKitchen($uid, $kitchen_id)
    {
        $sql = "SELECT * FROM res_kitchen WHERE uid=:uid AND kitchen_id=:kitchen_id";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'kitchen_id'=>$kitchen_id));
    }

	public function getUserKitchenAll($uid)
    {
        $sql = "SELECT * FROM res_kitchen WHERE uid=:uid ORDER BY kitchen_id ";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

	public function getUserKitchenLock($uid, $kitchen_id)
    {
        $sql = "SELECT * FROM res_kitchen WHERE uid=:uid AND kitchen_id=:kitchen_id FOR UPDATE";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'kitchen_id'=>$kitchen_id));
    }

	//fly set
	public function getKitchenFlySet($uid, $kitchen_id)
    {
        $sql = "SELECT * FROM res_kitchen_set_fly WHERE uid=:uid AND kitchen_id=:kitchen_id";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'kitchen_id'=>$kitchen_id));
    }

    public function insertKitchenFlySet($info)
    {
        $this->_wdb->insert('res_kitchen_set_fly', $info);
    }

    public function updateKitchenFlySet($info, $uid, $kitchen_id)
    {
        $where = array($this->_wdb->quoteInto('uid=?', $uid),
                       $this->_wdb->quoteInto('kitchen_id=?', $kitchen_id));

        $this->_wdb->update('res_kitchen_set_fly', $info, $where);
    }

	public function deleteKitchenFlySet($uid, $kitchen_id)
    {
        $sql = "DELETE FROM res_kitchen_set_fly WHERE uid=:uid AND kitchen_id=:kitchen_id";
        return $this->_wdb->query($sql, array('uid' => $uid, 'kitchen_id' => $kitchen_id));
    }

    //spice add
	public function getKitchenSpice($uid, $kitchen_id, $addUid)
    {
        $sql = "SELECT * FROM res_kitchen_add_spice WHERE uid=:uid AND kitchen_id=:kitchen_id AND add_spice_uid=:add_spice_uid";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'kitchen_id'=>$kitchen_id, 'add_spice_uid'=>$addUid));
    }

    public function insertKitchenSpice($info)
    {
        $this->_wdb->insert('res_kitchen_add_spice', $info);
    }

	public function deleteKitchenSpice($uid, $kitchen_id)
    {
        $sql = "DELETE FROM res_kitchen_add_spice WHERE uid=:uid AND kitchen_id=:kitchen_id";
        return $this->_wdb->query($sql, array('uid' => $uid, 'kitchen_id' => $kitchen_id));
    }

    //taste food
	public function getKitchenTaste($uid, $kitchen_id, $tasteUid)
    {
        $sql = "SELECT * FROM res_kitchen_taste WHERE uid=:uid AND kitchen_id=:kitchen_id AND taste_uid=:taste_uid";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'kitchen_id'=>$kitchen_id, 'taste_uid'=>$tasteUid));
    }

	public function getKitchenTasteAll($uid, $kitchen_id)
    {
        $sql = "SELECT * FROM res_kitchen_taste WHERE uid=:uid AND kitchen_id=:kitchen_id ";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid, 'kitchen_id'=>$kitchen_id));
    }

    public function insertKitchenTaste($info)
    {
        $this->_wdb->insert('res_kitchen_taste', $info);
    }

	public function deleteKitchenTaste($uid, $kitchen_id)
    {
        $sql = "DELETE FROM res_kitchen_taste WHERE uid=:uid AND kitchen_id=:kitchen_id";
        return $this->_wdb->query($sql, array('uid' => $uid, 'kitchen_id' => $kitchen_id));
    }

	public function updateKitchenRate($uid, $kitchen_id, $rate)
	{
		$sql = "UPDATE res_kitchen SET rate=rate+:rate WHERE uid=:uid AND kitchen_id=:kitchen_id";
		$this->_wdb->query($sql, array('rate' => $rate, 'uid' => $uid, 'kitchen_id' => $kitchen_id));
	}
	
	public function updateKitchenTime($uid, $kitchen_id, $cook1, $cook2, $cook3)
	{
		if ($cook3 === null) {
			$sql = "UPDATE res_kitchen SET cooking_part1=$cook1,cooking_part2=$cook2
					WHERE uid=:uid AND kitchen_id=:kitchen_id";
			
			$this->_wdb->query($sql, array('uid' => $uid, 'kitchen_id' => $kitchen_id));
		}
		else {
			$sql = "UPDATE res_kitchen SET cooking_part1=$cook1,cooking_part2=$cook2,cooking_part3=$cook3
					WHERE uid=:uid AND kitchen_id=:kitchen_id";
			
			$this->_wdb->query($sql, array('uid' => $uid, 'kitchen_id' => $kitchen_id));
		}
	}
	
	public function friendCookingCnt($fids)
	{
		$fids = $this->_rdb->quote($fids);
		$sql = "SELECT COUNT(1) FROM (SELECT uid FROM res_kitchen WHERE cooking_start_time > 0 AND uid IN ($fids) GROUP BY uid) AS x";
		return $this->_rdb->fetchOne($sql);
	}
	
	public function getKitchenOrder($tarUid, $kitchen_id, $actUid)
    {
        $sql = "SELECT * FROM res_kitchen_order WHERE tar_uid=:tar_uid AND kitchen_id=:kitchen_id AND act_uid=:act_uid";
        return $this->_rdb->fetchRow($sql, array('tar_uid'=>$tarUid, 'kitchen_id'=>$kitchen_id, 'act_uid'=>$actUid));
    }
	
    public function insertKitchenOrder($info)
    {
        $this->_wdb->insert('res_kitchen_order', $info);
    }
    
	public function deleteKitchenOrder($tarUid, $kitchen_id)
	{
        $sql = "DELETE FROM res_kitchen_order WHERE tar_uid=:tar_uid AND kitchen_id=:kitchen_id";
        return $this->_wdb->query($sql, array('tar_uid' => $tarUid, 'kitchen_id' => $kitchen_id));
	}
	
	public function getListFriendKitchen($fids)
	{
		
		$fids = $this->_rdb->quote($fids);
		$sql = "SELECT * FROM res_kitchen WHERE uid IN ($fids) ORDER BY uid";
		$re =  $this->_rdb->fetchAll($sql);
		
		return $re;
		/*
		 * //$fids = array('0' => '1113');
		define('MAX_CREATE_UID', "(SELECT uid FROM res_kitchen WHERE cooking_start_time=(SELECT max(cooking_start_time) FROM res_kitchen WHERE uid IN ($fids)) AND uid IN ($fids) limit 1)");
		
		$sql = "SELECT * FROM res_kitchen WHERE uid IN (
					SELECT uid FROM res_kitchen WHERE cooking_start_time=(SELECT max(cooking_start_time) FROM res_kitchen WHERE uid IN ($fids)) UNION 
					SELECT uid FROM res_kitchen WHERE cooking_start_time=(SELECT max(cooking_start_time) FROM res_kitchen WHERE uid IN ($fids) AND uid NOT IN 
						(SELECT uid FROM res_kitchen WHERE cooking_start_time=(SELECT max(cooking_start_time) FROM res_kitchen WHERE uid IN ($fids))))
				)";
		
		$sql = "SELECT * FROM res_kitchen WHERE uid IN (
					" . MAX_CREATE_UID . " UNION 
					SELECT uid FROM res_kitchen WHERE cooking_start_time=(
						SELECT max(cooking_start_time) FROM res_kitchen WHERE uid IN ($fids) AND uid NOT IN (
							" . MAX_CREATE_UID . "
						)
					)
				) ORDER BY uid";
		*/
	}
	
	public function getMaxCookingTime($fids)
	{
		$fids = $this->_rdb->quote($fids);
		$sql = "SELECT uid FROM res_kitchen WHERE cooking_start_time=(SELECT max(cooking_start_time) FROM res_kitchen WHERE uid IN ($fids)) AND uid IN ($fids) limit 1";
		$re =  $this->_rdb->fetchOne($sql);
		return $re;
	}
	
	public function getCheerTwo($uid, $profileUid)
	{
		$sql = "SELECT * FROM res_kitchen_cheertwo WHERE act_uid=:uid AND tar_uid=:profileUid";
		$re =  $this->_rdb->fetchRow($sql, array('uid' => $uid, 'profileUid' => $profileUid));
		return $re;
	}
	
    public function insertKitchenCheerTwo ($info)
    {
        $this->_wdb->insert('res_kitchen_cheertwo', $info);
    }
    
	public function updateKitchenCheerTwo($uid, $profileUid, $date)
	{
		$sql = "UPDATE res_kitchen_cheertwo SET act_date=:newdate WHERE act_uid=:uid AND tar_uid=:profileUid";
		$this->_wdb->query($sql, array('newdate' => $date, 'uid' => $uid, 'profileUid' => $profileUid));
	}
    
}