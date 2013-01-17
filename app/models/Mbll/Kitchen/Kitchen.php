<?php

/**
 * Mobile kitchen kitchen operation logic
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhangx  2010-1-9
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Kitchen extends Mbll_Abstract
{

	/**
     * auto set fly logic
     *
     * @param integer $uid
     * @param integer $kitchenId
     * @return boolean
     */
    public function autoSetFly($uid, $kitchenId)
    {
        $result = false;

        try {

        	if (empty($kitchenId)) {
        		return false;
        	}

			//check allow to set
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $rowKitchen = $mdalKitchen->getUserKitchen($uid, $kitchenId);
            //has already a fly
            if (empty($rowKitchen) || empty($rowKitchen['cooking_recipe_id']) || 1 == $rowKitchen['has_fly']) {
            	return false;
            }
        	//is cook ended
        	$nowTime = time();
            $needSeconds = ((int)$rowKitchen['cooking_part1'] + (int)$rowKitchen['cooking_part2'] + (int)$rowKitchen['cooking_part3'])*60;
            if ($rowKitchen['cooking_start_time'] + $needSeconds <= $nowTime) {
				return false;
            }

            //is not process 2 or not process 3
            if (($rowKitchen['cooking_start_time'] + ((int)$rowKitchen['cooking_part1']*60)) > $nowTime) {
            	return false;
            }

            //is auto setted
            $rowFlySet = $mdalKitchen->getKitchenFlySet($uid, $kitchenId);
            $inProc = 2;
            if (empty($rowFlySet)) {
            	$mdalKitchen->insertKitchenFlySet(array('uid' => $uid, 'kitchen_id' => $kitchenId, 'set_fly_uid' => 0));
            }
            //proc 2
            if (($rowKitchen['cooking_start_time'] + (((int)$rowKitchen['cooking_part1'] + (int)$rowKitchen['cooking_part2'])*60)) > $nowTime) {
            	if ((int)$rowFlySet['auto_set_proc'] >= 2) {
					return false;
            	}
            }
            //proc 3
            else {
            	$inProc = 3;
            	if ((int)$rowFlySet['auto_set_proc'] >= 3) {
					return false;
            	}
            }


            $this->_wdb->beginTransaction();

            $rowKitchen = $mdalKitchen->getUserKitchenLock($uid, $kitchenId);
            //update kitchen data
			$mdalKitchen->update(array('has_fly' => 1), $uid, $kitchenId);
			//update kitchen fly data
			$mdalKitchen->updateKitchenFlySet(array('auto_set_proc' => $inProc), $uid, $kitchenId);

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
     * set fly logic
     *
     * @param integer $uid
     * @param integer $tarUid
     * @param integer $kitchenId
     * @return boolean
     */
    public function setFly($uid, $tarUid, $kitchenId)
    {
        $result = false;

        try {

        	if ($uid == $tarUid || empty($kitchenId)) {
        		return false;
        	}
			//check allow to set
			require_once 'Mdal/Kitchen/User.php';
			$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
			$rowUser = $mdalUser->getUser($uid);
			//exp were not add
			//if ($rowUser['set_fly_count'] >= 50) {
			//	return false;
			//}
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $rowKitchen = $mdalKitchen->getUserKitchen($tarUid, $kitchenId);
            //has already a fly
            if (empty($rowKitchen) || empty($rowKitchen['cooking_recipe_id']) || 1 == $rowKitchen['has_fly']) {
            	return false;
            }
        	//is cook ended
        	$nowTime = time();
            $needSeconds = ((int)$rowKitchen['cooking_part1'] + (int)$rowKitchen['cooking_part2'] + (int)$rowKitchen['cooking_part3'])*60;
            if ($rowKitchen['cooking_start_time'] + $needSeconds <= $nowTime) {
				return false;
            }

            $this->_wdb->beginTransaction();

            $rowKitchen = $mdalKitchen->getUserKitchenLock($tarUid, $kitchenId);
            //update kitchen data
			$mdalKitchen->update(array('has_fly' => 1), $tarUid, $kitchenId);

			$rowFlySet = $mdalKitchen->getKitchenFlySet($tarUid, $kitchenId);
			if (empty($rowFlySet)) {
				$mdalKitchen->insertKitchenFlySet(array('uid' => $tarUid, 'kitchen_id' => $kitchenId, 'set_fly_uid' => $uid));
			}
			else {
				$mdalKitchen->updateKitchenFlySet(array('set_fly_uid' => $uid), $tarUid, $kitchenId);
			}
			//update my set fly count
			$mdalUser->updateUser(array('last_set_fly_time' => time(), 'set_fly_count' => $rowUser['set_fly_count'] + 1), $uid);

			//update visit
			require_once 'Mdal/Kitchen/Visit.php';
			$mdalVist = Mdal_Kitchen_Visit::getDefaultInstance();
			$rowVisit = $mdalVist->getVisitFoot($tarUid, $uid, date('Y-m-d'));
			if (empty($rowVisit)) {
				$mdalVist->insertVisitFoot(array('uid'=>$tarUid,'visit_uid'=>$uid,'action_date'=>date('Y-m-d'),'action'=>2,'visit_count'=>1,'update_time'=>time()));
			}
			else {
				$mdalVist->updateVisitFoot($tarUid, $uid, date('Y-m-d'), time(), 2);
			}
			
            //update target`s history
            //update daily param
            require_once 'Mdal/Kitchen/Daily.php';
            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
            $dailyInfo = $mdalDaily->getDaily($tarUid);
            if ($dailyInfo) {
            	$mdalDaily->updateDaily(array('history' => $dailyInfo['history'] + 1), $tarUid);
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
     * remove fly logic
     *
     * @param integer $uid
     * @param integer $tarUid
     * @param integer $kitchenId
     * @return integer [0-failed / 1-level not up / 2-level up  / 3 - gain zero exp]
     */
    public function removeFly($uid, $tarUid, $kitchenId)
    {
        $result = false;

        try {

			//check allow to remove
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $rowKitchen = $mdalKitchen->getUserKitchen($tarUid, $kitchenId);
            //has no fly
            if (empty($rowKitchen) || empty($rowKitchen['cooking_recipe_id']) || 0 == $rowKitchen['has_fly']) {
            	return false;
            }
        	//is cook ended
        	$nowTime = time();
            $needSeconds = ((int)$rowKitchen['cooking_part1'] + (int)$rowKitchen['cooking_part2'] + (int)$rowKitchen['cooking_part3'])*60;
            if ($rowKitchen['cooking_start_time'] + $needSeconds <= $nowTime) {
				return false;
            }

            require_once 'Mdal/Kitchen/User.php';
            $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
            $rowUser = $mdalUser->getUser($uid);

            $this->_wdb->beginTransaction();

            $isSelfRemove = 0;
            if ($uid == $tarUid || empty($tarUid)) {
            	 $isSelfRemove = 1;
            }

            //update kitchen data
            $rowKitchen = $mdalKitchen->getUserKitchenLock($tarUid, $kitchenId);
			$mdalKitchen->update(array('has_fly'=>0, 'kill_fly_count'=>((int)$rowKitchen['kill_fly_count'] + 1)), $tarUid, $kitchenId);

            //exp were not add
            //if ($rowUser['set_fly_count'] > 50) {
            //    $result = 3;
            //}
            //else {
	        //exp add
            $gainExp = 5;
            $result = $this->_gainExpAndLevelUp($uid, $gainExp);
            //}

			if (!$isSelfRemove) {
				//update visit
				require_once 'Mdal/Kitchen/Visit.php';
				$mdalVist = Mdal_Kitchen_Visit::getDefaultInstance();
				$rowVisit = $mdalVist->getVisitFoot($tarUid, $uid, date('Y-m-d'));
				if (empty($rowVisit)) {
					$mdalVist->insertVisitFoot(array('uid'=>$tarUid,'visit_uid'=>$uid,'action_date'=>date('Y-m-d'),'action'=>1,'visit_count'=>1,'update_time'=>time()));
				}
				else {
					$mdalVist->updateVisitFoot($tarUid, $uid, date('Y-m-d'), time(), 1);
				}
				
			    //update target`s history
	            //update daily param
	            require_once 'Mdal/Kitchen/Daily.php';
	            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
	            $dailyInfo = $mdalDaily->getDaily($tarUid);
	            if ($dailyInfo) {
	            	$mdalDaily->updateDaily(array('history' => $dailyInfo['history'] + 1), $tarUid);
	            }
			}

            $this->_wdb->commit();
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

	/**
     * add spice
     *
     * @param integer $uid
     * @param integer $tarUid
     * @param integer $kitchenId
     * @return integer [1-level not up / 2-level up 0-failed]
     */
    public function addSpice($uid, $tarUid, $kitchenId)
    {
        $result = false;

        try {

			//check allow to add
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $rowKitchen = $mdalKitchen->getUserKitchen($tarUid, $kitchenId);
            //is cook ended
        	$nowTime = time();
            $needSeconds = ((int)$rowKitchen['cooking_part1'] + (int)$rowKitchen['cooking_part2'] + (int)$rowKitchen['cooking_part3'])*60;
            if (empty($rowKitchen) || empty($rowKitchen['cooking_recipe_id'])
            		|| ($rowKitchen['cooking_start_time'] + $needSeconds <= $nowTime)) {
				return false;
            }

        	$rowKitchenSpice = $mdalKitchen->getKitchenSpice($tarUid, $kitchenId, $uid);
            //has already added
            if (!empty($rowKitchenSpice)) {
            	return false;
            }

            $this->_wdb->beginTransaction();

            //add spice
            $mdalKitchen->insertKitchenSpice(array('uid'=>$tarUid, 'kitchen_id'=>$kitchenId, 'add_spice_uid'=>$uid));
            //update kitchen
            $rowKitchen = $mdalKitchen->getUserKitchenLock($tarUid, $kitchenId);
            $mdalKitchen->update(array('add_spice_count'=>((int)$rowKitchen['add_spice_count'] + 1)), $tarUid, $kitchenId);

            $addValue = 10;
            //$isSelf = 0;
            if ($uid == $tarUid) {
            	//gain exp
				$result = $this->_gainExpAndLevelUp($uid, $addValue);
            }
            else {
            	//gain point
            	require_once 'Mdal/Kitchen/User.php';
				$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
				$rowUser = $mdalUser->getUser($uid);
				$mdalUser->updateUser(array('point' => ($rowUser['point'] + $addValue)), $uid);

				//update visit
				require_once 'Mdal/Kitchen/Visit.php';
				$mdalVist = Mdal_Kitchen_Visit::getDefaultInstance();
				$rowVisit = $mdalVist->getVisitFoot($tarUid, $uid, date('Y-m-d'));
				if (empty($rowVisit)) {
					$mdalVist->insertVisitFoot(array('uid'=>$tarUid,'visit_uid'=>$uid,'action_date'=>date('Y-m-d'),'action'=>3,'visit_count'=>1,'update_time'=>time()));
				}
				else {
					$mdalVist->updateVisitFoot($tarUid, $uid, date('Y-m-d'), time(), 3);
				}
				
                //update target`s history
	            //update daily param
	            require_once 'Mdal/Kitchen/Daily.php';
	            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
	            $dailyInfo = $mdalDaily->getDaily($tarUid);
	            if ($dailyInfo) {
	            	$mdalDaily->updateDaily(array('history' => $dailyInfo['history'] + 1), $tarUid);
	            }
	            
				$result = 1;
            }

            $this->_wdb->commit();
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

	/**
     * try food logic
     *
     * @param integer $uid
     * @param integer $tarUid
     * @param integer $kitchenId
     * @return integer [1-level not up / 2-level up 0-failed]
     */
    public function tryFood($uid, $tarUid, $kitchenId)
    {
        $result = false;

        try {
			//check allow to remove
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $rowKitchen = $mdalKitchen->getUserKitchen($tarUid, $kitchenId);
            //can not try
            if (empty($rowKitchen) || empty($rowKitchen['cooking_recipe_id']) || $uid == $tarUid) {
            	return false;
            }
            $rowKitchenTaste = $mdalKitchen->getKitchenTaste($tarUid, $kitchenId, $uid);
            if (!empty($rowKitchenTaste)) {
            	return false;
            }
            //modified by zhaoxh 20100316
            $nowTime = time();
            $partAllSeconds = $rowKitchen['cooking_start_time'] + ((int)$rowKitchen['cooking_part1'] + (int)$rowKitchen['cooking_part2'] + (int)$rowKitchen['cooking_part3'])*60;
			if ($nowTime < $partAllSeconds) {
        		return false;
        	}
        	//modify over
        	
            $this->_wdb->beginTransaction();

            //$rowKitchen = $mdalKitchen->getUserKitchenLock($tarUid, $kitchenId);
            //add taste
            $mdalKitchen->insertKitchenTaste(array('uid'=>$tarUid, 'kitchen_id'=>$kitchenId, 'taste_uid'=>$uid));

			//exp add
			$gainExp = 10;
			$result = $this->_gainExpAndLevelUp($uid, $gainExp);

			//is learned
			require_once 'Mbll/Kitchen/Cache.php';
			$recipe_id = $rowKitchen['cooking_recipe_id'];
			$nbRecipe = Mbll_Kitchen_Cache::getRecipe($recipe_id);
			$rnd = rand(1, 100);
			
			require_once 'Mdal/Kitchen/User.php';
			$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
			$rowUser = $mdalUser->getUser($uid);
			$nbRecipe['taste_success_rate'] += $rowUser['try_rate_add'];
			
			//zhaoxh tryfood modify (not learned genre , % = 0)
            require_once 'Mdal/Kitchen/Restaurant.php';
            $mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
			$isLearnGenre = $mdalRest->hasRest($uid, $nbRecipe['genre']);
			if (!$isLearnGenre) {
				 $nbRecipe['taste_success_rate'] = 0;
			}
			
			$isLearned = ($rnd <= $nbRecipe['taste_success_rate']);
			require_once 'Mdal/Kitchen/Recipe.php';
	    	$mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
	    	$hasRecipe = $mdalRecipe->hasRecipe($uid, $recipe_id);
	    	
			if ($isLearned && !$hasRecipe) {
            	$recipe = array('uid' => $uid,
            					'recipe_id' => $recipe_id,
            					'genre' => $rowKitchen['genre'],
            					'lucky_flag' => 0,
            					'cooking_times' => 0,
            					'create_time' => time());
            	$mdalRecipe->insert($recipe);

            	//add res_user_resaurant recipe_count
            	$mdalRest->addRecipeCount($uid, $rowKitchen['genre']);
				
				$mdalUser->updateUser(array('total_recipe_count' => $rowUser['total_recipe_count'] + 1, 'try_rate_add' => 0), $uid);
			}
			else {
				if ($rowUser['try_rate_add'] != 0) {
					$mdalUser->updateUser(array('try_rate_add' => 0), $uid);
				}
			}
			$result .= '|' . (($isLearned && !$hasRecipe) ? $recipe_id : '0');

			//update visit
			require_once 'Mdal/Kitchen/Visit.php';
			$mdalVist = Mdal_Kitchen_Visit::getDefaultInstance();
			$rowVisit = $mdalVist->getVisitFoot($tarUid, $uid, date('Y-m-d'));
			if (empty($rowVisit)) {
				$mdalVist->insertVisitFoot(array('uid'=>$tarUid,'visit_uid'=>$uid,'action_date'=>date('Y-m-d'),'action'=>4,'visit_count'=>1,'update_time'=>time()));
			}
			else {
				$mdalVist->updateVisitFoot($tarUid, $uid, date('Y-m-d'), time(), 4);
			}

            //update target`s history
            //update daily param
            require_once 'Mdal/Kitchen/Daily.php';
            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
            $dailyInfo = $mdalDaily->getDaily($tarUid);
            if ($dailyInfo) {
            	$mdalDaily->updateDaily(array('history' => $dailyInfo['history'] + 1), $tarUid);
            }
            
            $this->_wdb->commit();
            
            
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return false;
        }

        return $result;
    }

	/**
     * cook begin
     *
     * @param integer $uid
     * @param integer $kitchenId
     * @param integer $recipeId
     * @return boolean
     */
    public function cookBegin($uid, $kitchenId, $recipeId)
    {
        $result = false;

        try {

			//check allow to add
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $rowKitchen = $mdalKitchen->getUserKitchen($uid, $kitchenId);
            //get nb recipe info
            require_once 'Mbll/Kitchen/Cache.php';
            $nbRecipe = Mbll_Kitchen_Cache::getRecipe($recipeId);

            //is allow to begin
            if (empty($rowKitchen) || empty($nbRecipe) || !empty($rowKitchen['cooking_recipe_id'])) {
            	return false;
            }

            //is learned recipe
            require_once 'Mdal/Kitchen/Recipe.php';
			$mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
			if (!$mdalRecipe->hasRecipe($uid, $recipeId)) {
				return false;
			}
            //invalid restaurant info
            require_once 'Mdal/Kitchen/Restaurant.php';
			$mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
			$rowRes = $mdalRes->getActiveRestaurant($uid);
			if (empty($rowRes) || $rowRes['genre'] != $nbRecipe['genre'] || (int)$rowRes['estate'] < $kitchenId) {
				return false;
			}

			$this->_wdb->beginTransaction();

			//clear kitchen data
            $mdalKitchen->deleteKitchenFlySet($uid, $kitchenId);
            $mdalKitchen->deleteKitchenSpice($uid, $kitchenId);
            $mdalKitchen->deleteKitchenTaste($uid, $kitchenId);
			//update cook kitchen info
            $aryKitchen = array();
            $aryKitchen['genre'] = $nbRecipe['genre'];
            $aryKitchen['cooking_recipe_id'] = $recipeId;
            $aryKitchen['cooking_start_time'] = time();
            $aryKitchen['cooking_part1'] = $nbRecipe['part1_time'];
            $aryKitchen['cooking_part2'] = $nbRecipe['part2_time'];
            $aryKitchen['cooking_part3'] = $nbRecipe['part3_time'];
            $aryKitchen['rate'] = 100;
            $aryKitchen['kill_fly_count'] = 0;
            $aryKitchen['add_spice_count'] = 0;
            $aryKitchen['has_fly'] = 0;
            //$aryKitchen['failure_count'] = 0;
			$mdalKitchen->update($aryKitchen, $uid, $kitchenId);
			
			require_once 'Mdal/Kitchen/User.php';
			$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
			$mdalUser->updateUser(array('nakano_order' => $aryKitchen['cooking_start_time']), $uid);
			
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            info_log($e->getMessage(), 'kitchen-cookbegin');
            return $result;
        }

        return $result;
    }

	/**
     * cook finish
     *
     * @param integer $uid
     * @param integer $kitchenId
     * @return integer [1-level not up / 2-level up 0-failed]
     */
    public function cookFinish($uid, $kitchenId)
    {
        $result = false;

        try {

			//check allow to add
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $rowKitchen = $mdalKitchen->getUserKitchen($uid, $kitchenId);

            //is allow to finish
            if (empty($rowKitchen) || empty($rowKitchen['cooking_recipe_id'])) {
            	return false;
            }
            //not finish
            $nowTime = time();
            $needSeconds = ((int)$rowKitchen['cooking_part1'] + (int)$rowKitchen['cooking_part2'] + (int)$rowKitchen['cooking_part3'])*60;
            if ($rowKitchen['cooking_start_time'] + $needSeconds > $nowTime) {
				return false;
            }

            //get nb recipe info
            require_once 'Mbll/Kitchen/Cache.php';
            $nbRecipe = Mbll_Kitchen_Cache::getRecipe($rowKitchen['cooking_recipe_id']);
            require_once 'Mdal/Kitchen/Restaurant.php';
			$mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
			$rowRes = $mdalRes->getActiveRestaurant($uid);
			require_once 'Mdal/Kitchen/Recipe.php';
			$mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
			$rowUsrRecipe = $mdalRecipe->getUserRecipeById($uid, $rowKitchen['cooking_recipe_id']);
			if (empty($nbRecipe) || empty($rowRes) || empty($rowUsrRecipe)) {
				return false;
			}
			
            $this->_wdb->beginTransaction();
            //finish cook
            $rowKitchen = $mdalKitchen->getUserKitchenLock($uid, $kitchenId);
            //gain exp
            $baseDiv = (((int)$rowRes['level']-(int)$nbRecipe['allow_level']-4) <= 0) ? 1 : ((int)$rowRes['level']-(int)$nbRecipe['allow_level']-4);
			$gainExp = (int)($nbRecipe['gain_exp']/$baseDiv);
			$gainExp = (int)((($rowKitchen['exp_rate'] + $rowKitchen['rate'] - 100) * $gainExp) / 100);
			//rate calculate
			//$gainExp = (int)($rowKitchen['rate'] * $gainExp / 100);
			$result = $this->_gainExpAndLevelUp($uid, $gainExp);

			//gain point
			$baseLuckN = ($rowUsrRecipe['lucky_flag'] ? 1.5 : 1)*$nbRecipe['point'];
			$percentA = (((int)$rowKitchen['kill_fly_count']+(int)$rowKitchen['add_spice_count'])>10) ? 10 : ((int)$rowKitchen['kill_fly_count']+(int)$rowKitchen['add_spice_count']);
			$percentA = $percentA/20;
            //if has fly
        	if (1 == (int)$rowKitchen['has_fly']) {
        	    $percentA = (($percentA - 0.2) < 0) ? 0 : ($percentA - 0.2);
        	}
			$gainPoint = ($baseLuckN + ($nbRecipe['point']*$percentA))>(2*$nbRecipe['point']) ? (2*$nbRecipe['point']) : (int)($baseLuckN + ($nbRecipe['point']*$percentA));

			//taste count calc
			$lstTaste = $mdalKitchen->getKitchenTasteAll($uid, $kitchenId);

			//rate calculate
			$mCount = (int)$rowKitchen['kill_fly_count']+(int)$rowKitchen['add_spice_count'];
			$mCount = $mCount > 10 ? 10 : $mCount;
			$qtPercent = round(($mCount*5 + 50) * ((int)$rowKitchen['rate']/100), 0);
            $remainCount = ((int)$rowKitchen['complete_quantity'] - count($lstTaste)) < 10 ? 10 : ((int)$rowKitchen['complete_quantity'] - count($lstTaste));
            $remainPercent = round(($remainCount/(int)$rowKitchen['complete_quantity'])*100, 0);
            $gainPoint = (int)($gainPoint*($remainPercent/100));
			$gainPoint = (int)($rowKitchen['rate'] * $gainPoint / 100) > (2*$nbRecipe['point']) ? (2*$nbRecipe['point']) : (int)($rowKitchen['rate'] * $gainPoint / 100);

			//update restaurant
			$mdalRecipe->update($uid, $rowKitchen['cooking_recipe_id'], array('cooking_times' => (int)$rowUsrRecipe['cooking_times'] + 1));
			//update user
			require_once 'Mdal/Kitchen/User.php';
			$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
			$rowUser = $mdalUser->getUser($uid);
			$mdalUser->updateUser(array('point' => ($rowUser['point'] + $gainPoint)), $uid);

			//clear kitchen data
            $mdalKitchen->deleteKitchenFlySet($uid, $kitchenId);
            $mdalKitchen->deleteKitchenSpice($uid, $kitchenId);
            $mdalKitchen->deleteKitchenTaste($uid, $kitchenId);
            $mdalKitchen->deleteKitchenOrder($uid, $kitchenId);
            $aryKitchen = array();
            $aryKitchen['genre'] = null;
            $aryKitchen['cooking_recipe_id'] = null;
            $aryKitchen['cooking_start_time'] = null;
            $aryKitchen['cooking_part1'] = null;
            $aryKitchen['cooking_part2'] = null;
            $aryKitchen['cooking_part3'] = null;
            $aryKitchen['rate'] = 100;
            $aryKitchen['exp_rate'] = 100;
            $aryKitchen['kill_fly_count'] = null;
            $aryKitchen['add_spice_count'] = null;
            $aryKitchen['has_fly'] = null;
            $aryKitchen['failure_count'] = 0;
            $aryKitchen['complete_quantity'] = 20;
            $mdalKitchen->update($aryKitchen, $uid, $kitchenId);

            $this->_wdb->commit();
            $result .= '|' . $gainPoint . '|' . $gainExp . '|' . $rowKitchen['cooking_recipe_id'];
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log($e->getMessage(), 'kitchen-cookfinish');
            return $result;
        }

        return $result;
    }

    public function cheertwo($uid, $profileUid, $nowDate)
    {
        $result = 0;

        try {
        	require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
	        $rowCheerTwo = $mdalKitchen->getCheerTwo($uid, $profileUid);
	        
        	$this->_wdb->beginTransaction();
        	
        	if (empty($rowCheerTwo['act_date'])) {
				$mdalKitchen->insertKitchenCheerTwo(array('act_uid'=>$uid, 'tar_uid'=>$profileUid, 'act_date'=>$nowDate));
			}
			else if ($nowDate != $rowCheerTwo['act_date']) {
				$mdalKitchen->updateKitchenCheerTwo($uid, $profileUid, $nowDate);
			}
			else if ($nowDate == $rowCheerTwo['act_date']){
				return $result;
			}
        	
        	//get 10 point
        	require_once 'Mdal/Kitchen/User.php';
			$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
			$mdalUser->updateUserBy($uid, 'point', 10);
			
			//update visit
			require_once 'Mdal/Kitchen/Visit.php';
			$mdalVist = Mdal_Kitchen_Visit::getDefaultInstance();
			$rowVisit = $mdalVist->getVisitFoot($profileUid, $uid, date('Y-m-d'));
			if (empty($rowVisit)) {
				$mdalVist->insertVisitFoot(array('uid'=>$profileUid,'visit_uid'=>$uid,'action_date'=>date('Y-m-d'),'action'=>5,'visit_count'=>1,'update_time'=>time()));
			}
			else {
				$mdalVist->updateVisitFoot($profileUid, $uid, date('Y-m-d'), time(), 5);
			}
			
			//update target`s history
            //update daily param
            require_once 'Mdal/Kitchen/Daily.php';
            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
            $dailyInfo = $mdalDaily->getDaily($profileUid);
            if ($dailyInfo) {
            	$mdalDaily->updateDaily(array('history' => $dailyInfo['history'] + 1), $profileUid);
            }
			
            $this->_wdb->commit();
            $result = 1;
        }
        
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log($e->getMessage(), 'kitchen-cookcancel');
            return $result;
        }

        return $result;
    }
    
    /**
     * cook cancel bll
     *
     * @param string $uid
     * @param string $kitchenId
     * @return boolean
     */
    /*
    public function cookCancel($uid, $kitchenId)
    {
        $result = false;

        try {
        	require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            
        	$this->_wdb->beginTransaction();
        	//clear kitchen data
            $mdalKitchen->deleteKitchenFlySet($uid, $kitchenId);
            $mdalKitchen->deleteKitchenSpice($uid, $kitchenId);
            $mdalKitchen->deleteKitchenTaste($uid, $kitchenId);
            $aryKitchen = array();
            $aryKitchen['genre'] = null;
            $aryKitchen['cooking_recipe_id'] = null;
            $aryKitchen['cooking_start_time'] = null;
            $aryKitchen['cooking_part1'] = null;
            $aryKitchen['cooking_part2'] = null;
            $aryKitchen['cooking_part3'] = null;
            $aryKitchen['rate'] = 100;
            $aryKitchen['kill_fly_count'] = null;
            $aryKitchen['add_spice_count'] = null;
            $aryKitchen['has_fly'] = null;
            //$aryKitchen['failure_count'] = 0;
            $aryKitchen['complete_quantity'] = 20;
            $mdalKitchen->update($aryKitchen, $uid, $kitchenId);

            $this->_wdb->commit();
            $result = true;
        }
        
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log($e->getMessage(), 'kitchen-cookcancel');
            return $result;
        }

        return $result;
    }    
	*/
    
    /**
     * order action
     *
     * @param string $actUid
     * @param string $tarUid
     * @param int $kitchenId
     * @return 1 or 0
     */
    public function order($actUid, $tarUid, $kitchenId)
    {
        $result = 0;

        try {
        	require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $kitchenRow = $mdalKitchen->getUserKitchen($tarUid, $kitchenId);
            $rowOrder = $mdalKitchen->getKitchenOrder($tarUid, $kitchenId, $actUid);
            
        	$this->_wdb->beginTransaction();
        	if ($rowOrder || $kitchenRow['cooking_recipe_id'] || $kitchenRow['cooking_start_time']){
        		return $result;
        	}
        	
        	//insert order
        	$mdalKitchen->insertKitchenOrder(array('tar_uid'=>$tarUid, 'kitchen_id'=>$kitchenId, 'act_uid'=>$actUid));
        	
        	//get 10 point
        	require_once 'Mdal/Kitchen/User.php';
			$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
			$mdalUser->updateUserBy($actUid, 'point', 10);
			
			/*
			//update visit
			require_once 'Mdal/Kitchen/Visit.php';
			$mdalVist = Mdal_Kitchen_Visit::getDefaultInstance();
			$rowVisit = $mdalVist->getVisitFoot($tarUid, $actUid, date('Y-m-d'));
			if (empty($rowVisit)) {
				$mdalVist->insertVisitFoot(array('uid'=>$tarUid,'visit_uid'=>$actUid,'action_date'=>date('Y-m-d'),'action'=>5,'visit_count'=>1,'update_time'=>time()));
			}
			else {
				$mdalVist->updateVisitFoot($tarUid, $actUid, date('Y-m-d'), time(), 5);
			}
			*/
			
            $this->_wdb->commit();
            $result = 1;
        }
        
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log($e->getMessage(), 'kitchen-cookcancel');
            return $result;
        }

        return $result;
    }  
    
    
    /**
     * gain exp and level up ****** must in transaction ******
     *
     * @param integer $uid
     * @param integer $gainExp
     * @return integer [1-level not up / 2-level up ]
     */
    private function _gainExpAndLevelUp($uid, $gainExp)
    {
    	$isLevelUp = 1;
    	require_once 'Mdal/Kitchen/NbLevel.php';
    	require_once 'Mdal/Kitchen/Restaurant.php';
		$mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
		$rowRes = $mdalRes->getActiveRestaurant($uid);
		$newExp = (int)$rowRes['exp'] + $gainExp;
		$aryRes = array();
		$aryRes['exp'] = $newExp;
		$mdalLevel = Mdal_Kitchen_NbLevel::getDefaultInstance();
		$rowNextLevel = $mdalLevel->getNbLevelExp($rowRes['level']);
		require_once 'Mdal/Kitchen/User.php';
		$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
		$rowUser = $mdalUser->getUser($uid);
		//if is level up
		if ($newExp >= $rowNextLevel['exp']) {
			$isLevelUp = 2;
			$aryRes['level'] = $rowNextLevel['level'];
			$mdalUser->updateUser(array('total_level' => ($rowUser['total_level'] + 1)), $uid);
		}
		$mdalRes->updateRestaurant($aryRes, $rowRes['uid'], $rowRes['genre']);
		$mdalUser->updateUser(array('total_exp' => ($rowUser['total_exp'] + $gainExp)), $uid);

		if (2 == $isLevelUp) {
			//send level up gift
			$rowUpGift = $mdalLevel->getLevelUpGift($rowRes['genre'], $rowNextLevel['level']);
			if (!empty($rowUpGift)) {
				require_once 'Mdal/Kitchen/Gift.php';
				$mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();
				$aryGift = array();
				$aryGift['uid'] = 0;
				$aryGift['target_uid'] = $uid;
				$aryGift['gift_id'] = $rowUpGift['gift_id'];
				$aryGift['type'] = 2;//level up gift
				$aryGift['create_time'] = time();
				$giftId = $mdalGift->insertSendGift($aryGift);
				
				//update daily param
	            require_once 'Mdal/Kitchen/Daily.php';
	            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
	            
			    $dailyInfo = $mdalDaily->getDaily($uid);
	            if ($dailyInfo && $dailyInfo['gift'] == 0) {
	            	$mdalDaily->updateDaily(array('gift'=>1), $uid);
	            }
			}
		}

		return $isLevelUp;
    }
}