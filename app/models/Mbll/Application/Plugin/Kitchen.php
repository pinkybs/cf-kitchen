<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

class Mbll_Application_Plugin_Kitchen implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        // campain gift add ,zhaoxh 20100510
        $mbllgift = new Mbll_Kitchen_Gift();
        $mbllgift->addCampainGift($uid);
        
        //campain gift 2 add 
        $mbllgift->addCampainGiftTwo($uid);
    }

    public function postUpdateFriend($fid)
    {
        //TODO:
    }

    public function postUpdateFriendship($uid, array $fids)
    {
        //TODO:
    }

    public function updateAppFriendship($uid, array $fids)
    {
        //TODO:
    }

    public function postRun(Bll_Application_Interface $application)
    {
    	$url = '/mobile/kitchenfirst/firstlogin/cf_ts/' . time();
        $application->redirect($url);
    }
}