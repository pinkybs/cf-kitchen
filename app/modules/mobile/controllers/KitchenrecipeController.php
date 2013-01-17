<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile kitchenrecipe Controller(modules/mobile/controllers/KitchenrecipeController.php)
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  hch  2010-1-7
 */
class KitchenrecipeController extends MyLib_Zend_Controller_Action_Mobile
{
    private $_kitchen_id;

    private $_recipe_count_per_genre = 25;

    function preDispatch()
    {
        $this->_kitchen_id = $this->getParam('CF_kitchen_id');
        $this->view->kitchenID = $this->_kitchen_id;
    }

    /**
     * select action
     *
     */
    public function selectAction()
    {
        $this->render();
    }

    /**
     * make action
     *
     */
    public function makeAction()
    {
        $step = $this->getParam('CF_step', "start");

        $_SESSION['kitchen_kit_selectfood'] = null;
        unset($_SESSION['kitchen_kit_selectfood']);

        if ($step == "start") {
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $fail = $mdalKitchen->getUserKitchenFailCount($this->_USER_ID, $this->_kitchen_id);
            $this->view->leaveCount = 3-$fail;

            if ($fail == 3) {
                $step = "failure2";
            }
        }
        elseif ($step == "success") {
            $recipeId = $this->getParam('CF_recipe_id');
            if (empty($recipeId)) {
                $this->_redirect($this->_baseUrl . '/mobile/kitchenrecipe/make/CF_step/start/CF_kitchen_id/' . $this->_kitchen_id);
            }

            //cooking start
            require_once 'Mbll/Kitchen/Kitchen.php';
            $mbllKitchen = new Mbll_Kitchen_Kitchen();
            $mbllKitchen->cookBegin($this->_USER_ID, $this->_kitchen_id, $recipeId);

            require_once 'Mbll/Kitchen/Cache.php';
            $recipeInfo = Mbll_Kitchen_Cache::getRecipe($recipeId);
            $this->view->recipe = $recipeInfo;

            //access analyse -uu
		    require_once 'Mbll/Kitchen/Access.php';
	        $insertUu = Mbll_Kitchen_Access::tryInsert($this->_USER_ID, 5);

	        //2010-04-23 add by xial : 25:調理開始UU; 26:調理開始数
            Mbll_Kitchen_Access::insertAccess($this->_USER_ID, 25, 26);

            //zhaoxh20100206   activity 8
	        require_once 'Mbll/Kitchen/Activity.php';
	        $activity = Mbll_Kitchen_Activity::getActivity(8, $recipeInfo['recipe_name'], $recipeId);
	        $aryActivity = explode('|', $activity);

	        require_once 'Bll/Restful.php';
	        $restful = Bll_Restful::getInstance($this->_USER_ID, $this->_APP_ID);
	        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');
        }
        elseif ($step == "failure1") {
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $fail = $mdalKitchen->getUserKitchenFailCount($this->_USER_ID, $this->_kitchen_id);
            $this->view->leaveCount = 3-$fail;

            if ($fail == 3) {
                $step = "failure2";
            }
        }
        elseif ($step == "failure2") {
        }
        else {
            $step = "start";
        }

        $this->view->uid = $this->_USER_ID;
        $this->view->rand = time();
        $this->view->step = $step;
        $this->render();
    }

    /**
     * make recipe logic action
     *
     */
    public function makerecipeAction()
    {
        $foodId1 = $this->getParam('CF_food1');
        $foodId2 = $this->getParam('CF_food2');
        $foodId3 = $this->getParam('CF_food3');
        $foodId4 = $this->getParam('CF_food4');

        require_once 'Mbll/Kitchen/Recipe.php';
        $mbllRecipe = new Mbll_Kitchen_Recipe();
        $result = $mbllRecipe->research($this->_USER_ID, $this->_kitchen_id, $foodId1, $foodId2, $foodId3, $foodId4, $recipeId);

        //access analyse -uu
	    require_once 'Mbll/Kitchen/Access.php';
        $insertUu = Mbll_Kitchen_Access::tryInsert($this->_USER_ID, 8);

        //xial 2010-04-26: 27:レシピ開発数;28:レシピ開発数UU;
        Mbll_Kitchen_Access::insertAccess($this->_USER_ID, 28, 27);
        if ($result == 1) {
            //zhaoxh20100421 minifeed 8
            $recipeInfo = Mbll_Kitchen_Cache::getRecipe($recipeId);
	        $miniFeed = Mbll_Kitchen_Activity::getMiniFeed(8, $recipeInfo['recipe_name'], $recipeId);
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

	        //xial 2010-04-26 : 29 レシピ開発成功数;
	        try {
	            $mdalKitchenAccess = Mdal_Kitchen_Access::getDefaultInstance();
	            $mdalKitchenAccess->insertUu(array('type' => 29, 'create_time' => time()));
	        }
	        catch (Exception $e){
	        }

        	$this->_redirect($this->_baseUrl . '/mobile/kitchenrecipe/make/CF_step/success/CF_recipe_id/' . $recipeId . '/CF_kitchen_id/' . $this->_kitchen_id);
        }
        else if ($result == 2) {
	        //xial 2010-04-26 : 30 レシピ開発失敗数;
	        try {
	            $mdalKitchenAccess = Mdal_Kitchen_Access::getDefaultInstance();
	            $mdalKitchenAccess->insertUu(array('type' => 30, 'create_time' => time()));
	        }
	        catch (Exception $e){
	        }

        	$this->_redirect($this->_baseUrl . '/mobile/kitchenrecipe/make/CF_step/failure1/CF_kitchen_id/' . $this->_kitchen_id);
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
    }

    public function recipeconfirmAction()
    {
        $foodId1 = $this->getParam('CF_food1');
        $foodId2 = $this->getParam('CF_food2');
        $foodId3 = $this->getParam('CF_food3');
        $foodId4 = $this->getParam('CF_food4');

        require_once 'Mdal/Kitchen/Food.php';
        $dalFood = Mdal_Kitchen_Food::getDefaultInstance();
        require_once 'Mbll/Kitchen/Cache.php';
        $foodInfo1 = Mbll_Kitchen_Cache::getFood($foodId1);
        $foodInfo2 = Mbll_Kitchen_Cache::getFood($foodId2);
        $foodInfo3 = Mbll_Kitchen_Cache::getFood($foodId3);
        $foodInfo4 = Mbll_Kitchen_Cache::getFood($foodId4);

        $userFood1 = $dalFood->getUserFoodInfo($this->_USER_ID, $foodId1);
        $userFood2 = $dalFood->getUserFoodInfo($this->_USER_ID, $foodId2);
        $userFood3 = $dalFood->getUserFoodInfo($this->_USER_ID, $foodId3);
        $userFood4 = $dalFood->getUserFoodInfo($this->_USER_ID, $foodId4);

        $idstr = $foodId1 . $foodId2;

        //minus2
        $minus2 = $foodId1 == $foodId2 ? 1 : 0;

        //minus3
        // . $foodId4;
        $cnt = substr_count($idstr, $foodId3);
        $minus3 = $cnt;

        //minus4
        $idstr .= $foodId3;
        $cnt = substr_count($idstr, $foodId4);
    	$minus4 = $cnt;

        $this->view->foodInfo1 = $foodInfo1;
        $this->view->foodInfo2 = $foodInfo2;
        $this->view->foodInfo3 = $foodInfo3;
        $this->view->foodInfo4 = $foodInfo4;

        $this->view->userFood1 = $userFood1;
        $this->view->userFood2 = $userFood2;
        $this->view->userFood3 = $userFood3;
        $this->view->userFood4 = $userFood4;

        $this->view->minus2 = $minus2;
        $this->view->minus3 = $minus3;
        $this->view->minus4 = $minus4;

        $this->view->paramStr = '/CF_food1/' . $foodId1 . '/CF_food2/' . $foodId2 . '/CF_food3/' . $foodId3 . '/CF_food4/' . $foodId4;
        $this->view->uid = $this->_USER_ID;
        $this->view->rand = time();
        $this->render();

    }

    /**
     * choice recipe action
     *
     */
    public function choiceAction()
    {
    	$_SESSION['kitchen_kit_selectfood'] = null;
        unset($_SESSION['kitchen_kit_selectfood']);
        $step = $this->getParam('CF_step', "start");

        if ($step == "start") {
        	//check is first login
	        require_once 'Mdal/Kitchen/Restaurant.php';
	        $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
	        $this->view->myRes = $mdalRes->getActiveRestaurant($this->_USER_ID);

            $pageSize = 10;
            $pageIndex = $this->getParam('CF_page', 1);
            $orderType = $this->getParam('CF_ordertype', "total_time");
            $order = $this->getParam('CF_order', "ASC");

            require_once 'Mdal/Kitchen/Recipe.php';
            $mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
            $recipeList = $mdalRecipe->getUserRecipe($this->_USER_ID, $pageIndex, $pageSize, $orderType, $order);
            $recipeCount = $mdalRecipe->getUserRecipeCount($this->_USER_ID);

            $this->view->ordertype = $orderType;
            $this->view->start = ($pageIndex - 1) * $pageSize + 1;
            $this->view->end = ($pageIndex - 1) * $pageSize + count($recipeList);
            $this->view->recipeList = $recipeList;

            //get pager info
            $this->view->pager = array('count' => $recipeCount,
                                       'pageIndex' => $pageIndex,
                                       'requestUrl' => "mobile/kitchenrecipe/choice/CF_ordertype/$orderType/CF_order/$order/CF_kitchen_id/" . $this->_kitchen_id,
                                       'pageSize' => $pageSize,
                                       'maxPager' => ceil($recipeCount / $pageSize)
                                       );

            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            $fail = $mdalKitchen->getUserKitchenFailCount($this->_USER_ID, $this->_kitchen_id);
            $this->view->leaveCount = 3-$fail;

            $maxLevel = $mdalRes->getMaxLevel($this->_USER_ID);
            $this->view->maxLevel = $maxLevel;
        }
        elseif ($step == "finish") {
            $recipeId = $this->getParam('CF_recipe');

            //check recipe is user
            require_once 'Mdal/Kitchen/Recipe.php';
            $mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
            $result = $mdalRecipe->hasRecipe($this->_USER_ID, $recipeId);

            if (!$result) {
                $this->_redirect($this->_baseUrl . '/mobile/kitchenrecipe/choice/CF_kitchen_id/' . $this->_kitchen_id);
            }

            //cooking start
            require_once 'Mbll/Kitchen/Kitchen.php';
            $mbllKitchen = new Mbll_Kitchen_Kitchen();
            $result = $mbllKitchen->cookBegin($this->_USER_ID, $this->_kitchen_id, $recipeId);

            if (!$result) {
            	return $this->_redirect($this->_baseUrl . '/mobile/error/error');
            }

            require_once 'Mbll/Kitchen/Cache.php';
            $recipeInfo = Mbll_Kitchen_Cache::getRecipe($recipeId);
            $this->view->recipe = $recipeInfo;

            //access analyse -uu
		    require_once 'Mbll/Kitchen/Access.php';
	        $insertUu = Mbll_Kitchen_Access::tryInsert($this->_USER_ID, 5);

	        //2010-04-23 add by xial : 25:調理開始UU; 26:調理開始数
            Mbll_Kitchen_Access::insertAccess($this->_USER_ID, 25, 26);

            //zhaoxh20100206   activity 7
	        require_once 'Mbll/Kitchen/Activity.php';
	        $activity = Mbll_Kitchen_Activity::getActivity(7, $recipeInfo['recipe_name'], $recipeId);
	        $aryActivity = explode('|', $activity);

	        require_once 'Bll/Restful.php';
	        $restful = Bll_Restful::getInstance($this->_USER_ID, $this->_APP_ID);
	        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

        }
        elseif ($step == "first") {
            require_once 'Mbll/Kitchen/Cache.php';
            $recipe1 = Mbll_Kitchen_Cache::getRecipe('y24');
            $recipe2 = Mbll_Kitchen_Cache::getRecipe('y25');
            $this->view->recipe = array_merge(array($recipe1),array($recipe2));
        }
        else {
            $step = "start";
        }

        $this->view->uid = $this->_USER_ID;
        $this->view->rand = time();
        $this->view->step = $step;
        $this->render();
    }

    //********************** add by shenhw **************************************

    /**
     * recipe list action
     *
     */
    public function recipelistAction()
    {
        $uid = $this->getParam('CF_uid', $this->_user->getId());
        $pageIndex = $this->getParam('CF_page', 1);
        $genre = $this->getParam('CF_genre', 1);
        $pageSize = 9;

        $userInfo = array('uid' => $uid);
        require_once 'Bll/User.php';
        Bll_User::appendPerson($userInfo, 'uid');
        $this->view->userInfo = $userInfo;

        require_once 'Mdal/Kitchen/Recipe.php';
        $mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();

        //get recipe list
        $recipeList = $mdalRecipe->getUserRecipeByGenre($uid, $genre, $pageIndex, $pageSize);
        $recipeCount = $mdalRecipe->getUserRecipeCountByGenre($uid, $genre);

        $this->view->recipeList = $recipeList;
        $this->view->recipeCount = $recipeCount ? $recipeCount : 0;

        require_once 'Mbll/Kitchen/Cache.php';
        $nbGenreList =  Mbll_Kitchen_Cache::getNbGenreList();
        foreach ($nbGenreList as $nbGenre) {
        	if ($genre == $nbGenre['genre']) {
        	   $this->_recipe_count_per_genre = $nbGenre['recipe_count'];
        	   break;
        	}
        }
        $this->view->recipeCountPerGenre = $this->_recipe_count_per_genre;
        $this->view->genre = $genre;

        //get pager info
        $this->view->pager = array('count' => $this->_recipe_count_per_genre,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/kitchenrecipe/recipelist',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($this->_recipe_count_per_genre / $pageSize),
                                   'pageParam' => '&CF_uid=' . $uid . '&CF_genre=' . $genre
                                   );

        $this->render();
    }

    /**
     * recipe detail action
     *
     */
    public function recipedetailAction()
    {

        $recipeId = $this->getParam('CF_recipe_id');
        $uid = $this->getParam('CF_uid', $this->_user->getId());
        $viewer = $this->_user->getId();
        $canDisplayFood = true;
        //if ($uid == $viewer) {
            require_once 'Mdal/Kitchen/Recipe.php';
            $mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
            //get user recipe info
            $userRecipeInfo = $mdalRecipe->getUserRecipeById($viewer, $recipeId);

            if (!$userRecipeInfo) {
                $canDisplayFood = false;
            }
        //} else {
        //    $canDisplayFood = false;
        //}

        $userInfo = array('uid' => $uid);
        require_once 'Bll/User.php';
        Bll_User::appendPerson($userInfo, 'uid');
        $this->view->userInfo = $userInfo;

        require_once 'Mbll/Kitchen/Cache.php';
        //get recipe info
        $recipeInfo = Mbll_Kitchen_Cache::getRecipe($recipeId);;

        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRestaurant = new Mbll_Kitchen_Restaurant();
        $recipeInfo['genre_name'] = $mbllKitchenRestaurant->getGenreNameById($recipeInfo['genre']);

        //food info
        $food1 = "";
        $food2 = "";
        $food3 = "";
        $luckyFood = "";

        require_once 'Mbll/Kitchen/Food.php';
        $mbllFood = new Mbll_Kitchen_Food();

        //get food1 info
        if ($recipeInfo['food1']) {
            $food1 = Mbll_Kitchen_Cache::getFood($recipeInfo['food1']);
            $food1['food_category_name'] = $mbllFood->getFoodCatogeryById($food1['food_category']);
        }

        //get food2 info
        if ($recipeInfo['food2']) {
            $food2 = Mbll_Kitchen_Cache::getFood($recipeInfo['food2']);
            $food2['food_category_name'] = $mbllFood->getFoodCatogeryById($food2['food_category']);
        }

        //get food3 info
        if ($recipeInfo['food3']) {
            $food3 = Mbll_Kitchen_Cache::getFood($recipeInfo['food3']);
            $food3['food_category_name'] = $mbllFood->getFoodCatogeryById($food3['food_category']);
        }

        //get lucky food info
        if ($recipeInfo['lucky_food']) {
            $luckyFood = Mbll_Kitchen_Cache::getFood($recipeInfo['lucky_food']);
            $luckyFood['food_category_name'] = $mbllFood->getFoodCatogeryById($luckyFood['food_category']);
        }

        $luckyFlag = false;
        if ($userRecipeInfo && '1' == $userRecipeInfo['lucky_flag']) {
            $luckyFlag = true;
        }

        $this->view->food1 = $food1;
        $this->view->food2 = $food2;
        $this->view->food3 = $food3;
        $this->view->luckyFood = $luckyFood;
        $this->view->userRecipeInfo = $userRecipeInfo;
        $this->view->recipeInfo = $recipeInfo;
        $this->view->canDisplayFood = $canDisplayFood;
        $this->view->luckyFlag = $luckyFlag;

        $this->render();
    }

    public function recipeaskAction()
    {
    	$recipeId = $this->getParam('CF_recipe_id');
        $uid = $this->getParam('CF_uid', $this->_user->getId());

        $userInfo = array('uid' => $uid);
        require_once 'Bll/User.php';
        Bll_User::appendPerson($userInfo, 'uid');
        $this->view->userInfo = $userInfo;

        require_once 'Mbll/Kitchen/Cache.php';
        //get recipe info
        $recipeInfo = Mbll_Kitchen_Cache::getRecipe($recipeId);;

        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRestaurant = new Mbll_Kitchen_Restaurant();
        $recipeInfo['genre_name'] = $mbllKitchenRestaurant->getGenreNameById($recipeInfo['genre']);

        $this->view->recipeInfo = $recipeInfo;

        $commFeedTitle = $recipeInfo['recipe_name'] . 'の作り方知ってる?';

        $picurl = Zend_Registry::get('static') . '/apps/kitchen/mobile/img/meal/40x40/' . $recipeInfo['f'] . '/' . $recipeInfo['n'] . '.gif';
        //$picurl = 'http://kitchen.zhaoxh.cn/static' . '/apps/kitchen/mobile/img/meal/40x40/' . $recipeInfo['f'] . $recipeInfo['n'] . '.gif';

        $commFeedUrl = urlencode($picurl) . ',image/gif';

        //communication feed
        $this->view->commFeedTitle = $commFeedTitle;
        $this->view->commFeedUrl = $commFeedUrl;
        $this->view->appId = $this->_APP_ID;

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