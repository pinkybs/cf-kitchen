<?php

/**
 * application lifecycle controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/08/07    HLJ
 */
class LifecycleController extends Zend_Controller_Action
{

    /**
     * index Action
     *
     */
    public function indexAction()
    {
    	echo 'lifecycle';
    	exit;
    }
    
    //http://developer.mixi.co.jp/appli/pc/lets_enjoy_making_mixiapp/lifecycle_event
    private function checkSignature(&$parameters)
    {
        require_once 'osapi/external/MixiSignatureMethodForLife.php';
        //Build a request object from the current request
        $request = OAuthRequest::from_request(null, null, null, true);
                
        //Initialize the new signature method
        $signature_method = new MixiSignatureMethodForLife();
        //Check the request signature
        $signature = rawurldecode($request->get_parameter('oauth_signature'));
        $request->sort_multiple_params = false;
        $signature_valid = $signature_method->check_signature($request, null, null, $signature);
                
        if ($signature_valid) {
            $parameters = $request->get_parameters();
        }
        else {
            $parameters = array();
        }
        
        return $signature_valid;
    }

    private function isMultipleIds()
    {
        $query = $_SERVER['QUERY_STRING'];
        if (!empty($query)) {
            $a = explode('&', $query);
            $id = array();
            for($i = 0, $n = count($a); $i < $n; $i++) {
                $b = explode('=', $a[$i]);
                if (isset($b[0]) && $b[0] == 'id') {
                    $id[] = $b[1];
                }
            }
            if (count($id) > 1) {
                $_GET['id'] = $id;
            }
        }
    }

    public function addappAction()
    {
        $this->isMultipleIds();
        $signature_valid = $this->checkSignature($parameters);
        if ($signature_valid == true) {
            $eventtype = $parameters['eventtype'];
            $opensocial_app_id = $parameters['opensocial_app_id'];
            $id = $parameters['id'];
            $mixi_invite_from = $parameters['mixi_invite_from'];

            if ($eventtype == 'event.addapp' && !empty($id) && !isset($parameters['opensocial_owner_id'])) {
                info_log($_SERVER['QUERY_STRING'], 'lifecycle.add');
                $impl = Bll_Lifecycle_Factory::getImplByAppId($opensocial_app_id);
                if ($impl) {
                    $result = $impl->add($opensocial_app_id, $id, $mixi_invite_from);
                }
                //add addapp log
                require_once 'Bll/Statistics.php';
                $result = Bll_Statistics::addLogin($opensocial_app_id, $id, $mixi_invite_from);
                //update successed invited  stats
                require_once 'Bll/Invite.php';
                $result = Bll_Invite::update($opensocial_app_id, $id, $mixi_invite_from);
            }
        } else {
            info_log($_SERVER['QUERY_STRING'], 'lifecycle.add.invalid');
        }
        
        if (!$result) {
            header("HTTP/1.1 500 Internal Server Error");
        } else {
            ini_set('default_charset', null);
            header('HTTP/1.1 200 OK');
        }
        
        exit;
    }
    
    public function removeappAction()
    {
        $this->isMultipleIds();
        $signature_valid = $this->checkSignature($parameters);
        if ($signature_valid == true) {
            $eventtype = $parameters['eventtype'];
            $opensocial_app_id = $parameters['opensocial_app_id'];
            $id = $parameters['id'];
            if ($eventtype == 'event.removeapp' && !empty($id) && !isset($parameters['opensocial_owner_id'])) {
                info_log($_SERVER['QUERY_STRING'], 'lifecycle.remove');
                $impl = Bll_Lifecycle_Factory::getImplByAppId($opensocial_app_id);
                if ($impl) {
                    $impl->remove($opensocial_app_id, $id);
                }
                //remove app
                require_once 'Bll/Statistics.php';
                $result = Bll_Statistics::addRemove($opensocial_app_id, $id);
            }
        } else {
            info_log($_SERVER['QUERY_STRING'], 'lifecycle.remove.invalid');
        }
        
        if (!$result) {
            header("HTTP/1.1 500 Internal Server Error");
        } else {
            ini_set('default_charset', null);
            header('HTTP/1.1 200 OK');
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
