<?php
/**
 * Admin Board Ajax Controller
 * Manager ajax operation
 *
 * @copyright  Copyright (c) 2010 Community Factory Inc. (http://communityfactory.com)
 * @create    2010/04/03    xial
 */
class Admin_AjaxmykitchenController extends MyLib_Zend_Controller_Action_AdminAjax
{
    /**
     * post-Initialize
     * called after parent::init method execution.
     * it can override
     * @return void
     */
    public function postInit()
    {

    }

    public function ajaxnblistAction()
    {
        $buyType = $this->_request->getParam('CF_buyType', 'food');

        $AdminBllMykitchen = new Admin_Bll_Mykitchen();
        $nbList = $AdminBllMykitchen->getNbList($buyType);
        $response = Zend_Json::encode($nbList);
        echo $response;
    }
}