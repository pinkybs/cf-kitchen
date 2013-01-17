<?php

/**
 * Mobile kitchen invite bussiness logic layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  shenhw  2010/1/14
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Invite extends Mbll_Abstract
{

    /**
     * invite user
     *
     * @param integer $uid
     * @param string $recipientIds
     * @return boolean
     */
    public function invite($uid, $recipientIds)
    {
        $result = false;

        $aryInvite = explode(',', $recipientIds);

        if (!$aryInvite) {
            return $result;
        }

        require_once 'Mdal/Kitchen/Invite.php';
        $mdalInvite = Mdal_Kitchen_Invite::getDefaultInstance();

        try {
            $this->_wdb->beginTransaction();

            //insert invite info
            $count = count($aryInvite);
            for ($i = 0; $i < $count; $i++) {
                $hasInvited = $mdalInvite->getInviteByUser($uid, $aryInvite[$i]);

                if (0 == $hasInvited) {
                    info_log('Kitchen invite:' . $aryInvite[$i] . '-invite_from:' . $uid, 'kitchen_invite');
                    $invite = array('actor' => $uid, 'target' => $aryInvite[$i], 'create_time' => time());
                    $mdalInvite->insertInvite($invite);
                }
            }

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

    /**
     * invite user
     *
     * @param integer $uid
     * @param integer $inviteUid
     * @return boolean
     */
    public function inviteComplete($uid, $inviteUid)
    {
        $result = false;

        info_log('Kitchen inviteComplete:' . $uid . '-invite_from:' . $inviteUid, 'kitchen_invite_complete');

        require_once 'Mdal/Kitchen/Invite.php';
        $mdalInvite = Mdal_Kitchen_Invite::getDefaultInstance();

        //get invite info
        $inviteInfo = $mdalInvite->getInviteByUser($inviteUid, $uid, 0);
        $setInviteSuccess = false;
        if ($inviteInfo) {
            $setInviteSuccess = true;
        }
        else {
            return $result;
        }

        try {
            $this->_wdb->beginTransaction();

            if ($setInviteSuccess) {

                require_once 'Mdal/Kitchen/InviteSuccess.php';
                $mdalInviteSuccess = Mdal_Kitchen_InviteSuccess::getDefaultInstance();

                require_once 'Mdal/Kitchen/Gacha.php';
                $mdalGacha = Mdal_Kitchen_Gacha::getDefaultInstance();

                require_once 'Mdal/Kitchen/User.php';
                $mdalUser = Mdal_Kitchen_User::getDefaultInstance();

                //update status => 1
                $mdalInvite->updateInvite(array('status' => 1), $inviteUid, $uid);

                //insert invite success
                $mdalInviteSuccess->insertInviteSuccess(array('uid' => $inviteUid, 'target_uid' => $uid, 'create_time' => time()));

                //update res_user_gacha
                $userGacha = $mdalGacha->getUserGacha($inviteUid);
                $userGacha['invite_count'] += 1;
                $userGacha['gacha_count'] += 1;
                $mdalGacha->updateUserGacha($userGacha, $inviteUid);

                //admin page shopping info
                $userInfo = $mdalUser->getUser($inviteUid);
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$adminDalkitchen->insert('gold', array('uid' => $inviteUid,
	        										   'gold' => 100,
	        										   'start_count' => $userInfo['gold'],
	        										   'end_count' => $userInfo['gold'] + 100,
	        										   'create_type' => 'invite send',
	        										   'description' => 'invite send:gold + 100',
	        										   'buy_time' => time()));
                //update user's gold + 100
                $mdalUser->updateUserBy($inviteUid, 'gold', 100);
            }

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }

        if ($result) {
	        //access analyse
	        require_once 'Mdal/Kitchen/Access.php';
	        $mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
	        try {
	        	$mdalAccess->insertMoney(array('uid' => $inviteUid,
	        								   'amount' => 100,
	        								   'type' => 4,
	        								   'description' => 'invite_gold',
	        								   'create_time' => time()));
	        }
	        catch (Exception $e){
	        }
        }
        return $result;
    }

}