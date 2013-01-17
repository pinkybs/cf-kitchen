<?php

/**
 * Mobile kitchen item bussiness logic layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-11
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Item extends Mbll_Abstract
{
	/**
	 * Kitchen Item
	 *
	 * @param string $uid
	 * @param string $paytype
	 * @param array $iteminfo
	 * @param string $itemType :'item' , 'beauty'
	 * @return string
	 */
    public function buyItem($uid, $payType, $itemInfo, $itemType = 'item')
    {
    	$result = false;

        try {
            require_once 'Mdal/Kitchen/Item.php';
            $dalItem = Mdal_Kitchen_Item::getDefaultInstance();
            require_once 'Mdal/Kitchen/User.php';
            $dalUser = Mdal_Kitchen_User::getDefaultInstance();
            $userInfo = $dalUser->getUser($uid);

            $hasItem = $dalItem->hasItem($uid, $itemInfo['item_id']);

            $this->_wdb->beginTransaction();

            if ($hasItem) {
                //admin page shopping info
                $itemCount = $dalItem->getItemCount($uid, $itemInfo['item_id']);
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$adminDalkitchen->insert($itemType, array( 'uid' => $uid,
		        										   'shop_id' => $itemInfo['item_id'],
		        										   'shop_name' => $itemInfo['item_name'],
		        										   $payType => $itemInfo['item_price_' . $payType],
		        										   'buy_place' => $itemType == 'item' ? 'よろず屋' : '美容室',
		        										   'start_count' => $itemCount,
		        										   'end_count' => $itemCount + $itemInfo['item_count'],
		        										   'description' => 'start:' . $userInfo[$payType] . '-',
		        										   'buy_time' => time()));

            	$dalItem->addItemNum($uid, $itemInfo['item_id'], $itemInfo['item_count']);
            }
            else {
            	$info = array('uid' => $uid,
                              'item_id' => $itemInfo['item_id'],
            	              'item_count' => $itemInfo['item_count'],
            	              'kitchen_only' => $itemInfo['kitchen_only']);
            	$dalItem->insertItem($info);

            	//admin page shopping info
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$adminDalkitchen->insert($itemType, array( 'uid' => $uid,
		        										   'shop_id' => $itemInfo['item_id'],
		        										   'shop_name' => $itemInfo['item_name'],
		        										   $payType => $itemInfo['item_price_' . $payType],
		        										   'buy_place' => $itemType == 'item' ? 'よろず屋' : '美容室',
		        										   'start_count' => 0,
		        										   'end_count' => $itemInfo['item_count'],
		        										   'description' => 'start:' . $userInfo[$payType] . '-',
		        										   'buy_time' => time()));
            }

            $info3 = array($payType => $userInfo[$payType] - $itemInfo['item_price_' . $payType]);
        	if ($payType == 'point') {
            	$info3['discount'] = 100;
            }
            $dalUser->updateUser($info3, $uid);

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

    public function useItemSp($uid, $itemId)
    {
    	$result = 'error';
        //check if user has item
    	require_once 'Mdal/Kitchen/Item.php';
    	$mdalItem = Mdal_Kitchen_Item::getDefaultInstance();
    	if (!$mdalItem->hasItem($uid, $itemId)) {
    		return $result;
    	}

    	if ($mdalItem->hasItemCnt($uid, $itemId) == 0) {
    		return $result;
    	}


        require_once 'Mbll/Kitchen/Cache.php';
    	$item = Mbll_Kitchen_Cache::getItem($itemId);
    	$genre = $item['effect_rate'];

    	require_once 'Mdal/Kitchen/Restaurant.php';
    	$mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
    	$hasGenre = $mdalRest->hasRest($uid, $genre);
    	if (!$hasGenre) {
    		return 'learned';
    	}

    	//get user recipe which don't have
    	require_once 'Mdal/Kitchen/Recipe.php';
    	$mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
    	$recipe = $mdalRecipe->getUserRecipeNot($uid, $genre);

    	$gachaRecipes = $mdalRecipe->getGachaBasedRecipes($genre);

    	$recipe_id = 0;
    	while (!empty($recipe)) {
    		$recipe_id = $recipe[0]['recipe_id'];

	    	if (!in_array($recipe_id, $gachaRecipes)) {
	    		break;
	    	}
	    	array_splice($recipe, 0, 1);

    		$recipe_id = 0;
    	}

    	if (empty($recipe_id)) {
    		return 'learned';
    	}

    	$this->_wdb->beginTransaction();

    	try {
	    	$recipeInsert = array('uid' => $uid,
	    					'recipe_id' => $recipe_id,
	    					'genre' => $genre,
	    					'lucky_flag' => 0,
	    					'cooking_times' => 0,
	    					'create_time' => time());
	    	$mdalRecipe->insert($recipeInsert);

	    	//add res_user_resaurant recipe_count
	    	$mdalRest->addRecipeCount($uid, $genre);

	    	//add res_user_profile total_recipe_count
	    	require_once 'Mdal/Kitchen/User.php';
	    	$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
	    	$mdalUser->updateUserBy($uid, 'total_recipe_count', 1);

	        //admin page shopping info
            $itemCnt = $mdalItem->getItemCount($uid, $itemId);
        	$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        	$adminDalkitchen->insert('item', array('uid' => $uid,
        										   'shop_id' => $itemId,
        										   'shop_name' => $item['item_name'],
        										   'start_count' => $itemCnt,
        										   'end_count' => $itemCnt - 1,
        										   'description' => 'useItemSp',
        										   'create_type' => 'useSp',
        										   'buy_time' => time()));

            //update user item count
	        $mdalItem->addItemNum($uid, $itemId, -1);

	        $this->_wdb->commit();
	        $result = $recipe_id;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }


    /**
     * use item
     *
     * @param integer $uid
     * @param integer $itemId
     */
    public function useItem($uid, $itemId, $kitchenId)
    {
    	$result = -1;
    	$kitchenOnly = array(1,2,3,4,5,6,7,8,9,10,11,12,20,21,22,23);
    	if ($kitchenId == 0 && in_array($itemId, $kitchenOnly)) {
    		return -2;
    	}

    	//check if user has item
    	require_once 'Mdal/Kitchen/Item.php';
    	$mdalItem = Mdal_Kitchen_Item::getDefaultInstance();
    	if (!$mdalItem->hasItem($uid, $itemId)) {
    		return $result;
    	}

    	if ($mdalItem->hasItemCnt($uid, $itemId) == 0) {
    		return $result;
    	}

    	if ($itemId == 20 || $itemId == 21) {
    		require_once 'Mdal/Kitchen/User.php';
    		$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
    		$userInfo = $mdalUser->getUser($uid);
    		if ($userInfo['try_rate_add'] != 0) {
    			return $result;
    		}
    	}

    	$this->_wdb->beginTransaction();

        try {
        	if ($itemId == 1 || $itemId == 2 || $itemId == 3 || $itemId == 4 || $itemId == 5 || $itemId == 6) {
        		$this->itemLogicRate($uid, $itemId, $kitchenId);
        	}
        	else if ($itemId == 7 || $itemId == 8 || $itemId == 9 || $itemId == 10 || $itemId == 11 || $itemId == 12) {
        		$this->itemLogicTime($uid, $itemId, $kitchenId);
        	}
        	else if ($itemId == 13 || $itemId == 14 || $itemId == 15) {
        		$this->itemLogicDiscount($uid, $itemId);
        	}
        	else if ($itemId == 19) {
        		$this->itemLogic19($uid);
        	}
        	else if ($itemId == 20 || $itemId == 21) {
        		//try_food_rate
        		$this->itemLogicTryRate($uid, $itemId);
        	}
        	else if ($itemId == 22 || $itemId == 23) {
        		//exp_add
        		$this->itemLogicExp($uid, $itemId, $kitchenId);
        	}
        	else {
        		return $result;
        	}

        	//admin page shopping info
        	$dalItem = Mdal_Kitchen_Item::getDefaultInstance();
            $itemCnt = $dalItem->getItemCount($uid, $itemId);
            $item = Mbll_Kitchen_Cache::getItem($itemId);
        	$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        	$adminDalkitchen->insert('item', array('uid' => $uid,
        										   'shop_id' => $itemId,
        										   'shop_name' => $item['item_name'],
        										   'start_count' => $itemCnt,
        										   'end_count' => $itemCnt - 1,
        										   'description' => 'useItem',
        										   'create_type' => 'use item',
        										   'buy_time' => time()));

        	//update user item count
        	$mdalItem->addItemNum($uid, $itemId, -1);

        	$this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

    private function itemLogicRate($uid, $itemId, $kitchenId)
    {
        require_once 'Mbll/Kitchen/Cache.php';
    	$item = Mbll_Kitchen_Cache::getItem($itemId);

        //get current effect rate
        require_once 'Mdal/Kitchen/Kitchen.php';
    	$mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
    	$mdalKitchen->updateKitchenRate($uid, $kitchenId, $item['effect_rate']);
    }

    private function itemLogicTime($uid, $itemId, $kitchenId)
    {
    	require_once 'Mbll/Kitchen/Cache.php';
    	$item = Mbll_Kitchen_Cache::getItem($itemId);

    	require_once 'Mdal/Kitchen/Kitchen.php';
    	$mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
    	$kitchen = $mdalKitchen->getUserKitchen($uid, $kitchenId);

    	$cooking_part1 = round($kitchen['cooking_part1']*(100-$item['effect_rate'])/100);
    	//$cooking_part1 = $cooking_part1 == 0 ? 1 : $cooking_part1;

    	$cooking_part2 = round($kitchen['cooking_part2']*(100-$item['effect_rate'])/100);
    	//$cooking_part2 = $cooking_part2 == 0 ? 1 : $cooking_part2;

    	if (!empty($kitchen['cooking_part3'])) {
    		$cooking_part3 = round($kitchen['cooking_part3']*(100-$item['effect_rate'])/100);
    		//$cooking_part3 = $cooking_part3 == 0 ? 1 : $cooking_part2;
    	}

    	$mdalKitchen->updateKitchenTime($uid, $kitchenId, $cooking_part1, $cooking_part2, $cooking_part3);
    }

    private function itemLogicDiscount($uid, $itemId)
    {
    	require_once 'Mbll/Kitchen/Cache.php';
    	$item = Mbll_Kitchen_Cache::getItem($itemId);

    	require_once 'Mdal/Kitchen/User.php';
    	$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$user = $mdalUser->getUser($uid);

    	$discount = round($user['discount'] * $item['effect_rate'] / 100);
    	$discount = $discount == 0 ? 1 : $discount;

    	$mdalUser->updateUser(array('discount' => $discount), $uid);
    }

    private function itemLogicTryRate($uid, $itemId)
    {
    	require_once 'Mbll/Kitchen/Cache.php';
    	$item = Mbll_Kitchen_Cache::getItem($itemId);

    	require_once 'Mdal/Kitchen/User.php';
    	$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
    	/*
    	$user = $mdalUser->getUser($uid);
    	$tryRate = $user['try_rate_add'] + $item['effect_rate'];
    	$mdalUser->updateUser(array('try_rate_add' => $tryRate), $uid);
		*/
    	$mdalUser->updateUserBy($uid, 'try_rate_add', $item['effect_rate']);
    }

    private function itemLogicExp($uid, $itemId, $kitchenId)
    {
    	require_once 'Mbll/Kitchen/Cache.php';
    	$item = Mbll_Kitchen_Cache::getItem($itemId);

    	require_once 'Mdal/Kitchen/Kitchen.php';
    	$mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
    	$kitchen = $mdalKitchen->getUserKitchen($uid, $kitchenId);

    	$expRate = $kitchen['exp_rate'] + $item['effect_rate'];
    	$expRate = $expRate > 1000 ? 1000 : $expRate;

    	$mdalKitchen->update(array('exp_rate' => $expRate), $uid, $kitchenId);
    }

    private function itemLogic19($uid)
    {
    	require_once 'Mdal/Kitchen/User.php';
    	$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$mdalUser->updateUser(array('allow_editchara' => 1), $uid);
    }

    public function reSpoon($uid, $rate)
    {
    	$result = false;

    	require_once 'Mdal/Kitchen/Item.php';
        $dalItem = Mdal_Kitchen_Item::getDefaultInstance();
        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();

        $itemId = $rate == 50 ? 20 : 21;

        $this->_wdb->beginTransaction();

        try {

            //admin page shopping info
            $itemCnt = $dalItem->getItemCount($uid, $itemId);
            $item = Mbll_Kitchen_Cache::getItem($itemId);
        	$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        	$adminDalkitchen->insert('item', array('uid' => $uid,
        										   'shop_id' => $itemId,
        										   'shop_name' => $item['item_name'],
        										   'start_count' => $itemCnt,
        										   'end_count' => $itemCnt + 1,
        										   'description' => 'try_rate_add',
        										   'create_type' => 'try_rate_add',
        										   'buy_time' => time()));

            $dalItem->addItemNum($uid, $itemId, 1);

            $info['try_rate_add'] = 0;

            $dalUser->updateUser($info, $uid);

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }
}