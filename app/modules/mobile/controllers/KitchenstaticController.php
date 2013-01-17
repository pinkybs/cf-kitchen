<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile kitchen static Controller(modules/mobile/controllers/KitchenfirstController.php)
 *
 * @copyright  Copyright (c) 2010 Community Factory Inc. (http://communityfactory.com)
 * @create  shenhw  2010-1-7
 */
class KitchenstaticController extends MyLib_Zend_Controller_Action_Mobile
{
    /**
     * preDispatch
     *
     */
    function preDispatch()
    {
        $this->view->uid = $this->_USER_ID;
    }
    
    /**
     * info list action
     *
     */
    public function infoAction()
    {
        $parm = $this->getParam('CF_info', 'infolist');
        $this->view->parm = $parm;
        
        $clearDaily = $this->getParam('CF_clearDaily', 0);
        if ($clearDaily) {
        	require_once 'Mdal/Kitchen/Daily.php';
            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
            //update daily param
            $mdalDaily->updateDaily(array('announce' => 0), $this->_user->getId());
        }
        
        $this->render();
    }
    
    /**
     * help action
     *
     */
    public function helpAction()
    {
        $parm = $this->getParam('CF_help', 'index');
        $this->view->parm = $parm;

        $this->render();
    }
        
    /**
     * qa action
     *
     */
    public function qaAction()
    {
        $parm = $this->getParam('CF_qa', 'qa');
        $this->view->parm = $parm;
        
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