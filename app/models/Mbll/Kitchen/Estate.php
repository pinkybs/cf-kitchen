<?php

/**
 * Mobile kitchen estate bussiness logic layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-7
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Estate extends Mbll_Abstract
{
	/**
	 * Kitchen Estate
	 *
	 * @param string $uid
	 * @param string $paytype
	 * @param array $estateinfo
	 * @return string
	 */
    public function buyEstate($uid, $payType, $estateInfo)
    {
    	$result = false;
    	
        try {
            require_once 'Mdal/Kitchen/User.php';
            $dalUser = Mdal_Kitchen_User::getDefaultInstance();
            require_once 'Mdal/Kitchen/Restaurant.php';
        	$dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
            require_once 'Mdal/Kitchen/Kitchen.php';
        	$dalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            require_once 'Mdal/Kitchen/Recipe.php';
        	$dalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
        	
        	
        	$userInfo = $dalUser->getUser($uid);
            
            $hasRest = $dalRest->hasRest($uid, $estateInfo['genre']);
            
            $restInfo = $dalRest->getActiveRestaurant($uid);
            
            $this->_wdb->beginTransaction();
            
            //2010-1-18
            if ($hasRest) {
            	$info = array('estate' => $estateInfo['estate_id']);
            	
                if ($restInfo['genre'] == $estateInfo['genre']) {
	            	$info2 = array('uid' => $uid,
	                               'kitchen_id' => $estateInfo['estate_id'],
	            	               'genre' => $estateInfo['genre']);
	            	$dalKitchen->insert($info2);
                }
                
                $dalRest->updateRestaurant($info, $uid, $estateInfo['genre']);
				
            }
            else {
            	//get gift
        		require_once 'Mdal/Kitchen/Food.php';
                $dalFood = Mdal_Kitchen_Food::getDefaultInstance();
                require_once 'Mdal/Kitchen/Item.php';
                $dalItem = Mdal_Kitchen_Item::getDefaultInstance();
                
                if ($estateInfo['genre'] == 2){
	                $foodId1 = 'fi01';
	                $foodId2 = 'me02';
	                $itemId = '1';
	            }
            	else if ($estateInfo['genre'] == 3){
            		return $result;
            	}
            	else {
            		return $result;
            	}
                
                require_once 'Mbll/Kitchen/Cache.php';
        		$foodInfo1 = Mbll_Kitchen_Cache::getFood($foodId1);
	            $hasFood1 = $dalFood->hasFood($uid, $foodInfo1['food_id']);
	            if ($hasFood1) {
	            	$dalFood->addFoodNum($uid, $foodInfo1['food_id'], $foodInfo1['food_count']);
	            }
	            else {
	            	$foodInsert1 = array('uid' => $uid,
	                              'food_id' => $foodInfo1['food_id'],
	            	              'food_count' => $foodInfo1['food_count'],
	            	              'food_category' => $foodInfo1['food_category']);
	            	$dalFood->insertFood($foodInsert1);
	            }
	            
                $foodInfo2 = Mbll_Kitchen_Cache::getFood($foodId2);
	            $hasFood2 = $dalFood->hasFood($uid, $foodInfo2['food_id']);
	            if ($hasFood2) {
	            	$dalFood->addFoodNum($uid, $foodInfo2['food_id'], $foodInfo2['food_count']);
	            }
	            else {
	            	$foodInsert2 = array('uid' => $uid,
	                              'food_id' => $foodInfo2['food_id'],
	            	              'food_count' => $foodInfo2['food_count'],
	            	              'food_category' => $foodInfo2['food_category']);
	            	$dalFood->insertFood($foodInsert2);
	            }
	            
	            $itemInfo = Mbll_Kitchen_Cache::getItem($itemId);
	            $hasItem = $dalItem->hasItem($uid, $itemInfo['item_id']);
	            if ($hasItem) {
	            	$dalItem->addItemNum($uid, $itemInfo['item_id'], 1);
	            }
	            else {
	            	$info = array('uid' => $uid,
	                              'item_id' => $itemInfo['item_id'],
	            	              'item_count' => 1,
	            	              'kitchen_only' => $itemInfo['kitchen_only']);
	            	$dalItem->insertItem($info);
	            }
                
                //add recipe 
            	if ($estateInfo['genre'] == 2) {
            		$recipeId1 = 'r20';
            		//$recipeId2 = 'r19';
            	}
            	else if ($estateInfo['genre'] == 3){
            		return $result;
            	}
            	else {
            		return $result;
            	}
            	
            	$recipe1 = $dalRecipe->hasRecipe($uid, $recipeId1);
            	//$recipe2 = $dalRecipe->hasRecipe($uid, $recipeId2);
            	
            	if (!$recipe1) {
	            	$info21 = array('uid' => $uid,
	            	               'recipe_id' => $recipeId1,
	            	               'genre' => $estateInfo['genre'],
	            	               'lucky_flag' => 0,
	            	               'create_time' => time());
	            	$dalRecipe->insert($info21);
            	}
            	/*
            	else {
            		$dalRecipe->update($uid, $recipeId1, array('lucky_flag' => 1));
            	}
            	

            	if (!$recipe2){
	            	$info22 = array('uid' => $uid,
	            	               'recipe_id' => $recipeId2,
	            	               'genre' => $estateInfo['genre'],
	            	               'lucky_flag' => 0,
	            	               'create_time' => time());
	            	$dalRecipe->insert($info22);
            	}
            	
            	else {
            		$dalRecipe->update($uid, $recipeId2, array('lucky_flag' => 1));
            	}
                */
            	
            	$recipeIncr = (!$recipe1) ? 1 : 0;
            	//$dalUser->updateUserBy($uid, 'total_recipe_count', $recipeIncr);
            	
            	$basicRecipeCnt = $dalRecipe->getUserRecipeCountByGenre($uid, $estateInfo['genre']);
            	$info = array('uid' => $uid,
                              'genre' => $estateInfo['genre'],
            	              'name' => $restInfo['name'],
            	              'recipe_count' => $basicRecipeCnt);
            	
            	$dalRest->insertRestaurant($info);
            	
            	$info3 = array('total_level' => $userInfo['total_level'] + 1);
            	if ($recipeIncr) {
            		$info3['total_recipe_count'] = $userInfo['total_recipe_count'] + $recipeIncr;
	            }
            }
            
            $info3[$payType] = $userInfo[$payType] - $estateInfo['estate_price_' . $payType];
        	if ($payType == 'point') {
            	$info3['discount'] = 100;
            }
            
            $dalUser->updateUser($info3, $uid);
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            //debug_log('Mbll_Kitchen_Estate e: ' . $e->getMessage());
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }
}