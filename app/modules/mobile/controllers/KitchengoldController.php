<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile kitchengold Controller(modules/mobile/controllers/KitchenshopController.php)
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-3-1
 */
class KitchengoldController extends MyLib_Zend_Controller_Action_Mobile
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
    
    //bankActions##start
    public function goldlistAction()
    {
    	$uid = $this->_user->getId();
        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	
    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];
    	
    	require_once 'Mdal/Kitchen/Gold.php';
        $dalGold = Mdal_Kitchen_Gold::getDefaultInstance();
        $goldList = $dalGold->getAllGold();
        $count = count($goldList);
    	$this->view->goldList = $goldList;
        $this->view->count = $count;
    	$this->render();
    }
    
    public function goldconfirmAction()
    {
    	$uid = $this->_user->getId();
    	
        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	
    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];
    	
    	$gold_id = $this->_request->getParam('CF_gold_id');
    	require_once 'Mdal/Kitchen/Gold.php';
        $dalGold = Mdal_Kitchen_Gold::getDefaultInstance();
        $goldInfo = $dalGold->getGold($gold_id);
        
        $this->view->goldInfo = $goldInfo;
    	
    	$this->render();
    }
    
    public function goldfinishAction()
    {
    	$uid = $this->_user->getId();
    	
        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	
    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];
    	
    	$gold_id = $this->_request->getParam('CF_gold_id');
    	require_once 'Mdal/Kitchen/Gold.php';
        $dalGold = Mdal_Kitchen_Gold::getDefaultInstance();
        $goldInfo = $dalGold->getGold($gold_id);
        
        $this->view->goldInfo = $goldInfo;
    	
    	$this->render();
    }
    
    
    
    /**
     * buy gold part1
     *
     */
    public function buygoldsendAction()
    {
        $uid = $this->_user->getId();
        $gold_id = $this->getParam("CF_gold_id");

        if (empty($gold_id)) {
            exit(0);
        }

		$gold_id = $this->_request->getParam('CF_gold_id');
    	require_once 'Mdal/Kitchen/Gold.php';
        $mdalGold = Mdal_Kitchen_Gold::getDefaultInstance();
        $goldInfo = $mdalGold->getGold($gold_id);

        $payment = array('callback_url' => Zend_Registry::get('host') . '/mobile/kitchengold/buygoldreceive',
                         'finish_url'   => Zend_Registry::get('host') . '/mobile/kitchengold/goldlist',
                         'item'         => array(array('id'    => $goldInfo['id'],
                                                       'name'  => $goldInfo['gold_name'],
                                                       'point' => $goldInfo['gold_price'])));

        //pay start
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
        $data = $restful->createPoint($payment, $payment['item']);
        
        /*
        $data['id'] = '213dfdsfsdfdf';
        $data['updated'] = '1263278856';
        $data['link'] = $payment['callback_url'];
        */
        
		//buy item fail
        if(empty($data)) {
            $this->_redirect($payment['finish_url']);
        }

        
        $pay = array('point_code' => $data['id'],
                     'uid' => $uid,
                     'gold_id' => $gold_id,
                     'create_time' => $data['updated']);

        require_once 'Mbll/Kitchen/Gold.php';
        $mbllGold = new Mbll_Kitchen_Gold();
        $result = $mbllGold->insertGoldLog($pay);

        if ($result) {
            $this->_redirect($data['link']);
        }
        else {
            $this->_redirect($payment['finish_url']);
        }

    }

    /*
     * buy gold part2
     */
    public function buygoldreceiveAction()
    {
        ob_end_clean();
        ob_start();
        ini_set('default_charset', null);
        header('HTTP/1.1 200 OK');
        header('Status: 200');
        header('Content-Type: text/plain');
		
        /*
        $point_code = '213dfdsfsdfdf';
        $pay_status = 10;
        */
        
        $point_code = $this->getParam('point_code');
        //status = 20, cancel, status = 10 buy submit
        $pay_status = $this->getParam('status', 20);
        //$updated = $this->getParam('updated');

        if (empty($point_code) || $pay_status == 20) {
            require_once 'Mdal/Kitchen/Gold.php';
        	$mdalGold = Mdal_Kitchen_Gold::getDefaultInstance();
            $mdalGold->updateGoldLogStatus($point_code, 2, time());

            echo 'OK';
            exit(0);
        }

        require_once 'Mbll/Kitchen/Gold.php';
        $mbllGold = new Mbll_Kitchen_Gold();
        $result = $mbllGold->buyGoldSubmit($point_code);

        echo $result == 1 ? 'OK' : 'CANCEL';
        exit(0);
    }
    
    
    //bankActions##end
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