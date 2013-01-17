<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * application callback controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/09/23    HLJ
 */
class CallbackController extends MyLib_Zend_Controller_Action_Mobile
{
    public function inviteAction()
    {
        
        $recipientIds = $this->_request->getParam('invite_member');
        $forward = $this->_request->getParam('forward');

        $app_id = $this->_request->getParam('opensocial_app_id');
        $user_id = $this->_request->getParam('opensocial_owner_id');

        
        if ($recipientIds) {
            $count = count(explode(',', $recipientIds));

            require_once 'Bll/Invite.php';
            $result = Bll_Invite::addMultiple($app_id, $user_id, $recipientIds);
            
            require_once 'Bll/Application/Log.php';
            $result = Bll_Application_Log::invite($app_id, $user_id, $recipientIds, $count, 'mobile');
            
            if ($app_id == 16235) {
                require_once 'Mbll/Kitchen/Invite.php';
                $mbllInvite = new Mbll_Kitchen_Invite();
                $mbllInvite->invite($user_id, $recipientIds);
            }
        
        }

        if ($forward) {
            $this->_redirect($forward);
        }

        exit;
    }

    /**
     * magic function
     *   if call the function is undefined,then echo undefined
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        echo 'undefined method name: ' . $methodName;
        exit;
    }

 }
