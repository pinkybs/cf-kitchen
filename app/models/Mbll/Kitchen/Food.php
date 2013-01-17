<?php

/**
 * Mobile kitchen food bussiness logic layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-5
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Food extends Mbll_Abstract
{
	/**
	 * Kitchen Food
	 *
	 * @param string $uid
	 * @param string $paytype
	 * @param array $foodinfo
	 * @return string
	 */
    public function buyFood($uid, $payType, $foodInfo)
    {
    	$result = false;

        try {
            require_once 'Mdal/Kitchen/Food.php';
            $dalFood = Mdal_Kitchen_Food::getDefaultInstance();
            require_once 'Mdal/Kitchen/User.php';
            $dalUser = Mdal_Kitchen_User::getDefaultInstance();
            $userInfo = $dalUser->getUser($uid);

            $hasFood = $dalFood->hasFood($uid, $foodInfo['food_id']);

            $this->_wdb->beginTransaction();

            if ($hasFood) {
                //admin page shopping info
                $countFood = $dalFood->getFoodCount($uid, $foodInfo['food_id']);
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$adminDalkitchen->insert('food', array('uid' => $uid,
	        										   'shop_id' => $foodInfo['food_id'],
	        										   'shop_name' => $foodInfo['food_name'],
	        										   $payType => $foodInfo['food_price_' . $payType],
	        										   'buy_place' => '食材市場',
	        										   'start_count' => $countFood,
	        										   'end_count' => $countFood + $foodInfo['food_count'],
	        										   'description' => 'start:' . $userInfo[$payType] . '-',
	        										   'buy_time' => time()));

                $dalFood->addFoodNum($uid, $foodInfo['food_id'], $foodInfo['food_count']);
            }
            else {
            	$info = array('uid' => $uid,
                              'food_id' => $foodInfo['food_id'],
            	              'food_count' => $foodInfo['food_count'],
            	              'food_category' => $foodInfo['food_category']);
            	$dalFood->insertFood($info);

            	//admin page shopping info
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$adminDalkitchen->insert('food', array('uid' => $uid,
	        										   'shop_id' => $foodInfo['food_id'],
	        										   'shop_name' => $foodInfo['food_name'],
	        										   $payType => $foodInfo['food_price_' . $payType],
	        										   'buy_place' => '食材市場',
	        										   'start_count' => 0,
	        										   'end_count' => $foodInfo['food_count'],
	        										   'description' => 'start:' . $userInfo[$payType] . '-',
	        										   'buy_time' => time()));
            }

            $info3 = array($payType => $userInfo[$payType] - $foodInfo['food_price_' . $payType]);
            if ($payType == 'point') {
            	$info3['discount'] = 100;
            }
            $dalUser->updateUser($info3, $uid);

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            //debug_log('Mbll_Kitchen_Food e: ' . $e->getMessage());
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }


    /************* add by shenhw********************/

    public function getFoodCatogeryList() {
        return array('1' => "魚介類", '2' => "穀類", '3' => "調味料", '4' => "肉類", '5' => "野菜類", '6' => "乳卵・豆", '7' => "フルーツ");
    }

    public function getFoodCatogeryById($id) {
        $catogeryList = $this->getFoodCatogeryList();
        return $catogeryList[$id];
    }
}