<?php
class Admin_IndexController extends Zend_Controller_Action
{
    protected $_baseUrl;

    /**
     * page init
     *
     */
    function init()
    {
        $this->_baseUrl = $this->_request->getBaseUrl();
        $this->view->baseUrl = $this->_baseUrl;
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');
    }

    public function indexAction()
    {
        $this->view->title = 'mykitchen-No | Admin Index';
        $this->_forward('login', 'auth');
    }
}