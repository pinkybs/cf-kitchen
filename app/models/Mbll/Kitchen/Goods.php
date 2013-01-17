<?php

/**
 * Mobile kitchen goods bussiness logic layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-6
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Goods extends Mbll_Abstract
{
	/**
	 * Kitchen Goods
	 *
	 * @param string $uid
	 * @param string $paytype
	 * @param array $goodsinfo
	 * @return string
	 */
    public function buyGoods($uid, $payType, $goodsInfo, $position, $setGoods)
    {
    	$result = false;

        try {
            require_once 'Mdal/Kitchen/Goods.php';
            $dalGoods = Mdal_Kitchen_Goods::getDefaultInstance();
            require_once 'Mdal/Kitchen/User.php';
            $dalUser = Mdal_Kitchen_User::getDefaultInstance();

            $userInfo = $dalUser->getUser($uid);

            $this->_wdb->beginTransaction();

            $info = array('uid' => $uid,
                          'goods_id' => $goodsInfo['goods_id'],
            	          'create_time' => time());
            $lastId = $dalGoods->insertGoods($info);

            if ($setGoods) {
            	$info2 = array('gid' => $lastId,
	                           'uid' => $uid,
	                           'position' => $position,
            	               'genre' => $goodsInfo['genre']);
            	$dalGoods->insertGoodsPosition($info2);
            }

            //admin page shopping info
            $goodsCnt = $dalGoods->getUserGoodsCount($uid);
        	$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        	$adminDalkitchen->insert('goods', array('uid' => $uid,
        										   'shop_id' => $goodsInfo['goods_id'],
        										   'shop_name' => $goodsInfo['goods_name'],
        										   $payType => $goodsInfo['goods_price_' . $payType],
        										   'buy_place' => 'ざっか屋',
        										   'start_count' => $goodsCnt,
        										   'end_count' => $goodsCnt + 1,
        										   'description' => 'start:' . $userInfo[$payType] . '-,lastGid=' . $lastId,
        										   'buy_time' => time()));

            $info3 = array($payType => $userInfo[$payType] - $goodsInfo['goods_price_' . $payType]);
        	if ($payType == 'point') {
            	$info3['discount'] = 100;
            }
            $dalUser->updateUser($info3, $uid);

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            //debug_log('Mbll_Kitchen_Goods e: ' . $e->getMessage());
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }
}