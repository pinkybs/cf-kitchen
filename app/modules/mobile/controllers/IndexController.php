<?php

/* Mbll_Application */
require_once 'Mbll/Application.php';

/**
 * index controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2008/08/11    HCH
 */
class IndexController extends Zend_Controller_Action
{
    /**
     * index Action
     *
     */
    public function indexAction()
    {
        $application = Mbll_Application::newInstance($this);
        if ($application->autoRegisterPlugin()) {
            $application->run();
        } else {
            $application->redirect404();
        }
    }

    public function getpicAction() {
        $profileUid = $this->_request->getParam('CF_uid');

        $tt = new Mbll_Kitchen_Tt($profileUid);
        $filename = $profileUid . '_cheftt.gif';
        
        if ($tt->hasObject($filename)) {
            header( "Content-Type: image/gif" );
            $streamOut = $tt->getObject($filename);
            echo $streamOut;
        }
        exit();
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
        return $this->_forward('notfound','error','mobile');
    }

 }
