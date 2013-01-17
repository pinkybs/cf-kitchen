<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile kitchenshop Controller(modules/mobile/controllers/KitchenshopController.php)
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-4
 */
class KitchenshopController extends MyLib_Zend_Controller_Action_Mobile
{
    protected $_pageSize = 10;

    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * dispatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        $userName = $this->_user->getDisplayName();
        $this->view->app_name = 'kitchen';
        $this->view->uid = $uid;
        $this->view->userName = $userName;

        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();
        $this->view->boardAppId = BOARD_APP_ID;

        //lv2 enterance limit
        require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $levelForEnter = $dalRest->getMaxLevel($uid);
        if ($levelForEnter < 2) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchen/home');
        }
    }

    /**
     * index action -- welcome page
     *
     */
    public function indexAction()
    {
        $uid = $this->_user->getId();
        $this->_redirect($this->_baseUrl . '/mobile/kitchen/home');
        return;
        //$this->render();
    }

    /**
     * to shopping index page
     *
     */
    public function shoppingAction()
    {
    	$uid = $this->_user->getId();

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];

    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();

    	$levelForEnter = $dalRest->getMaxLevel($uid);
    	$this->view->level = $levelForEnter;

    	$this->render();
    }

    //foodActions##start
    public function foodlistAction()
    {
    	$uid = $this->_user->getId();

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];

    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
    	$restInfo = $dalRest->getActiveRestaurant($uid);

        $category = $this->_request->getParam('category',0);
        $pageStartEdit = $this->_request->getParam('start',1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 5;
        $genre = $restInfo['genre'];
        //$level = $restInfo['level'];
        $maxLevel = $dalRest->getMaxLevel($uid);

        if ($maxLevel < 3) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        require_once 'Mdal/Kitchen/Food.php';
        $dalFood = Mdal_Kitchen_Food::getDefaultInstance();
        $foodlist = $dalFood->listFood($category, $pageStart, $pageSize, $genre, $maxLevel);
        $count = $dalFood->cntListFood($category, $genre, $maxLevel);

    	$this->view->foodlist = $foodlist;
        $this->view->count = $count;

        $this->view->category = $category;
        $this->view->start = $pageStartEdit;
    	$this->view->startPrev = max(1,$pageStartEdit - 5);
    	$this->view->startNext = $pageStartEdit + 5;

    	$this->view->level = $maxLevel;
    	$this->view->actionName = 'foodlist';
    	$this->render();
    }

    public function foodconfirmAction()
    {
    	$uid = $this->_user->getId();
    	$foodId = $this->_request->getParam('food_id');
        $payType = $this->_request->getParam('pay');
    	$payTypeStr = 'food_price_' . $payType;

    	if ($payType != 'gold' && $payType != 'point') {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];

    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        //$restInfo = $dalRest->getActiveRestaurant($uid);
        $maxLevel = $dalRest->getMaxLevel($uid);

    	require_once 'Mbll/Kitchen/Cache.php';
        $foodInfo = Mbll_Kitchen_Cache::getFood($foodId);

        if ($userInfo['discount'] != 100 && $payType == 'point') {
        	$foodInfo['disct'] = intval($foodInfo[$payTypeStr] * (100 - $userInfo['discount']) / 100);
        }
        $foodInfo['money_left'] = $userInfo[$payType] - $foodInfo[$payTypeStr] + $foodInfo['disct'];
        $this->view->result = $foodInfo['money_left'] >= 0;

    	$canBuy =  $maxLevel >= $foodInfo['level'];

        if (!$foodId || !$canBuy) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($foodInfo['type'] == 0) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	if ($maxLevel < 3) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $foodInfo['categoryName'] = $this->_getFoodCategoryName($foodInfo['food_category']);

        $this->view->payType = $payType;

        $this->view->foodInfo = $foodInfo;


    	$this->render();
    }

    function _getFoodCategoryName($category)
    {
        require_once 'Mbll/Kitchen/Food.php';
        $mbllFood = new Mbll_Kitchen_Food();

        return $mbllFood->getFoodCatogeryById($category);

        /*
        switch ($category) {
    		case 1:
    			$categoryName = '魚介類';
    			break;
    		case 2:
    			$categoryName = '穀類';
    			break;
			case 3:
    			$categoryName = '調味料';
    			break;
    		case 4:
    			$categoryName = '肉類';
    			break;
    		case 5:
    			$categoryName = '野菜類';
    			break;
    		case 6:
    			$categoryName = '乳卵豆';
    			break;
    		case 7:
    			$categoryName = 'フルーツ';
    			break;
    		default:
    			break;
    	}
    	return $categoryName;
        */
    }

    public function foodfinishAction()
    {
    	$uid = $this->_user->getId();
    	$foodId = $this->_request->getParam('food_id');
    	$payType = $this->_request->getParam('pay');
    	$payTypeStr = 'food_price_' . $payType;

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        //$restInfo = $dalRest->getActiveRestaurant($uid);
        $maxLevel = $dalRest->getMaxLevel($uid);

    	require_once 'Mbll/Kitchen/Cache.php';
        $foodInfo = Mbll_Kitchen_Cache::getFood($foodId);

        //$foodInfo['food_price_gold'] = intval($foodInfo['food_price_gold'] * $userInfo['discount'] / 100);
        $foodInfo['food_price_point'] = intval($foodInfo['food_price_point'] * $userInfo['discount'] / 100);

    	$canBuy =  $maxLevel >= $foodInfo['level'];

        if (!$foodId || !$canBuy) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($foodInfo['type'] == 0) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	if ($maxLevel < 3) {
    		$this->_redirect($this->_baseUrl . '/mobile/error/error');
    	}

        if ($foodInfo[$payTypeStr] <= $userInfo[$payType]) {
    		require_once 'Mbll/Kitchen/Food.php';
        	$bllFood = new Mbll_Kitchen_Food();

        	$result = $bllFood->buyFood($uid, $payType, $foodInfo);

        	if ($result) {
        		//access analyse
		        require_once 'Mdal/Kitchen/Access.php';
        		$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();

        		try {
        			$mdalAccess->insertMoney(array('uid' => $uid,
        										   'amount' => $foodInfo[$payTypeStr],
        										   'type' => $payType == 'point' ? 1 : 3,
        										   'description' => 'buy_food',
        										   'create_time' => time()));

        		}
        		catch (Exception $e){
		        }

        		//zhaoxh20100206   activity 9
		        require_once 'Mbll/Kitchen/Activity.php';
		        $activity = Mbll_Kitchen_Activity::getActivity(9, $foodInfo['food_name'], 'food/40x40/' . $foodInfo['food_picture']);
		        $aryActivity = explode('|', $activity);

		        require_once 'Bll/Restful.php';
		        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
		        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

        		$this->render();
        	}
        	else {
        		$this->_redirect($this->_baseUrl . '/mobile/error/error');
        	}
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/foodconfirm?food_id=' . $foodId . '&pay=' . $payType);
        }
    }
    //foodActions##end


    //goodsActions##start
	public function goodslistAction()
    {
    	$uid = $this->_user->getId();

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];

    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $restInfo = $dalRest->getActiveRestaurant($uid);

        $genre = $this->_request->getParam('genre', $restInfo['genre']);
        $pageStartEdit = $this->_request->getParam('start',1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 5;

        require_once 'Mdal/Kitchen/Goods.php';
        $dalGoods = Mdal_Kitchen_Goods::getDefaultInstance();
        $goodsList = $dalGoods->listGoods($genre, $pageStart, $pageSize);
        $count = $dalGoods->cntListGoods($genre);

        $genreLevel = array(1 => $dalRest->getGenreLv($uid, 1),
                            2 => $dalRest->getGenreLv($uid, 2),
                            3 => $dalRest->getGenreLv($uid, 3),
                            4 => $dalRest->getGenreLv($uid, 4));

        $cntGoodsList = count($goodsList);
        for ($i = 0; $i < $cntGoodsList; $i++) {
        	$goodsList[$i]['genreName'] = $this->_getGenreName($goodsList[$i]['genre']);
        	$goodsList[$i]['genreLevel'] = $genreLevel[$goodsList[$i]['genre']];
        }

    	$this->view->goodsList = $goodsList;
        $this->view->count = $count;

        $this->view->genre = $genre;
        $this->view->start = $pageStartEdit;
    	$this->view->startPrev = max(1,$pageStartEdit - 5);
    	$this->view->startNext = $pageStartEdit + 5;

    	$this->view->actionName = 'goodslist';
    	$this->view->osUID = $this->_USER_ID;
    	$this->render();
    }

	public function goodsconfirmAction()
    {
    	$uid = $this->_user->getId();
    	$goodsId = $this->_request->getParam('CF_goodsId');
    	$position = $this->_request->getParam('CF_position');
    	$payType = $this->_request->getParam('pay');
    	$payTypeStr = 'goods_price_' . $payType;
    	$okuType = substr($goodsId,3,1);
    	$goodsId = substr($goodsId,0,3);

    	if ($okuType == 1) {
	    	require_once 'Mdal/Kitchen/User.php';
	        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
	    	$userInfo = $dalUser->getUser($uid);

	    	$this->view->gold = $userInfo['gold'];
	    	$this->view->point = $userInfo['point'];

	    	if ($payType != 'gold' && $payType != 'point') {
	        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
	        }

	    	require_once 'Mbll/Kitchen/Cache.php';
        	$goodsInfo = Mbll_Kitchen_Cache::getGoods($goodsId);

	        if ($userInfo['discount'] != 100 && $payType == 'point') {
	        	$goodsInfo['disct'] = intval($goodsInfo[$payTypeStr] * (100 - $userInfo['discount']) / 100);
	        }
	        $goodsInfo['money_left'] = $userInfo[$payType] - $goodsInfo[$payTypeStr] + $goodsInfo['disct'];
	        $this->view->result = $goodsInfo['money_left'] >= 0;

	        if (!$goodsId || !$goodsInfo['type']) {
	        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
	        }

	        require_once 'Mdal/Kitchen/Restaurant.php';
	        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
	        $genreLevel = $dalRest->getGenreLv($uid, $goodsInfo['genre']);

	        $restInfo = $dalRest->getActiveRestaurant($uid);

	        if ($restInfo['genre'] != $goodsInfo['genre']) {
	        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
	        }

	    	if ($goodsInfo['level'] > $genreLevel) {
	        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
	        }

	        $genreName = $this->_getGenreName($goodsInfo['genre']);

	        $this->view->genreName = $genreName;
	        $this->view->goodsInfo = $goodsInfo;
	        $this->view->position = $position;
	        $this->view->payType = $payType;
	    	$this->render();
    	}
    	else if ($okuType == 2) {
    		$this->_redirect($this->_baseUrl . '/mobile/kitchenitem/addgood?CF_goodId=' . $goodsId . '&CF_position=' . $position);
    	}
    	else {
    		$this->_redirect($this->_baseUrl . '/mobile/error/error');
    	}

    }

	public function goodsfinishAction()
    {
    	$uid = $this->_user->getId();
    	$goodsId = $this->_request->getParam('goods_id');
    	$position = $this->_request->getParam('position');
    	$payType = $this->_request->getParam('pay');
    	$payTypeStr = 'goods_price_' . $payType;

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	require_once 'Mbll/Kitchen/Cache.php';
        $goodsInfo = Mbll_Kitchen_Cache::getGoods($goodsId);

    	if ($payType != 'gold' && $payType != 'point') {
	      	$this->_redirect($this->_baseUrl . '/mobile/error/error');
	    }

    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $restInfo = $dalRest->getActiveRestaurant($uid);
        $genreLevel = $dalRest->getGenreLv($uid, $goodsInfo['genre']);

        if ($restInfo['genre'] != $goodsInfo['genre']) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        //$goodsInfo['goods_price_gold'] = intval($goodsInfo['goods_price_gold'] * $userInfo['discount'] / 100);
        $goodsInfo['goods_price_point'] = intval($goodsInfo['goods_price_point'] * $userInfo['discount'] / 100);

        if (!$goodsId || !$position) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($goodsInfo['type'] == 0) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	if ($goodsInfo['level'] > $genreLevel) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($goodsInfo[$payTypeStr] <= $userInfo[$payType]) {
    		require_once 'Mbll/Kitchen/Goods.php';
        	$bllGoods = new Mbll_Kitchen_Goods();

        	$setGoods = $restInfo['genre'] == $goodsInfo['genre'] ? 1 : 0;

        	$result = $bllGoods->buyGoods($uid, $payType, $goodsInfo, $position, $setGoods);

        	if ($result) {
        		//access analyse
		        require_once 'Mdal/Kitchen/Access.php';
        		$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();

        		try {
        			$mdalAccess->insertMoney(array('uid' => $uid,
        										   'amount' => $goodsInfo[$payTypeStr],
        										   'type' => $payType == 'point' ? 1 : 3,
        										   'description' => 'buy_goods',
        										   'create_time' => time()));
        		}
        		catch (Exception $e){
		        }

        		//zhaoxh20100206   activity 9
		        require_once 'Mbll/Kitchen/Activity.php';
		        $activity = Mbll_Kitchen_Activity::getActivity(9, $goodsInfo['goods_name'], 'zakka/40x40/' . $goodsInfo['goods_picture']);
		        $aryActivity = explode('|', $activity);

		        require_once 'Bll/Restful.php';
		        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
		        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

        		$this->render();
        	}
        	else {
        		$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/goodslist');
        	}
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/goodsconfirm?goods_id=' . $goodsId . '&position=' . $position . '&pay=' . $payType);
        }


    }

    //goodsActions##end

    //estateActions##start

	public function estatelistAction()
    {
    	$uid = $this->_user->getId();

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];

    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
    	$restInfo = $dalRest->getActiveRestaurant($uid);

        require_once 'Mdal/Kitchen/Recipe.php';
        $dalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();

    	$maxLevel = $dalRest->getMaxLevel($uid);

    	if ($maxLevel < 5) {
    		$this->_redirect($this->_baseUrl . '/mobile/error/error');
    	}

    	$arrGenreEstateEdit = $dalRest->getArrGenreEstate($uid);

    	$cntGenreEstateEdit = count($arrGenreEstateEdit);

    	$arrGenreEstate = array();
    	$arrGenreEstate = array();
        for ($i = 0; $i < $cntGenreEstateEdit; $i++) {
    		$arrGenreEstate[$arrGenreEstateEdit[$i]['genre']] = $arrGenreEstateEdit[$i]['estate'];
    		$arrGenreRecipeCount[$arrGenreEstateEdit[$i]['genre']] = $arrGenreEstateEdit[$i]['recipe_count'];
    	}

        for ($i = 1; $i <= 4; $i++) {
        	if (!$arrGenreEstate[$i]) {
    			$arrGenreEstate[$i] = 0;
    			$arrGenreRecipeCount[$i] = $dalRecipe->getUserRecipeCountByGenre($uid, $i);
        	}
    	}

        $genre = $this->_request->getParam('genre', 0);
        $pageStartEdit = $this->_request->getParam('start',1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 5;

        require_once 'Mdal/Kitchen/Estate.php';
        $dalEstate = Mdal_Kitchen_Estate::getDefaultInstance();
        $estateList = $dalEstate->listEstate($genre, $pageStart, $pageSize);
        $count = $dalEstate->cntListEstate($genre);

        $cntEstateList = count($estateList);
        for ($i = 0; $i < $cntEstateList; $i++) {
        	$estateList[$i]['genreName'] = $this->_getGenreName($estateList[$i]['genre']);
        	$estateList[$i]['genreEstate'] = $arrGenreEstate[$estateList[$i]['genre']];

        	/*
        	if ($arrGenreRecipeCount[$estateList[$i]['up_genre']] >= $estateList[$i]['up_recipe_count'] &&
        	    ($arrGenreEstate[$estateList[$i]['genre']] == $estateList[$i]['estate_id'] - 1 ||
        	    ($arrGenreEstate[$estateList[$i]['genre']] == 0 && $estateList[$i]['estate_id'] == 2))){
        		//can buy  (do not consider point)
            	$estateList[$i]['msgType'] = 0;
        	}
        	else if ($arrGenreRecipeCount[$estateList[$i]['up_genre']] < $estateList[$i]['up_recipe_count'] &&
        	    ($arrGenreEstate[$estateList[$i]['genre']] == $estateList[$i]['estate_id'] - 1 ||
        	    ($arrGenreEstate[$estateList[$i]['genre']] == 0 && $estateList[$i]['estate_id'] == 2))){
        		//need more recipe
        		$estateList[$i]['up_genreName'] = $this->_getGenreName($estateList[$i]['up_genre']);
                $estateList[$i]['msgType'] = 1;
        	}
            else if ($arrGenreEstate[$estateList[$i]['genre']] < $estateList[$i]['estate_id'] - 1 && $arrGenreEstate[$estateList[$i]['genre']] != 0){
            	//need upper estate
        		$estateList[$i]['msgType'] = 2;
        	}
        	else if ($arrGenreEstate[$estateList[$i]['genre']] > $estateList[$i]['estate_id'] - 1 && $arrGenreEstate[$estateList[$i]['genre']] != 0){
        		//lower estate
        		$estateList[$i]['msgType'] = 3;
        	}
            */

        	//1.25 zhaoxh modify
        	//has house of this genre
        	if ($arrGenreEstate[$estateList[$i]['genre']]) {
        		if ($arrGenreEstate[$estateList[$i]['genre']] == $estateList[$i]['estate_id'] - 1) {
        			if ($arrGenreRecipeCount[$estateList[$i]['up_genre']] >= $estateList[$i]['up_recipe_count']) {
        				$estateList[$i]['msgType'] = 0;
        			}
        			else {
        				$estateList[$i]['up_genreName'] = $this->_getGenreName($estateList[$i]['up_genre']);
        				$estateList[$i]['msgType'] = 1;
        			}
        		}
        		else if ($arrGenreEstate[$estateList[$i]['genre']] < $estateList[$i]['estate_id'] - 1) {
        			$estateList[$i]['msgType'] = 2;
        		}
        		else {
        			$estateList[$i]['msgType'] = 3;
        		}
        	}
        	//do not has house of this genre
        	else {
        		if ($estateList[$i]['estate_id'] == 2) {
        			if ($arrGenreRecipeCount[$estateList[$i]['up_genre']] >= $estateList[$i]['up_recipe_count']) {
        				$estateList[$i]['msgType'] = 0;
        			}
        			else {
        				$estateList[$i]['up_genreName'] = $this->_getGenreName($estateList[$i]['up_genre']);
        				$estateList[$i]['msgType'] = 1;
        			}
        		}
        		else {
        			$estateList[$i]['msgType'] = 2;
        		}
        	}
        }

    	$this->view->estateList = $estateList;
        $this->view->count = $count;

        $this->view->genre = $genre;
        //$this->view->inUseGenre = $inUseGenre;
        $this->view->start = $pageStartEdit;
    	$this->view->startPrev = max(1,$pageStartEdit - 5);
    	$this->view->startNext = $pageStartEdit + 5;

    	//$this->view->level = $maxLevel;
    	$this->view->actionName = 'estatelist';
    	$this->render();
    }

    function _confirmDataEstate()
    {
    	$uid = $this->_user->getId();
    	$estateId = $this->_request->getParam('estate_id');
    	$genre = $this->_request->getParam('genre');
    	$payType = 'point';
    	$payTypeStr = 'estate_price_point';

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->point = $userInfo['point'];

    	require_once 'Mdal/Kitchen/Estate.php';
        $dalEstate = Mdal_Kitchen_Estate::getDefaultInstance();
        $estateInfo = $dalEstate->getEstate($estateId, $genre);

        if ($userInfo['discount'] != 100 && $payType == 'point') {
        	$estateInfo['disct'] = intval($estateInfo[$payTypeStr] * (100 - $userInfo['discount']) / 100);
        }
        $estateInfo['money_left'] = $userInfo[$payType] - $estateInfo[$payTypeStr] + $estateInfo['disct'];

        require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();

    	$restOneInfo = $dalRest->getOneRestaurant($uid, $estateInfo['up_genre']);
        $restTwoInfo = $dalRest->getOneRestaurant($uid, $genre);

    	$maxLevel = $dalRest->getMaxLevel($uid);
    	if ($maxLevel < 5) {
    		$this->_redirect($this->_baseUrl . '/mobile/error/error');
    	}

        if (!$estateId || !$genre) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	if ($restOneInfo['recipe_count'] < $estateInfo['up_recipe_count']) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	if ($restTwoInfo['estate'] == $estateId) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/estatelist');
        }

        if ($estateId == 2 && $restTwoInfo['estate']) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	if ($estateId > 2 && $restTwoInfo['estate'] != $estateId - 1) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $this->view->estateInfo = $estateInfo;
        $this->view->payType = $payType;
        $genreName = $this->_getGenreName($estateInfo['genre']);

        $this->view->genreName = $genreName;
    }

	public function estateconfirmpointAction()
    {
    	$uid = $this->_user->getId();
    	$estateId = $this->_request->getParam('estate_id');
    	$genre = $this->_request->getParam('genre');

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->point = $userInfo['point'];

    	require_once 'Mdal/Kitchen/Estate.php';
        $dalEstate = Mdal_Kitchen_Estate::getDefaultInstance();
        $estateInfo = $dalEstate->getEstate($estateId, $genre);
        $estateInfo['estate_price_point'] = intval($estateInfo['estate_price_point'] * $userInfo['discount'] / 100);

        if (!$estateId || !$genre) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($estateInfo['estate_price_point'] <= $userInfo['point']) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/estateconfirmpointok?estate_id=' . $estateId . '&genre=' . $genre);
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/estateconfirmpointfail?estate_id=' . $estateId . '&genre=' . $genre);
        }

    	$this->render();
    }


    public function estateconfirmpointokAction()
    {
    	$this->_confirmDataEstate();

    	$this->render();
    }

    public function estateconfirmpointfailAction()
    {
    	$this->_confirmDataEstate();

    	$this->render();
    }

	public function estatefinishAction()
    {
    	$uid = $this->_user->getId();
    	$estateId = $this->_request->getParam('estate_id');
    	$genre = $this->_request->getParam('genre');
    	$payType = $this->_request->getParam('pay');
    	$payTypeStr = 'estate_price_' . $payType;

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	require_once 'Mdal/Kitchen/Estate.php';
        $dalEstate = Mdal_Kitchen_Estate::getDefaultInstance();
        $estateInfo = $dalEstate->getEstate($estateId, $genre);
        $estateInfo['estate_price_point'] = intval($estateInfo['estate_price_point'] * $userInfo['discount'] / 100);

        require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $restInfo = $dalRest->getActiveRestaurant($uid);
        //careful params
        $restOneInfo = $dalRest->getOneRestaurant($uid, $estateInfo['up_genre']);
        $restTwoInfo = $dalRest->getOneRestaurant($uid, $genre);

    	$maxLevel = $dalRest->getMaxLevel($uid);
    	if ($maxLevel < 5) {
    		$this->_redirect($this->_baseUrl . '/mobile/error/error');
    	}

        if (!$estateId || !$genre) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	if ($restOneInfo['recipe_count'] < $estateInfo['up_recipe_count']) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	if ($restTwoInfo['estate'] == $estateId) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/estatelist');
        }

        if ($estateId == 2 && $restTwoInfo['estate']) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	if ($estateId > 2 && $restTwoInfo['estate'] != $estateId - 1) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($estateInfo[$payTypeStr] <= $userInfo[$payType]) {
    		require_once 'Mbll/Kitchen/Estate.php';
        	$bllEstate = new Mbll_Kitchen_Estate();

        	$result = $bllEstate->buyEstate($uid, $payType, $estateInfo, $restOneInfo);

        	if ($result) {
        		$this->view->genreNow = $restInfo['genre'];
        		$this->view->genreBuy = $genre;

        		//access analyse
		        require_once 'Mdal/Kitchen/Access.php';
        		$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
        		try {
        			$mdalAccess->insertMoney(array('uid' => $uid,
        										   'amount' => $estateInfo[$payTypeStr],
        										   'type' => $payType == 'point' ? 1 : 3,
        										   'description' => 'buy_estate',
        										   'create_time' => time()));
        		}
        		catch (Exception $e){
		        }

        		//zhaoxh20100206   activity 9
		        require_once 'Mbll/Kitchen/Activity.php';
		        $activity = Mbll_Kitchen_Activity::getActivity(9, $estateInfo['estate_name'], 'estate/40x40/' . $estateInfo['estate_picture']);
		        $aryActivity = explode('|', $activity);

		        require_once 'Bll/Restful.php';
		        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
		        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

        		$this->render();
        	}
        	else {
        		$this->_redirect($this->_baseUrl . '/mobile/error/error');
        	}
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/estateconfirm' . $payType. '?estate_id=' . $estateId);
        }
    }
    //estateActions##end



    //itemActions##start
	public function itemlistAction()
    {
    	$uid = $this->_user->getId();

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];

        $pageStartEdit = $this->_request->getParam('start',1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 5;

        require_once 'Mdal/Kitchen/Item.php';
        $dalItem = Mdal_Kitchen_Item::getDefaultInstance();
        $itemList = $dalItem->listItem($pageStart, $pageSize);
        $count = $dalItem->cntListItem();

    	$this->view->itemList = $itemList;
        $this->view->count = $count;

        $this->view->start = $pageStartEdit;
    	$this->view->startPrev = max(1,$pageStartEdit - $pageSize);
    	$this->view->startNext = $pageStartEdit + $pageSize;

    	$this->view->actionName = 'itemlist';
    	$this->render();
    }

    public function itemconfirmAction()
    {
    	$uid = $this->_user->getId();
    	$itemId = $this->_request->getParam('item_id');
    	$payType = $this->_request->getParam('pay');
    	$payTypeStr = 'item_price_' . $payType;

        if (!$itemId) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        require_once 'Mbll/Kitchen/Cache.php';
        $itemInfo = Mbll_Kitchen_Cache::getItem($itemId);

        if ($payType != 'gold' && $payType != 'point') {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($itemInfo['type'] == 0) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($payType == 'point' && $itemInfo['type'] == 3){
        	//gold only items
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        /*
        if ($itemId == 19) {
            require_once 'Mdal/Kitchen/Restaurant.php';
	        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
	        $maxLevel = $dalRest->getMaxLevel($uid);
	        if ($maxLevel < 10) {
	        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
	        }
    	}
		*/

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];

        if ($userInfo['discount'] != 100 && $payType == 'point') {
        	$itemInfo['disct'] = intval($itemInfo[$payTypeStr] * (100 - $userInfo['discount']) / 100);
        }
        $itemInfo['money_left'] = $userInfo[$payType] - $itemInfo[$payTypeStr] + $itemInfo['disct'];
        $this->view->result = $itemInfo['money_left'] >= 0;

        $this->view->payType = $payType;
        $this->view->itemInfo = $itemInfo;

    	$this->render();
    }

	public function itemfinishAction()
    {
    	$uid = $this->_user->getId();
    	$itemId = $this->_request->getParam('item_id');
    	$payType = $this->_request->getParam('pay');
    	$payTypeStr = 'item_price_' . $payType;

    	if ($itemId == 19) {
    		$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/beautyfinish?item_id=' . $itemId . '&pay=' . $payType);
    	}

        if (!$itemId) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        require_once 'Mbll/Kitchen/Cache.php';
        $itemInfo = Mbll_Kitchen_Cache::getItem($itemId);

        if ($payType != 'gold' && $payType != 'point') {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($itemInfo['type'] == 0) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($payType == 'point' && $itemInfo['type'] == 3){
        	//gold only items
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	//require_once 'Mbll/Kitchen/Cache.php';
        //$itemInfo = Mbll_Kitchen_Cache::getItem($itemId);
        //$itemInfo['item_price_gold'] = intval($itemInfo['item_price_gold'] * $userInfo['discount'] / 100);
        $itemInfo['item_price_point'] = intval($itemInfo['item_price_point'] * $userInfo['discount'] / 100);

        if ($itemInfo[$payTypeStr] <= $userInfo[$payType]) {
    		require_once 'Mbll/Kitchen/Item.php';
        	$bllItem = new Mbll_Kitchen_Item();

        	$result = $bllItem->buyItem($uid, $payType, $itemInfo);

        	if ($result) {
        	    //access analyse
		        require_once 'Mdal/Kitchen/Access.php';
        		$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
        		try {
        			$mdalAccess->insertMoney(array('uid' => $uid,
        										   'amount' => $itemInfo[$payTypeStr],
        										   'type' => $payType == 'point' ? 1 : 3,
        										   'description' => 'buy_item',
        										   'create_time' => time()));
        		}
        		catch (Exception $e){
		        }

        		//zhaoxh20100206   activity 9
		        require_once 'Mbll/Kitchen/Activity.php';
		        $activity = Mbll_Kitchen_Activity::getActivity(9, $itemInfo['item_name'], 'yorozu/40x40/' . $itemInfo['item_picture']);
		        $aryActivity = explode('|', $activity);

		        require_once 'Bll/Restful.php';
		        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
		        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

        		$this->render();
        	}
        	else {
        		$this->_redirect($this->_baseUrl . '/mobile/error/error');
        	}
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/itemconfirm?item_id=' . $itemId . '&pay=' . $payType);
        }
    }

    //itemActions##end



    //beautyActions##start
	public function beautylistAction()
    {
    	$uid = $this->_user->getId();

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
		
    	/*
        require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $maxLevel = $dalRest->getMaxLevel($uid);
        
        if ($maxLevel < 10) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
		*/		

        require_once 'Mbll/Kitchen/Cache.php';
        $itemInfo = Mbll_Kitchen_Cache::getItem(19);

    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];
        $this->view->itemInfo = $itemInfo;
    	$this->render();
    }

	public function beautyfinishAction()
    {
    	$uid = $this->_user->getId();
    	$itemId = $this->_request->getParam('item_id');
    	$payType = $this->_request->getParam('pay');
    	$payTypeStr = 'item_price_' . $payType;

    	if ($itemId != 19) {
    		$this->_redirect($this->_baseUrl . '/mobile/error/error');
    	}

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	require_once 'Mbll/Kitchen/Cache.php';
        $itemInfo = Mbll_Kitchen_Cache::getItem($itemId);
        //$itemInfo['item_price_gold'] = intval($itemInfo['item_price_gold'] * $userInfo['discount'] / 100);
    	if ($userInfo['discount'] != 100) {
        	$itemInfo['item_price_point_show'] = $itemInfo['item_price_point'] . 'P-' . strval($itemInfo['item_price_point'] * (100 - $userInfo['discount']) / 100);
        	$itemInfo['item_price_point'] = intval($itemInfo['item_price_point'] * $userInfo['discount'] / 100);
        }
        else {
        	$itemInfo['item_price_point_show'] = $itemInfo['item_price_point'];
        }

        require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $maxLevel = $dalRest->getMaxLevel($uid);

        if (!$itemId) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($itemInfo[$payTypeStr] <= $userInfo[$payType]) {
    		require_once 'Mbll/Kitchen/Item.php';
        	$bllItem = new Mbll_Kitchen_Item();

        	$result = $bllItem->buyItem($uid, $payType, $itemInfo, 'beauty');

        	$this->view->uid = $userInfo['uid'];
        	$this->view->changeChara = $userInfo['allow_editchara'];
        	if ($result) {
        	    //access analyse
		        require_once 'Mdal/Kitchen/Access.php';
        		$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
        		try {
        			$mdalAccess->insertMoney(array('uid' => $uid,
        										   'amount' => $itemInfo[$payTypeStr],
        										   'type' => $payType == 'point' ? 1 : 3,
        										   'description' => 'buy_beauty',
        										   'create_time' => time()));
        		}
        		catch (Exception $e){
		        }

        		//zhaoxh20100206   activity 9
		        require_once 'Mbll/Kitchen/Activity.php';
		        $activity = Mbll_Kitchen_Activity::getActivity(9, $itemInfo['item_name'], 'yorozu/40x40/' . $itemInfo['item_picture']);
		        $aryActivity = explode('|', $activity);

		        require_once 'Bll/Restful.php';
		        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
		        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

        		$this->render();
        	}
        	else {
        		$this->_redirect($this->_baseUrl . '/mobile/error/error');
        	}
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/itemconfirm?item_id=' . $itemId . '&pay=' . $payType);
        }
    }

    public function beautyuseAction()
    {
    	$uid = $this->_user->getId();
    	require_once 'Mdal/Kitchen/Item.php';
        $dalItem = Mdal_Kitchen_Item::getDefaultInstance();

        $hasItemCnt = $dalItem->hasItemCnt($uid, 19);

        if ($hasItemCnt > 0) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenitem/useitem?CF_itemId=19');
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
    }

    //beautyActions##end

    //giftActions##start
	public function giftlistAction()
    {
    	$uid = $this->_user->getId();

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];

        $pageStartEdit = $this->_request->getParam('start',1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 5;
        $type = 1;

        require_once 'Mdal/Kitchen/Gift.php';
        $dalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        $giftList = $dalGift->listGift($pageStart, $pageSize, $type);
        $count = $dalGift->cntListGift($type);

    	$this->view->giftList = $giftList;
        $this->view->count = $count;

        $this->view->start = $pageStartEdit;
    	$this->view->startPrev = max(1,$pageStartEdit - 5);
    	$this->view->startNext = $pageStartEdit + 5;

    	$this->view->actionName = 'giftlist';
    	$this->render();
    }

	public function giftconfirmAction()
    {
    	$uid = $this->_user->getId();
    	$giftId = $this->_request->getParam('gift_id');
    	$payType = $this->_request->getParam('pay');
    	$payTypeStr = 'price_' . $payType;

    	if ($payType != 'gold' && $payType != 'point') {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];
    	$this->view->payType = $payType;

    	require_once 'Mbll/Kitchen/Cache.php';
        $giftInfo = Mbll_Kitchen_Cache::getGift($giftId);

        if ($userInfo['discount'] != 100 && $payType == 'point') {
        	$giftInfo['disct'] = intval($giftInfo[$payTypeStr] * (100 - $userInfo['discount']) / 100);
        }
        $giftInfo['money_left'] = $userInfo[$payType] - $giftInfo[$payTypeStr] + $giftInfo['disct'];
        $this->view->result = $giftInfo['money_left'] >= 0;

        if (!$giftId || !$giftInfo['type']) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $this->view->giftInfo = $giftInfo;

        //get gift details
        $unitList = explode(',', $giftInfo['food']);
        require_once 'Mdal/Kitchen/Food.php';
        $dalFood = Mdal_Kitchen_Food::getDefaultInstance();
        $cntUnit = count($unitList);
        for ($i = 0; $i < $cntUnit; $i++) {
        	$unitList[$i] = $dalFood->getFoodUnit($unitList[$i]);
        }

        require_once 'Mdal/Kitchen/Goods.php';
        $dalGoods = Mdal_Kitchen_Goods::getDefaultInstance();

        $unitList[count($unitList)] = $dalGoods->getGoodsUnit($giftInfo['goods']);

        $this->view->unitList = $unitList;


    	$this->render();
    }

	public function giftfinishAction()
    {
    	$uid = $this->_user->getId();
    	$giftId = $this->_request->getParam('gift_id');
    	$payType = $this->_request->getParam('pay');
    	$payTypeStr = 'price_' . $payType;

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	require_once 'Mbll/Kitchen/Cache.php';
        $giftInfo = Mbll_Kitchen_Cache::getGift($giftId);

        //$giftInfo['price_gold'] = intval($giftInfo['price_gold'] * $userInfo['discount'] / 100);
        $giftInfo['price_point'] = intval($giftInfo['price_point'] * $userInfo['discount'] / 100);

        if (!$giftId) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        if ($giftInfo[$payTypeStr] <= $userInfo[$payType]) {
    		require_once 'Mbll/Kitchen/Gift.php';
        	$bllGift = new Mbll_Kitchen_Gift();

        	$result = $bllGift->buyGift($uid, $payType, $giftInfo);

        	if ($result) {
        	    //access analyse
		        require_once 'Mdal/Kitchen/Access.php';
        		$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
        		try {
        			$mdalAccess->insertMoney(array('uid' => $uid,
        										   'amount' => $giftInfo[$payTypeStr],
        										   'type' => $payType == 'point' ? 1 : 3,
        										   'description' => 'buy_gift',
        										   'create_time' => time()));
        		}
        		catch (Exception $e){
		        }

        		$this->render();
        	}
        	else {
        		$this->_redirect($this->_baseUrl . '/mobile/error/error');
        	}
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/giftconfirm?gift_id=' . $giftId . '&pay=' . $payType);
        }
    }


    //giftActions##end
    //fortuneActions##start
    public function gachalistAction()
    {
    	$uid = $this->_user->getId();

        $pageStartEdit = $this->_request->getParam('start',1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 10;

        require_once 'Mdal/Kitchen/Gacha.php';
        $dalGacha = Mdal_Kitchen_Gacha::getDefaultInstance();
        $gachaList = $dalGacha->listGacha($pageStart, $pageSize);
        $count = $dalGacha->cntListGacha();
        $userGachaInfo = $dalGacha->getUserGacha($uid);

    	$this->view->gachaList = $gachaList;
        $this->view->count = $count;
    	$this->view->userGachaInfo = $userGachaInfo;

        $this->view->start = $pageStartEdit;
    	$this->view->startPrev = max(1,$pageStartEdit - $pageSize);
    	$this->view->startNext = $pageStartEdit + $pageSize;

    	$this->view->actionName = 'gachalist';
    	$this->render();
    }

    public function gachainfoAction()
    {
    	$uid = $this->_user->getId();

    	$gacha_id = $this->_request->getParam('gacha_id');

        require_once 'Mdal/Kitchen/Gacha.php';
        $dalGacha = Mdal_Kitchen_Gacha::getDefaultInstance();
        $gachaInfo = $dalGacha->getGacha($gacha_id);

    	if (!$gachaInfo) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $pctArr = explode(',', $gachaInfo['percent']);
        $gachaDetail = array();
        for ($i = 1; $i <= count($pctArr); $i++) {
        	$gachaDetail[$i] = $dalGacha->getGachaDetail($gachaInfo['id' . $i], $gachaInfo['table' . $i]);
        }

        $userGachaInfo = $dalGacha->getUserGacha($uid);

    	$this->view->gachaInfo = $gachaInfo;
        $this->view->gachaDetail = $gachaDetail;
    	$this->view->userGachaInfo = $userGachaInfo;
    	$this->view->osUID = $this->_USER_ID;
    	$this->render();
    }

	public function gachaconfirmAction()
    {
    	$uid = $this->_user->getId();

    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	$this->view->gold = $userInfo['gold'];

    	$gacha_id = $this->_request->getParam('gacha_id');

        require_once 'Mdal/Kitchen/Gacha.php';
        $dalGacha = Mdal_Kitchen_Gacha::getDefaultInstance();
        $gachaInfo = $dalGacha->getGacha($gacha_id);

        //$gachaInfo['gacha_price_gold'] = intval($gachaInfo['gacha_price_gold'] * $userInfo['discount'] / 100);

        if (!$gachaInfo) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $pctArr = explode(',', $gachaInfo['percent']);
        $gachaDetail = array();
        for ($i = 1; $i <= count($pctArr); $i++) {
        	$gachaDetail[$i] = $dalGacha->getGachaDetail($gachaInfo['id' . $i], $gachaInfo['table' . $i]);
        }

    	$this->view->gachaInfo = $gachaInfo;
        $this->view->gachaDetail = $gachaDetail;
    	$this->view->osUID = $this->_USER_ID;
    	$this->render();
    }

	public function gachafinishAction()
    {
        $uid = $this->_user->getId();

        require_once 'Mdal/Kitchen/Gacha.php';
        $dalGacha = Mdal_Kitchen_Gacha::getDefaultInstance();
        $userGacha = $dalGacha->getUserGacha($uid);
    	if ($userGacha['playing_gacha_id'] == 0 ) {
    		$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/gachalist');
    	}

        $gachaInfo = $dalGacha->getGacha($userGacha['playing_gacha_id']);

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	//$gachaInfo['gacha_price_gold'] = intval($gachaInfo['gacha_price_gold'] * $userInfo['discount'] / 100);

    	if ($userGacha['playing_pay'] == 2 && $userInfo['gold'] < $gachaInfo['gacha_price_gold'] ) {
    		$this->_redirect($this->_baseUrl . '/mobile/error/error');
    	}
    	if ($userGacha['playing_pay'] == 1 && $userGacha['gacha_count'] < 1 ) {
    		$this->_redirect($this->_baseUrl . '/mobile/error/error');
    	}

        require_once 'Mbll/Kitchen/Gacha.php';
        $bllGacha = new Mbll_Kitchen_Gacha();

        $re = $bllGacha->resultGacha($uid, $userGacha, $gachaInfo);
		if ($re && $userGacha['playing_pay'] == 2) {
			//access analyse
	        require_once 'Mdal/Kitchen/Access.php';
        	$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
        	try {
        		$mdalAccess->insertMoney(array('uid' => $uid,
        									   'amount' => $gachaInfo['gacha_price_gold'],
        									   'type' => 3,
        									   'description' => 'buy_gacha',
        									   'create_time' => time()));
        	}
        	catch (Exception $e){
	        }
		}

        $this->view->reName = $re['name'];
		$this->view->rePath = $re['picfolder'] . "/130x130/" . $re['picture'];
		/*
        if ($re['picfolder'] == 'food') {
			$this->view->rePath = $re['picfolder'] . "/130x130/" . $re['picture'];
		}
		else if ($re['picfolder'] == 'zakka') {
			$this->view->rePath = $re['picfolder'] . "/130x130/" . $re['picture'];
		}
		else if ($re['picfolder'] == 'yorozu') {
			$this->view->rePath = $re['picfolder'] . "/130x130/" . $re['picture'];
		}
		*/
    	$this->render();
    }

    //fortuneActions##end

    function _getGenreName($genre)
    {
        require_once 'Mbll/Kitchen/Restaurant.php';
        $bllRest = new Mbll_Kitchen_Restaurant();

        return $bllRest->getGenreNameById($genre);
    	/*
    	switch ($genre) {
    		case 1:
    			$genreName = '洋食';
    			break;
    		case 2:
    			$genreName = 'ﾘｽﾄﾗﾝﾃ';
    			break;
			case 3:
    			$genreName = '和食';
    			break;
    		case 4:
    			$genreName = '中華';
    			break;
    		case 5:
    			$genreName = 'ｴｽﾆｯｸ';
    			break;
    		case 6:
    			$genreName = 'ﾒｷｼｶﾝ';
    			break;
    		default:
    			break;
    	}
    	return $genreName;
        */
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