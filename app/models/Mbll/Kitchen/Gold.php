<?php

/**
 * Mobile kitchen gold bussiness logic layer
 *
 * @copyright  Copyright (c) 2010 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-3-1
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Gold extends Mbll_Abstract
{
	public function insertGoldLog($pay)
    {
        $result = false;

        $this->_wdb->beginTransaction();

        try {
            //insert into Gold Log
            require_once 'Mdal/Kitchen/Gold.php';
            $mdalGold = Mdal_Kitchen_Gold::getDefaultInstance();
            $mdalGold->insertGoldLog($pay);

            $this->_wdb->commit();

            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

	public function buyGoldSubmit($pointCode)
    {
        $result = -1;

        require_once 'Mdal/Kitchen/Gold.php';
        $mdalGold = Mdal_Kitchen_Gold::getDefaultInstance();
        $goldLog = $mdalGold->getGoldLogByCode($pointCode);

        if (empty($goldLog)) {
            return $result;
        }

        $goldInfo = $mdalGold->getGold($goldLog['gold_id']);

        require_once 'Mdal/Kitchen/User.php';
        $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $mdalUser->getUser($goldLog['uid']);

        $this->_wdb->beginTransaction();

        try {
            //insert buy gold success log
            $buyGoldLogSuccess = array('uid' => $goldLog['uid'],
		                               'gold_id' => $goldLog['gold_id'],
		                               'before_gold' => $userInfo['gold'],
		                               'after_gold' => $userInfo['gold'] + $goldInfo['gold_value'],
		                               'buy_time' => date('Y-m-d H:i:s'));

            $mdalGold->updateGoldLogStatus($pointCode, 1, time());
            $mdalGold->insertGoldLogSuccess($buyGoldLogSuccess);
            $mdalUser->updateUserBy($goldLog['uid'], 'gold', $goldInfo['gold_value']);

            //admin page shopping info
        	$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        	$adminDalkitchen->insert('gold', array('uid' => $goldLog['uid'],
        										   'shop_id' => $goldInfo['id'],
        										   'shop_name' => $goldInfo['gold_name'],
        										   'mixi_gold' => $goldInfo['gold_price'],
        										   'buy_place' => 'マイキチ銀行',
        										   'description' => 'start:' . $userInfo['gold'] . '+',
        										   'buy_time' => time()));

            $this->_wdb->commit();

            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }

        if ($result == 1) {
	        //access analyse
	        require_once 'Mdal/Kitchen/Access.php';
	        $mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
	        try {
	        	$mdalAccess->insertMoney(array('uid' => $goldLog['uid'],
	        								   'amount' => $goldInfo['gold_value'],
	        								   'type' => 4,
	        								   'description' => 'mpBuy_gold',
	        								   'create_time' => time()));
	        }
	        catch (Exception $e){
	        }
        }

        return $result;
    }
}