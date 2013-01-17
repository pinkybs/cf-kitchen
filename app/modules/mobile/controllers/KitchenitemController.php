<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile kitchenrecipe Controller(modules/mobile/controllers/KitchenrecipeController.php)
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  hch  2010-1-11
 */
class KitchenitemController extends MyLib_Zend_Controller_Action_Mobile
{

    function preDispatch()
    {
    }

    /**
     * index action
     *
     */
    public function indexAction()
    {
        $this->render();
    }

    /**
     * food action
     *
     */
    public function foodAction()
    {
        $pageSize = 5;
        $pageIndex = $this->getParam('CF_page', 1);
        $category = $this->getParam('CF_category', 0);

        require_once 'Mdal/Kitchen/Food.php';
        $mdalFood = Mdal_Kitchen_Food::getDefaultInstance();
        $foodList = $mdalFood->getUserFood($this->_USER_ID, $category, $pageIndex, $pageSize);
        $foodCount = $mdalFood->getUserFoodCount($this->_USER_ID, $category);

        $this->view->start = ($pageIndex - 1) * $pageSize + 1;
        $this->view->end = ($pageIndex - 1) * $pageSize + count($foodList);
        $this->view->food = $foodList;

        //zhaoxh begin
        $this->view->category = $category;
        require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $this->view->level = $dalRest->getMaxLevel($this->_USER_ID);
        //zhaoxh over

        //get pager info
        $this->view->pager = array('count' => $foodCount, 'pageIndex' => $pageIndex, 'requestUrl' => "mobile/kitchenitem/food/CF_category/$category", 'pageSize' => $pageSize, 'maxPager' => ceil($foodCount / $pageSize));

        $this->render();
    }

    /**
     * zakka action
     *
     */
    public function goodAction()
    {
        $pageSize = 10;
        $pageIndex = $this->getParam('CF_page', 1);

        //get user used genre
    	require_once 'Mdal/Kitchen/Restaurant.php';
    	$mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
    	$rest = $mdalRest->getActiveRestaurant($this->_USER_ID);

        require_once 'Mdal/Kitchen/Goods.php';
        $mdalGoods = Mdal_Kitchen_Goods::getDefaultInstance();
        $goodList = $mdalGoods->getUserGoods($this->_USER_ID, $rest['genre'], $pageIndex, $pageSize);
        $goodCount = $mdalGoods->getUserGoodsCount($this->_USER_ID);

        $this->view->start = ($pageIndex - 1) * $pageSize + 1;
        $this->view->end = ($pageIndex - 1) * $pageSize + count($goodList);
        $this->view->goods = $goodList;

        //get pager info
        $this->view->genre = $rest['genre'];
        $this->view->pager = array('count' => $goodCount, 'pageIndex' => $pageIndex, 'requestUrl' => "mobile/kitchenitem/good", 'pageSize' => $pageSize, 'maxPager' => ceil($goodCount / $pageSize));
        $this->view->osUID = $this->_USER_ID;
        $this->view->uid = $this->_USER_ID;
        $this->view->rand = time();
        $this->render();
    }

    public function addgoodAction()
    {
        $step = $this->getParam('CF_step', "confirm");
        $goodId = $this->getParam('CF_goodId');
        $position = $this->getParam('CF_position');

        if (empty($goodId)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($step == "complete") {
            require_once 'Mdal/Kitchen/Goods.php';
            $mdalGoods = Mdal_Kitchen_Goods::getDefaultInstance();

            require_once 'Mdal/Kitchen/Restaurant.php';
	    	$mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
	    	$rest = $mdalRest->getActiveRestaurant($this->_USER_ID);

            //check goods is user and not used
            $hasGoods = $mdalGoods->checkUserGoods($this->_USER_ID, $goodId);
            if ($hasGoods) {
                $this->_redirect($this->_baseUrl . '/mobile/error/error');
            }

            //check genre and goodsId is match
            $matchGenre = $mdalGoods->matchGenreGoods($this->_USER_ID, $goodId);
            if (!$matchGenre) {
                $this->_redirect($this->_baseUrl . '/mobile/error/error');
            }

            //check position
            $isNullPosition = $mdalGoods->isNullPosition($this->_USER_ID, $position, $rest['genre']);
            if ($isNullPosition) {
            	//get goods gid
            	$gid = $mdalGoods->getNoPositionGoodsGid($this->_USER_ID, $goodId);

            	//insert into goods position
                $mdalGoods->insertGoodsPosition(array('uid' => $this->_USER_ID, 'position' => $position, 'genre'=>$rest['genre'], 'gid' => $gid));

                //zhaoxh20100206   activity 13
                require_once 'Mbll/Kitchen/Cache.php';
    			$goods = Mbll_Kitchen_Cache::getGoods($gid);

		        require_once 'Mbll/Kitchen/Activity.php';
		        $activity = Mbll_Kitchen_Activity::getActivity(13, $goods['goods_name'], $goods['goods_picture']);
		        $aryActivity = explode('|', $activity);

		        require_once 'Bll/Restful.php';
		        $restful = Bll_Restful::getInstance($this->_USER_ID, $this->_APP_ID);
		        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

	            //zhaoxh20100421 minifeed 13
		        $miniFeed = Mbll_Kitchen_Activity::getMiniFeed(13, $goods['goods_name'], $goods['goods_picture']);
		        $aryMiniFeed = explode('|', $miniFeed);

		        $fids = Bll_Friend::getFriends($this->_USER_ID);
		        require_once 'Mdal/Kitchen/Activity.php';
		        $mdalActivity = Mdal_Kitchen_Activity::getDefaultInstance();
		        try {
			        foreach ($fids as $value) {
			        	$arrInsert = array('send_uid' => $this->_USER_ID,
			        	                   'rcv_uid' => $value,
			        	                   'feed' => $aryMiniFeed[0],
			        	                   'create_time' => time());
			        	$mdalActivity->insertMiniFeed($arrInsert);
			        }
		        }
		        catch (Exception $e) {
		        }
            }
            else {
            	$this->_redirect($this->_baseUrl . '/mobile/error/error');
            }
        }

        require_once 'Mbll/Kitchen/Cache.php';
        $this->view->good = Mbll_Kitchen_Cache::getGoods($goodId);

        $this->view->position = $position;
        $this->view->goodId = $goodId;
        $this->view->step = $step;
        $this->render();
    }

    public function delgoodAction()
    {
    	$step = $this->getParam('CF_step', "complete");
        $goodId = $this->getParam('CF_goodId');
        $gid = $this->getParam('CF_gid');

        if (empty($goodId) || empty($gid)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        require_once 'Mdal/Kitchen/Goods.php';
        $mdalGoods = Mdal_Kitchen_Goods::getDefaultInstance();

        $mdalGoods->delGoodsPosition($this->_USER_ID, $gid);

        require_once 'Mbll/Kitchen/Cache.php';
        $this->view->good = Mbll_Kitchen_Cache::getGoods($goodId);

        $this->view->gid = $gid;
        $this->view->goodId = $goodId;
        $this->view->step = $step;
        $this->render();
    }

    /**
     * yorozu action
     *
     */
    public function itemAction()
    {
        $pageSize = 5;
        $pageIndex = $this->getParam('CF_page', 1);
        $kitchenId = $this->getParam('CF_kitchen_id', 0);

    	require_once 'Mdal/Kitchen/Item.php';
        $mdalItem = Mdal_Kitchen_Item::getDefaultInstance();
        $itemList = $mdalItem->getUserItem($this->_USER_ID, $kitchenId, $pageIndex, $pageSize);
        $itemCount = $mdalItem->getUserItemCount($this->_USER_ID, $kitchenId);

        $this->view->start = ($pageIndex - 1) * $pageSize + 1;
        $this->view->end = ($pageIndex - 1) * $pageSize + count($itemList);
        $this->view->item = $itemList;
        $this->view->kitchenId = $kitchenId;

        //get pager info
        $this->view->pager = array('count' => $itemCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => "mobile/kitchenitem/item/CF_kitchen_id/$kitchenId",
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($itemCount / $pageSize));


        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
        $user = $mdalProfile->getUser($this->_USER_ID);
        $this->view->usedChara = $user['allow_editchara'] ;

    	$this->render();
    }

    public function useitemAction()
    {
    	$step = $this->getParam('CF_step', "confirm");
    	$itemId = $this->getParam('CF_itemId');
    	$kitchenId = $this->getParam('CF_kitchen_id', 0);

    	require_once 'Mbll/Kitchen/Cache.php';
    	$item = Mbll_Kitchen_Cache::getItem($itemId);

    	if (empty($item) && $step != 'spoonList') {
    		$this->_redirect($this->_baseUrl . '/mobile/error/error');
    	}

    	if ($item['kitchen_only'] == 1 && $kitchenId == 0) {
			$step = 'caution';
		}

		if ($itemId == 19) {
			require_once 'Mdal/Kitchen/Restaurant.php';
	        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
	        $user = $mdalProfile->getUser($this->_USER_ID);
	        $this->view->usedChara = $user['allow_editchara'] ;
		}

		require_once 'Mbll/Kitchen/Item.php';
		$mbllItem = new Mbll_Kitchen_Item();

    	if ($step == "complete") {
			if ($item['item_category'] != 2) {
				//use normal item
				$result = $mbllItem->useItem($this->_USER_ID, $itemId, $kitchenId);

				if ($result == -2) {
					$step = 'caution';
				}
				if ($result == 1) {
					if ($item['item_category'] == 4) {
						$profileUid = $this->getParam('CF_uid', 0);
						if (!empty($profileUid)){
			    			$this->_redirect($this->_baseUrl . '/mobile/kitchen/tryfood/CF_spoonOver/1/CF_uid/' . $profileUid . '/CF_kitchen_id/' . $kitchenId);
						}
	        		}

					//zhaoxh20100206   activity 12
			        require_once 'Mbll/Kitchen/Activity.php';
			        $activity = Mbll_Kitchen_Activity::getActivity(12, $item['item_name'], $item['item_picture']);
			        $aryActivity = explode('|', $activity);

			        require_once 'Bll/Restful.php';
			        $restful = Bll_Restful::getInstance($this->_USER_ID, $this->_APP_ID);
			        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

				}
				if ($result == -1) {
					$this->_redirect($this->_baseUrl . '/mobile/error/error');
				}
			}
			else {
				//special items
				$result = $mbllItem->useItemSp($this->_USER_ID, $itemId);

				if ($result == 'error') {
					$this->_redirect($this->_baseUrl . '/mobile/error/error');
				}
				else if ($result == 'learned') {
					$step = 'cautionTwo';
				}
				else {
					$recipeInfo = Mbll_Kitchen_Cache::getRecipe($result);
					$this->view->recipeInfo = $recipeInfo;
					$step = 'recipeGot';
					//get new recipe
				}
			}
    	}
    	else if ($step == 'confirm') {
    		//can use book items or not
    		if ($item['item_category'] == 2) {
	    		$genre = $item['effect_rate'];

    			require_once 'Mdal/Kitchen/Restaurant.php';
		    	$mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
		    	$hasGenre = $mdalRest->hasRest($this->_USER_ID, $genre);
		    	if (!$hasGenre) {
		    		// can not use
		    		$step = 'cautionTwo';
		    	}
		    	else {
			    	//get user recipe which don't have
			    	require_once 'Mdal/Kitchen/Recipe.php';
			    	$mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
			    	$recipe = $mdalRecipe->getUserRecipeNot($this->_USER_ID, $genre);

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
			    		// can not use
			    		$step = 'cautionTwo';
			    	}
		    	}
    		}
    		if ($item['item_category'] == 4) {
    			$this->view->profileUid = $this->getParam('CF_uid', 0);
    		}
    	}
    	else if ($step == 'recipeGotFinish') {
    		$recipeInfo = Mbll_Kitchen_Cache::getRecipe($this->getParam('CF_recipeId'));
			$this->view->recipeInfo = $recipeInfo;
    	}
    	else if ($step == 'spoonList') {
    		require_once 'Mdal/Kitchen/Item.php';
	        $mdalItem = Mdal_Kitchen_Item::getDefaultInstance();
	        $userSpoon = $mdalItem->getUserSpoon($this->_USER_ID);
	        if (count($userSpoon) <= 1 ) {
	        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
	        }
	        else {
	        	$this->view->userSpoon = $userSpoon;
	        }
	        $this->view->profileUid = $this->getParam('CF_uid', 0);
    		if (empty($this->view->profileUid)) {
	    		$this->_redirect($this->_baseUrl . '/mobile/error/error');
			}
    	}

    	$this->view->kitchenId = $kitchenId;
    	$this->view->uid = $this->_USER_ID;
    	$this->view->rand = time();
        $this->view->item = $item;
    	$this->view->itemId = $itemId;
    	$this->view->step = $step;
    	$this->render();
    }

    /**
     * gift action
     *
     */
    public function giftAction()
    {
        $uid = $this->_user->getId();
        $taruid = $this->_request->getParam('taruid', 0);
        $this->view->taruid = $taruid;

        $pageStartEdit = $this->_request->getParam('start', 1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 5;
        $type = 1;

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
        $userInfo = $dalUser->getUser($uid);
        $this->view->userInfo = $userInfo;

        require_once 'Mdal/Kitchen/Gift.php';
        $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        $giftList = $dalGift->listMyGift($uid, $pageStart, $pageSize, $type);
        $count = $dalGift->cntListMyGift($uid, $type);
        $cntMyGift = $dalGift->cntMyGift($uid, $pageStart, $pageSize);

        $cnt = count($giftList);
        for ($i = 0; $i < $cnt; $i++) {
            $giftList[$i]['sum'] = $cntMyGift[$i]['sum'];
        }

        $this->view->giftList = $giftList;
        $this->view->count = $count;

        $this->view->start = $pageStartEdit;
        $this->view->startPrev = max(1, $pageStartEdit - 5);
        $this->view->startNext = $pageStartEdit + 5;

        $this->view->actionName = 'gift';
        $this->render();
    }

    public function giftselectAction()
    {
        $uid = $this->_user->getId();

        $giftId = $this->_request->getParam('giftId');
        if (!$giftId) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $rankBy = $this->_request->getParam('rankBy', 'asc');
        if ($rankBy != 'asc' && $rankBy != 'desc') {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $pageStartEdit = $this->_request->getParam('start', 1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 5;

        require_once 'Mdal/Kitchen/Gift.php';
        $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        $giftInfo = $dalGift->getGift($giftId);

        $fidsStr = Bll_Friend::getFriendIds($uid);
        $fids = explode(',', $fidsStr);
        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
        $friendUids = $dalUser->getFriendUids($fids, $pageStart, $pageSize, $rankBy);
        $cntFriendInKitchen = $dalUser->cntFriendInKitchen($fids);

        Bll_User::appendPeople($friendUids);

        $this->view->friendList = $friendUids;
        $this->view->count = $cntFriendInKitchen;
        $this->view->giftId = $giftId;
        $this->view->giftInfo = $giftInfo;

        $this->view->rankBy = $rankBy;
        $this->view->start = $pageStartEdit;
        $this->view->startPrev = max(1, $pageStartEdit - 5);
        $this->view->startNext = $pageStartEdit + 5;

        $this->view->actionName = 'giftselect';
        $this->render();

    }

    public function giftconfirmAction()
    {
        $uid = $this->_user->getId();

        $giftId = $this->_request->getParam('giftId');
        $taruid = array('uid' => $this->_request->getParam('taruid'));
        if (!$giftId || !$taruid['uid']) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
        Bll_User::appendPerson($taruid);

        require_once 'Mdal/Kitchen/Gift.php';
        $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        $giftSelect = $dalGift->getOneUserGift($uid, $giftId);
        $giftInfo = $dalGift->getGift($giftId);

        if ($giftSelect) {
            $this->view->taruid = $taruid['uid'];
            $this->view->tarName = $taruid['displayName'];
            $this->view->gid = $giftSelect['gid'];
            $this->view->giftInfo = $giftInfo;

            $this->render();
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    }

    public function giftfinishAction()
    {
        $uid = $this->_user->getId();

        $gid = $this->_request->getPost('hidgid');
        $taruid = array('uid' => $this->_request->getPost('hidtar'));
        if (!$gid || !$taruid['uid']) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
        Bll_User::appendPerson($taruid);

        require_once 'Mdal/Kitchen/Gift.php';
        $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        $giftSelect = $dalGift->getOneUserGiftByGid($gid);
        $giftInfo = $dalGift->getGift($giftSelect['gift_id']);

        require_once 'Mbll/Kitchen/Gift.php';
        $bllGift = new Mbll_Kitchen_Gift();

        if ($giftSelect['uid'] == $uid) {
            $result = $bllGift->sendGift($giftSelect, $uid, $taruid['uid']);
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/kitchenitem/gift');
        }

        if (!$result) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
        else {
        	//zhaoxh20100206   activity 15
	        require_once 'Mbll/Kitchen/Activity.php';
	        $activity = Mbll_Kitchen_Activity::getActivity(15, $taruid['displayName'], $giftInfo['picture']);
	        $aryActivity = explode('|', $activity);

	        require_once 'Bll/Restful.php';
	        $restful = Bll_Restful::getInstance($this->_USER_ID, $this->_APP_ID);
	        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

	        //xial 2010-04-15 : giftを送信した総数
	        try {
                $mdalKitchenAccess = Mdal_Kitchen_Access::getDefaultInstance();
			    $mdalKitchenAccess->insertUu(array('type' => 12, 'create_time' => time()));
	        } catch (Exception $e) {

	        }

			//xial 2010-04-15 : giftを送信した人数
			$insertUu = Mbll_Kitchen_Access::tryInsert($uid, 13);
        }

        $this->view->tarName = $taruid['displayName'];

        $this->view->giftInfo = $giftInfo;

        $this->render();
    }

    /************** add by shenhw ***********************/

    public function freegiftlistAction()
    {
        $uid = $this->_user->getId();
        //$resend = $this->_request->getParam('resend',0);

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
        $userInfo = $dalUser->getUser($uid);

        //if (0 == $userInfo['daily_gift'] && 0 == $resend) {
//        if (0 == $userInfo['daily_gift']) {
//            $this->_redirect($this->_baseUrl . '/mobile/error/error');
//        }

        $this->view->gold = $userInfo['gold'];
        $this->view->point = $userInfo['point'];

        $pageStartEdit = $this->_request->getParam('start',1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 10;
        $type = 4;

        require_once 'Mdal/Kitchen/Gift.php';
        $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        $giftList = $dalGift->listGift($pageStart, $pageSize, $type);
        $count = $dalGift->cntListGift($type);

        $this->view->giftList = $giftList;
        $this->view->count = $count;

        $this->view->start = $pageStartEdit;
        $this->view->startPrev = max(1,$pageStartEdit - 5);
        $this->view->startNext = $pageStartEdit + 5;

        //add by xial 2010-04-23
        $result = $this->_request->getParam('result');
        if ($result == 'success') {
            //23:communication feed配信UU; 24:communication feed配信数
            Mbll_Kitchen_Access::insertAccess($uid, 23, 24);
        }

        $this->view->actionName = 'freegiftlist';
        $this->render();
    }

    public function freegiftselectAction()
    {
        $uid = $this->_user->getId();

        $giftId = $this->_request->getParam('giftId');
        $error = $this->_request->getParam('error', 0);

        require_once 'Mdal/Kitchen/Gift.php';
        $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        $giftInfo = $dalGift->getFreeGift($giftId);

        if (!$giftInfo) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $pageStartEdit = $this->_request->getParam('start', 1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 10;

        $fidsStr = Bll_Friend::getFriendIds($uid);
        $fids = explode(',', $fidsStr);

        //delete the friend who has been send free gift today
        $sendFriends = $dalGift->getSendFriends(date('Y-m-d'), $uid);
        $arySendFriends = array();
        foreach ($sendFriends as $friend) {
            $arySendFriends[] = $friend[target];
        }
        $fids = array_diff($fids, $arySendFriends);

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
        $friendUids = $dalUser->getFriendUids2($fids, $pageStart, $pageSize, "last_login_time desc, total_level asc");
        $cntFriendInKitchen = $dalUser->cntFriendInKitchen($fids);
        Bll_User::appendPeople($friendUids);

        $this->view->friendList = $friendUids;
        $this->view->count = $cntFriendInKitchen;
        $this->view->giftId = $giftId;
        $this->view->giftInfo = $giftInfo;

        $this->view->rankBy = $rankBy;
        $this->view->start = $pageStartEdit;
        $this->view->startPrev = max(1, $pageStartEdit - $pageSize);
        $this->view->startNext = $pageStartEdit + $pageSize;
        $this->view->pageSize = $pageSize;

        $this->view->actionName = 'freegiftselect';
        $this->view->error = $error;
        $this->render();

    }

    public function freegiftconfirmAction()
    {
        $uid = $this->_user->getId();

        $gid = $this->_request->getPost('gid');
        $taruids = $this->_request->getPost('friends');
        if (!$gid) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
        if (!$taruids) {
            $this->_redirect($this->_baseUrl . '/mobile/kitchenitem/freegiftselect?giftId=' . $gid . "&error=1");
        }

        $tarCount = count($taruids);

        require_once 'Mdal/Kitchen/Gift.php';
        $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        $giftInfo = $dalGift->getFreeGift($gid);
        
        $strTarUids = implode(',', $taruids);
        $dalGift->insertFreeGiftTemp(array('uid' => $uid, 'target_uids' => $strTarUids, 'gid' => $gid, 'create_time' => time()));

        $picurl = Zend_Registry::get('static') . '/apps/kitchen/mobile/img';
        if ($giftInfo['item_gift']) {
            $picurl .= '/yorozu/40x40/' . $giftInfo['picture'] . '.gif';
        } else if ($giftInfo['goods_gift']) {
            $picurl .= '/zakka/40x40/' . $giftInfo['picture'] . '.gif';
        }

        $commFeedTitle = 'マイミク' . $tarCount . '人に' . $giftInfo['name'] . 'を贈りました!';
        $commFeedUrl = urlencode($picurl) . ',image/gif';

        $this->view->commFeedTitle = $commFeedTitle;
        $this->view->commFeedUrl = $commFeedUrl;
        $this->view->appId = $this->_APP_ID;

        $this->view->tarCount = $tarCount;
        $this->view->taruids = $taruids;
        $this->view->giftInfo = $giftInfo;
        $this->view->gid = $gid;
        $this->render();
    }
    
    public function freegiftfinishAction()
    {
        $uid = $this->_user->getId();

        require_once 'Mdal/Kitchen/Gift.php';
        $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        
        $freeGift = $dalGift->getFreeGiftTempByUid($uid);
        
        $gid = $freeGift['gid'];
        $taruids = explode(',', $freeGift['target_uids']);

        $tarCount = count($taruids);
        $giftInfo = $dalGift->getFreeGift($gid);

//        $comFeedFlag = $this->_request->getParam('result');
//        if ($comFeedFlag == 'success') {
            info_log($uid . ' : ' . $freeGift['target_uids'], 'free_gift_comfeed');
            //23:ask recipe communication feed配信UU; 24:ask recipe communication feed配信数
            Mbll_Kitchen_Access::insertAccess($uid, 23, 24);

            require_once 'Mbll/Kitchen/Gift.php';
            $bllGift = new Mbll_Kitchen_Gift();
            $result = $bllGift->sendFreeGift($gid, $uid, $taruids);
    
            if (!$result) {
                $this->_redirect($this->_baseUrl . '/mobile/error/error');
            }
            else {
                require_once 'Mdal/Kitchen/User.php';
                $dalUser = Mdal_Kitchen_User::getDefaultInstance();
                $dalUser->updateUser(array('daily_gift' => 0), $uid);
    
                $picurl = Zend_Registry::get('static') . '/apps/kitchen/mobile/img';
                if ($giftInfo['item_gift']) {
                    $picurl .= '/yorozu/40x40/' . $giftInfo['picture'] . '.gif';
                } else if ($giftInfo['goods_gift']) {
                    $picurl .= '/zakka/40x40/' . $giftInfo['picture'] . '.gif';
                }
    
                require_once 'Mbll/Kitchen/Activity.php';
                $activity = Mbll_Kitchen_Activity::getActivity(17, $tarCount, $picurl, $giftInfo['name']);
                $aryActivity = explode('|', $activity);
    
                require_once 'Bll/Restful.php';
                $restful = Bll_Restful::getInstance($this->_USER_ID, $this->_APP_ID);
                $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');
            }

            //xial 2010-04-20 : free gift を送信した総数
            try {
                $mdalKitchenAccess = Mdal_Kitchen_Access::getDefaultInstance();
                $mdalKitchenAccess->insertUu(array('type' => 19, 'create_time' => time()));
            }
            catch (Exception $e){
            }
            //free gift を送信した人数
            $insertUu = Mbll_Kitchen_Access::tryInsert($uid, 20);
//        }

        //clear record
        $dalGift->updateFreeGiftTemp($uid);
        
        $this->view->giftInfo = $giftInfo;
        $this->view->taruids = $taruids;

        $this->render();
    }

    /**
     * my gift list action
     *
     */
    public function mygiftlistAction()
    {

        $pageIndex = $this->getParam('CF_page', 1);
        $pageSize = 5;
        $orderType = $this->getParam('CF_ordertype', "create_time");
        $order = "DESC";

        $clearDaily = $this->getParam('CF_clearDaily', 0);
        if ($clearDaily) {
        	require_once 'Mdal/Kitchen/Daily.php';
            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
            //update daily param
            $mdalDaily->updateDaily(array('gift'=>0), $this->_user->getId());
        }

        require_once 'Mbll/Kitchen/Gift.php';
        $mbllGift = new Mbll_Kitchen_Gift();

        $giftList = $mbllGift->getMyGiftList($this->_user->getId(), $pageIndex, $pageSize, $orderType, $order);
        $giftCount = $mbllGift->getMyGiftCount($this->_user->getId());

        $this->view->giftList = $giftList;
        $this->view->giftCount = $giftCount;

        //get pager info
        $this->view->pager = array('count' => $giftCount, 'pageIndex' => $pageIndex, 'requestUrl' => '/mobile/kitchenitem/mygiftlist', 'pageSize' => $pageSize, 'maxPager' => ceil($giftCount / $pageSize), 'pageParam' => '&CF_ordertype=' . $orderType);

        $this->render();
    }

    /**
     * open gift action
     *
     */
    public function opengiftAction()
    {
        $id = $this->getParam('CF_id');
        $sendUid = $this->getParam('CF_send_uid', 0);
        $giftId = $this->getParam('CF_gift_id');
        $type = $this->getParam('CF_type');
        $visitGiftType = $this->getParam('CF_visit_gift_type');

        if (1 == $type) {
            //gift
            require_once 'Mbll/Kitchen/Cache.php';
            $mbllKitchenCache = new Mbll_Kitchen_Cache();;
            $gift = $mbllKitchenCache->getGift($giftId);
            //Mbll_Kitchen_Cache::clearGift(1);
            $name = $gift['name'];
            $introduce = $gift['introduce'];
            $picture = $gift['picture'];
        } else if (2 == $type) {
            //levelup gift
            $name = "ごほうびギフト";
            $introduce = "レベルアップした時にもらえるギフト";
            $picture = "etc/03.gif";
        } else if (3 == $type) {
            //visit gift
            $name = "デイリーギフト";
            $introduce = "1日1回のログイン時にもらえるギフト";
            $picture = "etc/01.gif";
        }

        //get send user info
        if ($sendUid) {
            require_once 'Mdal/Kitchen/User.php';
            $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
            $sendUserInfo = $mdalUser->getUser($sendUid);

            require_once 'Bll/User.php';
            Bll_User::appendPerson($sendUserInfo, 'uid');
        }

        //get total recipe count
        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRestaurant = new Mbll_Kitchen_Restaurant();
        $genreList = $mbllKitchenRestaurant->getGenreList();
        $totalRecipeCount = 0;
        foreach ($genreList as $genre) {
            $totalRecipeCount += $genre['recipe_count'];
        }
        $this->view->totalRecipeCount = $totalRecipeCount;

        $this->view->sendUserInfo = $sendUserInfo;
        $this->view->id = $id;
        $this->view->sendUid = $sendUid;
        $this->view->giftId = $giftId;
        $this->view->type = $type;
        $this->view->visitGiftType = $visitGiftType;
        $this->view->name = $name;
        $this->view->introduce = $introduce;
        $this->view->picture = $picture;

        $this->render();
    }

    /**
     * open gift finish action
     *
     */
    public function opengiftfinishAction()
    {
        $id = $this->getParam('CF_id');
        $giftId = $this->getParam('CF_gift_id');
        $type = $this->getParam('CF_type');

        if (1 == $type) {
            //gift
            require_once 'Mbll/Kitchen/Cache.php';
            $mbllKitchenCache = new Mbll_Kitchen_Cache();
            $giftInfo = $mbllKitchenCache->getGift($giftId);
            $name = $giftInfo['name'];
        } else if (2 == $type) {
            //levelup gift
            $name = "ごほうびギフト";
        } else if (3 == $type) {
            //visit gift
            $name = "デイリーギフト";
        } else if (4 == $type) {
            //visit gift
            $name = "ともだちギフト";
        } else if (5 == $type) {
            //visit gift
            $name = "ごほうびギフト";
        }

        require_once 'Mbll/Kitchen/Gift.php';
        $mbllGift = new Mbll_Kitchen_Gift();

        //check send gift
        $gift = $mbllGift->getSendGift($id);

        if ($gift) {
            $sendUid = $gift['uid'];
            $result = $mbllGift->openGift($gift);

            if ($sendUid) {
                require_once 'Mdal/Kitchen/User.php';
                $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
                $sendUserInfo = $mdalUser->getUser($sendUid);

                require_once 'Bll/User.php';
                Bll_User::appendPerson($sendUserInfo, 'uid');
            }

            //get total recipe count
            require_once 'Mbll/Kitchen/Restaurant.php';
            $mbllKitchenRestaurant = new Mbll_Kitchen_Restaurant();

            $genreList = $mbllKitchenRestaurant->getGenreList();
            $totalRecipeCount = 0;
            foreach ($genreList as $genre) {
                $totalRecipeCount += $genre['recipe_count'];
            }
            $this->view->totalRecipeCount = $totalRecipeCount;

            //genre of owner's active restaurant
            require_once 'Mdal/Kitchen/Restaurant.php';
            $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
            $ownerRes = $mdalRes->getActiveRestaurant($this->_user->getId());
            $ownerGenreId = $ownerRes['genre'];

            $hasGift = $mbllGift->getMyGiftCount($this->_user->getId());

            $this->view->result = $result;
            $this->view->sendUserInfo = $sendUserInfo;
            $this->view->id = $id;
            $this->view->sendUid = $sendUid;
            $this->view->name = $name;
            $this->view->ownerGenreId = $ownerGenreId;
            $this->view->hasGift = $hasGift;
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
        $this->view->osUID = $this->_USER_ID;

        //zhaoxh20100206   activity 10 11 16
        require_once 'Mbll/Kitchen/Activity.php';
        if ($gift['type'] == 1) {
        	require_once 'Bll/User.php';
	        $rowProfile = array('uid' => $gift['uid']);
	        Bll_User::appendPerson($rowProfile, 'uid');
        	$activity = Mbll_Kitchen_Activity::getActivity(16, $rowProfile['displayName'], $gift['picture']);
        }
        else if ($gift['type'] == 2) {
        	$activity = Mbll_Kitchen_Activity::getActivity(11, 'levelup', 'etc/03.gif');
        }
        else if ($gift['type'] == 3) {
        	$activity = Mbll_Kitchen_Activity::getActivity(10, 'daily', 'etc/01.gif');
        }
        else if ($gift['type'] == 4) {
        }
    	else if ($gift['type'] == 5) {
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $aryActivity = explode('|', $activity);

        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($this->_USER_ID, $this->_APP_ID);
        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

        //xial 2010-04-15 : gift開封数
        try {
            $mdalKitchenAccess = Mdal_Kitchen_Access::getDefaultInstance();
            $mdalKitchenAccess->insertUu(array('type' => 14, 'create_time' => time()));
        }
        catch (Exception $e){
        }

        $this->render();
    }

    /**
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_redirect($this->_baseUrl . '/mobile/error/error');
    }
}