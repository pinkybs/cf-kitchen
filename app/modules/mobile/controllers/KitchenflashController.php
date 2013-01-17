<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';
require_once 'Zend/Http/Client.php';

/**
 * Mobile Kitchen Flash Controller(modules/mobile/controllers/KitchenflashController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/12/31
 */
class KitchenflashController extends MyLib_Zend_Controller_Action_Mobile
{

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
     * deipatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();
    }

    /**
     * school flash action
     *
     */
    public function changcharaAction()
    {
        $uid = $this->_user->getId();

        // get swf
        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swf = Mbll_Kitchen_FlashCache::getChangeChara($uid, $mixiUrl);

        if ( !$swf ) {
            $this->_redirect($mixiUrl . urlencode(Zend_Registry::get('host') . '/mobile/error/error'));
            return;
        }
        
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        echo $swf;
        exit(0);
    }

    /**
     * profile action
     *
     */
    public function profileAction()
    {
        $uid = $this->_user->getId();

        // get swf
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swfPath = Mbll_Kitchen_FlashCache::getProfile($uid);
		$swf = @file_get_contents($swfPath);

        if ( !$swf ) {
            $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
            $this->_redirect($mixiUrl . urlencode(Zend_Registry::get('host') . '/mobile/error/error'));
            return;
        }
		
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");

        echo $swf;
        exit(0);
    }

	/**
     * kitchen flash action-kitchen
     *
     */
    public function kitchenAction()
    {        
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        
        // get swf
        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swf = Mbll_Kitchen_FlashCache::getKitchen($uid, $profileUid, $mixiUrl);
        
        if ( !$swf ) {
            $this->_redirect($mixiUrl . urlencode(Zend_Registry::get('host') . '/mobile/error/error'));
            return;
        }
        
        //$this->render();
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        echo $swf;
        exit(0);
    }

	/**
     * kitchen flash action-selectgenre
     *
     */
    public function selectgenreAction()
    {
        $uid = $this->_user->getId();
		$kitchenId = $this->getParam('CF_kitchen_id');
		
		$select = $this->getParam('select');
        $genre = $this->getParam('genre');//2魚類 3肉類 4乳卵・豆 5調味料 6穀類 7野菜 8フルーツ
        $food = $this->getParam('food');

        $reset = $this->getParam('CF_reset');
        if (!empty($reset)) {
        	$_SESSION['kitchen_kit_selectfood'] = null;
        	unset($_SESSION['kitchen_kit_selectfood']);
        	//info_log('unset_session', 'recipeconfrim');
        }
        // get swf
        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swf = Mbll_Kitchen_FlashCache::getSelectGenre($uid, $kitchenId, $select, $genre, $food, $mixiUrl, $this->_APP_ID);

        if ( !$swf ) {
            $this->_redirect($mixiUrl . urlencode(Zend_Registry::get('host') . '/mobile/error/error'));
            return;
        }
        
        //$this->render();
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        echo $swf;
        exit(0);
    }

	/**
     * kitchen flash action-selectfood
     *
     */
    public function selectfoodAction()
    {
    	$mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&url=' : '/?url=');
        $uid = $this->_user->getId();
        $select = $this->getParam('select', 1);
        $genre = $this->getParam('genre', 2);//2魚類 3肉類 4乳卵・豆 5調味料 6穀類 7野菜 8フルーツ
       
    	$pageType = $this->getParam('type');
    	
        if ('mix' == $pageType) {
        	//$_SESSION['kitchen_kit_selectfood'] = null;
			//unset($_SESSION['kitchen_kit_selectfood']);
        	$appUrl = Zend_Registry::get('host') . '/mobile/kitchenrecipe/';
        	$kitchenId = $this->getParam('CF_kitchen_id');
        	
        	$food1 = $this->getParam('f1');
        	$food2 = $this->getParam('f2');
        	$food3 = $this->getParam('f3');
        	$food4 = $this->getParam('f4');
        	$this->_redirect($mixiUrl . urlencode($appUrl . 'recipeconfirm?CF_kitchen_id=' . $kitchenId
        					. '&CF_food1=' . $food1 . '&CF_food2=' . $food2 . '&CF_food3=' . $food3 . '&CF_food4=' . $food4));
        	return;
        }
       
        // get swf
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swf = Mbll_Kitchen_FlashCache::getSelectFood($uid, $select, $genre, $mixiUrl, $this->_APP_ID);
   
        if ( !$swf ) {
            $_SESSION['kitchen_kit_selectfood'] = null;
            unset($_SESSION['kitchen_kit_selectfood']);
            $this->_redirect($mixiUrl . urlencode(Zend_Registry::get('host') . '/mobile/error/error'));
            return;
        }
        
        //$this->render();
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        //header("Content-Encoding: gzip");
        echo $swf;
        exit(0);
    }

    public function setgoodsAction()
    {
        $uid = $this->_user->getId();

        $goodsId = $this->getParam('goodsId');

        $pay = $this->getParam('pay');

        if (!$goodsId) {
        	exit(0);
        }
        //want to buy it 
        if ($pay != 'gold' && $pay != 'point' && substr($goodsId,3,1) == 1) {
        	exit(0);
        }
        /*
        //want to send it 
        if (strlen($goodsId) > 4 && substr($goodsId,3,1) != 3) {
        	exit(0);
        }
		*/

        // get swf
        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swf = Mbll_Kitchen_FlashCache::getSetGoods($uid, $goodsId, $pay, $mixiUrl);

        if ( !$swf ) {
            $this->_redirect($mixiUrl . urlencode(Zend_Registry::get('host') . '/mobile/error/error'));
            return;
        }
        
        //$this->render();
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        echo $swf;
        exit(0);
    }

    public function gachaAction()
    {
        $uid = $this->_user->getId();
        $gacha_id = $this->getParam('gacha_id');
        $pay = $this->getParam('pay');

        if (!$gacha_id || ($pay != 1 && $pay != 2)) {
        	exit(0);
        }

        require_once 'Mdal/Kitchen/Gacha.php';
        $dalGacha = Mdal_Kitchen_Gacha::getDefaultInstance();
        $gachaInfo = $dalGacha->getGacha($gacha_id);
        $userGacha = $dalGacha->getUserGacha($uid);

        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);

    	if ( $pay == 2 && $userInfo['gold'] < $gachaInfo['gacha_price_gold'] ) {
    		exit(0);
    	}
    	if ( $pay == 1 && $userGacha['gacha_count'] < 1 ) {
    		exit(0);
    	}

        $userGachaEdit = $userGacha;

        $userGachaEdit['playing_gacha_id'] = $gacha_id;
        $userGachaEdit['playing_pay'] = $pay;

        $dalGacha->updateUserGacha($userGachaEdit, $uid);

        // get swf
        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swf = Mbll_Kitchen_FlashCache::getGacha($uid, $mixiUrl);

        if ( !$swf ) {
            $this->_redirect($mixiUrl . urlencode(Zend_Registry::get('host') . '/mobile/error/error'));
            return;
        }
        
        //$this->render();
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        echo $swf;
        exit(0);
    }

 	/**
     * school flash call back action
     *
     */
    public function flashfwdAction()
    {
        $uid = $this->_user->getId();
        $parameter = $this->getParam('CF_fwd');
        if (empty($parameter) || strlen($parameter) < 4) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }
        $aryWeekDay = array(1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat');
        $strwday = substr($parameter, 0, 3);
        $wday = array_search($strwday, $aryWeekDay);
        $part = (int)(substr($parameter, 3));
        if (empty($wday) || empty($part)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }

        require_once 'Mdal/School/Timepart.php';
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
        $rowNowClass = $mdalTimepart->getTimepartScheduleByPk($uid, $wday, $part);
        if (!empty($rowNowClass)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/class?CF_cid=' . $rowNowClass['cid']);
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/school/classnameadd?CF_wday=' . $wday . '&CF_part=' . $part);
        }
        return;
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
        return $this->_redirect($this->_baseUrl . '/mobile/error/notfound');
    }
}