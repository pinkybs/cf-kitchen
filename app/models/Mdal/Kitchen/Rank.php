<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Rank extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'res_user_profile';
    
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Rank
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * get user ranking number
     *
     * @param integer $uid
     * @param array $fids
     * @param string $orderType (total_level, total_recipe_count, point)
     * @param string $order
     * @return integer
     */
    public function getUserRankNum($uid, $fids, $orderType, $order)
    {
        //check order type
        

        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        
        if (empty($fids)) {
            $fids = '0';
        }
        else {
            $fids = $this->_rdb->quote($fids);
        }
        
        $sql = "SELECT rank FROM (SELECT @pos:=@pos+1 AS rank,uid FROM $this->table_user WHERE uid IN ($fids, :uid)
                    ORDER BY $orderType $order, id ASC) AS r WHERE r.uid=:uid";
        $result = $this->_rdb->fetchOne($sql, array('uid' => $uid));
        
        return $result;
    }

    /**
     * get rank list
     * @param integer $uid
     * @param array $fids
     * @param integer $pageIndex
     * @param integer $pageSize
     * @param string $orderType (total_level, total_recipe_count, point)
     * @param string $order
     * @return array
     */
    public function getRankList($uid, $fids, $pageIndex = 1, $pageSize = 5, $orderType, $order)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $sql1 = "SET @pos=" . $start;
        $this->_rdb->query($sql1);
        
        if (empty($fids)) {
            $fids = '0';
        }
        else {
            $fids = $this->_rdb->quote($fids);
        }
        
        $sql = "SELECT @pos:=@pos+1 AS rank, uid, total_level, total_recipe_count, point
                FROM $this->table_user WHERE uid IN('$uid',$fids)
                ORDER BY  $orderType $order, id ASC
                LIMIT $start, $pageSize";
        
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        
        return $result;
    }

    /**
     * get rank user count
     * @author lp
     * @param integer $uid
     * @param array $idArray
     * @param integer $type
     * @return array
     */
    public function getRankCount($uid, $fids)
    {
        if (empty($fids)) {
            $fids = '0';
        }
        else {
            $fids = $this->_rdb->quote($fids);
        }
        
        $sql = "SELECT COUNT(uid) AS count FROM $this->table_user WHERE uid IN ($fids, :uid)";
        
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    public function getSelfRank($uid, $ranknum)
    {
        $sql = "SELECT $ranknum AS rank, uid, total_level, total_recipe_count, point FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    public function getCookingDish($fidsCut, $fidsInSetStr)
    {
        $fidsCut = $this->_rdb->quote($fidsCut);
        
        $t = time();
        $sql = "SELECT count(1) AS cooking_dish,uid FROM res_kitchen WHERE uid IN ($fidsCut) 
        		AND $t < cooking_start_time + (cooking_part1 + cooking_part2 + cooking_part3)*60 AND cooking_start_time IS NOT NULL 
				GROUP BY uid ORDER BY FIND_IN_SET(uid,$fidsInSetStr)";
        
        $result = $this->_rdb->fetchAll($sql);
        
        return $result;
    }
    
    public function getCookOverDish($fidsCut, $fidsInSetStr)
    {
        $fidsCut = $this->_rdb->quote($fidsCut);
        
        $t = time();
        $sql = "SELECT count(1) AS cookover_dish,uid FROM res_kitchen WHERE uid IN ($fidsCut) 
        		AND $t >= cooking_start_time + (cooking_part1 + cooking_part2 + cooking_part3)*60 AND cooking_start_time IS NOT NULL 
				GROUP BY uid ORDER BY FIND_IN_SET(uid,$fidsInSetStr)";
        
        $result = $this->_rdb->fetchAll($sql);
        
        return $result;
    }   	
    
    
    
    
}