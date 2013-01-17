<?php
/**
 * Admin Mykitchen Controller(modules/admin/controllers/Admin_MykitchenController.php)
 * Mixi Admin Mykitchen Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2010/04/2    xial
 */
class Admin_MykitchenController extends MyLib_Zend_Controller_Action_Admin
{
   /**
     * admin website base URL
     * @var string
     */
    protected $_baseUrl;

    /**
     * page init
     *
     */
    public function postInit()
    {
        $this->_baseUrl = $this->_request->getBaseUrl();
        $this->view->baseUrl = $this->_baseUrl;
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');
    }

    public function preDispatch()
    {
        //app list
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        $result = $dalApp->getAppList($this->_user->uid, 1, 10);
        $this->view->appList = $result;
    }

    public function indexAction()
    {
        $this->_redirect($this->_baseUrl . 'mykitchen/usersearch');
        return;
    }

   /**
     * user search action
     *
     */
    public function usersearchAction()
    {
        $uid = $this->_request->getParam('txtUid');
        $name = $this->_request->getParam('txtName');

        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 20);

        $dalMykitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        $uids = '';
        if ($name && empty($uid)) {
        	$uids = $dalMykitchen->getUidByLikeName($name);
        }

        $userInfo = $dalMykitchen->getUserSearch($uid, $uids, $pageIndex, $pageSize);
        $count = $dalMykitchen->getUserSearchCount($uid, $uids);

        $appId = 16235;
        foreach ($userInfo as $key => $value) {
            $status = 0;
        	$log = $dalMykitchen->isRemoveApp($appId, $value['uid']);
        	if ($log) {
        		$status = 1;
        	}
            $userInfo[$key]['status'] = $status;
        }

        Bll_User::appendPeople($userInfo, 'uid');

        $pageCount = ceil($count / 20);
        $page = array();
        for ($i = 0; $i < $pageCount && $i < 20; $i++) {
            $page[$i] = $i + 1;
        }

        $start = min(($pageSize * $pageIndex - $pageSize) + 1, $count);
        $search = min($pageSize * $pageIndex, $count);

        $this->view->page = $page;
        $this->view->pageIndex = $pageIndex;
        $this->view->startCount = $start;
        $this->view->searchCount = $search;
        $this->view->count = $count;

        $this->view->name = $name;
        $this->view->uid = $uid;
        $this->view->userInfo = $userInfo;
        $this->view->title = 'ユーザ検索';
        $this->render();
    }

    /**
     * user info action
     *
     */
    public function userinfoAction()
    {
        $appId = 16235;
        $uid = $this->_request->getParam('CF_uid');

        $bllMykitchen = new Admin_Bll_Mykitchen();
        $userInfo = $bllMykitchen->getUserInfo($appId, $uid);

        $this->view->userInfo = $userInfo;
        $this->view->title = 'ユーザ詳細';
        $this->render();
    }

    /**
     * buy shop action
     *
     */
    public function buyshopAction()
    {
        $uid = $this->_request->getParam('CF_uid');
        $buyType = $this->_request->getParam('shop', 'food');

        $now = time();
        $startTime = $this->_request->getParam('txtStartTime', date('Y-m-d', $now));
        $endTime = $this->_request->getParam('txtEndTime');

        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 20);

        $DalMykitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        $buyShop = $DalMykitchen->getUserBuyInfoById($uid, $buyType, $startTime, $endTime, $pageIndex, $pageSize);
        $count = $DalMykitchen->getUserBuyInfoCntById($uid, $buyType, $startTime, $endTime);

        $totalPoint = 0;
        $totalGold = 0;
        $totalMixigold = 0;

        if ($buyShop) {
            foreach ($buyShop as $key => $value) {
        		$totalPoint += $value['point'];
        		$totalGold += $value['gold'];
        		$totalMixigold += $value['mixi_gold'];

        		$buyShop[$key]['point'] = number_format($value['point']);
        		$buyShop[$key]['gold'] = number_format($value['gold']);
        	}
        }

        $this->view->totalPoint = number_format($totalPoint);
        $this->view->totalGold = number_format($totalGold);
        $this->view->totalMixigold = $totalMixigold;

        $pageCount = ceil($count / 20);
        $page = array();
        for ($i = 0; $i < $pageCount && $i < 20; $i++) {
            $page[$i] = $i + 1;
        }

        $start = min(($pageSize * $pageIndex - $pageSize) + 1, $count);
        $search = min($pageSize * $pageIndex, $count);

        $this->view->page = $page;
        $this->view->pageIndex = $pageIndex;
        $this->view->startCount = $start;
        $this->view->searchCount = $search;
        $this->view->count = $count;

        if ($uid) {
        	$person = array('uid' => $uid);
	        Bll_User::appendPerson($person, 'uid');
	        $this->view->person = $person;
	        $this->view->uid = $uid;
        }

        $this->view->shopList = $buyShop;
        $this->view->shopType = $buyType;
        $this->view->startTime = $startTime;
        $this->view->endTime = $endTime;

        $this->view->title = '購入履歴';
        $this->render();
    }

    public function actiondisplayAction()
    {
        $uid = $this->_request->getParam('CF_uid');
        $buyType = $this->_request->getParam('shop', 'food');
        $nbTypeId = $this->_request->getParam('nbshop');

        $now = time();
        $startTime = $this->_request->getParam('txtStartTime', date('Y-m-d', $now));
        $endTime = $this->_request->getParam('txtEndTime');

        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 20);

        $AdminBllMykitchen = new Admin_Bll_Mykitchen();
        $actionInfo = $AdminBllMykitchen->getActionList($uid, $buyType, $startTime, $endTime, $pageIndex, $pageSize, $nbTypeId);
        $count = $AdminBllMykitchen->getActionCnt($uid, $buyType, $startTime, $endTime, $nbTypeId);

        $pageCount = ceil($count / 20);
        $page = array();
        for ($i = 0; $i < $pageCount && $i < 20; $i++) {
            $page[$i] = $i + 1;
        }

        $start = min(($pageSize * $pageIndex - $pageSize) + 1, $count);
        $search = min($pageSize * $pageIndex, $count);

        $this->view->page = $page;
        $this->view->pageIndex = $pageIndex;
        $this->view->startCount = $start;
        $this->view->searchCount = $search;
        $this->view->count = $count;

        if ($uid) {
        	$person = array('uid' => $uid);
	        Bll_User::appendPerson($person, 'uid');
	        $this->view->person = $person;
	        $this->view->uid = $uid;
        }

        $nbList = $AdminBllMykitchen->getNbList($buyType);

        $this->view->startTime = $startTime;
        $this->view->endTime = $endTime;
        $this->view->actionInfo = $actionInfo;
        $this->view->nblist = $nbList;
        $this->view->shopType = $buyType;
        $this->view->nbTypeId = $nbTypeId;
        $this->view->title = 'アクション履歴';
        $this->render();
    }
}