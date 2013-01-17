<?php
/**
 * Admin Dal School
 * LinNo Admin School Data Access Layer
 *
 * @package    Admin/Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2010/04/06    xial
 */
class Admin_Bll_Mykitchen extends Admin_Dal_Abstract
{
	/**
	 * get user info
	 *
	 * @param integer $appId
	 * @param integer $uid
	 * @return array
	 */
	public function getUserInfo($appId = 16235, $uid)
	{
		try {
            $dalMykitchen = Admin_Dal_Mykitchen::getDefaultInstance();
            $userInfo = $dalMykitchen->getUserInfoByUid($uid);
            Bll_User::appendPerson($userInfo, 'uid');
            //
            $log = $dalMykitchen->isRemoveApp($appId, $uid);
            $userInfo['status'] = empty($log) == true ? 0 : 1;
            $gachaCount = $dalMykitchen->getUserGachaCount($uid);
            $userInfo['gacha_count'] = $gachaCount;
            $userInfo['genre'] = $this->getGenreList($uid);

            //genre info
            $usedGenre = $dalMykitchen->getUsedRestaurant($uid);
            $userInfo['used_genre_name'] = $this->getGenreName($usedGenre);

            //invite info
            $inviteUser = $dalMykitchen->getInviteUserById($uid);
            if ($inviteUser) {
            	Bll_User::appendPerson($inviteUser, 'uid');
                $userInfo['invite_user'] = $inviteUser;
            }

            //send invite message
            $sendInvite = $dalMykitchen->getSendInviteInfo($uid);
            if ($sendInvite) {
            	Bll_User::appendPeople($sendInvite, 'target');
                $userInfo['send_invite_user'] = $sendInvite;
            }

            //invite success user
            $inviteSuccess = $dalMykitchen->getUserInviteSuccess($uid);
            if ($inviteSuccess) {
            	Bll_User::appendPeople($inviteSuccess, 'target_uid');
               $userInfo['invite_success'] = $inviteSuccess;
            }

            //friend info
            $fids = Bll_Friend::getFriendIds($uid);
            if ($fids) {
            	$friends = $dalMykitchen->getUserFriends($fids);
	            Bll_User::appendPeople($friends, 'uid');
	            $userInfo['friends'] = $friends;
            }

            return $userInfo;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
	}

	/**
	 * get user genre list
	 *
	 * @param integer $uid
	 * @return array
	 */
	public function getGenreList($uid)
	{
        $dalMykitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        $genreInfo = $dalMykitchen->getActiveRestaurant($uid);

        if ($genreInfo) {
	        foreach ($genreInfo as $key => $value) {
		        $genreInfo[$key]['genre_name'] = $this->getGenreName($value['genre']);
                $genreInfo[$key]['exp'] = number_format($value['exp']);
	        }
        }
        return $genreInfo;
	}

	/**
	 * get genre name by Id
	 *
	 * @param integer $genre
	 * @return string
	 */
	public function getGenreName($genre)
	{
	    $genre_name = '洋食';
		//1:洋食 2:リストランテ 3:日本料理 4:中華料理
        if (2 == $genre) {
            $genre_name = 'ﾘｽﾄﾗﾝﾃ';
        }
        else if (3 == $genre) {
            $genre_name = '和食';
        }
        else if (4 == $genre) {
            $genre_name = '中華';
        }
        return $genre_name;
	}

	public function getActionList($uid, $buyType, $startTime, $endTime, $pageIndex, $pageSize, $typeId)
	{
	    $tableName = "res_access_" . $buyType;
        $DalMykitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        return $DalMykitchen->getActionListById($uid, $tableName, $startTime, $endTime, $pageIndex, $pageSize, $typeId);
	}

	public function getActionCnt($uid, $buyType, $startTime, $endTime, $typeId)
	{
        $tableName = "res_access_" . $buyType;
        $DalMykitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        return $DalMykitchen->getActionCntById($uid, $tableName, $startTime, $endTime, $typeId);
	}

	/**
	 * get shop nb info list
	 *
	 * @param string $buyType
	 * @return array
	 */
	public function getNbList($buyType)
	{
	    $name = $buyType . '_name';
	    $id = $buyType . '_id';

	    if ($buyType == 'beauty') {
        	$buyType = 'item';
        	$name = 'item_name';
        	$id = 'item_id';
        }
        elseif ($buyType == 'gold') {
            $id = 'id';
        }

        $tableName = "res_shop_" . $buyType;

        if ($buyType == 'gift') {
        	$tableName = 'res_nb_gift';
        	$name = 'name';
        }

        $dalMykitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        return $dalMykitchen->selectNbListByType($tableName, $name, $id);
	}
}