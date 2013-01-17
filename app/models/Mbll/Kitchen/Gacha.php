<?php

/**
 * Mobile kitchen gacha bussiness logic layer
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-6
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Gacha extends Mbll_Abstract
{
	/**
	 * Kitchen Gacha
	 *
	 * @param string $uid
	 * @param string $paytype
	 * @param array $gachainfo
	 * @return string
	 */
    public function resultGacha($uid, $userGacha, $gachaInfo)
    {
    	$result = false;

        try {
            require_once 'Mdal/Kitchen/Gacha.php';
	        $dalGacha = Mdal_Kitchen_Gacha::getDefaultInstance();
	        //$userGacha = $dalGacha->getUserGacha($uid);
	        //$gachaInfo = $dalGacha->getGacha($userGacha['playing_gacha_id']);

	        require_once 'Mdal/Kitchen/User.php';
	        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
	    	$userInfo = $dalUser->getUser($uid);
            //$gachaInfo['gacha_price_gold'] = intval($gachaInfo['gacha_price_gold'] * $userInfo['discount'] / 100);

            $this->_wdb->beginTransaction();

            //pay for gacha
            $userGachaEdit = $userGacha;
            $userGachaEdit['playing_gacha_id'] = null;
	        $userGachaEdit['playing_pay'] = null;

	        if ($userGacha['playing_pay'] == 2) {
	        	$info3 = array('gold' => $userInfo['gold'] - $gachaInfo['gacha_price_gold']);
            	$dalUser->updateUser($info3, $uid);
	    	}
	    	else if ($userGacha['playing_pay'] == 1) {
	    		$userGachaEdit['gacha_count'] = $userGachaEdit['gacha_count'] - 1;

	    		require_once 'Mdal/Kitchen/InviteSuccess.php';
	            $dalInvite = Mdal_Kitchen_InviteSuccess::getDefaultInstance();
	            $oneInvite = $dalInvite->getInviteSuccess($uid);
	            $oneInvite['is_fortune_used'] = 1;
	            $dalInvite->updateInviteSuccess($oneInvite, $uid, $oneInvite['target_uid']);
	    	}

            $dalGacha->updateUserGacha($userGachaEdit, $uid);

            //get award
	        $pctArr = explode(',', $gachaInfo['percent']);
	        //$gachaDetail = array();
	        $pct = mt_rand(1,100);
	        for ($i = 1; $i <= count($pctArr); $i++) {
	        	if ($pct <= $pctArr[$i - 1]) {
	        		$gachaResultId = $i;
	        		break;
	        	}
	        }

	        //get award detail
            $result = $dalGacha->getGachaDetail($gachaInfo['id' . $gachaResultId], $gachaInfo['table' . $gachaResultId]);
            $infoAdmin = array();
            //food && item ----update  count or insert
            if ($gachaInfo['table' . $gachaResultId] != 'goods') {
            	$dataId = $gachaInfo['id' . $gachaResultId];
            	$dataTable = $gachaInfo['table' . $gachaResultId];
            	$dataSp = $gachaInfo['sp' . $gachaResultId];
            	//$dataCnt = 1;
            	if ($dataTable == 'food') {
            		$dataCnt = $dalGacha->getFoodCnt($dataId);

            		//xial add
            		$dalFood = Mdal_Kitchen_Food::getDefaultInstance();
            		$infoAdmin['count'] = $dalFood->getFoodCount($uid, $dataId);
                    $foodInfo = Mbll_Kitchen_Cache::getFood($dataId);
                    $infoAdmin['name'] = $foodInfo['food_name'];

            	}
            	else {
            		//$dataTable == 'item'
            		$dataCnt = $dalGacha->getItemCnt($dataId);

            		//xial add
            		$dalItem = Mdal_Kitchen_Item::getDefaultInstance();
                    $infoAdmin['count'] = $dalItem->getItemCount($uid, $dataId);
                    $itemInfo = Mbll_Kitchen_Cache::getItem($dataId);
                    $infoAdmin['name'] = $itemInfo['item_name'];
            		if ($dataId == 16 || $dataId == 17) {
            			$dataCnt = 3;
            		}
            	}

            	$hasData = $dalGacha->hasData($uid, $dataId, $dataTable);
            	if ($hasData) {
                    //admin page shopping info
	        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
	        		$adminDalkitchen->insert($dataTable, array('uid' => $uid,
			        										   'shop_id' => $dataId,
			        										   'shop_name' => $infoAdmin['name'],
			        										   'start_count' => $infoAdmin['count'],
			        										   'end_count' => $infoAdmin['count'] + $dataCnt,
			        										   'description' => 'gacha result:' . $infoAdmin['name'],
			        										   'create_type' => 'gacha result:' . $infoAdmin['name'],
			        										   'buy_time' => time()));

            		$dalGacha->resultUpdate($uid, $dataId, $dataTable, $dataCnt);
            	}
            	else {
            	    //admin page shopping info
	        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
	        		$adminDalkitchen->insert($dataTable, array('uid' => $uid,
			        										   'shop_id' => $dataId,
			        										   'shop_name' => $infoAdmin['name'],
			        										   'start_count' => 0,
			        										   'end_count' => $dataCnt,
			        										   'create_type' => 'gacha result:' . $infoAdmin['name'],
			        										   'description' => 'gacha result:' . $infoAdmin['name'],
			        										   'buy_time' => time()));

            		$info = array('uid' => $uid,
            		              $dataTable . '_id' => $dataId,
            		              $dataTable == 'food' ? 'food_category' : 'kitchen_only' => $dataSp,
            		              $dataTable . '_count' => $dataCnt);
            		$dalGacha->resultInsert($info, $dataTable);
            	}
            }
            //goods -- insert
            else {
            	$info = array('uid' => $uid,
            		          $gachaInfo['table' . $gachaResultId] . '_id' => $gachaInfo['id' . $gachaResultId],
            		          'create_time' => time());
            	$dalGacha->resultInsert($info, $gachaInfo['table' . $gachaResultId]);

            	//admin page shopping info
            	$dataTable = 'goods';
            	$dataId = $gachaInfo['id' . $gachaResultId];
            	$goodsInfo = Mbll_Kitchen_Cache::getGoods($gachaInfo['id' . $gachaResultId]);
            	$infoAdmin['name'] = $goodsInfo['goods_name'];

            	$dalGoods = Mdal_Kitchen_Goods::getDefaultInstance();
            	$goodsCnt = $dalGoods->getGoodsCount($uid, $dataId);
        		$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        		$adminDalkitchen->insert($dataTable, array('uid' => $uid,
        												'shop_id' => $goodsInfo['goods_id'],
        												'shop_name' => $goodsInfo['goods_name'],
        												'start_count' => $goodsCnt,
        												'end_count' => $goodsCnt + 1,
        										   		'create_type' => 'gacha result:' . $goodsInfo['goods_name'],
        												'description' => 'gacha result:' . $goodsInfo['goods_id'],
        												'buy_time' => time()));
            }

            //xial li 2010-01-16
            $gacha = array(	'gold' => 0,
				            'create_type' => 'invite_pay',
				            'buy_place' => '',
				            'start_count' => $userGachaEdit['gacha_count'] + 1);

            if ($userGacha['playing_pay'] == 2) {
                $gacha['gold'] = $gachaInfo['gacha_price_gold'];
                $gacha['start_count'] = $userGachaEdit['gacha_count'];
                $gacha['create_type'] = 'gold_pay';
                $gacha['buy_place'] = 'プレミアガチャ';
            }

            //admin page shopping info
        	$adminDalkitchen = Admin_Dal_Mykitchen::getDefaultInstance();
        	$adminDalkitchen->insert('gacha', array('uid' => $uid,
        										   'shop_id' => $gachaInfo['gacha_id'],
        										   'shop_name' => $gachaInfo['gacha_name'],
        										   'gold' => $gacha['gold'],
        										   'start_count' => $gacha['start_count'],
        										   'end_count' => $userGachaEdit['gacha_count'],
        										   'buy_place' => $gacha['buy_place'],
        										   'description' => 'gacna_id=' . $dataId,
        										   'create_type' => $gacha['create_type'] . ';result ' . $dataTable .':' . $infoAdmin['name'],
        										   'buy_time' => time()));

            $this->_wdb->commit();

        }
        catch (Exception $e){
            //debug_log('Mbll_Kitchen_Gacha e: ' . $e->getMessage());
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }
}