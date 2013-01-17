<?php

/**
 * Mobile kitchen gift bussiness logic layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-12
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Gift extends Mbll_Abstract
{

    /**
     * Kitchen Gift
     *
     * @param string $uid
     * @param string $paytype
     * @param array $giftinfo
     * @return string
     */
    public function buyGift($uid, $payType, $giftInfo)
    {
        $result = false;

        try {
            require_once 'Mdal/Kitchen/Gift.php';
            $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
            require_once 'Mdal/Kitchen/User.php';
            $dalUser = Mdal_Kitchen_User::getDefaultInstance();
            $userInfo = $dalUser->getUser($uid);

            $this->_wdb->beginTransaction();

            $info = array('uid' => $uid, 'gift_id' => $giftInfo['gift_id'], 'create_time' => time());
            $dalGift->insertGift($info);

            $info3 = array($payType => $userInfo[$payType] - $giftInfo['price_' . $payType]);
        	if ($payType == 'point') {
            	$info3['discount'] = 100;
            }
            $dalUser->updateUser($info3, $uid);

            //admin page shopping info
            $giftCnt = $dalGift->hasGift($uid, $giftInfo['gift_id']);
        	$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        	$adminDalkitchen->insert('gift', array('uid' => $uid,
        										   'shop_id' => $giftInfo['gift_id'],
        										   'shop_name' => $giftInfo['name'],
        										   $payType => $giftInfo['price_' . $payType],
        										   'buy_place' => 'ギフト屋',
        										   'start_count' => $giftCnt - 1,
        										   'end_count' => $giftCnt,
        										   'description' => 'start:' . $userInfo[$payType] . '-',
        										   'buy_time' => time()));

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            //debug_log('Mbll_Kitchen_Gift e: ' . $e->getMessage());
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

	public function sendGift($giftSelect, $uid, $taruid)
    {
    	$result = false;

        try {
            require_once 'Mdal/Kitchen/Gift.php';
            $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();

            //$hasGift = $dalGift->hasGift($uid, $giftInfo['gift_id']);

            $this->_wdb->beginTransaction();

            $info = array('uid' => $uid,
                          'target_uid' => $taruid,
                          'gift_id' => $giftSelect['gift_id'],
                          'type' => 1,
            	          'create_time' => time());

            $dalGift->insertSendGift($info);

            $dalGift->deleteUserGift($giftSelect['gid']);

            //update daily param
            require_once 'Mdal/Kitchen/Daily.php';
            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
            $dailyInfo = $mdalDaily->getDaily($taruid);
            if ($dailyInfo && $dailyInfo['gift'] == 0) {
            	$mdalDaily->updateDaily(array('gift'=>1), $taruid);
            }

            //admin page shopping info
            $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
            $giftInfo = $dalGift->getGift($giftSelect['gift_id']);
        	$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        	$adminDalkitchen->insert('gift', array('uid' => $uid,
        										   'shop_id' => $giftSelect['gift_id'],
        										   'shop_name' => $giftInfo['name'],
        										   'create_type' => '贈送' . $giftSelect['gid'],
        										   'description' => $giftSelect['gid'] . ';' . $taruid,
        										   'buy_time' => time()));

        	$adminDalkitchen->insert('gift', array('uid' => $taruid,
        										   'shop_id' => $giftSelect['gift_id'],
        										   'shop_name' => $giftInfo['name'],
        										   'create_type' => '友達贈送',
        										   'description' => 'friend gift',
        										   'buy_time' => time()));

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            //debug_log('Mbll_Kitchen_Gift e: ' . $e->getMessage());
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }


    /***************** add by shenhw**********************/

    public function sendFreeGift($gid, $uid, $taruids)
    {
        $result = false;

        try {
            require_once 'Mdal/Kitchen/Gift.php';
            $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
            $date = date('Y-m-d');

            require_once 'Mdal/Kitchen/Daily.php';
            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();

            $this->_wdb->beginTransaction();

            foreach ($taruids as $taruid) {
                $sendGiftInfo = array('uid' => $uid,
		                              'target_uid' => $taruid,
		                              'gift_id' => $gid,
		                              'type' => 4,
		                              'create_time' => time());
                $dalGift->insertSendGift($sendGiftInfo);

                //update daily param
                $dailyInfo = $mdalDaily->getDaily($taruid);
	            if ($dailyInfo && $dailyInfo['gift'] == 0) {
	            	$mdalDaily->updateDaily(array('gift' => 1), $taruid);
	            }

                $freeSend = $dalGift->getFreeSendByFid($uid, $taruid);
                if ($freeSend) {
                   $dalGift->updateFreeGift($date, $uid, $taruid);
                }
                else {
                    $sendFreeGiftInfo = array('action_date' => $date,
			                                  'actor' => $uid,
			                                  'target' => $taruid);
                    $dalGift->insertFreeSendGift($sendFreeGiftInfo);
                }
            }

            //admin page shopping info
            $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
            $giftInfo = $dalGift->getFreeGift($gid);
        	$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        	$adminDalkitchen->insert('gift', array('uid' => $uid,
        										   'shop_id' => $giftInfo['gift_id'],
        										   'shop_name' => $giftInfo['name'],
        										   'create_type' => '友達贈送free gift',
        										   'description' => 'free gift',
        										   'buy_time' => time()));

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            //debug_log('Mbll_Kitchen_Gift e: ' . $e->getMessage());
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

    public function getMyGiftList($uid, $pageIndex = 1, $pageSize = 10, $orderType, $order)
    {
        require_once 'Mdal/Kitchen/Gift.php';
        $mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();

        $giftList = $mdalGift->getMyGiftList($uid, $pageIndex, $pageSize, $orderType, $order);

        $count = count($giftList);
        for($i = 0; $i < $count; $i++) {
            //1:gift 2:levelup gift 3:visit gift
            if (1 == $giftList[$i]['type']) {
                require_once 'Mbll/Kitchen/Cache.php';
                $mbllKitchenCache = new Mbll_Kitchen_Cache();
                $gift = $mbllKitchenCache->getGift($giftList[$i]['gift_id']);

                $giftList[$i]['name'] = $gift['name'];
                $giftList[$i]['introduce'] = $gift['introduce'];
                $giftList[$i]['picture'] = $gift['picture'];
            } else if (2 == $giftList[$i]['type']) {
                $giftList[$i]['name'] = "ごほうびギフト";
                $giftList[$i]['introduce'] = "レベルアップした時にもらえるギフト";
                $giftList[$i]['picture'] = "etc/03.gif";
            } else if (3 == $giftList[$i]['type']) {
                $giftList[$i]['name'] = "デイリーギフト";
                $giftList[$i]['introduce'] = "1日1回のログイン時にもらえるギフト";
                $giftList[$i]['picture'] = "etc/01.gif";
            } else if (4 == $giftList[$i]['type']) {
                $giftList[$i]['name'] = "ともだちギフト";
                //$giftList[$i]['introduce'] = "1日1回のログイン時にもらえるギフト";
                $giftList[$i]['picture'] = "freegift.gif";
            } else if (5 == $giftList[$i]['type']) {
                $giftList[$i]['name'] = "ごほうびギフト";
                $giftList[$i]['introduce'] = "キャンペーン達成した時にもらえるギフト";
                $giftList[$i]['picture'] = "etc/03.gif";
            }
            
        }

        require_once 'Bll/User.php';
        Bll_User::appendPeople($giftList, 'uid');

        return $giftList;
    }

    /**
     * get my gift count
     * @param integer $uid
     * @return integer
     */
    public function getMyGiftCount($uid)
    {
        require_once 'Mdal/Kitchen/Gift.php';
        $mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();

        $giftCount = $mdalGift->getMyGiftCount($uid);

        return $giftCount;
    }

    /**
     * get my gift count
     * @param integer $uid
     * @return integer
     */
    public function getSendGift($id)
    {
        require_once 'Mdal/Kitchen/Gift.php';
        $mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();

        $gift = $mdalGift->getSendGiftById($id);

        return $gift;
    }

    /**
     * open gift
     * @param array $gift
     * @return array
     */
    public function openGift($gift)
    {
        $result = false;
        $foods = array();
        $goods = array();
        $items = array();
        $point = 0;
        $giftItems = array();

        require_once 'Mdal/Kitchen/Gift.php';
        $mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();

        require_once 'Mbll/Kitchen/Cache.php';
        $mbllKitchenCache = new Mbll_Kitchen_Cache();

        //1:gift 2:levelup gift 3:visit gift 4: free gift
        if (1 == $gift['type']) {
            $sendGift = $mbllKitchenCache->getGift($gift['gift_id'], $gift['type']);

            //get food list
            $foods = $sendGift['food'] ? explode(',', $sendGift['food']) : $foods;

            //get goods list
            $goods = $sendGift['goods'] ? explode(',', $sendGift['goods']) : $goods;


        } else if (2 == $gift['type']) {
            //1:gift 2:levelup gift 3:visit gift
            $levelupGift = $mbllKitchenCache->getGift($gift['gift_id'], $gift['type']);

            //get food list
            $foods = $levelupGift['food_gift'] ? explode(',', $levelupGift['food_gift']) : $foods;

            //get item list
            $goods = $levelupGift['goods_gift'] ? explode(',', $levelupGift['goods_gift']) : $goods;

            //get goods list
            $items = $levelupGift['item_gift'] ? explode(',', $levelupGift['item_gift']) : $items;

        } else if (3 == $gift['type']) {
            //1:gift 2:levelup gift 3:visit gift
            $visitGift = $mbllKitchenCache->getGift($gift['gift_id'], $gift['type']);

            $visit_gift_type = $gift['visit_gift_type'];
            $visitGiftItems = array();

            if ('a' == $visit_gift_type) {
                $visitItems = $visitGift['item_a'] ? explode(',', $visitGift['item_a']) : array();
                if ($visitGift['point_a']) {
                    $visitItems[] = $visitGift['point_a'];
                }

                $i = rand(1, 300);

                switch ($i) {
                    //send card
                    case $i <= 100 :
                        $items[] = $visitItems[0];
                        break;
                    case 100 < $i && $i <= 200 :
                        $items[] = $visitItems[1];
                        break;
                    case 200 < $i && $i <= 300 :
                        $point = $visitItems[2];
                        break;
                    default :
                        break;
                }
            } else if ('b' == $visit_gift_type) {
                $visitItems = $visitGift['item_b'] ? explode(',', $visitGift['item_b']) : array();
                $i = rand(1, 200);

                switch ($i) {
                    //send card
                    case $i <= 100 :
                        $items[] = $visitItems[0];
                        break;
                    case 100 < $i && $i <= 200 :
                        $items[] = $visitItems[1];
                        break;
                    default :
                        break;
                }
            } else if ('c' == $visit_gift_type) {
                $items[] = $visitGift['item_c'];
            }
        } else if (4 == $gift['type']) {
            //1:gift 2:levelup gift 3:visit gift
            $freeGift = $mbllKitchenCache->getGift($gift['gift_id'], $gift['type']);

            //get food list
            $foods = $freeGift['food_gift'] ? explode(',', $freeGift['food_gift']) : $foods;

            //get item list
            $goods = $freeGift['goods_gift'] ? explode(',', $freeGift['goods_gift']) : $goods;

            //get goods list
            $items = $freeGift['item_gift'] ? explode(',', $freeGift['item_gift']) : $items;

        } else if (5 == $gift['type']) {
            //1:gift 2:levelup gift 3:visit gift 4: free gift 5:campain gift
            $campainGift = $mbllKitchenCache->getGift($gift['gift_id'], $gift['type']);
            
            //get food list
            $foods = $campainGift['food_gift'] ? explode(',', $campainGift['food_gift']) : $foods;
            
            //get goodslist
            $goods = $campainGift['goods_gift'] ? explode(',', $campainGift['goods_gift']) : $goods;
            
            //get item list
            $items = $campainGift['item_gift'] ? explode(',', $campainGift['item_gift']) : $items;
        
        }


        try {
            $this->_wdb->beginTransaction();

            //food
            if ($foods) {
                $foodCount = count($foods);

                require_once 'Mbll/Kitchen/Food.php';
                $mbllFood = new Mbll_Kitchen_Food();

                require_once 'Mdal/Kitchen/Food.php';
                $mdalFood = Mdal_Kitchen_Food::getDefaultInstance();

                for ($i = 0; $i < $foodCount; $i++) {
                    $food = $mbllKitchenCache->getFood($foods[$i]);

                    $itemFood['type'] = "food";
                    $itemFood['name'] = $food['food_name'];
                    $itemFood['introduce'] = $food['food_introduce'];
                    $itemFood['picture'] = $food['food_picture'];

                    $itemFood['food_category'] = $mbllFood->getFoodCatogeryById($food['food_category']);
                    $itemFood['food_count'] = $food['food_count'];

                    //admin page shopping info
                    $foodCnt = $mdalFood->getFoodCount($gift['target_uid'], $food['food_id']);
        			$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        			$adminDalkitchen->insert('food', array('uid' => $gift['target_uid'],
		        										   'shop_id' => $food['food_id'],
		        										   'shop_name' => $food['food_name'],
		        										   'create_type' => '打開gift',
		        										   'start_count' => $foodCnt,
		        										   'end_count' => $foodCnt + $food['food_count'],
		        										   'description' => 'open_gift:openGid=' . $gift['id'],
		        										   'buy_time' => time()));

                    $mdalFood->insertFoodFromGift($gift['target_uid'],$food);
                    //$count = count($giftItems);
                    $giftItems[] = $itemFood;
                }
            }

            //goods
            if ($goods) {
                $goodsCount = count($goods);

                //require_once 'Mbll/Kitchen/Goods.php';
                //$mbllGoods = new Mbll_Kitchen_Goods();

                require_once 'Mdal/Kitchen/Goods.php';
                $mdalGoods = Mdal_Kitchen_Goods::getDefaultInstance();

                for ($i = 0; $i < $goodsCount; $i++) {
                    $good = $mbllKitchenCache->getGoods($goods[$i]);

                    $itemGoods['type'] = "goods";
                    $itemGoods['name'] = $good['goods_name'];
                    $itemGoods['introduce'] = $good['goods_introduce'];
                    $itemGoods['picture'] = $good['goods_picture'];

                    $itemGoods['goods_id'] = $good['goods_id'];

                    require_once 'Mbll/Kitchen/Restaurant.php';
                    $mbllKitchenRes = new Mbll_Kitchen_Restaurant();
                    $itemGoods['genre_id'] = $good['genre'];
                    $itemGoods['genre'] = $mbllKitchenRes->getGenreNameById($good['genre']);

                    //admin page shopping info
                    $goodsCnt = $mdalGoods->getUserGoodsCount($gift['target_uid']);
        			$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        			$adminDalkitchen->insert('goods', array('uid' => $gift['target_uid'],
		        										   'shop_id' => $good['goods_id'],
		        										   'shop_name' => $good['goods_name'],
		        										   'start_count' => $goodsCnt,
		        										   'end_count' => $goodsCnt + 1,
		        										   'create_type' => '打開 gift',
		        										   'description' => 'open_gift:openGid=' . $gift['id'],
		        										   'buy_time' => time()));

                    $mdalGoods->insertGoods(array('uid' => $gift['target_uid'], 'goods_id' => $good['goods_id'], 'create_time' => time()));
                    //$count = count($giftItems);
                    $giftItems[] = $itemGoods;
                }
            }

            //items
            if ($items) {
                $itemCount = count($items);

                //require_once 'Mbll/Kitchen/Item.php';
                //$mbllItem = new Mbll_Kitchen_Item();

                require_once 'Mdal/Kitchen/Item.php';
                $mdalItem = Mdal_Kitchen_Item::getDefaultInstance();

                for ($i = 0; $i < $itemCount; $i++) {
                    $item = $mbllKitchenCache->getItem($items[$i]);

                    $itemItem['type'] = "item";
                    $itemItem['name'] = $item['item_name'];
                    $itemItem['introduce'] = $item['item_introduce'];
                    $itemItem['picture'] = $item['item_picture'];
                    $itemItem['item_count'] = $item['item_count'];

                    //admin page shopping info
                    $itemCnt = $mdalItem->getItemCount($gift['target_uid'], $item['item_id']);
        			$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        			$adminDalkitchen->insert('item', array('uid' => $gift['target_uid'],
		        										   'shop_id' => $item['item_id'],
		        										   'shop_name' => $item['item_name'],
		        										   'create_type' => '打開gift',
		        										   'start_count' => $itemCnt,
		        										   'end_count' => $itemCnt + $item['item_count'],
		        										   'description' => 'open_gift:openGid' . $gift['id'],
		        										   'buy_time' => time()));

                    $mdalItem->insertItemFromGift($gift['target_uid'], $item);
                    $giftItems[] = $itemItem;
                }
            }

            require_once 'Mdal/Kitchen/User.php';
            $mdalUser = Mdal_Kitchen_User::getDefaultInstance();

            //update user point
            if ($point) {
                $mdalUser->updateUserBy($gift['target_uid'], "point", $point);

                $itemPoint['type'] = "point";
                $itemPoint['name'] = "ﾁｯﾌﾟ";
                $itemPoint['introduce'] = "獲得したﾁｯﾌﾟ";
                $itemPoint['picture'] = "etc/02";
                $itemPoint['point'] = $point;
                $giftItems[] = $itemPoint;
                $result = true;

                //admin page shopping info
	        	$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
	        	$adminDalkitchen->insert('gift', array('uid' => $gift['target_uid'],
	        										   'point' => $point,
	        										   'create_type' => '打開gift,get point',
	        										   'description' => 'open_gift:point;openGid=' . $gift['id'],
	        										   'buy_time' => time()));
            }

            //update gift cout
            $mdalUser->updateUserBy($gift['target_uid'], 'gift_count', 1);

            //delete send gift for target_uid
            $mdalGift->deleteSendGift($gift['id']);

            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e){
            //debug_log('Mbll_Kitchen_Gift e: ' . $e->getMessage());
            $this->_wdb->rollBack();
            return $result;
        }
    	if ($result) {
            //access analyse
	        require_once 'Mdal/Kitchen/Access.php';
	        $mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();

	        try {
		        $mdalAccess->insertMoney(array('uid' => $gift['target_uid'],
		        						       'amount' => $point,
		        						       'type' => 2,
		        						       'description' => 'daily_gift',
		        						       'create_time' => time()));
	        }
        	catch (Exception $e){
	        }
        }

        return $giftItems;
    }
    
    public function addCampainGift($uid)
    {
    	$result = 0;
    	require_once 'Mdal/Kitchen/InviteSuccess.php';
	    $mdalInviteSuccess = Mdal_Kitchen_InviteSuccess::getDefaultInstance();
	    
	    require_once 'Mdal/Kitchen/Gift.php';
	    $mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();

	    require_once 'Mdal/Kitchen/User.php';
	    $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
	    //#campain No.1
	    //invite total=3 
	    //then add  item16 and 300m
	    $campainId = 1;
	    
	    $timeNow = time();
	    $start = 1272639600;
	    $end = 1275318000;
	    
	    $campainRe = $mdalGift->getCampain($uid, $campainId);
	    
	    if (empty($campainRe)) {
	    	$invScusCnt = $mdalInviteSuccess->getInviteSuccessCountLimitTime($uid, $start, $end);
	    	if ($invScusCnt >= 3) {
		    	try {
		            $this->_wdb->beginTransaction();
			    	
			    	$mdalGift->addCampain($uid, $campainId, 1);
			    	$info = array('uid' => 0,
	    					      'target_uid' => $uid,
	    		                  'gift_id' => 5000 + $campainId,
	    					      'type' => 5,
			    				  'create_time' => $timeNow);
			    	$mdalGift->insertSendGift($info);
			    	
			    	$mdalUser->updateUserBy($uid, 'gold', 300);
			    	
			        $this->_wdb->commit();
		        }
		        catch (Exception $e){
		            $this->_wdb->rollBack();
		            return $result;
		        }
	    	}
	    }
	    $result = 1;
	    
	    return $result;
	    
    }
    
    //#campain No.2
    public function addCampainGiftTwo($uid)
    {
    	$result = 0;
    	require_once 'Mdal/Kitchen/InviteSuccess.php';
	    $mdalInviteSuccess = Mdal_Kitchen_InviteSuccess::getDefaultInstance();
	    
	    require_once 'Mdal/Kitchen/Gift.php';
	    $mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();

	    require_once 'Mdal/Kitchen/User.php';
	    $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
	    //#campain No.2
	    //login in 4/1---5/14 
	    //then add  item12 and 500point
	    $campainId = 2;
	    
	    $timeNow = time();
	    $start = 1270047600;
	    $end = 1275031800;
	    
	    $campainRe = $mdalGift->getCampain($uid, $campainId);
	    
	    if (empty($campainRe)) {
	    	$userInfo = $mdalUser->getUser($uid);
	    	if ($userInfo['last_login_time'] >= $start && $userInfo['last_login_time'] <= $end) {
		    	try {
		            $this->_wdb->beginTransaction();
			    	
			    	$info = array('uid' => 0,
	    					      'target_uid' => $uid,
	    		                  'gift_id' => 5000 + $campainId,
	    					      'type' => 5,
			    				  'create_time' => $timeNow);
			    	$mdalGift->insertSendGift($info);
			    	
			    	$mdalUser->updateUserBy($uid, 'point', 500);
			    	
			        $this->_wdb->commit();
		        }
		        catch (Exception $e){
		            $this->_wdb->rollBack();
		            return $result;
		        }
	    	}
	    	$mdalGift->addCampain($uid, $campainId, 1);
	    }
	    
	    
	    $result = 1;
	    
	    return $result;
	    //#campain No.2
    }
}