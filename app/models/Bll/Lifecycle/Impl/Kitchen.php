<?php

/**
 * application lifecycle event callback implementation for app kitchen
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2010/01/14    shenhw
 */
class Bll_Lifecycle_Impl_Kitchen implements Bll_Lifecycle_Interface
{

    /**
     * user add app event callback
     *
     * @param int $app_id
     * @param int $uid
     * @param int $mixi_invite_from
     */
    public function add($app_id, $uid, $mixi_invite_from = null)
    {
        $result = true;
        
        if ($mixi_invite_from) {
            require_once 'Mbll/Kitchen/Invite.php';
            $mbllInvite = new Mbll_Kitchen_Invite();
            $result = $mbllInvite->inviteComplete($uid, $mixi_invite_from);
        }
        
        return $result;
    }

    /**
     * user remove app event callback
     *
     * @param int $app_id
     * @param int $uid
     */
    public function remove($app_id, $uid)
    {
        /*require_once 'Mbll/Ship/User.php';
        $mbllShipUser = new Mbll_Ship_User();
        return $mbllShipUser->removeShipUser($uid);*/
    }
}
