<?php

/**
 * flash controller
 * init each flash 
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2010/03/03    HCH
 */
class FlashController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $key = $this->_request->getParam('CF_key');
        $uid = $this->_request->getParam('CF_uid');
                
        require_once 'Mbll/Kitchen/Tt.php';
        $tt = new Mbll_Kitchen_Tt($uid);	       
        $swf = $tt->getObject($key);        
        
        if (empty($swf)) {
            $swf = file_get_contents('/mnt/user/home/admin/website/mixi/cf/swf_xml/error.swf');
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
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_forward('index');
    }
}