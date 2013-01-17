<?php

/**
 * Mobile kitchen recipe logic
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  hch  2010-1-7
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Recipe extends Mbll_Abstract
{
	/**
	 * research recipe
	 *
	 * @param integer $uid
	 * @param integer $kitchenID
	 * @param integer $recipeId1
	 * @param integer $recipeId2
	 * @param integer $recipeId3
	 * @param integer $recipeId4
	 * @return integer
	 */
    public function research($uid, $kitchenID, $foodId1, $foodId2, $foodId3, $foodId4, &$recipeId)
    {
        $result = -1;

        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $rest = $mdalRest->getActiveRestaurant($uid);

        //check user has kitchen and the kitchen is not in cooking
        require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $kitchen = $mdalKitchen->getUserKitchen($uid, $kitchenID);

        if (empty($kitchen)) {
        	return $result;
        }
        if (!empty($kitchen['cooking_recipe_id'])) {
        	return $result;
        }
        if ($kitchen['failure_count'] == 3) {
        	return -2;
        }

        //check user food
        require_once 'Mdal/Kitchen/Food.php';
        $mdalFood = Mdal_Kitchen_Food::getDefaultInstance();
        if (!empty($foodId1)) {
        	if (!$mdalFood->checkUserHasFood($uid, $foodId1)) {
        		return $result;
        	}
        }
        if (!empty($foodId2)) {
        	if (!$mdalFood->checkUserHasFood($uid, $foodId2)) {
        		return $result;
        	}
        }
        if (!empty($foodId3)) {
        	if (!$mdalFood->checkUserHasFood($uid, $foodId3)) {
        		return $result;
        	}
        }
        if (!empty($foodId4)) {
        	if (!$mdalFood->checkUserHasFood($uid, $foodId4)) {
        		return $result;
        	}
        }

        //check recipe
        $recipe = $this->checkRecipe($rest['genre'], $foodId1, $foodId2, $foodId3, $foodId4);

        //has no recipe
        if ($recipe === false) {
        	//reduce research num
        	return $this->researchFailure($uid, $kitchenID, $foodId1, $foodId2, $foodId3, $foodId4);
        }
        else {
        	require_once 'Mdal/Kitchen/Recipe.php';
    		$mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
        	$userRecipe = $mdalRecipe->getEasyUserRecipeByGenre($uid,$rest['genre']);

        	for ($i = 0; $i < count($recipe); $i++) {
        		$newRecipe = true;
        		$r_id = $recipe[$i]['recipe_id'];
        		$r_luck = $recipe[$i]['lucky_flag'];
        		for ($j = 0; $j < count($userRecipe); $j++) {
        			if ($r_id == $userRecipe[$j]['recipe_id'] && ($userRecipe[$j]['lucky_flag'] == 1 || $r_luck == 0)) {
        				$newRecipe = false;
        			}
        		}
        		if ($newRecipe) {
        			break;
        		}
        	}
        }

        //if success,add new recipe
        $flag = $this->addRecipe($uid, $rest['genre'], $r_id, $r_luck, $foodId1, $foodId2, $foodId3, $foodId4);
        $recipeId = $r_id;

        if ($flag) {
        	$result = 1;
        }

        return $result;
    }

    /**
     * check recipe
     *
     * @param integer $genre
     * @param integer $foodId1
     * @param integer $foodId2
     * @param integer $foodId3
     * @param integer $foodId4
     * @return array
     */
    private function checkRecipe($genre, $foodId1, $foodId2, $foodId3, $foodId4)
    {
        //check recipe
        require_once 'Mbll/Kitchen/Cache.php';
        $recipe = Mbll_Kitchen_Cache::getRecipe();

        $developArr = array();

        $foodBack1 = $foodId1;
        $foodBack2 = $foodId2;
        $foodBack3 = $foodId3;
        $foodBack4 = $foodId4;

        foreach ($recipe as $item) {
            if ($item['genre'] == $genre) {

                $foodId1 = $foodBack1;
                $foodId2 = $foodBack2;
                $foodId3 = $foodBack3;
                $foodId4 = $foodBack4;

                $food1_used = false;$food2_used = false;$food3_used = false;$food4_used = false;
            	if (empty($item['food3'])) {
            		$temp = array ($item['food1'], $item['food2'], 'test');
            	}
            	else {
            	    $temp = array ($item['food1'], $item['food2'], $item['food3']);
            	}
            	if (!empty($foodId1)) {
            	    if (in_array($foodId1, $temp)) {
            	        $food1_used = true;
            	        for ($i = 0; $i < count($temp); $i++) {
            	            if ($foodId1 == $temp[$i]) {
            	                $temp[$i] = 'test';
            	                break;
            	            }
            	        }
            	    }
            	}
            	if (!empty($foodId2)) {
            	    if (in_array($foodId2, $temp)) {
            	        $food2_used = true;
            	        for ($i = 0; $i < count($temp); $i++) {
            	            if ($foodId2 == $temp[$i]) {
            	                $temp[$i] = 'test';
            	                break;
            	            }
            	        }
            	    }
            	}
            	if (!empty($foodId3)) {
            	    if (in_array($foodId3, $temp)) {
            	        $food3_used = true;
            	        for ($i = 0; $i < count($temp); $i++) {
            	            if ($foodId3 == $temp[$i]) {
            	                $temp[$i] = 'test';
            	                break;
            	            }
            	        }
            	    }
            	}
            	if (!empty($foodId4)) {
            	    if (in_array($foodId4, $temp)) {
            	        $food4_used = true;
            	        for ($i = 0; $i < count($temp); $i++) {
            	            if ($foodId4 == $temp[$i]) {
            	                $temp[$i] = 'test';
            	                break;
            	            }
            	        }
            	    }
            	}
            	//make success
            	if ($temp[0] == 'test' && $temp[1] == 'test' && $temp[2] == 'test') {
            	    $foodId1 = $food1_used ? false : $foodId1;
            	    $foodId2 = $food2_used ? false : $foodId2;
            	    $foodId3 = $food3_used ? false : $foodId3;
            	    $foodId4 = $food4_used ? false : $foodId4;

            		//check lucky food
            		if ($foodId1 ==  $item['lucky_food'] || $foodId2 ==  $item['lucky_food'] || $foodId3 ==  $item['lucky_food'] || $foodId4 ==  $item['lucky_food']) {
            			//has lucky food
            			//return array('recipe_id' => $item['recipe_id'], 'lucky_flag' => 1);
            			$developArr[] = array('recipe_id' => $item['recipe_id'], 'lucky_flag' => 1);
            		}
            		else {
            			//return array('recipe_id' => $item['recipe_id'], 'lucky_flag' => 0);
            			$developArr[] = array('recipe_id' => $item['recipe_id'], 'lucky_flag' => 0);
            		}

            		//break;
            	}
            }
        }

        //return false;
        return empty($developArr) ? false : $developArr;
    }

    /**
     * add recipe
     *
     * @param integer $uid
     * @param integer $genre
     * @param integer $recipe_id
     * @param integer $lucky
     * @return boolean
     */
    private function addRecipe($uid, $genre, $recipe_id, $lucky, $foodId1 = '0', $foodId2 = '0', $foodId3 = '0', $foodId4 = '0')
    {
    	$result = false;
    	$this->_wdb->beginTransaction();

    	try {
    		require_once 'Mdal/Kitchen/Recipe.php';
    		$mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();

            //check before recipe
            $hasRecipe = $mdalRecipe->hasRecipe($uid, $recipe_id);
            if ($hasRecipe) {
            	if ($lucky == 1) {
            		$mdalRecipe->update($uid, $recipe_id, array('lucky_flag'=>1));
            	}
            }
            else {
            	$recipe = array('uid' => $uid,
            					'recipe_id' => $recipe_id,
            					'genre' => $genre,
            					'lucky_flag' => $lucky,
            					'cooking_times' => 0,
            					'create_time' => time());
            	$mdalRecipe->insert($recipe);

            	//add res_user_resaurant recipe_count
            	require_once 'Mdal/Kitchen/Restaurant.php';
            	$mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
            	$mdalRest->addRecipeCount($uid, $genre);

            	//add res_user_profile total_recipe_count
            	require_once 'Mdal/Kitchen/User.php';
            	$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
            	$mdalUser->updateUserBy($uid, 'total_recipe_count', 1);
            }

        	//reduce user foods
        	require_once 'Mdal/Kitchen/Food.php';
        	$mdalFood = Mdal_Kitchen_Food::getDefaultInstance();
        	if ($foodId1 != '0') {
        		//admin page shopping info
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$mbllKitchenCache = new Mbll_Kitchen_Cache();
        		$food1 = $mbllKitchenCache->getFood($foodId1);
        		$food1Cnt = $mdalFood->getFoodCount($uid, $foodId1);
        		$adminDalkitchen->insert('food', array( 'uid' => $uid,
	        										   	 'shop_id' => $foodId1,
	        										   	 'shop_name' => $food1['food_name'],
	        										   	 'start_count' => $food1Cnt,
	        										   	 'end_count' => $food1Cnt - 1,
	        										   	 'create_type' => '配合',
	        										   	 'description' => '配合',
	        										   	 'buy_time' => time()));

        		$mdalFood->addFoodNum($uid, $foodId1, -1);
        	}
        	if ($foodId2 != '0') {
        		//admin page shopping info
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$mbllKitchenCache = new Mbll_Kitchen_Cache();
        		$food2 = $mbllKitchenCache->getFood($foodId2);
        		$food2Cnt = $mdalFood->getFoodCount($uid, $foodId2);
        		$adminDalkitchen->insert('food', array('uid' => $uid,
	        										   	 'shop_id' => $foodId2,
	        										   	 'shop_name' => $food2['food_name'],
	        										   	 'start_count' => $food2Cnt,
	        										   	 'end_count' => $food2Cnt - 1,
	        										   	 'create_type' => '配合',
	        										   	 'description' => '配合',
	        										   	 'buy_time' => time()));

        		$mdalFood->addFoodNum($uid, $foodId2, -1);
        	}
        	if ($foodId3 != '0') {
        		//admin page shopping info
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$mbllKitchenCache = new Mbll_Kitchen_Cache();
        		$food3 = $mbllKitchenCache->getFood($foodId3);
        		$food3Cnt = $mdalFood->getFoodCount($uid, $foodId3);
        		$adminDalkitchen->insert('food', array('uid' => $uid,
	        										   	 'shop_id' => $foodId3,
	        										   	 'shop_name' => $food3['food_name'],
	        										   	 'start_count' => $food3Cnt,
	        										   	 'end_count' => $food3Cnt - 1,
	        										   	 'create_type' => '配合',
	        										   	 'description' => '配合',
	        										   	 'buy_time' => time()));

        		$mdalFood->addFoodNum($uid, $foodId3, -1);
        	}
        	if ($foodId4 != '0') {
        		//admin page shopping info
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$mbllKitchenCache = new Mbll_Kitchen_Cache();
        		$food4 = $mbllKitchenCache->getFood($foodId4);
        		$food4Cnt = $mdalFood->getFoodCount($uid, $foodId4);
        		$adminDalkitchen->insert('food', array('uid' => $uid,
	        										   	 'shop_id' => $foodId4,
	        										   	 'shop_name' => $food4['food_name'],
	        										   	 'start_count' => $food4Cnt,
	        										   	 'end_count' => $food4Cnt - 1,
	        										   	 'create_type' => '配合',
	        										   	 'description' => '配合',
	        										   	 'buy_time' => time()));

        		$mdalFood->addFoodNum($uid, $foodId4, -1);
        	}

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

    /**
     * research failure
     *
     * @param integer $uid
     * @param integer $kitchenID
     * @param string $foodId1
     * @param string $foodId2
     * @param string $foodId3
     * @param string $foodId4
     * @return integer
     */
    public function researchFailure($uid, $kitchenID, $foodId1, $foodId2, $foodId3, $foodId4)
    {
        $result = -1;
    	$this->_wdb->beginTransaction();

    	try {
    		require_once 'Mdal/Kitchen/Kitchen.php';
        	$mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        	$mdalKitchen->updateFailureCount($uid, $kitchenID, 1);

        	require_once 'Mdal/Kitchen/Food.php';
        	$mdalFood = Mdal_Kitchen_Food::getDefaultInstance();

        	if ($foodId1 != '0') {
        		//admin page shopping info
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$mbllKitchenCache = new Mbll_Kitchen_Cache();
        		$food1 = $mbllKitchenCache->getFood($foodId1);
        		$food1Cnt = $mdalFood->getFoodCount($uid, $foodId1);
        		$adminDalkitchen->insert('food', array('uid' => $uid,
	        										   	 'shop_id' => $foodId1,
	        										   	 'shop_name' => $food1['food_name'],
	        										   	 'start_count' => $food1Cnt,
	        										   	 'end_count' => $food1Cnt - 1,
	        										   	 'create_type' => '配合失敗',
	        										   	 'description' => '配合失敗',
	        										   	 'buy_time' => time()));

        		$mdalFood->addFoodNum($uid, $foodId1, -1);
        	}
        	if ($foodId2 != '0') {
        		//admin page shopping info
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$mbllKitchenCache = new Mbll_Kitchen_Cache();
        		$food2 = $mbllKitchenCache->getFood($foodId2);
        		$food2Cnt = $mdalFood->getFoodCount($uid, $foodId2);
        		$adminDalkitchen->insert('food', array('uid' => $uid,
	        										   	 'shop_id' => $foodId2,
	        										   	 'shop_name' => $food2['food_name'],
	        										   	 'start_count' => $food2Cnt,
	        										   	 'end_count' => $food2Cnt - 1,
	        										   	 'create_type' => '配合失敗',
	        										   	 'description' => '配合失敗',
	        										   	 'buy_time' => time()));

        		$mdalFood->addFoodNum($uid, $foodId2, -1);
        	}
        	if ($foodId3 != '0') {
        		//admin page shopping info
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$mbllKitchenCache = new Mbll_Kitchen_Cache();
        		$food3 = $mbllKitchenCache->getFood($foodId3);
        		$food3Cnt = $mdalFood->getFoodCount($uid, $foodId3);
        		$adminDalkitchen->insert('food', array('uid' => $uid,
	        										   	 'shop_id' => $foodId3,
	        										   	 'shop_name' => $food3['food_name'],
	        										   	 'start_count' => $food3Cnt,
	        										   	 'end_count' => $food3Cnt - 1,
	        										   	 'create_type' => '配合失敗',
	        										   	 'description' => '配合失敗',
	        										   	 'buy_time' => time()));
        		$mdalFood->addFoodNum($uid, $foodId3, -1);
        	}
        	if ($foodId4 != '0') {
        		//admin page shopping info
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$mbllKitchenCache = new Mbll_Kitchen_Cache();
        		$food4 = $mbllKitchenCache->getFood($foodId4);
        		$food4Cnt = $mdalFood->getFoodCount($uid, $foodId4);
        		$adminDalkitchen->insert('food', array('uid' => $uid,
	        										   	 'shop_id' => $foodId4,
	        										   	 'shop_name' => $food4['food_name'],
	        										   	 'start_count' => $food4Cnt,
	        										   	 'end_count' => $food4Cnt - 1,
	        										   	 'create_type' => '配合失敗',
	        										   	 'description' => '配合失敗',
	        										   	 'buy_time' => time()));
        		$mdalFood->addFoodNum($uid, $foodId4, -1);
        	}

            $this->_wdb->commit();
            $result = 2;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
        }

        return $result;
    }
}