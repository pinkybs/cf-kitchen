<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile kitchen first login Controller(modules/mobile/controllers/KitchenfirstController.php)
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  hch  2010-1-6
 */
class KitchenfirstController extends MyLib_Zend_Controller_Action_Mobile
{
    /**
     * preDispatch
     *
     */
    function preDispatch()
    {
        $this->view->uid = $this->_USER_ID;
        $this->view->rand = time();
    }
    
    /**
     * first login action
     *
     */
    public function firstloginAction()
    {
        //first login logic
        //check is join app
        require_once 'Mdal/Kitchen/User.php';
        $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
        $isJoin = $mdalUser->isJoin($this->_USER_ID);
        
        if ($isJoin) {
            //if today first login,send gift
            require_once 'Mbll/Kitchen/Firstlogin.php';
	        $mbllFirst = new Mbll_Kitchen_Firstlogin();
	        $mbllFirst->loginTwice($this->_USER_ID);
	        
            $this->_redirect($this->_baseUrl . '/mobile/kitchen/top');
        }
		
        $this->render();
    }
    
    /**
     * edit chara action
     *
     */
    public function editcharaAction()
    {
        require_once 'Mdal/Kitchen/User.php';
        $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
        $user = $mdalUser->getUser($this->_USER_ID);
        $isJoin = empty($user) ? false : true;
        
        $face = $this->getParam("CF_face", 1);
        $rabbit = $this->getParam("CF_rabbit", 1);
        $ear = $this->getParam("CF_ear", 1);
        $head_m = $this->getParam("CF_head_m", 1);
        $eye = $this->getParam("CF_eye", 1);
        $eyemask = $this->getParam("CF_eyemask", 1);
        $chef = array('uid' => $this->_USER_ID,
                      'face' => $face,
                      'rabbit' => $rabbit,
                      'ear' => $ear,
                      'head_m' => $head_m,
                      'eye' => $eye,
                      'eyemask' => $eyemask,
                      'ischanged' => 0);
                      
        if ($isJoin) {
        	//second edit chara
        	//check allow_editchara
        	if ($user['allow_editchara'] == 0) {
            	$this->_redirect($this->_baseUrl . '/mobile/kitchen/top');
        	}
        	
        	$chef['ischanged'] = 1;
        	
        	require_once 'Mbll/Kitchen/Firstlogin.php';
	        $mbllFirst = new Mbll_Kitchen_Firstlogin();
	        $result = $mbllFirst->editChara($this->_USER_ID, $chef);
	        
	        if (!$result) {
	            $this->_redirect($this->_baseUrl . '/mobile/error/error');
	        }
	        else {
	            //access analyse -uu
		        require_once 'Mdal/Kitchen/Access.php';
        		$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
        		try {
        			$mdalAccess->insertUu(array('type' => 1,
        								        'create_time' => time()));
        		}
        		catch (Exception $e){
		        }
	        	
	        	$this->_redirect($this->_baseUrl . '/mobile/kitchen/profile');
	        }
        }
        else {
	        require_once 'Mbll/Kitchen/Firstlogin.php';
	        $mbllFirst = new Mbll_Kitchen_Firstlogin();
	        $result = $mbllFirst->login($this->_USER_ID, $chef);
	        
	        if (!$result) {
	            $this->_redirect($this->_baseUrl . '/mobile/error/error');
	        }
	        else {
	        	//access analyse -uu
			    require_once 'Mbll/Kitchen/Access.php';
		        $insertUu = Mbll_Kitchen_Access::tryInsert($this->_USER_ID, 1);
		        //access analyse -uu
			    require_once 'Mbll/Kitchen/Access.php';
		        $insertUu = Mbll_Kitchen_Access::tryInsert($this->_USER_ID, 2);
		        
	        	require_once 'Bll/User.php';
            	$mixiUser = Bll_User::getPerson($this->_USER_ID);
            	
            	//add --open shop alert-- to all my friends
	        	$friendArr = Bll_Friend::getFriends($this->_USER_ID);
	        	if (!empty($friendArr)) {
		        	//info_log('111','new_editchar_nofriend');
		        	require_once 'Mdal/Kitchen/Visit.php';
			        $mdalVisit = Mdal_Kitchen_Visit::getDefaultInstance();
			        $mdalVisit->insertOpenAlert($friendArr, $this->_USER_ID, $mixiUser->getDisplayName() . '食堂');
	        	}
	        	else {
	        		//info_log('o','new_editchar_nofriend');
	        	}
	        	//zhaoxh20100206   activity 6
		        require_once 'Mbll/Kitchen/Activity.php';
		        $activity = Mbll_Kitchen_Activity::getActivity(6, $mixiUser->getDisplayName() . '食堂');
		        $aryActivity = explode('|', $activity);
		        
		        require_once 'Bll/Restful.php';
		        $restful = Bll_Restful::getInstance($this->_USER_ID, $this->_APP_ID);
		        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');
		        $this->_redirect($this->_baseUrl . '/mobile/kitchen/top');
	        }
        }
        
        //require_once 'Mbll/Kitchen/FlashCache.php';
        //$this->view->swfFile = Mbll_Kitchen_FlashCache::getProfile($this->_USER_ID);
        require_once 'Mdal/Kitchen/Chef.php';
        $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
        $chefArr = $mdalChef->getCfChef($this->_USER_ID);
        $paramStr = http_build_query($chefArr);
    	$this->view->paramStr = $paramStr;
    	
        $this->view->rand = time();
        $this->render();
    }
    
    /**
     * edit name action
     *
     */
    public function editnameAction()
    {
        $step = $this->getParam('CF_step', "start");
        $genre = $this->getParam('CF_genre', "1");
        $changeFlag = $this->getPost('CF_changeFlag', 0);
        if (empty($changeFlag)) {
            $changeFlag = $this->getParam('CF_changeFlag', 0);
        }
        
        require_once 'Mbll/Emoji.php';
        $mbllEmoji = new Mbll_Emoji();

        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        
        if ($step == "start") {
            
        	$name = $this->getPost('CF_restaurant_name');
            if (empty($name)) {
                $res = $mdalRest->getOneRestaurant($this->_USER_ID, $genre);
                $name = $res['name'];
            }
            
            $this->view->name = $name;
            $this->view->changeFlag = $changeFlag;
        }
        elseif ($step == "confirm") {
            $name = $this->getPost('CF_restaurant_name');
            $name = mb_ereg_replace( "^(　| |\t|\n|\r|\0|\x0B)*|(　| |\t|\n|\r|\0|\x0B)*$", "", $name);
            $this->view->name = $name;
            
            if ($name == "" ) {
                $this->view->error = 1;
                $step = "start";
            } else if (mb_strlen($name,'utf-8') > 15) {
                $this->view->error = 2;
                $step = "start";
            } else {
                $escapeEmojiName = $mbllEmoji->escapeEmoji($name, true);
                if ($escapeEmojiName != $name) {
                    $this->view->error = 3;
                    $step = "start";
                }
            }
            
            $this->view->changeFlag = $changeFlag;
        }
        elseif ($step == "complete") {
            $name = $this->getPost('CF_restaurant_name');
            
            $res = array('name'=>$name);
            
            $mdalRest->updateRestaurant($res, $this->_USER_ID, $genre);
            
            /*
            //access analyse -uu
	        require_once 'Mdal/Kitchen/Access.php';
        	$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
        	try {
        		$mdalAccess->insertUu(array('type' => 2,
        							        'create_time' => time()));
        	}
        	catch (Exception $e){
	        }
			*/
            if ($changeFlag != 1) {
	            //zhaoxh20100206   activity 6
		        require_once 'Mbll/Kitchen/Activity.php';
		        $activity = Mbll_Kitchen_Activity::getActivity(6, $name);
		        $aryActivity = explode('|', $activity);
		        
		        require_once 'Bll/Restful.php';
		        $restful = Bll_Restful::getInstance($this->_USER_ID, $this->_APP_ID);
		        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');
            }
            $this->view->changeFlag = $changeFlag;
	    }
        else {
            $step = "start";
        }
        
        $this->view->genre = $genre;
        $this->view->step = $step;
        $this->render();
    }
    
    /**
     * kitchen first action
     *
     */
    public function kitchenfirstAction()
    {
        $this->render();
    }
    
	public function editaccessAction()
    {
        $step = $this->getParam('CF_step', "start");
        require_once 'Mdal/Kitchen/User.php';
        $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
        $userInfo = $mdalUser->getUser($this->_USER_ID);
        
        if ($step == "start") {
            $this->view->nowValue = $userInfo['friend_only'];
        }
        elseif ($step == "confirm") {
            $nowValue = $this->getPost('editAccess', 0);
            $step = $nowValue == $userInfo['friend_only'] ? "finish" : "confirm";
            $this->view->nowValue = $nowValue;
        }
        elseif ($step == "finish") {
            $nowValue = $this->getPost('nowValue', 0);
            $mdalUser->updateUser(array('friend_only' => $nowValue), $this->_USER_ID);
        }
        else {
            $step = "start";
        }
        $this->view->step = $step;
        $this->render();
    }
    
	public function selectcharaAction()
    {   	
    	$arr20 = array(
		array('CF_face' => 3,'CF_rabbit' => 1,'CF_ear' => 4,'CF_head_m' => 3,'CF_eye' => 1,'CF_eyemask' => 3),
		array('CF_face' => 3,'CF_rabbit' => 0,'CF_ear' => 2,'CF_head_m' => 3,'CF_eye' => 1,'CF_eyemask' => 2),
		array('CF_face' => 16,'CF_rabbit' => 0,'CF_ear' => 5,'CF_head_m' => 6,'CF_eye' => 16,'CF_eyemask' => 3),
		array('CF_face' => 16,'CF_rabbit' => 1,'CF_ear' => 16,'CF_head_m' => 16,'CF_eye' => 8,'CF_eyemask' => 1),
		array('CF_face' => 16,'CF_rabbit' => 0,'CF_ear' => 1,'CF_head_m' => 16,'CF_eye' => 16,'CF_eyemask' => 3),
		array('CF_face' => 1,'CF_rabbit' => 0,'CF_ear' => 8,'CF_head_m' => 5,'CF_eye' => 15,'CF_eyemask' => 2),
		array('CF_face' => 9,'CF_rabbit' => 1,'CF_ear' => 9,'CF_head_m' => 5,'CF_eye' => 8,'CF_eyemask' => 1),
		array('CF_face' => 15,'CF_rabbit' => 1,'CF_ear' => 14,'CF_head_m' => 3,'CF_eye' => 1,'CF_eyemask' => 1),
		array('CF_face' => 5,'CF_rabbit' => 1,'CF_ear' => 5,'CF_head_m' => 5,'CF_eye' => 1,'CF_eyemask' => 1),
		array('CF_face' => 3,'CF_rabbit' => 1,'CF_ear' => 3,'CF_head_m' => 3,'CF_eye' => 2,'CF_eyemask' => 1),
		array('CF_face' => 1,'CF_rabbit' => 1,'CF_ear' => 1,'CF_head_m' => 4,'CF_eye' => 5,'CF_eyemask' => 1),
		array('CF_face' => 9,'CF_rabbit' => 0,'CF_ear' => 1,'CF_head_m' => 9,'CF_eye' => 1,'CF_eyemask' => 1),
		array('CF_face' => 10,'CF_rabbit' => 0,'CF_ear' => 8,'CF_head_m' => 2,'CF_eye' => 1,'CF_eyemask' => 1),
		array('CF_face' => 11,'CF_rabbit' => 0,'CF_ear' => 11,'CF_head_m' => 11,'CF_eye' => 1,'CF_eyemask' => 2),
		array('CF_face' => 4,'CF_rabbit' => 0,'CF_ear' => 3,'CF_head_m' => 8,'CF_eye' => 1,'CF_eyemask' => 2),
		array('CF_face' => 10,'CF_rabbit' => 1,'CF_ear' => 8,'CF_head_m' => 8,'CF_eye' => 1,'CF_eyemask' => 1),
		array('CF_face' => 9,'CF_rabbit' => 0,'CF_ear' => 3,'CF_head_m' => 3,'CF_eye' => 2,'CF_eyemask' => 2),
		array('CF_face' => 10,'CF_rabbit' => 0,'CF_ear' => 11,'CF_head_m' => 16,'CF_eye' => 16,'CF_eyemask' => 3),
		array('CF_face' => 14,'CF_rabbit' => 0,'CF_ear' => 12,'CF_head_m' => 13,'CF_eye' => 13,'CF_eyemask' => 3),
		array('CF_face' => 6,'CF_rabbit' => 1,'CF_ear' => 6,'CF_head_m' => 11,'CF_eye' => 2,'CF_eyemask' => 2)
    	);
    	
    	$i = rand(0,19);
    	$ii = $i + 1;
    	$ii = $ii < 10 ? '0' . $ii : $ii;
    	
    	$paramStr = http_build_query($arr20[$i]);
    	$this->view->paramStr = $paramStr;
    	$this->view->swfName = $ii . '.swf';
    	$this->_redirect($this->_baseUrl . '/mobile/kitchenfirst/editchara?' . $paramStr);
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
        return $this->_redirect($this->_baseUrl . '/mobile/error/notfound');
    }
}