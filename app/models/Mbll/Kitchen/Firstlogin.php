<?php

/**
 * Mobile kitchen first login logic
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  hch  2010-1-6
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Firstlogin extends Mbll_Abstract
{
    /**
     * user first login logic
     *
     * @param integer $uid
     * @param array $chef
     * @return boolean
     */
    public function login($uid, $chef)
    {
        $result = false;
    	
        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
            
        try {
            $this->_wdb->beginTransaction();
            
            //insert into user_profile
            require_once 'Mdal/Kitchen/User.php';
            $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
            $user = array('uid' => $uid, 'point'=>300, 'total_recipe_count'=>1, 'create_time'=>time(), 'last_login_time'=>time());
            $mdalUser->insertUser($user);
            
            //insert into user_gacha
            require_once 'Mdal/Kitchen/Gacha.php';
            $mdalGacha = Mdal_Kitchen_Gacha::getDefaultInstance();
            $mdalGacha->insert(array('uid' => $uid));
            
            require_once 'Bll/User.php';
            $mixiUser = Bll_User::getPerson($uid);
            
            //send a restaurant to user
            $rest = array('uid' => $uid,
                          'genre' => 1,
                          'estate' => 2,
                          'in_use' => 1,
                          'name' => $mixiUser->getDisplayName() . '食堂',
                          'exp' => 0,
                          'level' => 1,
                          'recipe_count' => 1);
            $mdalRest->insertRestaurant($rest);
            
            //send chef
            require_once 'Mdal/Kitchen/Chef.php';
            $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
            $mdalChef->insertChef($chef);
            
            //send three foods
            require_once 'Mdal/Kitchen/Food.php';
            $mdalFood = Mdal_Kitchen_Food::getDefaultInstance();
            $food2 = array('uid'=>$uid, 'food_id'=>'me01', 'food_count'=>3, 'food_category'=>4);
            $mdalFood->insertfood($food2);
            $food3 = array('uid'=>$uid, 'food_id'=>'ve17', 'food_count'=>3, 'food_category'=>5);
            $mdalFood->insertfood($food3);
            
            //send two recipes
            require_once 'Mdal/Kitchen/Recipe.php';
            $mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
            $recipe = array('uid'=>$uid, 'recipe_id'=>'y24', 'genre'=>1, 'lucky_flag'=>0, 'cooking_times'=>2, 'create_time'=>time());
            $mdalRecipe->insert($recipe);
            
            //send two kitchen and tow complete dishes
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $kitchen1 = array('uid' => $uid,
                              'kitchen_id' => 1,
                              'genre' => 1,
                              'cooking_recipe_id' => 'y24',
                              'cooking_start_time' => time() - 7200,
                              'cooking_part1' => 20,
                              'cooking_part2' => 20,
                              'kill_fly_count' => 0,
                              'add_spice_count' => 0,
                              'has_fly' => 0);
            $mdalKitchen->insert($kitchen1);
            
            $kitchen2 = array('uid' => $uid,
                              'kitchen_id' => 2,
                              'genre' => 1,
                              'cooking_recipe_id' => 'y24',
                              'cooking_start_time' => time() - 7200,
                              'cooking_part1' => 20,
                              'cooking_part2' => 20,
                              'kill_fly_count' => 0,
                              'add_spice_count' => 0,
                              'has_fly' => 0);
            $mdalKitchen->insert($kitchen2);
                        
            //send first login gift
            require_once 'Mdal/Kitchen/Gift.php';
			$mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();
			$aryGift = array();
			$aryGift['uid'] = 0;
			$aryGift['target_uid'] = $uid;
			$aryGift['gift_id'] = 2101;
			$aryGift['type'] = 3;//visit gift
			$aryGift['create_time'] = time();
			$aryGift['visit_gift_type'] = 'a';
			$mdalGift->insertSendGift($aryGift);
			
			//send user first login goods '111,112'
			require_once 'Mdal/Kitchen/Goods.php';
			$mdalGoods = Mdal_Kitchen_Goods::getDefaultInstance();
			$mdalGoods->insertGoods(array('uid'=>$uid, 'goods_id'=>111, 'create_time'=>time()));
            $mdalGoods->insertGoods(array('uid'=>$uid, 'goods_id'=>112, 'create_time'=>time()));
            
            //send user first login item
            require_once 'Mdal/Kitchen/Item.php';
            $mdalItem = Mdal_Kitchen_Item::getDefaultInstance();
            $mdalItem->insertItem(array('uid'=>$uid, 'item_id'=>7, 'item_count'=>1, 'kitchen_only'=>1));
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        /*
        //remove profile swf cache
        require_once 'Mbll/Kitchen/Amazon.php';
        $filename = $uid . '_profile.swf';
        if (Mbll_Kitchen_Amazon::hasObject($filename)) {
            Mbll_Kitchen_Amazon::removeObject($filename);
        }
		*/
        
        return $result;
    }
    
    public function editChara($uid, $chef)
    {
    	$result = false;
    	
        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
            
        try {
            $this->_wdb->beginTransaction();
            
            //update user chef
            require_once 'Mdal/Kitchen/Chef.php';
            $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
            $mdalChef->updateChef($chef, $uid);
            
            //update user_profile
            require_once 'Mdal/Kitchen/User.php';
            $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
            $mdalUser->updateUser(array('allow_editchara'=>0), $uid);
                        
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        /*
        //remove profile swf cache
        require_once 'Mbll/Kitchen/Amazon.php';
        $filename = $uid . '_profile.swf';
        if (Mbll_Kitchen_Amazon::hasObject($filename)) {
            Mbll_Kitchen_Amazon::removeObject($filename);
        }
		*/
        
        return $result;
    }
    
    public function loginTwice($uid)
    {
    	$result = false;
    	
    	require_once 'Mdal/Kitchen/User.php';
    	$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$user = $mdalUser->getUser($uid);
    	
    	require_once 'Mdal/Kitchen/Restaurant.php';
    	$mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
    	$rest = $mdalRest->getActiveRestaurant($uid);
    	
    	require_once 'Mdal/Kitchen/Gift.php';
        $mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();
    	$userGift = $mdalGift->getUserGiftByType($uid, 3);
    	
    	try {
            $this->_wdb->beginTransaction();
            
            //update user login time
            $mdalUser->updateUser(array('last_login_time' => time()), $uid);
            
            //today first login
            if ($user['last_login_time'] < strtotime(date('Y-m-d'))) {
            	//clear res_user_profile set_fly_count
            	$mdalUser->updateUser(array('set_fly_count' => 0), $uid);
            	
            	//give gift
            	require_once 'Mdal/Kitchen/NbLevel.php';
            	$mdalLevel = Mdal_Kitchen_NbLevel::getDefaultInstance();
            	$rowVisitGift = $mdalLevel->getVisitGift($rest['genre'], $rest['level']);
            	
				if (!empty($rowVisitGift)) {
					$aryGift = array();
					$aryGift['uid'] = 0;
					$aryGift['target_uid'] = $uid;
					$aryGift['gift_id'] = $rowVisitGift['gift_id'];
					$aryGift['type'] = 3;//visit gift
					$aryGift['create_time'] = time();
					
					if (time() - $user['last_login_time'] < 24 * 3600) {
            			$aryGift['visit_gift_type'] = 'a';
		            }
		            else if (time() - $user['last_login_time'] < 48 * 3600) {
		            	$aryGift['visit_gift_type'] = 'b';
		            }
		            else {
		            	$aryGift['visit_gift_type'] = 'c';
		            }
	            
		            require_once 'Mdal/Kitchen/Daily.php';
		            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
		            if (empty($userGift)) {
					   	$giftId = $mdalGift->insertSendGift($aryGift);
					   	//update daily param
		                $dailyInfo = $mdalDaily->getDaily($uid);
			            if ($dailyInfo && $dailyInfo['gift'] == 0) {
			            	$mdalDaily->updateDaily(array('gift'=>1), $uid);
			            }
		            }
		            else {
		                $mdalGift->updateGift($aryGift, $userGift['id']);
		                //update daily param
		            	$dailyInfo = $mdalDaily->getDaily($uid);
			            if ($dailyInfo && $dailyInfo['gift'] == 0) {
			            	$mdalDaily->updateDaily(array('gift'=>1), $uid);
			            }
		            }
				}
            }
                        
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){            
            $this->_wdb->rollBack();
            
            $error = 'ErrorCode :' . $e->getCode() . PHP_EOL
                   . 'ErrorLine :' . $e->getLine() . PHP_EOL
                   . 'ErrorFile :' . $e->getFile() . PHP_EOL
                   . 'ErrorMess :' . $e->getMessage() . PHP_EOL
                   . 'ErrorTrace :' . $e->getTraceAsString();
            info_log($error, 'FirstLogin_Error');
        }
        
        return $result;
    }
}