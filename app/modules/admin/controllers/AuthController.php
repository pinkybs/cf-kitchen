<?php

/**
 * Admin Auth Controller(modules/admin/controllers/Admin_AuthController.php)
 * Linno Admin Auth Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/18    zhangxin
 */
class Admin_AuthController extends Zend_Controller_Action
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
    function init()
    {
        //get admin website base url
        $this->_baseUrl = $this->_request->getBaseUrl();
        $this->view->baseUrl = $this->_baseUrl;
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');
    }

    /**
     * auth controller login action
     *
     */
    public function loginAction()
    {
        //if is post
        if ($this->_request->isPost()) {
            //get posted data from client
            $email = $this->_request->getPost('txtId');
            $password = $this->_request->getPost('txtPw');
            $this->view->errmsg = '';
            $this->view->adminId = $email;
            //check validate
            require_once 'Zend/Validate/EmailAddress.php';
            $vdEmail = new Zend_Validate_EmailAddress();
            //check string length
            if (empty($email) || empty($password)) {
                $this->view->errmsg = 'メールアドレスとパスワードを入力してください。';
            }
            else if (strlen($email) > 200) {
                $this->view->errmsg = 'メールアドレスは200字以下で入力してください。';
            }
            else if (strlen($password) > 12 || strlen($password) < 6) {
                $this->view->errmsg = 'パスワードは6字以上12字以下で入力してください。';
            }
            //check $email is right format
            else if (!$vdEmail->isValid($email)) {
                $this->view->errmsg = '有効なメールアドレスを入力してください。';
            }
            else {
                require_once 'Admin/Bll/Auth.php';
                $result = Admin_Bll_Auth::authenticate($email, sha1($password));
                if ($result == 1) {
                    $this->_redirect($this->_baseUrl . '/admin/mykitchen');
                    return;
                }
                //reject to pass
                else {
                    $this->view->errmsg = '登録しているメールアドレス、またはパスワードが違います。';
                }
            }
        }
        else {
            require_once 'Admin/Bll/Auth.php';
            $auth = Admin_Bll_Auth::getAuthInstance();
            if ($auth->hasIdentity()) {
                $this->_redirect($this->_baseUrl . '/admin/mykitchen');
                return;
            }
        }
        $this->view->title = 'ログイン｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * auth controller logout action
     *
     */
    public function logoutAction()
    {
        //clear admin session
        require_once 'Admin/Bll/Auth.php';
        $auth = Admin_Bll_Auth::getAuthInstance();
        if ($auth->hasIdentity()) {
            //clear Session
            $auth->clearIdentity();
        }

        Zend_Session::regenerateId();
        $this->_redirect($this->_baseUrl . '/');
        return;
    }

    /**
     * call
     *
     */
    function __call($methodName, $args)
    {
        return $this->_forward('notfound', 'error', 'admin');
    }

//**********************************************
}