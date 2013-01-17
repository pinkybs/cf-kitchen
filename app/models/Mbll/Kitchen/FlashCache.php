<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/** @see Mbll_Kitchen_Tt */
require_once 'Mbll/Kitchen/Tt.php';

define('FLASH_TPL_ROOT', ROOT_DIR . '/swf_xml');

/**
 * flash Cache
 *
 * @package    Mbll/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class Mbll_Kitchen_FlashCache
{
    private static $_prefix = 'Mbll_Kitchen_FlashCache';

    /**
     * get cache key
     *
     * @param string $salt
     * @param mixi $params
     * @return string
     */
    private static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get chara select flash
     *
     * @param integer $uid
     * @param string $mixiUrl
     * @return stream flash
     */
    public static function getChangeChara($uid, $mixiUrl)
    {
        $filename = $uid . '_changechara.swf.gz';
        $tt = new Mbll_Kitchen_Tt($uid);
        
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/kitchenfirst/editchara';

        require_once 'Mdal/Kitchen/Chef.php';
        $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
        $rowChef = $mdalChef->getChef($uid);

        //flash info combine and replace
        $aryVar = array();
        if (empty($rowChef)) {
            $aryVar['preset'] = 1;
        }
        else {
            $aryVar['preset'] = '';
            $aryVar['face'] = $rowChef['face'];
            $aryVar['eye'] = $rowChef['eye'];
            $aryVar['head_m'] = $rowChef['head_m'];
            $aryVar['ear'] = $rowChef['ear'];
            $aryVar['rabbit'] = $rowChef['rabbit'];
            $aryVar['eyemask'] = $rowChef['eyemask'];
        }
        $aryVar['express'] = 2;
        $aryVar['baseUrl'] = $mixiMobileBaseUrl . urlencode($appUrl);

        //flash cache deal
        $strTmp = http_build_query($aryVar);
        $cacheVal = md5($uid . $strTmp);
        $cacheKey = self::getCacheKey('getChangeChara', $uid);
        $savedCacheInfo = Bll_Cache::get($cacheKey);
        
        //load from cache
        if ($savedCacheInfo && $savedCacheInfo == $cacheVal) {
            if ($tt->hasObject($filename)) {
                $swfOutput = $tt->getObject($filename);
                return $swfOutput;
            }
        }

        //reset cache
        //get xml and replace values
        $xmlString = file_get_contents(FLASH_TPL_ROOT . '/changechara.xml');
        foreach ($aryVar as $key=>$value) {
            $xmlString = str_replace("{%" . $key . "%}", $value, $xmlString);
        }

        //set process param
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", ROOT_DIR . "/logs/kitchen_xml2swf_error.txt", "a")
        );

        $pipes = array();
        //XML to SWF
        // run swfmill and get $process
        $process = proc_open(SWFMILL_DIR . ' -e cp932 xml2swf stdin stdout', $descriptorspec, $pipes);

        if (is_resource($process)) {
            // set param $xmlString
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);

            self::swfTimeOut($pipes[1], array('uid'=>$uid, 'function'=>'getChangeChara'));
            
            // get $swfOutput
            $swfOutput = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // close $process
            proc_close($process);
        }

        if ($swfOutput) {
            $tt->saveObject($filename, $swfOutput);
            Bll_Cache::set($cacheKey, $cacheVal, Bll_Cache::LIFE_TIME_ONE_DAY);
        }

        return $swfOutput;
    }

    public static function getProfile($uid)
    {
        $filename = $uid . '_profile.swf';
        $tt = new Mbll_Kitchen_Tt($uid);
        
        if ($tt->hasObject($filename)) {
            return $tt->getFlashUrl($filename);
        }
        
        require_once 'Mdal/Kitchen/Chef.php';
        $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
        $rowChef = $mdalChef->getChef($uid);
        if (empty($rowChef)) {
            return '';
        }

        $nbColor = self::_getNbColor();
        $aryVar = array();
        $aryVar['mTxt'] = '';
        $aryVar['express'] = 'normal';//normal/sad/happy/boo
        //顔
        $faceId = $rowChef['face'];
        $aryVar['face_base_r'] = $nbColor[$faceId]['base_r'];
        $aryVar['face_base_g'] = $nbColor[$faceId]['base_g'];
        $aryVar['face_base_b'] = $nbColor[$faceId]['base_b'];
        $eyeId = $rowChef['eye'];
        $aryVar['eye_base_r'] = $nbColor[$eyeId]['base_r'];
        $aryVar['eye_base_g'] = $nbColor[$eyeId]['base_g'];
        $aryVar['eye_base_b'] = $nbColor[$eyeId]['base_b'];
        $head_mId = $rowChef['head_m'];
        $aryVar['head_m_base_r'] = $nbColor[$head_mId]['base_r'];
        $aryVar['head_m_base_g'] = $nbColor[$head_mId]['base_g'];
        $aryVar['head_m_base_b'] = $nbColor[$head_mId]['base_b'];
        $earId = $rowChef['ear'];
        $aryVar['ear_base_r'] = $nbColor[$earId]['base_r'];
        $aryVar['ear_base_g'] = $nbColor[$earId]['base_g'];
        $aryVar['ear_base_b'] = $nbColor[$earId]['base_b'];

        //ウサギ耳
        $rabbit = $rowChef['rabbit'];
        $aryVar['rabbit'] = $rowChef['rabbit'];
        $aryVar['ear2b_base_r'] = $nbColor[$earId]['ear2b_r'];
        $aryVar['ear2b_base_g'] = $nbColor[$earId]['ear2b_g'];
        $aryVar['ear2b_base_b'] = $nbColor[$earId]['ear2b_b'];

        //目の周囲
        $eyemask = $rowChef['eyemask'];
        if (1 == $eyemask) {
            $aryVar['eyemask_base_r'] = $nbColor[$faceId]['base_r'];
            $aryVar['eyemask_base_g'] = $nbColor[$faceId]['base_g'];
            $aryVar['eyemask_base_b'] = $nbColor[$faceId]['base_b'];
        }
        else if (2 == $eyemask) {
            $aryVar['eyemask_base_r'] = 255;
            $aryVar['eyemask_base_g'] = 255;
            $aryVar['eyemask_base_b'] = 255;
        }
        else {
            $aryVar['eyemask_base_r'] = $nbColor[$faceId]['eyemask_r'];
            $aryVar['eyemask_base_g'] = $nbColor[$faceId]['eyemask_g'];
            $aryVar['eyemask_base_b'] = $nbColor[$faceId]['eyemask_b'];
        }

        //鼻・口
        $aryVar['mouth_base_r'] = $aryVar['blow_base_r'] = 0;
        $aryVar['mouth_base_g'] = $aryVar['blow_base_g'] = 0;
        $aryVar['mouth_base_b'] = $aryVar['blow_base_b'] = 0;
        if (1 == $faceId) {
            $aryVar['mouth_base_r'] = $aryVar['blow_base_r'] = 255;
            $aryVar['mouth_base_g'] = $aryVar['blow_base_g'] = 255;
            $aryVar['mouth_base_b'] = $aryVar['blow_base_b'] = 255;
        }

        $xmlString = file_get_contents(FLASH_TPL_ROOT . '/profile.xml');

        foreach ($aryVar as $key=>$value) {
            $xmlString = str_replace("{%" . $key . "%}", $value, $xmlString);
        }

        //set process param
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", ROOT_DIR . "/logs/kitchen_xml2swf_error.txt", "a")
        );

        $pipes = array();
        //XML to SWF
        // run swfmill and get $process
        $process = proc_open(SWFMILL_DIR . ' -e cp932 xml2swf stdin stdout', $descriptorspec, $pipes);

        if (is_resource($process)) {
            // set param $xmlString
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);

            self::swfTimeOut($pipes[1], array('uid'=>$uid, 'function'=>'getProfile'));
            
            $swfOutput = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // close $process
            proc_close($process);
        }

        if ($swfOutput) {
            $tt->saveObject($filename, $swfOutput);
        }

        return $tt->getFlashUrl($filename);
    }

    /**
     * get setgoods flash
     *
     * @param string $uid
     * @param string $goodsId
     * @param string $mixiUrl
     * @return swf
     */
    public static function getSetGoods($uid, $goodsId, $pay, $mixiUrl)
    {
    	$filename = $uid . '_setgoods.swf.gz';
    	$tt = new Mbll_Kitchen_Tt($uid);
    	
        $mixiMobileBaseUrl = $mixiUrl;

        $appUrl = Zend_Registry::get('host') . '/mobile/kitchenshop/goodsconfirm/pay/' . $pay;
        
        require_once 'Mdal/Kitchen/Restaurant.php';
		$mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
		$rowRes = $mdalRes->getActiveRestaurant($uid);
        require_once 'Mdal/Kitchen/Goods.php';
        $mdalGoods = Mdal_Kitchen_Goods::getDefaultInstance();
        $arrGoodsPosition = $mdalGoods->getGoodsPosition($uid, (int)$rowRes['genre']);

        //flash info combine and replace
        $aryVar = array();
        if (empty($arrGoodsPosition)) {
            //$aryVar['preset'] = 1;
        }
        else {
        	//get 30 flg vars
        	$cnt = count($arrGoodsPosition);
        	$j = 0;
        	for ($i = 0; $i < $cnt; $i++) {
        		for ($j = $j + 1; $j < $arrGoodsPosition[$i]['position']; $j++){
		        	$aryVar['b' . $j . '_flg'] = 0;
		            //$aryVar['preset'] = '';
        		}
        		$aryVar['b' . $j . '_flg'] = 1;
        	}
        	for ($j = $j + 1; $j <= 30; $j++ )
        	{
        		$aryVar['b' . $j . '_flg'] = 0;
        	}
        }
        $aryVar['vid'] = $goodsId;
        $goodsId = substr($goodsId,0,3);
        $aryVar['baseUrl'] = $mixiMobileBaseUrl . urlencode($appUrl);
        $aryVar['oid'] = '';

        require_once 'Mdal/Kitchen/Estate.php';
        $mdalEstate = Mdal_Kitchen_Estate::getDefaultInstance();

        $aryVar['bg'] = self::_getBgData((int)$rowRes['genre']);
        $rowEstate = $mdalEstate->getEstate((int)$rowRes['estate'], (int)$rowRes['genre']);
        $aryVar['building'] = self::_getBuildingData($rowEstate['estate_picture']);

        $aryDefineObjectId = array('b01'=>'5','b02'=>'13','b03'=>'29','b04'=>'47','b05'=>'9',
                                   'b06'=>'21','b07'=>'39','b08'=>'57','b09'=>'15','b10'=>'31',
                                   'b11'=>'49','b12'=>'23','b13'=>'41','b14'=>'59','b15'=>'33',
                                   'b16'=>'51','b17'=>'25','b18'=>'43','b19'=>'61','b20'=>'17',
                                   'b21'=>'35','b22'=>'53','b23'=>'11','b24'=>'27','b25'=>'45',
                                   'b26'=>'63','b27'=>'7','b28'=>'19','b29'=>'37','b30'=>'55');
        $lstGoodsPos = $arrGoodsPosition;
        $arySetted = array();
        $arySettedGoods = array();
        $aryVar['b01_30'] = '';
        foreach ($lstGoodsPos as $keyIdx=>$pdata) {
			$arySetted[] = $pdata['position'];
			$arySettedGoods[] = $pdata['goods_id'];
        }
        for ($pos=1; $pos<=30; $pos++) {
        	$posKey = array_search($pos, $arySetted);
        	$strDefineShape = '';
        	if ($posKey === false) {
        		//$i = $pos < 10 ? ('0' . $pos) : $pos;
				//$aryVar['b'.$i] = ' ';//self::_getNbPosion($pos);
        	}
        	else {
        		$rowNbGoods = self::_getNbGoods($arySettedGoods[$posKey]);
        		$i = $pos < 10 ? ('0' . $pos) : $pos;
        		$strDefineShape = ' <DefineShape2 objectID="' . $aryDefineObjectId['b'.$i] . '">' . "\n";
        		$strDefineShape .= self::_getGoodsData($rowNbGoods['goods_picture']) . "\n";
        		$strDefineShape .= ' </DefineShape2>'. "\n";
        	}

        	if (!empty($strDefineShape)) {
        		$aryVar['b01_30'] .= $strDefineShape;
        	}
        }
        $focusGoods = $mdalGoods->getGoods($goodsId);
        $aryVar['object'] = self::_getGoodsData($focusGoods['goods_picture']);

        //flash cache deal
        $strTmp = http_build_query($aryVar);
        $cacheVal = md5($uid . $strTmp);
        $cacheKey = self::getCacheKey('getSetGoods', $uid);
        $savedCacheInfo = Bll_Cache::get($cacheKey);

        //load from cache
        if ($savedCacheInfo && $savedCacheInfo == $cacheVal) {
            //$cacheFile = TEMP_DIR . '/kitchen' . self::_getSavedDir($uid) . $uid . '_setgoods.swf.gz';
        	if ($tt->hasObject($filename)) {
                $swfOutput = $tt->getObject($filename);
                return $swfOutput;
            }
        }
        //reset cache
        //get xml and replace values
        $xmlString = file_get_contents(FLASH_TPL_ROOT . '/setgoods.xml');

        foreach ($aryVar as $key=>$value) {
            $xmlString = str_replace("{%" . $key . "%}", $value, $xmlString);
        }

        //set process param
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", ROOT_DIR . "/logs/kitchen_xml2swf_error.txt", "a")
        );

        $pipes = array();
        //XML to SWF
        // run swfmill and get $process
        $process = proc_open(SWFMILL_DIR . ' -e cp932 xml2swf stdin stdout', $descriptorspec, $pipes);

        if (is_resource($process)) {
            // set param $xmlString
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);

            self::swfTimeOut($pipes[1], array('uid'=>$uid, 'function'=>'getSetGoods'));
            
            // get $swfOutput
            $swfOutput = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // close $process
            proc_close($process);
        }

    	if ($swfOutput) {
            $tt->saveObject($filename, $swfOutput);
            Bll_Cache::set($cacheKey, $cacheVal, Bll_Cache::LIFE_TIME_ONE_DAY);
        }

        return $swfOutput;
    }


	public static function getGacha($uid, $mixiUrl)
    {
    	$filename = $uid . '_gacha.swf.gz';
    	$tt = new Mbll_Kitchen_Tt($uid);
    	
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/kitchenshop/gachafinish';
        $aryVar['requrl'] = $mixiMobileBaseUrl . urlencode($appUrl);

        //flash cache deal
        $strTmp = http_build_query($aryVar);
        $cacheVal = md5($uid . $strTmp);
        $cacheKey = self::getCacheKey('getGacha', $uid);
        $savedCacheInfo = Bll_Cache::get($cacheKey);

        //load from cache
        if ($savedCacheInfo && $savedCacheInfo == $cacheVal) {
            //$cacheFile = TEMP_DIR . '/kitchen' . self::_getSavedDir($uid) . $uid . '_gacha.swf.gz';
        	if ($tt->hasObject($filename)) {
                $swfOutput = $tt->getObject($filename);
                return $swfOutput;
            }
        }
        //reset cache
        //get xml and replace values
        $xmlString = file_get_contents(FLASH_TPL_ROOT . '/gacha.xml');

        foreach ($aryVar as $key=>$value) {
            $xmlString = str_replace("{%" . $key . "%}", $value, $xmlString);
        }
        //set process param
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", ROOT_DIR . "/logs/kitchen_xml2swf_error.txt", "a")
        );

        $pipes = array();
        //XML to SWF
        // run swfmill and get $process
        $process = proc_open(SWFMILL_DIR . ' -e cp932 xml2swf stdin stdout', $descriptorspec, $pipes);

        if (is_resource($process)) {
            // set param $xmlString
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);

            self::swfTimeOut($pipes[1], array('uid'=>$uid, 'function'=>'getGacha'));
            
            // get $swfOutput
            $swfOutput = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // close $process
            proc_close($process);
        }

        if ($swfOutput) {
            $tt->saveObject($filename, $swfOutput);
            Bll_Cache::set($cacheKey, $cacheVal, Bll_Cache::LIFE_TIME_ONE_DAY);
        }

        return $swfOutput;
    }

    /**
     * get restaurant flash (static)
     *
     * @param integer $uid
     * @param integer $profileUid
     * @return string flash file path
     */
    public static function getRestaurant($uid, $profileUid)
    {
    	/*
        if (0 == Zend_Registry::get('ua')) {
            return Zend_Registry::get('static'). '/apps/kitchen/mobile/swf/restaurant.swf?CF_vsrsion=' . time();
        }
		*/
    	
    	$isSelf = 0;
        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
            $isSelf = 1;
        }

    	$filename = $profileUid . '_' . $isSelf . '_restaurant';
		$tt = new Mbll_Kitchen_Tt($profileUid);
    	
        require_once 'Bll/User.php';
        require_once 'Mdal/Kitchen/Chef.php';
        $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
        $rowChef = $mdalChef->getChef($profileUid);
        if (empty($rowChef)) {
            return '';
        }

        require_once 'Mdal/Kitchen/User.php';
        $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
        $rowUser = $mdalUser->getUser($profileUid);

        $aryVar = array();
        //bg & building & zakka
        require_once 'Mdal/Kitchen/Goods.php';
        $mdalGoods = Mdal_Kitchen_Goods::getDefaultInstance();
        require_once 'Mdal/Kitchen/Estate.php';
        $mdalEstate = Mdal_Kitchen_Estate::getDefaultInstance();
        require_once 'Mdal/Kitchen/Restaurant.php';
		$mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
		$rowRes = $mdalRes->getActiveRestaurant($profileUid);
        $aryVar['bg'] = self::_getBgData((int)$rowRes['genre']);
        $rowEstate = $mdalEstate->getEstate((int)$rowRes['estate'], (int)$rowRes['genre']);
        $aryVar['building'] = self::_getBuildingData($rowEstate['estate_picture']);

        $aryDefineObjectId = array('b01'=>'11','b02'=>'19','b03'=>'35','b04'=>'53','b05'=>'15',
                                   'b06'=>'27','b07'=>'45','b08'=>'63','b09'=>'21','b10'=>'37',
                                   'b11'=>'55','b12'=>'29','b13'=>'47','b14'=>'65','b15'=>'39',
                                   'b16'=>'57','b17'=>'31','b18'=>'49','b19'=>'67','b20'=>'23',
                                   'b21'=>'41','b22'=>'59','b23'=>'17','b24'=>'33','b25'=>'51',
                                   'b26'=>'69','b27'=>'13','b28'=>'25','b29'=>'43','b30'=>'61');
        $lstGoodsPos = $mdalGoods->getGoodsPosition($profileUid, (int)$rowRes['genre']);
        $arySetted = array();
        $arySettedGoods = array();
        $aryVar['b01_30'] = '';
        foreach ($lstGoodsPos as $keyIdx=>$pdata) {
			$arySetted[] = $pdata['position'];
			$arySettedGoods[] = $pdata['goods_id'];
        }
        for ($pos=1; $pos<=30; $pos++) {
        	$posKey = array_search($pos, $arySetted);
        	$strDefineShape = '';
        	if ($posKey === false) {
        		//$i = $pos < 10 ? ('0' . $pos) : $pos;
				//$aryVar['b'.$i] = ' ';//self::_getNbPosion($pos);
        	}
        	else {
        		$rowNbGoods = self::_getNbGoods($arySettedGoods[$posKey]);
        		$i = $pos < 10 ? ('0' . $pos) : $pos;
        		$strDefineShape = ' <DefineShape2 objectID="' . $aryDefineObjectId['b'.$i] . '">' . "\n";
        		$strDefineShape .= self::_getGoodsData($rowNbGoods['goods_picture']) . "\n";
        		$strDefineShape .= ' </DefineShape2>'. "\n";
        	}

        	if (!empty($strDefineShape)) {
        		$aryVar['b01_30'] .= $strDefineShape;
        	}
        }

        /*
        //normal 表情 /sad 表情  / happy 表情  /boo 表情
        $msgText = '';
        $express = 'normal';
        require_once 'Mbll/Kitchen/Cache.php';
        require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $lstKitchen = $mdalKitchen->getUserKitchenAll($profileUid);
    	$rcpName = '';
    	$hasFly = false;
    	$needSpice = false;
    	$allCooking = true;
        foreach ($lstKitchen as $key=>$kData) {
        	if (!empty($kData['cooking_recipe_id']) && !empty($kData['cooking_start_time']) && !empty($kData['cooking_part1'])) {
        		$nowTime = time();
        		$needSeconds = ((int)$kData['cooking_part1'] + (int)$kData['cooking_part2'] + (int)$kData['cooking_part3'])*60;
				if (($kData['cooking_start_time'] + (int)$kData['cooking_part1'] * 60 <= $nowTime) &&
				    ($kData['cooking_start_time'] + $needSeconds > $nowTime)) {
        			$rowKitchenSpice = $mdalKitchen->getKitchenSpice($profileUid, $kData['kitchen_id'], $uid);
					if (empty($rowKitchenSpice)) {
						$needSpice = true;
					}
        		}
        		
        		if ($kData['cooking_start_time'] + $needSeconds > $nowTime) {
	        		if (1 == $kData['has_fly']) {
	        			$hasFly = true;
	        		}
        		}
        		
        		if (empty($rcpName)) {
	        		$nowTime = time();
	        		$needSeconds = ((int)$kData['cooking_part1'] + (int)$kData['cooking_part2'] + (int)$kData['cooking_part3'])*60;
	        		if ($kData['cooking_start_time'] + $needSeconds <= $nowTime) {
	        			$nbRecipe = Mbll_Kitchen_Cache::getRecipe($kData['cooking_recipe_id']);
			        	$rcpName = $nbRecipe['recipe_name'];
	        		}
        		}
        	}
        	else {
        		$allCooking = false;
        	}
        }

        $profileInfo = array('uid' => $profileUid);
        Bll_User::appendPerson($profileInfo, 'uid');

        if ($isSelf) {
        	require_once 'Mdal/Kitchen/InviteSuccess.php';
            $mdalInvite = Mdal_Kitchen_InviteSuccess::getDefaultInstance();
            require_once 'Mdal/Kitchen/Gift.php';
            $mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();

            //初回表示のみ
			if (0 == $rowUser['total_exp'] && 1 == $rowUser['total_level']) {
				$msgText = 'オーナー！今日から一流のお店を目指して一緒にがんばっていこうね♪';
				$express = 'normal';
			}
			//招待した友人がアプリを追加
			else if (($cntInvite = $mdalInvite->getInviteSuccessCount($profileUid)) > 0) {
				$msgText = "招待したマイミクさんがレストランを開店したよ～。遊びに行ってみようよぉ～";
				$express = 'happy';
			}
			//マイミクからギフトが未開封
			else if (($sendUid = $mdalGift->hasGiftFromFriend($profileUid)) != null) {
				//$friendInfo = array('uid' => $sendUid);
				//Bll_User::appendPerson($friendInfo, 'uid');
				$msgText = "オーナー宛てに、マイミクさんからギフトが届いてるよ～。何をくれたのかなぁ？";
				$express = 'happy';
			}
        	//ごほうびギフトが未開封
			else if ($mdalGift->hasGiftFromLevelUp($profileUid) != null) {
				$msgText = 'まだ未開封のごほうびギフトがあるみたいだよ～。はやく開けてみようよ～';
				$express = 'happy';
			}
        	//デイリーギフトが未開封
			else if ($mdalGift->hasGiftFromVisit($profileUid) != null) {
				$msgText = 'オーナー宛てに、デイリーギフトが届いているよ～。何が入ってるのかなぁ？';
				$express = 'happy';
			}
			//完成した料理あり
        	else if ($rcpName) {
				$msgText = $rcpName . 'が完成してるよ～。温かいうちに運んで欲しいなぁ…';
				$express = 'happy';
			}
        }

        //
        if (!$msgText) {
			if (!$allCooking) {
        		$msgText = 'はやくレシピを選択してもらって、次の料理に取り掛かりたいんだけどなぁ…';
				$express = 'boo';
			}
			else if ($hasFly) {
	        	$msgText = 'ハエが邪魔で調理に集中できないよ～。なんとかして欲しいんだけどなぁ…';
				$express = 'sad';
			}
			else if ($needSpice) {
        		$msgText = 'な～んか、味付けが足りない気がするんだなぁ。この仕事、向いてないのかなぁ…';
				$express = 'boo';
			}
			else {
	        	$msgText = 'オーナーに満足してもらうために、今日も一生懸命はたらいてるんだよ～';
				$express = 'normal';
			}
        }
        $aryVar['mTxt'] = htmlspecialchars(MyLib_String::unescapeString($msgText), ENT_QUOTES, 'UTF-8');
        $aryVar['express'] = $express;//normal/sad/happy/boo

        $nbColor = self::_getNbColor();
        //顔
        $faceId = $rowChef['face'];
        $aryVar['face_base_r'] = $nbColor[$faceId]['base_r'];
        $aryVar['face_base_g'] = $nbColor[$faceId]['base_g'];
        $aryVar['face_base_b'] = $nbColor[$faceId]['base_b'];
        $eyeId = $rowChef['eye'];
        $aryVar['eye_base_r'] = $nbColor[$eyeId]['base_r'];
        $aryVar['eye_base_g'] = $nbColor[$eyeId]['base_g'];
        $aryVar['eye_base_b'] = $nbColor[$eyeId]['base_b'];
        $head_mId = $rowChef['head_m'];
        $aryVar['head_m_base_r'] = $nbColor[$head_mId]['base_r'];
        $aryVar['head_m_base_g'] = $nbColor[$head_mId]['base_g'];
        $aryVar['head_m_base_b'] = $nbColor[$head_mId]['base_b'];
        $earId = $rowChef['ear'];
        $aryVar['ear_base_r'] = $nbColor[$earId]['base_r'];
        $aryVar['ear_base_g'] = $nbColor[$earId]['base_g'];
        $aryVar['ear_base_b'] = $nbColor[$earId]['base_b'];

        //ウサギ耳
        $rabbit = $rowChef['rabbit'];
        $aryVar['rabbit'] = $rowChef['rabbit'];
        $aryVar['ear2b_base_r'] = $nbColor[$earId]['ear2b_r'];
        $aryVar['ear2b_base_g'] = $nbColor[$earId]['ear2b_g'];
        $aryVar['ear2b_base_b'] = $nbColor[$earId]['ear2b_b'];

        //目の周囲
        $eyemask = $rowChef['eyemask'];
        if (1 == $eyemask) {
            $aryVar['eyemask_base_r'] = $nbColor[$faceId]['base_r'];
            $aryVar['eyemask_base_g'] = $nbColor[$faceId]['base_g'];
            $aryVar['eyemask_base_b'] = $nbColor[$faceId]['base_b'];
        }
        else if (2 == $eyemask) {
            $aryVar['eyemask_base_r'] = 255;
            $aryVar['eyemask_base_g'] = 255;
            $aryVar['eyemask_base_b'] = 255;
        }
        else {
            $aryVar['eyemask_base_r'] = $nbColor[$faceId]['eyemask_r'];
            $aryVar['eyemask_base_g'] = $nbColor[$faceId]['eyemask_g'];
            $aryVar['eyemask_base_b'] = $nbColor[$faceId]['eyemask_b'];
        }

        //鼻・口
        $aryVar['mouth_base_r'] = $aryVar['blow_base_r'] = 0;
        $aryVar['mouth_base_g'] = $aryVar['blow_base_g'] = 0;
        $aryVar['mouth_base_b'] = $aryVar['blow_base_b'] = 0;
        if (1 == $faceId) {
            $aryVar['mouth_base_r'] = $aryVar['blow_base_r'] = 255;
            $aryVar['mouth_base_g'] = $aryVar['blow_base_g'] = 255;
            $aryVar['mouth_base_b'] = $aryVar['blow_base_b'] = 255;
        }
		*/
		
        //flash cache deal
        $strTmp = http_build_query($aryVar);
        $cacheVal = md5($profileUid . $strTmp);
        $cacheKey = self::getCacheKey('getRestaurant2', $profileUid . $isSelf);
        $savedCacheInfo = Bll_Cache::get($cacheKey);
        
    	//load from cache
        if ($savedCacheInfo && $savedCacheInfo == $cacheVal) {
        	if ($tt->hasObject($filename)) {
                return $tt->getFlashUrl($filename);
            }
        }
        
        //get xml and replace values
        $xmlString = file_get_contents(FLASH_TPL_ROOT . '/restaurantt.xml');
        foreach ($aryVar as $key=>$value) {
            $xmlString = str_replace("{%" . $key . "%}", $value, $xmlString);
        }

        //set process param
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", ROOT_DIR . "/logs/kitchen_xml2swf_error.txt", "a")
        );

        $pipes = array();
        //XML to SWF
        // run swfmill and get $process
        $process = proc_open(SWFMILL_DIR . ' -e cp932 xml2swf stdin stdout', $descriptorspec, $pipes);

        if (is_resource($process)) {
            // set param $xmlString
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);

            self::swfTimeOut($pipes[1], array('uid'=>$uid, 'function'=>'getRestaurant'));
            
            // get $swfOutput
            $swfOutput = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // close $process
            proc_close($process);
        }
        
        if ($swfOutput) {
            $tt->saveObject($filename, $swfOutput);
            Bll_Cache::set($cacheKey, $cacheVal, Bll_Cache::LIFE_TIME_ONE_WEEK);
        }
        return $tt->getFlashUrl($filename);
    }

	/**
     * get kitchen flash
     *
     * @param integer $uid
     * @param integer $profileUid
     * @param string $mixiUrl
     * @return stream flash
     */
    public static function getKitchen($uid, $profileUid, $mixiUrl)
    {
        $isSelf = 0;
        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
            $isSelf = 1;
        }
  
        $filename = $profileUid . '_' . $isSelf . '_kitchen.swf.gz';
        $tt = new Mbll_Kitchen_Tt($profileUid);
        
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/kitchen/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/kitchenflash/';

        require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $lstKitchen = $mdalKitchen->getUserKitchenAll($profileUid);
        if (empty($lstKitchen) || count($lstKitchen) <=0) {
        	return false;
        }

        //auto set fly logic
        require_once 'Mbll/Kitchen/Kitchen.php';
        $mbllKitchen = new Mbll_Kitchen_Kitchen();
        $blnSeted = false;
        foreach ($lstKitchen as $kitData) {
			if ($mbllKitchen->autoSetFly($profileUid, $kitData['kitchen_id'])) {
				$blnSeted = true;
			}
        }
        if ($blnSeted) {
        	$lstKitchen = $mdalKitchen->getUserKitchenAll($profileUid);
        }

        require_once 'Mdal/Kitchen/Restaurant.php';
		$mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
		$rowRes = $mdalRes->getActiveRestaurant($profileUid);
        require_once 'Mdal/Kitchen/Recipe.php';
		$mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
    	require_once 'Mdal/Kitchen/User.php';
		$mdalUser = Mdal_Kitchen_User::getDefaultInstance();
		$rowProfileUser = $mdalUser->getUser($profileUid);
		//$rowUserSelf = $mdalUser->getUser($uid);

        require_once 'Mdal/Kitchen/Chef.php';
        $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
        $rowChef = $mdalChef->getChef($profileUid);
        $nbColor = self::_getNbColor();
        $aryVar = array();
        //顔
        $faceId = $rowChef['face'];
        $aryVar['face_base_r'] = $nbColor[$faceId]['base_r'];
        $aryVar['face_base_g'] = $nbColor[$faceId]['base_g'];
        $aryVar['face_base_b'] = $nbColor[$faceId]['base_b'];
        $eyeId = $rowChef['eye'];
        $aryVar['eye_base_r'] = $nbColor[$eyeId]['base_r'];
        $aryVar['eye_base_g'] = $nbColor[$eyeId]['base_g'];
        $aryVar['eye_base_b'] = $nbColor[$eyeId]['base_b'];
        $head_mId = $rowChef['head_m'];
        $aryVar['head_m_base_r'] = $nbColor[$head_mId]['base_r'];
        $aryVar['head_m_base_g'] = $nbColor[$head_mId]['base_g'];
        $aryVar['head_m_base_b'] = $nbColor[$head_mId]['base_b'];
        $earId = $rowChef['ear'];
        $aryVar['ear_base_r'] = $nbColor[$earId]['base_r'];
        $aryVar['ear_base_g'] = $nbColor[$earId]['base_g'];
        $aryVar['ear_base_b'] = $nbColor[$earId]['base_b'];

        //ウサギ耳
        $rabbit = $rowChef['rabbit'];
        $aryVar['rabbit'] = $rowChef['rabbit'];
        $aryVar['ear2b_base_r'] = $nbColor[$earId]['ear2b_r'];
        $aryVar['ear2b_base_g'] = $nbColor[$earId]['ear2b_g'];
        $aryVar['ear2b_base_b'] = $nbColor[$earId]['ear2b_b'];

        //目の周囲
        $eyemask = $rowChef['eyemask'];
        if (1 == $eyemask) {
            $aryVar['eyemask_base_r'] = $nbColor[$faceId]['base_r'];
            $aryVar['eyemask_base_g'] = $nbColor[$faceId]['base_g'];
            $aryVar['eyemask_base_b'] = $nbColor[$faceId]['base_b'];
        }
        else if (2 == $eyemask) {
            $aryVar['eyemask_base_r'] = 255;
            $aryVar['eyemask_base_g'] = 255;
            $aryVar['eyemask_base_b'] = 255;
        }
        else {
            $aryVar['eyemask_base_r'] = $nbColor[$faceId]['eyemask_r'];
            $aryVar['eyemask_base_g'] = $nbColor[$faceId]['eyemask_g'];
            $aryVar['eyemask_base_b'] = $nbColor[$faceId]['eyemask_b'];
        }

        //鼻・口
        $aryVar['mouth_base_r'] = $aryVar['blow_base_r'] = 0;
        $aryVar['mouth_base_g'] = $aryVar['blow_base_g'] = 0;
        $aryVar['mouth_base_b'] = $aryVar['blow_base_b'] = 0;
        if (1 == $faceId) {
            $aryVar['mouth_base_r'] = $aryVar['blow_base_r'] = 255;
            $aryVar['mouth_base_g'] = $aryVar['blow_base_g'] = 255;
            $aryVar['mouth_base_b'] = $aryVar['blow_base_b'] = 255;
        }

        $aryVar['bg1'] = self::_getKitchenBg($rowRes['genre'], $rowRes['estate'], 1);
        $aryVar['bg2'] = self::_getKitchenBg($rowRes['genre'], $rowRes['estate'], 2);
        $aryHdRGB = array();
        $aryHdRGB['y1'] = array('r' => 109, 'g' => 151, 'b' => 205);
        $aryHdRGB['y2'] = array('r' => 159, 'g' => 121, 'b' => 85);
        $aryHdRGB['y3'] = array('r' => 109, 'g' => 46, 'b' => 15);
        $aryHdRGB['y4'] = array('r' => 119, 'g' => 46, 'b' => 29);
        $aryHdRGB['y5'] = array('r' => 247, 'g' => 99, 'b' => 135);
        $aryHdRGB['r1'] = array('r' => 171, 'g' => 222, 'b' => 252);
        $aryHdRGB['r2'] = array('r' => 123, 'g' => 191, 'b' => 61);
        $aryHdRGB['r3'] = array('r' => 153, 'g' => 225, 'b' => 223);
        $aryHdRGB['r4'] = array('r' => 56, 'g' => 164, 'b' => 162);
        $aryHdRGB['r5'] = array('r' => 247, 'g' => 99, 'b' => 135);
    	$hdBgKey = 'y';
        if (2 == $rowRes['genre']) {
        	$hdBgKey = 'r';
        }
        $hdBgKey .= ((int)$rowRes['estate'] - 1);
        $aryVar['hdrbg_r'] = $aryHdRGB[$hdBgKey]['r'];
        $aryVar['hdrbg_g'] = $aryHdRGB[$hdBgKey]['g'];
        $aryVar['hdrbg_b'] = $aryHdRGB[$hdBgKey]['b'];

        if ($isSelf) {
            $aryVar['fwdTop'] = $mixiMobileBaseUrl . urlencode($appUrl . 'home?CF_flash=kitchen');
        }
        else {
            $aryVar['fwdTop'] = $mixiMobileBaseUrl . urlencode($appUrl . 'home?CF_uid=' . $profileUid);
        }

        $aryVar['baseUrl'] = $mixiMobileBaseUrl . urlencode($appUrl . 'flashcallback/CF_flash/kitchen');
        $aryVar['title'] = htmlspecialchars(MyLib_String::unescapeString($rowRes['name']), ENT_QUOTES, 'UTF-8');
        $aryVar['vid'] = $uid;
        $aryVar['oid'] = $profileUid;
        $aryVar['maxmove'] = count($lstKitchen);

        require_once 'Mbll/Kitchen/Cache.php';
        foreach ($lstKitchen as $key=>$kData) {
        	$i = $key + 1;
        	//kitchen status
        	if (empty($kData['cooking_recipe_id']) || empty($kData['cooking_start_time'])) {
				$aryVar['b'.$i.'_st'] = 2;//未選択
				//image
				$aryVar['meal_'.$i] = '';
				//name
				$aryVar['b'.$i.'_ft'] = '';
				//remain time
				$aryVar['b'.$i.'_tt'] = '';
				//proc pecent
				$aryVar['b'.$i.'_tm'] = '';
				//quantity
				$aryVar['b'.$i.'_qt'] = '';
				//val pecent
				$aryVar['b'.$i.'_vl'] = '';
				//point
				$aryVar['b'.$i.'_pr'] = '';
				//val num
				$aryVar['b'.$i.'_rm'] = '';
				//fly flg
				$aryVar['b'.$i.'_fly'] = '';
				//spice flg
				$aryVar['b'.$i.'_spice'] = '';
				//taste flg
				$aryVar['b'.$i.'_try'] = '';
        	}
        	else {
        		//name
				$nbRecipe = Mbll_Kitchen_Cache::getRecipe($kData['cooking_recipe_id']);
		        $aryVar['b'.$i.'_ft'] = htmlspecialchars($nbRecipe['recipe_name'], ENT_QUOTES, 'UTF-8');
        		$nowTime = time();
	            $needSeconds = ((int)$kData['cooking_part1'] + (int)$kData['cooking_part2'] + (int)$kData['cooking_part3'])*60;
	            $rowUsrRecipe = $mdalRecipe->getUserRecipeById($profileUid, $kData['cooking_recipe_id']);
	            if ($kData['cooking_start_time'] + $needSeconds > $nowTime) {
					$aryVar['b'.$i.'_st'] = 3;//調理中
					$procTime = $nowTime-$kData['cooking_start_time'];
					$strRemain = '';
					//proc 1
					if ($procTime < (int)$kData['cooking_part1']*60) {
						$strRemain = (int)$kData['cooking_part1']*60 - $procTime;
						$numProcTime = (int)$kData['cooking_part1']*60;
						$aryVar['meal_'.$i] = self::_getProcessData($nbRecipe['part1']);
						$canSpice = false;
					}
					//proc 2
					else if ($procTime < ((int)$kData['cooking_part1'] + (int)$kData['cooking_part2'])*60) {
						$strRemain = ((int)$kData['cooking_part1'] + (int)$kData['cooking_part2'])*60 - $procTime;
						$numProcTime = (int)$kData['cooking_part2']*60;
						$aryVar['meal_'.$i] = self::_getProcessData($nbRecipe['part2']);
						$canSpice = true;
					}
					//proc 3
					else if (!empty($kData['cooking_part3'])
							&& $procTime < $needSeconds) {
						$strRemain = $needSeconds - $procTime;
						$numProcTime = (int)$kData['cooking_part3']*60;
						$aryVar['meal_'.$i] = self::_getProcessData($nbRecipe['part3']);
						$canSpice = true;
					}

					$aryVar['b'.$i.'_tt'] = strftime('%M:%S', $strRemain);
					$aryVar['b'.$i.'_tm'] = (int)((($numProcTime-$strRemain)/$numProcTime)*100);//(int)(($procTime/$needSeconds)*100);
					$mCount = (int)$kData['kill_fly_count']+(int)$kData['add_spice_count'];
					$mCount = $mCount > 10 ? 10 : $mCount;
					$aryVar['b'.$i.'_qt'] = round(($mCount*5 + 50) * ((int)$kData['rate']/100), 0);
					$aryVar['b'.$i.'_qt'] = $aryVar['b'.$i.'_qt'] > 100 ? 100 : $aryVar['b'.$i.'_qt'];
					$aryVar['b'.$i.'_vl'] = '';
					$aryVar['b'.$i.'_pr'] = '1';
					$aryVar['b'.$i.'_rm'] = '';
					//0調理中 ： ハエを撃退（実行不可） 1調理中 ： ハエを撃退（実行可） 2調理中 ：ハエを飛ばす（実行可）
					if ($isSelf) {
						$aryVar['b'.$i.'_fly'] = 0;
						if ($kData['has_fly']) {
							$aryVar['b'.$i.'_fly'] = 1;
						}
					}
					else {
						if ($kData['has_fly']) {
							$aryVar['b'.$i.'_fly'] = 1;
							$rowSetFly = $mdalKitchen->getKitchenFlySet($profileUid, $kData['kitchen_id']);
							if ($rowSetFly['set_fly_uid'] == $uid) {
								$aryVar['b'.$i.'_fly'] = 0;
							}
						}
						else {
							$aryVar['b'.$i.'_fly'] = 2;
							//if ($rowUserSelf['set_fly_count'] >= 50) {
							//	$aryVar['b'.$i.'_fly'] = 0;
							//}
						}
					}
					//0	調理中 ： 味付けする（実行不可）  1　調理中 ： 味付けする（実行可）
					$rowKitchenSpice = $mdalKitchen->getKitchenSpice($profileUid, $kData['kitchen_id'], $uid);
					$aryVar['b'.$i.'_spice'] = '0';
					if (empty($rowKitchenSpice) && $canSpice) {
						$aryVar['b'.$i.'_spice'] = '1';
					}
					$aryVar['b'.$i.'_try'] = '';

					//item use
					//getUserItemCount
	            }
	            else {
	            	$aryVar['meal_'.$i] = self::_getMealData($kData['cooking_recipe_id']);
	            	$aryVar['b'.$i.'_st'] = 4;//完成
	            	$aryVar['b'.$i.'_tt'] = '';
	            	$aryVar['b'.$i.'_tm'] = '100';
	            	$aryVar['b'.$i.'_qt'] = '';

					$baseLuckN = ($rowUsrRecipe['lucky_flag'] ? 1.5 : 1)*$nbRecipe['point'];
					$percentA = (((int)$kData['kill_fly_count']+(int)$kData['add_spice_count'])>10) ? 10 : ((int)$kData['kill_fly_count']+(int)$kData['add_spice_count']);
					$percentA = $percentA/20;
	                //if has fly
	            	if (1 == (int)$kData['has_fly']) {
	            	    $percentA = (($percentA - 0.2) < 0) ? 0 : ($percentA - 0.2);
	            	}
					$gainPoint = ($baseLuckN + ($nbRecipe['point']*$percentA))>(2*$nbRecipe['point']) ? (2*$nbRecipe['point']) : (int)($baseLuckN + ($nbRecipe['point']*$percentA));

					//taste count
                    $lstTaste = $mdalKitchen->getKitchenTasteAll($profileUid, $kData['kitchen_id']);

                    //rate calculate
	            	$mCount = (int)$kData['kill_fly_count']+(int)$kData['add_spice_count'];
					$mCount = $mCount > 10 ? 10 : $mCount;
					$qtPercent = round(($mCount*5 + 50) * ((int)$kData['rate']/100), 0);
					$aryVar['b'.$i.'_rm'] = ((int)$kData['complete_quantity'] - count($lstTaste)) < 10 ? 10 : ((int)$kData['complete_quantity'] - count($lstTaste));
	            	$aryVar['b'.$i.'_vl'] = round(($aryVar['b'.$i.'_rm']/(int)$kData['complete_quantity'])*100, 0);
	            	$gainPoint = (int)($gainPoint*($aryVar['b'.$i.'_vl']/100));
                    $gainPoint = (int)($kData['rate'] * $gainPoint / 100) > (2*$nbRecipe['point']) ? (2*$nbRecipe['point']) : (int)($kData['rate'] * $gainPoint / 100);

	            	$aryVar['b'.$i.'_pr'] = number_format($gainPoint);
	            	$aryVar['b'.$i.'_fly'] = '';
	            	$aryVar['b'.$i.'_spice'] = '';
	            	//0 完成 ： 味見する（実行不可）  1 完成 ： 味見する（実行可）
	            	$rowKitchenTaste = $mdalKitchen->getKitchenTaste($profileUid, $kData['kitchen_id'], $uid);
					$aryVar['b'.$i.'_try'] = '0';
					if (empty($rowKitchenTaste)) {
						$aryVar['b'.$i.'_try'] = '1';
					}
					//modify by zhaoxh
					if (count($lstTaste) >= 10) {
						$aryVar['b'.$i.'_try'] = '0';
					}
	            }
        	}
			/*
			$aryVar['b'.$i.'_st'] = 1;
        	$aryVar['b'.$i.'_ft'] = '';
        	$aryVar['b'.$i.'_tt'] = '9:15';
            $aryVar['b'.$i.'_tm'] = '50';
            $aryVar['b'.$i.'_qt'] = '20';
            $aryVar['b'.$i.'_vl'] = '80';
            $aryVar['b'.$i.'_pr'] = '1';//number_format(4000);
            $aryVar['b'.$i.'_rm'] = '';
            $aryVar['b'.$i.'_fly'] = '1';
            $aryVar['b'.$i.'_spice'] = '0';
            $aryVar['b'.$i.'_try'] = '0';
			*/
        }
        for ($j=(count($lstKitchen)+1); $j<=6; $j++) {
        	$aryVar['meal_'.$j] = '';
        	$aryVar['b'.$j.'_st'] = '1';
			$aryVar['b'.$j.'_ft'] = '';
			$aryVar['b'.$j.'_tt'] = '';
			$aryVar['b'.$j.'_tm'] = '';
			$aryVar['b'.$j.'_qt'] = '';
			$aryVar['b'.$j.'_vl'] = '';
			$aryVar['b'.$j.'_pr'] = '';
			$aryVar['b'.$j.'_rm'] = '';
			$aryVar['b'.$j.'_fly'] = '';
			$aryVar['b'.$j.'_spice'] = '';
			$aryVar['b'.$j.'_try'] = '';
        }

        /*
        //flash cache deal
        $strTmp = http_build_query($aryVar);
        $cacheVal = md5($profileUid . $strTmp);
        $cacheKey = self::getCacheKey('getKitchen', $profileUid . '_' . $isSelf);
        $savedCacheInfo = Bll_Cache::get($cacheKey);

        //load from cache
        if ($savedCacheInfo && $savedCacheInfo == $cacheVal) {
            //$cacheFile = TEMP_DIR . '/kitchen' . self::_getSavedDir($profileUid) . $profileUid . '_' . $isSelf . '_kitchen.swf.gz';
        	if (Mbll_Kitchen_Amazon::hasObject($filename)) {
                $swfOutput = Mbll_Kitchen_Amazon::getObject($filename);
                return $swfOutput;
            }
        }
		*/

        
        //get xml and replace values
        $xmlString = file_get_contents(FLASH_TPL_ROOT . '/kitchen.xml');
        foreach ($aryVar as $key=>$value) {
            $xmlString = str_replace("{%" . $key . "%}", $value, $xmlString);
        }
//file_put_contents(FLASH_TPL_ROOT . '/kitchen18.xml', $xmlString);

        //set process param
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", ROOT_DIR . "/logs/kitchen_xml2swf_error.txt", "a")
        );

        $pipes = array();
        //XML to SWF
        // run swfmill and get $process
        $process = proc_open(SWFMILL_DIR . ' -e cp932 xml2swf stdin stdout', $descriptorspec, $pipes);

        if (is_resource($process)) {
            // set param $xmlString
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);

            self::swfTimeOut($pipes[1], array('uid'=>$uid, 'function'=>'getKitchen'));
            
            // get $swfOutput
            $swfOutput = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // close $process
            proc_close($process);
        }

        if ($swfOutput) {
            //Mbll_Kitchen_Amazon::saveObject($filename, $swfOutput);
            //Bll_Cache::set($cacheKey, $cacheVal, Bll_Cache::LIFE_TIME_ONE_DAY);
        }

        return $swfOutput;
    }

    /**
     * get selectGenre flash
     *
     * @param integer $uid
     * @param integer $kitchenId
     * @param integer $select
     * @param integer $genre
     * @param integer $food
     * @param string $mixiUrl
     * @return stream flash
     */
    public static function getSelectGenre($uid, $kitchenId, $select, $genre, $food, $mixiUrl, $appid)
    {
    	$filename = $uid . '_selectgenre.swf';
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/kitchen/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/kitchenflash/';
		$tt = new Mbll_Kitchen_Tt($uid);
        
         //load from session
		if (isset($_SESSION['kitchen_kit_selectfood']) && $_SESSION['kitchen_kit_selectfood'] != null) {
        	$arySelected = $_SESSION['kitchen_kit_selectfood'];
			if (!isset($arySelected['genre1'])) {
        		$arySelected['genre1'] = 0;
        	}
			if (!isset($arySelected['genre2'])) {
        		$arySelected['genre2'] = 0;
        	}
			if (!isset($arySelected['genre3'])) {
        		$arySelected['genre3'] = 0;
        	}
			if (!isset($arySelected['genre4'])) {
        		$arySelected['genre4'] = 0;
        	}
        	if (!isset($arySelected['food1'])) {
        		$arySelected['food1'] = 0;
        	}
			if (!isset($arySelected['food2'])) {
        		$arySelected['food2'] = 0;
        	}
			if (!isset($arySelected['food3'])) {
        		$arySelected['food3'] = 0;
        	}
			if (!isset($arySelected['food4'])) {
        		$arySelected['food4'] = 0;
        	}
            
			//$_SESSION['kitchen_kit_selectfood'] = null;
			//unset($_SESSION['kitchen_kit_selectfood']);
		}
		else {
			$arySelected['genre1'] = 0;
			$arySelected['genre2'] = 0;
			$arySelected['genre3'] = 0;
			$arySelected['genre4'] = 0;
			$arySelected['food1'] = 0;
			$arySelected['food2'] = 0;
			$arySelected['food3'] = 0;
			$arySelected['food4'] = 0;
		}
		if (!empty($select)) {
			$arySelected['genre'.$select] = $genre;
			if (!empty($food)) {
				$arySelected['food'.$select] = $food;
			}
		}

		$arySelected['kitchen_id'] = $kitchenId;
		$_SESSION['kitchen_kit_selectfood'] = $arySelected;

        require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $rowKitchen = $mdalKitchen->getUserKitchen($uid, $kitchenId);
        //if (!empty($rowKitchen['cooking_recipe_id'])) {
        //	return false;
        //}

        //flash info combine and replace
        $aryVar = array();
        $aryVar['selected_food1'] = self::_getFoodData($arySelected['food1']);
        $aryVar['selected_food2'] = self::_getFoodData($arySelected['food2']);
        $aryVar['selected_food3'] = self::_getFoodData($arySelected['food3']);
        $aryVar['selected_food4'] = self::_getFoodData($arySelected['food4']);
        $aryVar['remain'] = 3 - (int)$rowKitchen['failure_count'];

        $aryVar['vid'] = $uid;
        //$aryVar['baseUrl'] = $mixiMobileBaseUrl . urlencode($appUrl) . 'home?aa=1';
        $aryVar['baseUrl'] = ($appFlashUrl . 'selectfood?opensocial_app_id=' . $appid . '&amp;opensocial_owner_id='. $uid . '&amp;CF_kitchen_id=' . $kitchenId . '&amp;rand=' . time()) . '&amp;guid=ON';
        $aryVar['selected_1'] = empty($arySelected['food1']) ? 0 : 1;
        $aryVar['selected_2'] = empty($arySelected['food2']) ? 0 : 1;
        $aryVar['selected_3'] = empty($arySelected['food3']) ? 0 : 1;
        $aryVar['selected_4'] = empty($arySelected['food4']) ? 0 : 1;
        $aryVar['f1'] = $arySelected['food1'];
        $aryVar['f2'] = $arySelected['food2'];
        $aryVar['f3'] = $arySelected['food3'];
        $aryVar['f4'] = $arySelected['food4'];
        $cntSelectFood = 0;
        for ($idx=1; $idx<=4; $idx++) {
        	if (!empty($arySelected['food'.$idx])) {
        		$cntSelectFood += 1;
        	}
        }
        $aryVar['submit_flg'] = $cntSelectFood>=2 ? 1: 0;//0/1
        for ($idx=1; $idx<=4; $idx++) {
        	$aryVar['fish_'.$idx] = 0;
	        $aryVar['meat_'.$idx] = 0;
	        $aryVar['milk_'.$idx] = 0;
	        $aryVar['spice_'.$idx] = 0;
	        $aryVar['rice_'.$idx] = 0;
	        $aryVar['veget_'.$idx] = 0;
	        $aryVar['fruit_'.$idx] = 0;
        }
    	//2	魚類     /3	肉類     /4	乳卵・豆        /5	調味     /6	穀類     /7野菜     /8	フルーツ (flash)
    	$aryNbCate = array(2=>'fish_',3=>'meat_',4=>'milk_',5=>'spice_',6=>'rice_',7=>'veget_',8=>'fruit_');
    	/*
    	//check selected genie already
    	foreach ($aryNbCate as $cKey=>$cValue) {
    		if ($cKey == $arySelected['genre1'] && $arySelected['food1']) {
    			$aryVar[$cValue.'2'] = $aryVar[$cValue.'3'] = $aryVar[$cValue.'4'] = 1;
    		}
    		else if ($cKey == $arySelected['genre2'] && $arySelected['food2']) {
    			$aryVar[$cValue.'1'] = $aryVar[$cValue.'3'] = $aryVar[$cValue.'4'] = 1;
    		}
    		else if ($cKey == $arySelected['genre3'] && $arySelected['food3']) {
    			$aryVar[$cValue.'1'] = $aryVar[$cValue.'2'] = $aryVar[$cValue.'4'] = 1;
    		}
    		else if ($cKey == $arySelected['genre4'] && $arySelected['food4']) {
    			$aryVar[$cValue.'1'] = $aryVar[$cValue.'2'] = $aryVar[$cValue.'3'] = 1;
    		}
    	}*/
    	//not have this category food
    	require_once 'Mdal/Kitchen/Food.php';
        $mdalFood = Mdal_Kitchen_Food::getDefaultInstance();
        for ($idxCate=2; $idxCate<=8; $idxCate++) {
			if (0 == $mdalFood->getUserFoodByCategoryCount($uid, self::_changeCateToNb($idxCate))) {
				$aryVar[$aryNbCate[$idxCate].'1'] = $aryVar[$aryNbCate[$idxCate].'2']
					= $aryVar[$aryNbCate[$idxCate].'3'] = $aryVar[$aryNbCate[$idxCate].'4'] = 1;
			}
        }
        //get selected food count
        $aryFoodSelected = array();
        for ($i=1; $i<=4; $i++) {
        	if (!empty($arySelected['food'.$i])) {
                if (array_key_exists($arySelected['food'.$i], $aryFoodSelected)) {
                	$aryFoodSelected[$arySelected['food'.$i]] = (int)$aryFoodSelected[$arySelected['food'.$i]] + 1;
                }
                else {
                    $aryFoodSelected[$arySelected['food'.$i]] = 1;
                }
        	}
        }
        //check is select food used count=0
        $aryEmptyFood = array();
        foreach ($aryFoodSelected as $selKey => $cntValue) {
            $rowFoodInfo = $mdalFood->getUserFoodInfo($uid, $selKey);
            if ($rowFoodInfo['food_count'] <= $cntValue) {
            	$aryEmptyFood[$selKey] = $rowFoodInfo['food_category'];
            }
        }
        //now no such food
        foreach ($aryEmptyFood as $selKey=>$foodcate) {
        	if (1 == $mdalFood->getUserFoodByCategoryCount($uid, $foodcate)) {
        		for ($i=1; $i<=4; $i++) {
        			if (empty($arySelected['food'.$i])) {
        				$idxCate = self::_changeNbToCate($foodcate);
        				$aryVar[$aryNbCate[$idxCate].$i] = 1;
        			}
        		}
        	}
        }

        /*
        $aryVar['fish_1'] = 1;
        $aryVar['fish_2'] = 0;
        $aryVar['fish_3'] = 0;
        $aryVar['fish_4'] = 0;
        $aryVar['meat_1'] = 1;
        $aryVar['meat_2'] = 0;
        $aryVar['meat_3'] = 0;
        $aryVar['meat_4'] = 0;
        $aryVar['milk_1'] = 1;
        $aryVar['milk_2'] = 0;
        $aryVar['milk_3'] = 0;
        $aryVar['milk_4'] = 0;
        $aryVar['spice_1'] = 1;
        $aryVar['spice_2'] = 0;
        $aryVar['spice_3'] = 0;
        $aryVar['spice_4'] = 0;
        $aryVar['rice_1'] = 1;
        $aryVar['rice_2'] = 0;
        $aryVar['rice_3'] = 0;
        $aryVar['rice_4'] = 0;
        $aryVar['veget_1'] = 1;
        $aryVar['veget_2'] = 0;
        $aryVar['veget_3'] = 0;
        $aryVar['veget_4'] = 0;
        $aryVar['fruit_1'] = 1;
        $aryVar['fruit_2'] = 0;
        $aryVar['fruit_3'] = 0;
        $aryVar['fruit_4'] = 0;
		*/
        //flash cache deal
        $strTmp = http_build_query($aryVar);
        $cacheVal = md5($uid . $strTmp);
        $cacheKey = self::getCacheKey('getSelectGenre1', $uid);
        $savedCacheInfo = Bll_Cache::get($cacheKey);

        //load from cache
        if ($savedCacheInfo && $savedCacheInfo == $cacheVal) {
            //$cacheFile = TEMP_DIR . '/kitchen' . self::_getSavedDir($uid) . $uid . '_selectgenre.swf.gz';
        	if ($tt->hasObject($filename)) {
                $swfOutput = $tt->getObject($filename);
                return $swfOutput;
            }
        }

        //reset cache
        //get xml and replace values
        $xmlString = file_get_contents(FLASH_TPL_ROOT . '/selectgenre.xml');
        foreach ($aryVar as $key=>$value) {
            $xmlString = str_replace("{%" . $key . "%}", $value, $xmlString);
        }
//file_put_contents(FLASH_TPL_ROOT . '/school' . $rowDesign['did'] . '.xml', $xmlString);

        //set process param
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", ROOT_DIR . "/logs/kitchen_xml2swf_error.txt", "a")
        );

        $pipes = array();
        //XML to SWF
        // run swfmill and get $process
        $process = proc_open(SWFMILL_DIR . ' -e cp932 xml2swf stdin stdout', $descriptorspec, $pipes);

        if (is_resource($process)) {
            // set param $xmlString
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);

            self::swfTimeOut($pipes[1], array('uid'=>$uid, 'function'=>'getSelectGenre'));
            
            // get $swfOutput
            $swfOutput = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // close $process
            proc_close($process);
        }

        if ($swfOutput) {
            $tt->saveObject($filename, $swfOutput);
            Bll_Cache::set($cacheKey, $cacheVal, Bll_Cache::LIFE_TIME_ONE_DAY);
        }

        return $swfOutput;
    }

	/**
     * get selectfood flash
     *
     * @param integer $uid
     * @param integer $select
     * @param integer $genre
     * @param string $mixiUrl
     * @param string $appid
     * @return stream flash
     */
    public static function getSelectFood($uid, $select, $genre, $mixiUrl, $appid)
    {
    	$tt = new Mbll_Kitchen_Tt($uid);
        $filename = $uid . '_selectfood.swf';
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/kitchen/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/kitchenflash/';

        if (isset($_SESSION['kitchen_kit_selectfood']) && $_SESSION['kitchen_kit_selectfood'] != null) {
        	$arySelected = $_SESSION['kitchen_kit_selectfood'];
        	
		}
		else {
			$arySelected['genre1'] = 0;
			$arySelected['genre2'] = 0;
			$arySelected['genre3'] = 0;
			$arySelected['genre4'] = 0;
			$arySelected['food1'] = 0;
			$arySelected['food2'] = 0;
			$arySelected['food3'] = 0;
			$arySelected['food4'] = 0;
			$arySelected['kitchen_id'] = 0;
		}
		$arySelected['genre'.$select] = $genre;
		$_SESSION['kitchen_kit_selectfood'] = $arySelected;
		$kitchenId = $arySelected['kitchen_id'];
        
     	require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $rowKitchen = $mdalKitchen->getUserKitchen($uid, $arySelected['kitchen_id']);
        if (empty($kitchenId) || !empty($rowKitchen['cooking_recipe_id'])) {
        	return false;
        }
        
        require_once 'Mdal/Kitchen/Food.php';
        $mdalFood = Mdal_Kitchen_Food::getDefaultInstance();
        $lstFood = $mdalFood->listUserFoodByCategory($uid, self::_changeCateToNb($genre));

        //flash info combine and replace
        $aryVar = array();
        $aryVar['selected_food1'] = self::_getFoodData($arySelected['food1']);
        $aryVar['selected_food2'] = self::_getFoodData($arySelected['food2']);
        $aryVar['selected_food3'] = self::_getFoodData($arySelected['food3']);
        $aryVar['selected_food4'] = self::_getFoodData($arySelected['food4']);

        $aryVar['remain'] = 3 - (int)$rowKitchen['failure_count'];
        $aryVar['genre'] = $genre;
        $aryVar['vid'] = $uid;
        $aryVar['baseUrl'] = ($appFlashUrl . 'selectgenre?opensocial_app_id=' . $appid . '&amp;opensocial_owner_id='. $uid . '&amp;CF_kitchen_id=' . $kitchenId . '&amp;rand=' . time()) . '&amp;guid=ON';//$mixiMobileBaseUrl . urlencode($appUrl) . 'home?bb=2';
        $aryVar['backUrl'] = ($appFlashUrl . 'selectgenre?opensocial_app_id=' . $appid . '&amp;opensocial_owner_id='. $uid . '&amp;CF_kitchen_id=' . $kitchenId . '&amp;rand=' . time()) . '&amp;guid=ON';

        $aryVar['max'] = count($lstFood);
        $aryVar['select'] = $select;
        $aryVar['selected_1'] = empty($arySelected['food1']) ? 0 : 1;
        $aryVar['selected_2'] = empty($arySelected['food2']) ? 0 : 1;
        $aryVar['selected_3'] = empty($arySelected['food3']) ? 0 : 1;
        $aryVar['selected_4'] = empty($arySelected['food4']) ? 0 : 1;
        
        //get selected food count
        $aryFoodSelected = array();
        for ($i=1; $i<=4; $i++) {
            if (!empty($arySelected['food'.$i])) {
                if (array_key_exists($arySelected['food'.$i], $aryFoodSelected)) {
                    $aryFoodSelected[$arySelected['food'.$i]] = (int)$aryFoodSelected[$arySelected['food'.$i]] + 1;
                }
                else {
                    $aryFoodSelected[$arySelected['food'.$i]] = 1;
                }
            }
        }
        
        //check is select food used count=0
        $aryEmptyFood = array();
        foreach ($aryFoodSelected as $selKey => $cntValue) {
            $rowFoodInfo = $mdalFood->getUserFoodInfo($uid, $selKey);
            if ($rowFoodInfo['food_count'] <= $cntValue) {
                $aryEmptyFood[$selKey] = $rowFoodInfo['food_category'];
            }
        }
        //now no such food
        foreach ($aryEmptyFood as $selKey=>$foodcate) {
            if (1 == $mdalFood->getUserFoodByCategoryCount($uid, $foodcate)) {
                for ($i=1; $i<=4; $i++) {
                    if (empty($arySelected['food'.$i])) {
                        $idxCate = self::_changeNbToCate($foodcate);
                        $aryVar[$aryNbCate[$idxCate].$i] = 1;
                    }
                }
            }
        }
        
        $i = 0;
        require_once 'Mbll/Kitchen/Cache.php';
        foreach ($lstFood as $fKey=>$fValue) {
        	if (!array_key_exists($fValue['food_id'], $aryEmptyFood)) {
        		$i += 1;
        		$rowNbFood = Mbll_Kitchen_Cache::getFood($fValue['food_id']);
	            $aryVar['f_' . $i] = htmlspecialchars($rowNbFood['food_name'], ENT_QUOTES, 'UTF-8');
	            $aryVar['f_' . $i . '_id'] = $fValue['food_id'];
	            //pic
	            $aryVar['f' . $i] = self::_getFoodData($fValue['food_id'], 2);
        	}
        }
        $aryVar['max'] = $i;
        for($i=($i+1); $i<=60; $i++) {
			$aryVar['f_' . $i] = '';
        	$aryVar['f_' . $i . '_id'] = '';
            $aryVar['f' . $i] = '';
        }
        
        //flash cache deal
        $strTmp = http_build_query($aryVar);
        $cacheVal = md5($uid . $strTmp);
        $cacheKey = self::getCacheKey('getSelectFood', $uid);
        $savedCacheInfo = Bll_Cache::get($cacheKey);

        //load from cache
        if ($savedCacheInfo && $savedCacheInfo == $cacheVal) {
            //$cacheFile = TEMP_DIR . '/kitchen' . self::_getSavedDir($uid) . $uid . '_selectfood.swf.gz';
        	if ($tt->hasObject($filename)) {
                $swfOutput = $tt->getObject($filename);
                return $swfOutput;
            }
        }

        //reset cache
        //get xml and replace values
        $xmlString = file_get_contents(FLASH_TPL_ROOT . '/selectfood.xml');
        foreach ($aryVar as $key=>$value) {
            $xmlString = str_replace("{%" . $key . "%}", $value, $xmlString);
        }
//file_put_contents(FLASH_TPL_ROOT . '/school' . $rowDesign['did'] . '.xml', $xmlString);
       
        //set process param
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", ROOT_DIR . "/logs/kitchen_xml2swf_error.txt", "a")
        );

        $pipes = array();
        //XML to SWF
        // run swfmill and get $process
        $process = proc_open(SWFMILL_DIR . ' -e cp932 xml2swf stdin stdout', $descriptorspec, $pipes);

        if (is_resource($process)) {
            // set param $xmlString
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);

            self::swfTimeOut($pipes[1], array('uid'=>$uid, 'function'=>'getSelectFood'));
            
            // get $swfOutput
            $swfOutput = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // close $process
            proc_close($process);
        }
        
        if ($swfOutput) {
            $tt->saveObject($filename, $swfOutput);
            Bll_Cache::set($cacheKey, $cacheVal, Bll_Cache::LIFE_TIME_ONE_DAY);
        }

        return $swfOutput;
    }
    
    /**
     * get chef image url(size=180x180)
     *
     * @param string $profileUid
     * @return abstruct amazon s3 url
     */
    public static function getChefImage($profileUid, $express)
    {
    	$tt = new Mbll_Kitchen_Tt($profileUid);
    	
    	$arrExpress = array('normal' => array(0,0,0),
    						'happy' => array(0,1,1),
    						'sad' => array(1,1,1),
    						'boo' => array(1,2,1));
    	
    	if ($express != 'happy' && $express != 'sad' && $express != 'boo') {
    		$express = 'normal';
    	}
    	
    	$blow_type = $arrExpress[$express][0];
    	$eye_type = $arrExpress[$express][1];
    	$mouse_type = $arrExpress[$express][2];
    	
    	$filename = $profileUid . '_cheftt.gif';
    	
    	require_once 'Mdal/Kitchen/Chef.php';
        $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
        $rowChef = $mdalChef->getChef($profileUid);
        if (empty($rowChef)) {
            info_log($profileUid . 'chef data error', 'chefImg');
        	//exit(0);
            return '';
        }
        $faceId = $rowChef['face'] - 1;
        $eyeId = $rowChef['eye'] - 1;
        $head_mId = $rowChef['head_m'] -1;
        $earId = $rowChef['ear'] - 1;
        $rabbitId = $rowChef['rabbit'];
        $eyemaskId = $rowChef['eyemask'];
        
        //cache deal
        $cacheVal = md5($profileUid . ',' . $express . ','  . $faceId . ',' . $eyeId . ',' . $head_mId . ',' . $earId . ',' . $rabbitId . ',' . $eyemaskId);
        $cacheKey = self::getCacheKey('chefImagev13' . $profileUid, $express);
        $savedCacheInfo = Bll_Cache::get($cacheKey);
        
        //load from cache
        if ($savedCacheInfo && $savedCacheInfo == $cacheVal) {
            if ($tt->hasObject($filename)) {
                header( "Content-Type: image/gif" );
            	$streamOut = $tt->getObject($filename);
                return $streamOut;
            }
        }
    	    	
        $sourceDir = ROOT_DIR . '/img/chef/face/';
        //$sourceDir = ROOT_DIR . '/www/static/apps/kitchen/mobile/img/chef/face/';
        $bgImg = $sourceDir . 'bg.png';
        $clothImg = $sourceDir . 'cloth.png';
        $hatImg = $sourceDir . 'hat.png';
        $bodyImg = $sourceDir . 'body/' . $faceId . '.png';
        $blowImg = $sourceDir . 'blow' . $blow_type . '/' . ($faceId == 0 ? '0' : '1') . '.png';
        $mouseImg = $sourceDir . 'mouse' . $mouse_type . '/' . ($faceId == 0 ? '0' : '1') . '.png';
        
        $faceImg = $sourceDir . 'face/' . $faceId . '.png';
        $eyeImg = $sourceDir . 'eye' . $eye_type . '/' . $eyeId . '.png';
        $head_mImg = $sourceDir . 'head_m/' . $head_mId . '.png';
        $earImg = $sourceDir . 'ear' . $rabbitId . '/' . $earId . '.png';
        
        if ($eyemaskId == 3) {
            $eyemaskImg = $sourceDir . 'eyemask/' . $faceId . '.png';
        }
        else if ($eyemaskId == 2) {
        	$eyemaskImg = $sourceDir . 'eyemask/' . '0' . '.png';
        }
        
        
        if (!file_exists($bgImg) || !file_exists($faceImg) || !file_exists($eyeImg) || !file_exists($head_mImg)
            || !file_exists($earImg) || !$eyemaskId) {
            info_log('file_not_exist1(mbll_flashcache)'.$profileUid,'chefImg');
        	return '';
        }
    	if (!file_exists($clothImg) || !file_exists($hatImg) || !file_exists($bodyImg) || !file_exists($blowImg)
            || !file_exists($mouseImg)) {
            info_log('file_not_exist2(mbll_flashcache)'.$profileUid,'chefImg');
        	return '';
        }
        
        $imBg = new Imagick($bgImg);
        $imCloth = new Imagick($clothImg);
        $imHat = new Imagick($hatImg);
        $imBody = new Imagick($bodyImg);
        $imBlow = new Imagick($blowImg);
        $imMouse = new Imagick($mouseImg);
        
        $imFace = new Imagick($faceImg);
        $imEye = new Imagick($eyeImg);
        $imHead_m = new Imagick($head_mImg);
        $imEar = new Imagick($earImg);
        
        if ($eyemaskId != 1) {
        	$imEyemask = new Imagick($eyemaskImg);
        }
        
        $x = 0;
        $y = 0;
        $imBg->compositeImage($imBody, imagick::COMPOSITE_OVER, $x, $y );
        $imBg->compositeImage($imCloth, imagick::COMPOSITE_OVER, $x, $y );
        $imBg->compositeImage($imEar, imagick::COMPOSITE_OVER, $x, $y );
        $imBg->compositeImage($imFace, imagick::COMPOSITE_OVER, $x, $y );
        $imBg->compositeImage($imHead_m, imagick::COMPOSITE_OVER, $x, $y );
	    if ($eyemaskId != 1) {
	    	$imEyemask->thumbnailImage(180,180);
        	$imBg->compositeImage($imEyemask, imagick::COMPOSITE_OVER, $x, $y );
        }
        $imBg->compositeImage($imEye, imagick::COMPOSITE_OVER, $x, $y );
        $imBg->compositeImage($imHat, imagick::COMPOSITE_OVER, $x, $y );
        $imBg->compositeImage($imBlow, imagick::COMPOSITE_OVER, $x, $y );
        $imBg->compositeImage($imMouse, imagick::COMPOSITE_OVER, $x, $y );
        
        $imBg->thumbnailImage(40,40);
        $imBg->setFormat('gif');
        
        header( "Content-Type: image/gif" );
        $streamOut = $imBg->getImageBlob();
        
        if ($streamOut) {
	        $tt->saveObject($filename, $streamOut);
	        Bll_Cache::set($cacheKey, $cacheVal, Bll_Cache::LIFE_TIME_ONE_WEEK);
	    }
        return $streamOut;
    }
    
    

    private static function _changeNbToCate($type)
    {
        //01.魚介類 02.穀類 03.調味料 04.肉類 05.野菜類 06.乳卵豆 07.フルーツ         (nb db)
        //2 魚類     /3   肉類     /4   乳卵・豆        /5  調味     /6   穀類     /7野菜     /8  フルーツ (flash)
        $rtn = 2;
        if (1 == $type) {
            $rtn = 2;
        }
        else if (4 == $type) {
            $rtn = 3;
        }
        else if (6 == $type) {
            $rtn = 4;
        }
        else if (3 == $type) {
            $rtn = 5;
        }
        else if (2 == $type) {
            $rtn = 6;
        }
        else if (5 == $type) {
            $rtn = 7;
        }
        else if (7 == $type) {
            $rtn = 8;
        }
        return $rtn;
    }

 	private static function _changeCateToNb($type)
    {
    	//01.魚介類 02.穀類 03.調味料 04.肉類 05.野菜類 06.乳卵豆 07.フルーツ         (nb db)
    	//2	魚類     /3	肉類     /4	乳卵・豆        /5	調味     /6	穀類     /7野菜     /8	フルーツ (flash)
    	$rtn = 1;
        if (2 == $type) {
			$rtn = 1;
        }
        else if (3 == $type) {
			$rtn = 4;
        }
    	else if (4 == $type) {
			$rtn = 6;
        }
    	else if (5 == $type) {
			$rtn = 3;
        }
    	else if (6 == $type) {
			$rtn = 2;
        }
    	else if (7 == $type) {
			$rtn = 5;
        }
    	else if (8 == $type) {
			$rtn = 7;
        }
        return $rtn;
    }

    /**
     * get kitchen swf basic color
     *
     * @param null
     * @return array
     */
    private static function _getNbColor()
    {
        $key = self::getCacheKey('_getNbColor');

        if (!$result = Bll_Cache::get($key)) {
            require_once 'Mdal/Kitchen/NbColor.php';
            $mdalColor = Mdal_Kitchen_NbColor::getDefaultInstance();
            $result = $mdalColor->listNbColor();

            if ($result) {
                $arySave = array();
                foreach ($result as $idx => $cdata) {
                	$arySave[$idx+1] = $cdata;
                }
                Bll_Cache::set($key, $arySave, Bll_Cache::LIFE_TIME_MAX);
            }
        }
        return $result;
    }

	/**
     * get kitchen swf basic position info
     *
     * @param integer $positionId
     * @return string
     */
    private static function _getNbPosion($positionId)
    {
        $key = self::getCacheKey('_getNbPosion', $positionId);

        if (!$result = Bll_Cache::get($key)) {
            require_once 'Mdal/Kitchen/NbPosition.php';
            $mdalPosition = Mdal_Kitchen_NbPosition::getDefaultInstance();
            $result = $mdalPosition->getNbPosition($positionId);

            if ($result && $result['base_data']) {
                Bll_Cache::set($key, $result['base_data'], Bll_Cache::LIFE_TIME_MAX);
            }
        }
        return $result;
    }

	/**
     * get kitchen swf nb goods info
     *
     * @param string $goodsId
     * @return string
     */
    private static function _getNbGoods($goodsId)
    {
        $key = self::getCacheKey('_getNbGoods', $goodsId);

        if (!$result = Bll_Cache::get($key)) {
            require_once 'Mdal/Kitchen/Goods.php';
            $mdalGoods = Mdal_Kitchen_Goods::getDefaultInstance();
            $result = $mdalGoods->getGoods($goodsId);
            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
            }
        }
        return $result;
    }

    /**
     * get kitchen food xml data
     *
     * @param string $foodId
     * @param boolean $isbig
     * @return string
     */
    private static function _getFoodData($foodId, $isbig = 0)
    {
        $rtnData = '';
        if (empty($foodId) || strlen($foodId) < 4) {
            return $rtnData;
        }
        
        
        if ($isbig == 0) {
        	$size = '32x32';
        }
        else if ($isbig == 1) {
        	$size = '66x66';
        }
        else {
        	$size = '90x90';
        }
		
        //$size = $isbig ? '66x66' : '32x32';
        $tplPath = FLASH_TPL_ROOT . "/data/food/$size/" . substr($foodId, 0, 2) . '/' . substr($foodId, 2) . '.xml';
        
        $rtnData = @file_get_contents($tplPath);
        return $rtnData;
    }

	/**
     * get kitchen meal xml data
     *
     * @param string $foodId
     * @return string
     */
    private static function _getMealData($mealId)
    {
        $rtnData = '';
        if (empty($mealId) || strlen($mealId) < 3) {
            return $rtnData;
        }
        $dir = substr($mealId, 0, 1);
        $tplPath = FLASH_TPL_ROOT . "/data/meal/$dir/" . substr($mealId, 1) . '.xml';
        $rtnData = @file_get_contents($tplPath);
        return $rtnData;
    }

	/**
     * get process xml data
     *
     * @param string $proc
     * @return string
     */
    private static function _getProcessData($proc)
    {
        $rtnData = '';
        if (empty($proc)) {
            return $rtnData;
        }

        $tplPath = FLASH_TPL_ROOT . "/data/process/" . $proc . '.xml';
        $rtnData = @file_get_contents($tplPath);
        return $rtnData;
    }

	/**
     * get kitchen bg1 or bg2 xml data
     *
     * @param integer $genre
     * @param integer $estate
     * @param integer $no
     * @return string
     */
    private static function _getKitchenBg($genre, $estate, $no)
    {
        $rtnData = '';
        if (empty($genre) || $estate<2 || empty($no)) {
            return $rtnData;
        }

        $dir = 'bg' . $no . '/';
        if (1 == $genre) {
			$dir .= 'y';
        }
        else if (2 == $genre) {
        	$dir .= 'r';
        }
        else {
        	$dir .= 'y';
        }

        $tplPath = FLASH_TPL_ROOT . "/data/kitchen/$dir/" . '0' . ((int)$estate - 1) . '.xml';
        $rtnData = @file_get_contents($tplPath);
        return $rtnData;
    }

	/**
     * get bg xml data
     *
     * @param integer
     * @return string
     */
    private static function _getBgData($genre)
    {
        $rtnData = '';
        if (empty($genre)) {
            return $rtnData;
        }

        if (1 == $genre) {
			$dir = 'y';
        }
        else if (2 == $genre) {
        	$dir = 'r';
        }
        else {
        	$dir = 'y';
        }
        $tplPath = FLASH_TPL_ROOT . "/data/background/$dir/" . '01.xml';
        $rtnData = @file_get_contents($tplPath);
        return $rtnData;
    }

	/**
     * get building xml data
     *
     * @param integer
     * @return string
     */
    private static function _getBuildingData($building)
    {
        $rtnData = '';
        if (empty($building)) {
            return $rtnData;
        }

        $tplPath = FLASH_TPL_ROOT . "/data/building/$building" . '.xml';
        $rtnData = @file_get_contents($tplPath);
        return $rtnData;
    }

	/**
     * get goods xml data
     *
     * @param integer
     * @return string
     */
    private static function _getGoodsData($goods)
    {
        $rtnData = '';
        if (empty($goods)) {
            return $rtnData;
        }

        $tplPath = FLASH_TPL_ROOT . "/data/zakka/$goods" . '.xml';
        $rtnData = @file_get_contents($tplPath);
        return $rtnData;
    }

    /**
     * get swf gz saved directory
     *
     * @param integer uid
     * @return string
     */
    public static function _getSavedDir($uid)
    {
        $strMd5 = md5($uid);
        $dir0 = substr($strMd5, 0, 1);
        $dir1 = substr($strMd5, 1, 1);
        $dir2 = substr($strMd5, 2, 1);
        $dir3 = substr($strMd5, 3, 1);
        $dir4 = substr($strMd5, 4, 1);
        return '/' . $dir0 . '/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . $dir4 . '/';
    }

    /**
     * clear cache
     *
     * @param integer $uid
     * @param string $salt ['getChangeChara'/'getKitchen'/'getRestaurant'/'getSelectFood'/'getSelectGenre']
     */
    public static function clearFlash($uid, $salt)
    {
        Bll_Cache::delete(self::getCacheKey($salt, $uid));
    }
    
    /**
     * proc time out
     *
     * @param stream $fp
     * @param array $info uid,function name
     */
    private static function swfTimeOut(&$fp, $info)
    {
        // get $swfOutput
        stream_set_timeout($fp, 3);
        
        $info = stream_get_meta_data($fp);
        
        if ($info['timed_out']) {
            fclose($fp);
            info_log(Zend_Json::encode($info), 'Swfmill_Time_Out');
            global_error_output();
            exit();
        }
    }
}