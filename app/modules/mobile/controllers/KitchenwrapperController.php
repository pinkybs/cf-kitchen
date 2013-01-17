<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

define('FLASH_TPL_ROOT', ROOT_DIR . '/swf_xml');
//define('FLASH_TPL_ROOT', ROOT_DIR . '/www/static/apps/kitchen/mobile/swf');

/**
 * Mobile kitchen wrapper Controller(modules/mobile/controllers/KitchenfirstController.php)
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-2-24
 */
class KitchenwrapperController extends MyLib_Zend_Controller_Action_Mobile
{
    /**
     * preDispatch
     *
     */
    function preDispatch()
    {
        $this->view->uid = $this->_USER_ID;
        $this->view->rand = time();
    }
    
    public function profileswfAction()
    {
    	$paramArr = array('face' => $this->getParam('CF_face', 1),
    	                  'rabbit' => $this->getParam('CF_rabbit', 1),
    					  'ear' => $this->getParam('CF_ear', 1),
    					  'head_m' => $this->getParam('CF_head_m', 1),
    					  'eye' => $this->getParam('CF_eye', 1),
    					  'eyemask' => $this->getParam('CF_eyemask', 1));
    	ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
		
		echo $this->swf_wrapper(FLASH_TPL_ROOT . '/wrapper/profile.swf', $paramArr);
	    exit(0);
    }
    
	private function swf_wrapper($file,$item)
	{
		$tags	= $this->build_tags($item);
		$src	= file_get_contents($file);
		$i	= (ord($src[8])>>1)+5;
		$length	= ceil((((8-($i&7))&7)+$i)/8)+17;
		$head	= substr($src,0,$length);
		return(
			substr($head,0,4).
			pack("V",strlen($src)+strlen($tags)).
			substr($head,8).
			$tags.
			substr($src,$length)
		);
	}
	
	private function build_tags($item)
	{
		$tags = array();
		foreach($item as $k => $v){
			array_push( $tags, sprintf(
				"\x96%s\x00%s\x00\x96%s\x00%s\x00\x1d",
				pack("v",strlen($k)+2),	$k,
				pack("v",strlen($v)+2),	$v
			));
		}
		$s = implode('',$tags);
		return(sprintf(
			"\x3f\x03%s%s\x00",
			pack("V",strlen($s)+1),
			$s
		));
	}
	
	private function swf_wrapper_source($src,$item)
	{
		$tags	= $this->build_tags_convert($item);
		$i	= (ord($src[8])>>1)+5;
		$length	= ceil((((8-($i&7))&7)+$i)/8)+17;
		$head	= substr($src,0,$length);
		return(
			substr($head,0,4).
			pack("V",strlen($src)+strlen($tags)).
			substr($head,8).
			$tags.
			substr($src,$length)
		);
	}
	
	private function build_tags_convert($item)
	{
		$tags = array();
		foreach($item as $k => $v){
			$v = mb_convert_encoding($v,'SJIS','UTF-8');
			array_push( $tags, sprintf(
				"\x96%s\x00%s\x00\x96%s\x00%s\x00\x1d",
				pack("v",strlen($k)+2),	$k,
				pack("v",strlen($v)+2),	$v
			));
		}
		$s = implode('',$tags);
		return(sprintf(
			"\x3f\x03%s%s\x00",
			pack("V",strlen($s)+1),
			$s
		));
	}
	
	private function swf_wrapper_convert($file,$item)
	{
		$tags	= $this->build_tags_convert($item);
		$src	= file_get_contents($file);
		$i	= (ord($src[8])>>1)+5;
		$length	= ceil((((8-($i&7))&7)+$i)/8)+17;
		$head	= substr($src,0,$length);
		return(
			substr($head,0,4).
			pack("V",strlen($src)+strlen($tags)).
			substr($head,8).
			$tags.
			substr($src,$length)
		);
	}
    public function changecharaswfAction()
    {
    	
    	$mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        $appUrl = Zend_Registry::get('host') . '/mobile/kitchenfirst/editchara';
        
    	require_once 'Mdal/Kitchen/Chef.php';
        $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
        $rowChef = $mdalChef->getChef($this->_USER_ID);

        if (empty($rowChef)) {
            $paramArr = array('preset' => 1,
	    					  'baseUrl' => $mixiUrl . urlencode($appUrl),
	    	                  'express' => 2);
        }
        else {
        	$paramArr = array('preset' => '0',
	    					  'baseUrl' => $mixiUrl . urlencode($appUrl),
	    					  'face' => $rowChef['face'],
	    	                  'rabbit' => $rowChef['rabbit'],
	    					  'ear' => $rowChef['ear'],
	    					  'head_m' => $rowChef['head_m'],
	    					  'eye' => $rowChef['eye'],
	    					  'eyemask' => $rowChef['eyemask'],
	    	                  'express' => 2);
        }
    	
    	ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
		
		echo $this->swf_wrapper(FLASH_TPL_ROOT . '/wrapper/changechara.swf', $paramArr);
	    exit(0);
    }
    
    /*
    public function kitchenswfAction()
    {
    	$uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid', $uid);
        
        $appUrl = Zend_Registry::get('host');
    	$mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        
    	require_once 'Mdal/Kitchen/Chef.php';
        $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
        $rowChef = $mdalChef->getChef($profileUid);

        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $rowProRes = $mdalRes->getActiveRestaurant($profileUid);
        $maxLevel = $mdalRes->getMaxLevel($uid);
		$swfFile = $rowProRes['genre'] . '_' .$rowProRes['estate'];
        
//		if (Zend_Registry::get('ua') == 1) {
//			//docomo some mobile can only use POST
//			$agent = $_SERVER['HTTP_USER_AGENT'];
//			info_log($agent, 'ralf');
//			//DoCoMo/2.0 F09A3(c500;TB;W24H16)
// 			//DoCoMo/2.0 P07A3(c500;TB;W24H15)
// 			$agentSub = substr($agent,strpos($agent, '(') + 2, 3);
// 			info_log($agentSub, 'ralf');
// 			if ($agentSub >= 500) {
// 				$swfFile .= '_docomo';
// 				info_log($swfFile, 'ralf');
// 			}
//		}
		$paramArr = array(
					"url" => $appUrl . '/mobile/kitchenwrapper/loadclip/CF_uid/' . $profileUid . '/opensocial_app_id/' . $this->_APP_ID . '/opensocial_owner_id/' . $uid . '/rand/' . time(),
					"vid" => $uid,
					"oid" => $profileUid,
					"fst" => ($maxLevel > 1 ? 0 : 1),
					"ttl" => html_entity_decode($rowProRes['name']),
					"face" => $rowChef['face'],
                    "rabbit" => $rowChef['rabbit'],
    			    "ear" => $rowChef['ear'],
    			    "head_m" => $rowChef['head_m'],
    			    "eye" => $rowChef['eye'],
    			    "eyemask" => $rowChef['eyemask'],
					"baseUrl" => $mixiUrl . urlencode($appUrl . '/mobile/kitchen/wrapdispatch/CF_uid/' . $profileUid),//?opensocial_app_id=' . $this->_APP_ID . '&opensocial_owner_id='. $uid . '&rand=' . time(),
					"loadUrl" => $appUrl . '/mobile/kitchenwrapper/vars/CF_uid/' . $profileUid . '/opensocial_app_id/' . $this->_APP_ID . '/opensocial_owner_id/'. $uid . '/rand/' . time(),
					"home"    => $mixiUrl . urlencode($appUrl . '/mobile/kitchen/home/CF_uid/' . $profileUid),
					"list"    => $mixiUrl . urlencode($appUrl . '/mobile/kitchen/rankinglist')
					);
		
    	ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
		header("Content-Type: application/x-shockwave-flash");
		echo $this->swf_wrapper_convert(FLASH_TPL_ROOT . '/wrapper/estate/' . $swfFile . '.swf', $paramArr);
	    exit(0);
    }
    
    public function loadclipAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid', $uid);
        
        require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $kitchenAll = $mdalKitchen->getUserKitchenAll($profileUid);
        
    	//auto set fly logic
        require_once 'Mbll/Kitchen/Kitchen.php';
        $mbllKitchen = new Mbll_Kitchen_Kitchen();
        $blnSeted = false;
        foreach ($kitchenAll as $kitData) {
			if ($mbllKitchen->autoSetFly($profileUid, $kitData['kitchen_id'])) {
				$blnSeted = true;
			}
        }
        if ($blnSeted) {
        	$kitchenAll = $mdalKitchen->getUserKitchenAll($profileUid);
        }

        $paramArr = array();
        
        if (Zend_Registry::get('ua') == 1) {
			//docomo some mobile onload
			$agent = $_SERVER['HTTP_USER_AGENT'];
			info_log($agent, 'ralf');
 			$agentSub = substr($agent,strpos($agent, '(') + 2, 3);
 			if ($agentSub >= 500) {
 				info_log('loadcliponload=1', 'ralf');
 				$paramArr['/:onload'] = 1;
 			}
		}
        
        
        
	    foreach ($kitchenAll as $key=>$kData) {
	        if (!empty($kData['cooking_recipe_id']) && !empty($kData['cooking_start_time'])) {
	        	$nowTime = time();
	        	$partAllSeconds = $kData['cooking_start_time'] + ((int)$kData['cooking_part1'] + (int)$kData['cooking_part2'] + (int)$kData['cooking_part3'])*60;
				$partOneSeconds = $kData['cooking_start_time'] + (int)$kData['cooking_part1'] * 60;
	        	$nbRecipe = Mbll_Kitchen_Cache::getRecipe($kData['cooking_recipe_id']);
	        	
	        	if ($nowTime > $partAllSeconds) {
	        		//cooking finish
	        		$file[$kData['kitchen_id']] = $kData['cooking_recipe_id'];
	        		$path[$kData['kitchen_id']] = 'Recipe/' . ucfirst($kData['cooking_recipe_id']) . '.php';
	        		
	        		$paramArr['/:b' . $kData['kitchen_id'] . '_st'] = 3;
	        		$paramArr['/:b' . $kData['kitchen_id'] . '_ft'] = $nbRecipe['recipe_name'];
	        		
	        		//get cookPrice
	        		require_once 'Mdal/Kitchen/Recipe.php';
					$mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
					$rowUsrRecipe = $mdalRecipe->getUserRecipeById($profileUid, $kData['cooking_recipe_id']);
					//taste count
                    $lstTaste = $mdalKitchen->getKitchenTasteAll($profileUid, $kData['kitchen_id']);
					
	        		$baseLuckN = ($rowUsrRecipe['lucky_flag'] ? 1.5 : 1)*$nbRecipe['point'];
					$percentA = (((int)$kData['kill_fly_count']+(int)$kData['add_spice_count'])>10) ? 10 : ((int)$kData['kill_fly_count']+(int)$kData['add_spice_count']);
					$percentA = $percentA/20;
	                //if has fly
	            	if (1 == (int)$kData['has_fly']) {
	            	    $percentA = (($percentA - 0.2) < 0) ? 0 : ($percentA - 0.2);
	            	}
					$gainPoint = (int)($baseLuckN + ($nbRecipe['point']*$percentA));
					
					$paramArr['/:b' . $kData['kitchen_id'] . '_rt'] = ((int)$kData['complete_quantity'] - count($lstTaste)) < 10 ? 10 : ((int)$kData['complete_quantity'] - count($lstTaste));
	            	$paramArr['/:b' . $kData['kitchen_id'] . '_vp'] = round(($paramArr['/:b' . $kData['kitchen_id'] . '_rt']/(int)$kData['complete_quantity'])*100, 0);
	            	
	            	$gainPoint = (int)($gainPoint*($paramArr['/:b' . $kData['kitchen_id'] . '_vp']/100));
                    $gainPoint = (int)($kData['rate'] * $gainPoint / 100) > (2*$nbRecipe['point']) ? (2*$nbRecipe['point']) : (int)($kData['rate'] * $gainPoint / 100);
					
	        		$paramArr['/:b' . $kData['kitchen_id'] . '_pt'] = $gainPoint;
	        		
                   
					$paramArr['/:b' . $kData['kitchen_id'] . '_tt'] = '';
					$paramArr['/:b' . $kData['kitchen_id'] . '_tp'] = '';
				    $paramArr['/:b' . $kData['kitchen_id'] . '_qp'] = '';
				    $paramArr['/:b' . $kData['kitchen_id'] . '_spc'] = '';
	        		$paramArr['/:b' . $kData['kitchen_id'] . '_stf'] = '';
	        		$paramArr['/:b' . $kData['kitchen_id'] . '_rmf'] = '';
	        		
	        		if ($uid == $profileUid) {
	        			$paramArr['/:b' . $kData['kitchen_id'] . '_try'] = '';
	        		}
	        		else {
	        			$tasteRow = $mdalKitchen->getKitchenTaste($profileUid, $kData['kitchen_id'], $uid);
	        			$paramArr['/:b' . $kData['kitchen_id'] . '_try'] = empty($tasteRow) ? 1 : 0;
	        		}
        			$paramArr['/:b' . $kData['kitchen_id'] . '_chu'] = '';
	        	}
	        	else {
	        		//cooking
					if ($partOneSeconds > $nowTime) {
						// in part 1
						$paramArr['/:b' . $kData['kitchen_id'] . '_spc'] = 0;
						$file[$kData['kitchen_id']] = $nbRecipe['part1'];
						$path[$kData['kitchen_id']] = 'Process/' . ucfirst($nbRecipe['part1']) . '.php';
					}
		        	else if (($partOneSeconds <= $nowTime) && ($partAllSeconds > $nowTime)) {
		        		// in part 2 or 3
		        		$rowKitchenSpice = $mdalKitchen->getKitchenSpice($profileUid, $kData['kitchen_id'], $uid);
						$paramArr['/:b' . $kData['kitchen_id'] . '_spc'] = empty($rowKitchenSpice) ? 1 : 0;
						if ($kData['cooking_part3'] != 0 && ($nowTime > $partAllSeconds - (int)$kData['cooking_part3'] * 60)) {
							$file[$kData['kitchen_id']] = $nbRecipe['part3'];
							$path[$kData['kitchen_id']] = 'Process/' . ucfirst($nbRecipe['part3']) . '.php';
						}
						else {
							$file[$kData['kitchen_id']] = $nbRecipe['part2'];
							$path[$kData['kitchen_id']] = 'Process/' . ucfirst($nbRecipe['part2']) . '.php';
						}
						
					}
					$paramArr['/:b' . $kData['kitchen_id'] . '_st'] = 2;
					$paramArr['/:b' . $kData['kitchen_id'] . '_ft'] = $nbRecipe['recipe_name'];
					$paramArr['/:b' . $kData['kitchen_id'] . '_tt'] = floor(($partAllSeconds - $nowTime)/60) .':'. ($partAllSeconds - $nowTime)%60;
					//strftime('%H:%M:%S', $partAllSeconds - $nowTime);
					
					$paramArr['/:b' . $kData['kitchen_id'] . '_tp'] = (int)(($nowTime - $kData['cooking_start_time']) * 100 /($partAllSeconds - $kData['cooking_start_time']));
					
					$mCount = (int)$kData['kill_fly_count']+(int)$kData['add_spice_count'];
					$mCount = 50 + $mCount * 5;
					$paramArr['/:b' . $kData['kitchen_id'] . '_qp'] = $mCount > 100 ? 100 : $mCount;
					
					$paramArr['/:b' . $kData['kitchen_id'] . '_rt'] = '';
	            	$paramArr['/:b' . $kData['kitchen_id'] . '_vp'] = '';
	            	$paramArr['/:b' . $kData['kitchen_id'] . '_pt'] = '';
					
		        	$paramArr['/:b' . $kData['kitchen_id'] . '_stf'] = 1;
					$paramArr['/:b' . $kData['kitchen_id'] . '_rmf'] = 0;
					
					$paramArr['/:b' . $kData['kitchen_id'] . '_try'] = '';
					$paramArr['/:b' . $kData['kitchen_id'] . '_chu'] = '';
					
	        		if ($kData['has_fly'] == 1) {
	        			$flyRow = $mdalKitchen->getKitchenFlySet($profileUid, $kData['kitchen_id']);
	        			if ($flyRow['set_fly_uid'] == $uid) {
	        				$paramArr['/:b' . $kData['kitchen_id'] . '_stf'] = 0;
							$paramArr['/:b' . $kData['kitchen_id'] . '_rmf'] = 2;
	        			}
	        			else {
							$paramArr['/:b' . $kData['kitchen_id'] . '_stf'] = 0;
							$paramArr['/:b' . $kData['kitchen_id'] . '_rmf'] = 1;
	        			}
					}
	        	}
	        }
	        else {
	        	$paramArr['/:b' . $kData['kitchen_id'] . '_st'] = 1;
	        	$paramArr['/:b' . $kData['kitchen_id'] . '_ft'] = '';
        		$paramArr['/:b' . $kData['kitchen_id'] . '_rt'] = '';
            	$paramArr['/:b' . $kData['kitchen_id'] . '_vp'] = '';
            	$paramArr['/:b' . $kData['kitchen_id'] . '_pt'] = '';
            	$paramArr['/:b' . $kData['kitchen_id'] . '_tt'] = '';
				$paramArr['/:b' . $kData['kitchen_id'] . '_tp'] = '';
			    $paramArr['/:b' . $kData['kitchen_id'] . '_qp'] = '';
			    $paramArr['/:b' . $kData['kitchen_id'] . '_spc'] = '';
        		$paramArr['/:b' . $kData['kitchen_id'] . '_stf'] = '';
        		$paramArr['/:b' . $kData['kitchen_id'] . '_rmf'] = '';
        		$paramArr['/:b' . $kData['kitchen_id'] . '_try'] = '';
	        	if ($uid == $profileUid) {
	        		$paramArr['/:b' . $kData['kitchen_id'] . '_chu'] = '';
	        	}
	        	else {
	        		//##cheer
	        		$rowCheer = $mdalKitchen->getKitchenCheer($profileUid, $kData['kitchen_id'], $uid);
	        		
	        		$paramArr['/:b' . $kData['kitchen_id'] . '_chu'] = $rowCheer ? 0 : 1;
	        	}
	        	$file[$kData['kitchen_id']] = null;
	        	//$path[$kData['kitchen_id']] = 'norecipe.php';
	        }
	    }
	    $kitNum = count($kitchenAll);
	    for ($i = $kitNum + 1; $i <= 6; $i++) {
	    	$paramArr['/:b' . $i . '_st'] = 4;
            $paramArr['/:b' . $i . '_ft'] = '';
        	$paramArr['/:b' . $i . '_rt'] = '';
            $paramArr['/:b' . $i . '_vp'] = '';
            $paramArr['/:b' . $i . '_pt'] = '';
            $paramArr['/:b' . $i . '_tt'] = '';
			$paramArr['/:b' . $i . '_tp'] = '';
		    $paramArr['/:b' . $i . '_qp'] = '';
		    $paramArr['/:b' . $i . '_spc'] = '';
        	$paramArr['/:b' . $i . '_stf'] = '';
        	$paramArr['/:b' . $i . '_rmf'] = '';
        	$paramArr['/:b' . $i . '_try'] = '';
	    	$paramArr['/:b' . $i . '_chu'] = '';
	    	$file[$i] = 'disabled';
	    	$path[$i] = 'Disabled.php';
	    }
	    
    	for ($i = 1; $i <= 6; $i++) {
	    	if ($file[$i] != null) {
	    		require_once 'Mbll/Kitchen/Binarydata/' . $path[$i];
	    		$file[$i] = $$file[$i];
	    	}
	    }

		$paramArr['/:onload'] = 1;
		
		$head = "\x46\x57\x53\x04\x18\x05\x00\x00\x70\x00\x09\x60\x00\x00\x96\x00\x00\x0C\x01\x00\x43\x02\xFF\xFF\xFF";
		
		$f1 = isset($file[1]) ? "\xBF\x05" .$file[1][0]. "\x00\x00\x01\x00" .$file[1][1]. "\xFF\x09\x10\x00\x00\x00\x02\x00\x01\x00\x86\x06\x06\x01\x00\x01\x00\x00\x40\x00\x00\x00\xBF\x06\x11\x00\x00\x00\x26\x03\x00\x02\x00\xC1\xF0\x7D\xF0\x7C\xB3\xFC\xF2\x80\x6D\x31\x00" : "";
		
		$f2 = isset($file[2]) ? "\xBF\x05" .$file[2][0]. "\x00\x00\x03\x00" .$file[2][1]. "\xFF\x09\x10\x00\x00\x00\x04\x00\x01\x00\x86\x06\x06\x01\x00\x03\x00\x00\x40\x00\x00\x00\xBF\x06\x11\x00\x00\x00\x26\x05\x00\x04\x00\xC1\xF0\x7D\xF0\x7C\xD4\x0B\x0F\x28\x6D\x32\x00" : "";
		
		$f3 = isset($file[3]) ? "\xBF\x05" .$file[3][0]. "\x00\x00\x05\x00" .$file[3][1]. "\xFF\x09\x10\x00\x00\x00\x06\x00\x01\x00\x86\x06\x06\x01\x00\x05\x00\x00\x40\x00\x00\x00\xBF\x06\x11\x00\x00\x00\x26\x07\x00\x06\x00\xC1\xF0\x7D\xF0\x7C\xD7\x17\x0F\x28\x6D\x33\x00" : "";
		
		$f4 = isset($file[4]) ? "\xBF\x05" .$file[4][0]. "\x00\x00\x07\x00" .$file[4][1]. "\xFF\x09\x10\x00\x00\x00\x08\x00\x01\x00\x86\x06\x06\x01\x00\x07\x00\x00\x40\x00\x00\x00\xBF\x06\x11\x00\x00\x00\x26\x09\x00\x08\x00\xC1\xF0\x7D\xF0\x7C\xD0\xFF\x22\x10\x6D\x34\x00" : "";
		
		$f5 = isset($file[5]) ? "\xBF\x05" .$file[5][0]. "\x00\x00\x09\x00" .$file[5][1]. "\xFF\x09\x10\x00\x00\x00\x0A\x00\x01\x00\x86\x06\x06\x01\x00\x09\x00\x00\x40\x00\x00\x00\xBF\x06\x11\x00\x00\x00\x26\x0B\x00\x0A\x00\xC1\xF0\x7D\xF0\x7C\xD4\x0B\x22\x10\x6D\x35\x00" : "";
		
		$f6 = isset($file[6]) ? "\xBF\x05" .$file[6][0]. "\x00\x00\x0B\x00" .$file[6][1]. "\xFF\x09\x10\x00\x00\x00\x0C\x00\x01\x00\x86\x06\x06\x01\x00\x0B\x00\x00\x40\x00\x00\x00\xBF\x06\x11\x00\x00\x00\x26\x0D\x00\x0C\x00\xC1\xF0\x7D\xF0\x7C\xD7\x17\x22\x10\x6D\x36\x00" : "";
		
		$foot = "\xBF\x05\x1A\x00\x00\x00\x0D\x00\x48\x01\x90\x00\x64\x00\x01\x00\xD7\x82\x10\x00\x10\x13\xB8\xC8\xDD\x64\x6E\x4E\x37\x67\x00\x00" . "\xFF\x09\x10\x00\x00\x00\x0E\x00\x01\x00\x86\x06\x06\x01\x00\x0D\x00\x00\x40\x00\x00\x00\xBF\x06\x10\x00\x00\x00\x26\x01\x00\x0E\x00\x1D\xCE\x02\x5F\x80\x70\x61\x72\x61\x6D\x00\x40\x00";
		
		$source = $head . $f1 . $f2 . $f3 . $f4 . $f5 . $f6 . $foot;
		ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
		header("Content-Type: application/x-shockwave-flash");
		echo $this->swf_wrapper_source($source, $paramArr);
	    exit(0);
    }
    
    
    public function varsAction()
    {
    	$act = $this->getParam('mode');
		$kitchenId = $this->getParam('pos');
		$uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid', $uid);
		
		$paramArr = array();
		
    	if (Zend_Registry::get('ua') == 1) {
			//docomo some mobile onload
			$agent = $_SERVER['HTTP_USER_AGENT'];
			info_log($agent, 'ralf');
 			$agentSub = substr($agent,strpos($agent, '(') + 2, 3);
 			if ($agentSub >= 500) {
 				info_log('varsonload=1', 'ralf');
 				$paramArr['/:onload'] = 1;
 			}
		}
		
		$paramArr['/:haserror'] = 0;
        
		require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $kitchenRow = $mdalKitchen->getUserKitchen($profileUid, $kitchenId);
		
        $canDo = true;
        if ($uid != $profileUid) {
	        require_once 'Mdal/Kitchen/User.php';
	        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
	        $rowUserPro = $mdalProfile->getUser($profileUid);
	        if ($rowUserPro['friend_only'] == 1) {
	           	$isFriend = Bll_Friend::isFriend($uid, $profileUid);
	           	if (!$isFriend) {
	           		$canDo = false;
	           	}
	        }
        }
        
    	if (empty($kitchenId) || (!$canDo)) {
			$paramArr['/:tmp_tt'] = '';
			$paramArr['/:tmp_tp'] = '';
			$paramArr['/:tmp_qp'] = '';
			 
			$paramArr['/:tmp_rt'] = '';
			$paramArr['/:tmp_vp'] = '';
			$paramArr['/:tmp_pt'] = '';
			$paramArr['/:evt'] = 0;
        	$paramArr['/:evt_str'] = '';
            $paramArr['/:haserror'] = 1;
        }
        else {
			require_once 'Mbll/Kitchen/Kitchen.php';
		    $mbllKitchen = new Mbll_Kitchen_Kitchen();
		    require_once 'Mdal/Kitchen/Restaurant.php';
            $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
	        //switch action
			if ($act == 'spice' || $act == 'setfly' || $act == 'removefly') {
				if ($act == 'spice') {
					$rst = $mbllKitchen->addSpice($uid, $profileUid, $kitchenId);
					if (2 == $rst) {
			            $rowRes = $mdalRes->getActiveRestaurant($uid);
			            $maxLv = $mdalRes->getMaxLevel($uid);
			            $specialLv = $rowRes['level'] == $maxLv ? 1 : 0;
			            $recipeId = $kitchenRow['cooking_recipe_id'] ? $kitchenRow['cooking_recipe_id'] : 'zxh';
			            $paramArr['/:evt'] = 1;
			        	$paramArr['/:evt_str'] = '1_' .$specialLv. '_' .$recipeId. '_' .$rowRes['level'];
			        }
			        else if (1 == $rst){
			        	$paramArr['/:evt'] = 0;
			        	$paramArr['/:evt_str'] = '';
			        }
			        else {
			        	$paramArr['/:evt'] = 0;
			        	$paramArr['/:evt_str'] = '';
			        	$paramArr['/:haserror'] = 1;
			        }
				}
				else if ($act == 'setfly') {
					$rst = $mbllKitchen->setFly($uid, $profileUid, $kitchenId);
					$paramArr['/:evt'] = 0;
					$paramArr['/:evt_str'] = '';
					$paramArr['/:haserror'] = $rst ? 0 : 1;
				}
				else {
					//act = removefly
					$rst = $mbllKitchen->removeFly($uid, $profileUid, $kitchenId);
					if (2 == $rst) {
			            $rowRes = $mdalRes->getActiveRestaurant($uid);
			            $maxLv = $mdalRes->getMaxLevel($uid);
			            $specialLv = $rowRes['level'] == $maxLv ? 1 : 0;
			            $paramArr['/:evt'] = 1;
			        	$paramArr['/:evt_str'] = '3_' .$specialLv. '_' .$rowRes['level'];
			        }
			        else if (1 == $rst){
			        	$paramArr['/:evt'] = 0;
			        	$paramArr['/:evt_str'] = '';
			        }
			        else {
			        	$paramArr['/:evt'] = 0;
			        	$paramArr['/:evt_str'] = '';
			        	$paramArr['/:haserror'] = 1;
			        }
				}
				
				$nowTime = time();
		        $partAllSeconds = $kitchenRow['cooking_start_time'] + ((int)$kitchenRow['cooking_part1'] + (int)$kitchenRow['cooking_part2'] + (int)$kitchenRow['cooking_part3'])*60;
				$paramArr['/:tmp_tt'] = floor(($partAllSeconds - $nowTime)/60) .':'. ($partAllSeconds - $nowTime)%60;
				$paramArr['/:tmp_tp'] = (int)(($nowTime - $kitchenRow['cooking_start_time']) * 100 /($partAllSeconds - $kitchenRow['cooking_start_time']));
				
				$mCount = (int)$kitchenRow['kill_fly_count']+(int)$kitchenRow['add_spice_count'];
				$mCount = 50 + $mCount * 5;
				
				//add spice or removefly  value up
				$mCount = $mCount + ($act == 'setfly' ? 0 : 5);
				
				$paramArr['/:tmp_qp'] = $mCount > 100 ? 100 : $mCount;
				 
				$paramArr['/:tmp_rt'] = '';
				$paramArr['/:tmp_vp'] = '';
				$paramArr['/:tmp_pt'] = '';
			}
			
			else if ($act == 'try') {
				$rstStr = $mbllKitchen->tryFood($uid, $profileUid, $kitchenId);
				if (!$rstStr) {
		            $paramArr['/:tmp_tt'] = '';
					$paramArr['/:tmp_tp'] = '';
					$paramArr['/:tmp_qp'] = '';
					 
					$paramArr['/:tmp_rt'] = '';
					$paramArr['/:tmp_vp'] = '';
					$paramArr['/:tmp_pt'] = '';
					$paramArr['/:evt'] = 0;
		        	$paramArr['/:evt_str'] = '';
		        	$paramArr['/:haserror'] = 1;
		        }
		        else {
		        	$rstArr = explode('|', $rstStr);
			        $isUp = $rstArr[0];
			        $learn = $rstArr[1];
			        if (($isUp == 2) || $learn) {
			        	$specialLv = 0;
			        	if ($isUp == 2) {
				        	$rowRes = $mdalRes->getActiveRestaurant($uid);
				            $maxLv = $mdalRes->getMaxLevel($uid);
				            $specialLv = $rowRes['level'] == $maxLv ? 1 : 0;
			        	}
			        	
			        	if (!$learn) {
			        		$recipeGot = '0';
			        	}
			        	else {
			        		$recipeGot = $isUp == 2 ? '2' : '1';
			        	}
						$paramArr['/:evt'] = 1;
			        	$paramArr['/:evt_str'] = '4_' .$specialLv. '_' .$recipeGot. '_' . $kitchenRow['cooking_recipe_id']. '_' .$rowRes['level'] ;
			        }
			        else {
						$paramArr['/:evt'] = 0;
			        	$paramArr['/:evt_str'] = '';
			        }
			        $paramArr['/:tmp_tt'] = '';
					$paramArr['/:tmp_tp'] = '';
					$paramArr['/:tmp_qp'] = '';
					
			        $nbRecipe = Mbll_Kitchen_Cache::getRecipe($kitchenRow['cooking_recipe_id']);
			        //get cookPrice
	        		require_once 'Mdal/Kitchen/Recipe.php';
					$mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
					$rowUsrRecipe = $mdalRecipe->getUserRecipeById($profileUid, $kitchenRow['cooking_recipe_id']);
					//taste count
                    $lstTaste = $mdalKitchen->getKitchenTasteAll($profileUid, $kitchenRow['kitchen_id']);
					
	        		$baseLuckN = ($rowUsrRecipe['lucky_flag'] ? 1.5 : 1)*$nbRecipe['point'];
					$percentA = (((int)$kitchenRow['kill_fly_count']+(int)$kitchenRow['add_spice_count'])>10) ? 10 : ((int)$kitchenRow['kill_fly_count']+(int)$kitchenRow['add_spice_count']);
					$percentA = $percentA/20;
					
	                //if has fly
	            	if (1 == (int)$kitchenRow['has_fly']) {
	            	    $percentA = (($percentA - 0.2) < 0) ? 0 : ($percentA - 0.2);
	            	}
	            	
					$gainPoint = (int)($baseLuckN + ($nbRecipe['point']*$percentA));
					$paramArr['/:tmp_rt'] = ((int)$kitchenRow['complete_quantity'] - count($lstTaste)) < 10 ? 10 : ((int)$kitchenRow['complete_quantity'] - count($lstTaste));
	            	$paramArr['/:tmp_vp'] = round(($paramArr['/:tmp_rt']/(int)$kitchenRow['complete_quantity'])*100, 0);
	            	
	            	$gainPoint = (int)($gainPoint*($paramArr['/:tmp_vp']/100));
                    $gainPoint = (int)($kitchenRow['rate'] * $gainPoint / 100) > (2*$nbRecipe['point']) ? (2*$nbRecipe['point']) : (int)($kitchenRow['rate'] * $gainPoint / 100);
					
	        		$paramArr['/:tmp_pt'] = $gainPoint;
		        }
			}
			
			else if ($act == 'cheer') {
				$rst = $mbllKitchen->cheer($uid, $profileUid, $kitchenId);
				$paramArr['/:tmp_tt'] = '';
				$paramArr['/:tmp_tp'] = '';
				$paramArr['/:tmp_qp'] = '';
				 
				$paramArr['/:tmp_rt'] = '';
				$paramArr['/:tmp_vp'] = '';
				$paramArr['/:tmp_pt'] = '';
				$paramArr['/:evt'] = 0;
	        	$paramArr['/:evt_str'] = '';
	        	
	        	$paramArr['/:haserror'] = $rst ? 0 : 1;
			}
			
			else {
				$paramArr['/:tmp_tt'] = '';
				$paramArr['/:tmp_tp'] = '';
				$paramArr['/:tmp_qp'] = '';
				 
				$paramArr['/:tmp_rt'] = '';
				$paramArr['/:tmp_vp'] = '';
				$paramArr['/:tmp_pt'] = '';
				$paramArr['/:evt'] = 0;
	        	$paramArr['/:evt_str'] = '';
	        	$paramArr['/:haserror'] = 1;
			}
			//switch action over
        }
		
		$paramArr['/:onload'] = 1;
		
		
		//activity and -uu
		if ($paramArr['/:haserror'] == 0) {
			require_once 'Bll/User.php';
	        $rowProfile = array('uid' => $profileUid);
	        Bll_User::appendPerson($rowProfile, 'uid');
	        require_once 'Mbll/Kitchen/Activity.php';
	        require_once 'Mbll/Kitchen/Access.php';
	        
	        if ($act == 'spice') {
		        if ($uid != $profileUid) {
			        $activity = Mbll_Kitchen_Activity::getActivity(3, $rowProfile['displayName']);
			    }
		        $insertUu = Mbll_Kitchen_Access::tryInsert($uid, 6);
		    }
		    else if ($act == 'setfly') {
		        $activity = Mbll_Kitchen_Activity::getActivity(2, $rowProfile['displayName']);
		    }
			else if ($act == 'removefly') {
		    	$activity = Mbll_Kitchen_Activity::getActivity(1, $rowProfile['displayName']);
		    	$insertUu = Mbll_Kitchen_Access::tryInsert($uid, 7);
		    }
			else if ($act == 'try') {
				if (empty($recipeGot)) {
		    		$activity = Mbll_Kitchen_Activity::getActivity(5, $rowProfile['displayName'], $kitchenRow['cooking_recipe_id']);
				}
				else {
					$activity = Mbll_Kitchen_Activity::getActivity(4, $rowProfile['displayName'], $kitchenRow['cooking_recipe_id']);
				}
		    }
		    
		    if (!empty($activity)) {
			    $aryActivity = explode('|', $activity);
		        require_once 'Bll/Restful.php';
			    $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
			    $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');
		    }
		}

		header("Content-Type: application/x-shockwave-flash");
		
		$vars = "\x46\x57\x53\x04\x5F\x00\x00\x00\x48\x01\x90\x00\x64\x00\x00\x0C\x01\x00\x43\x02\xFF\xFF\xFF\xBF\x00\x1A\x00\x00\x00\x01\x00\x48\x01\x90\x00\x64\x00\x01\x00\xAF\x22\x2C\x00\x10\x13\xB8\xC8\xDD\x64\x6E\x4E\x37\x67\x00\x00\xFF\x09\x10\x00\x00\x00\x02\x00\x01\x00\x86\x06\x06\x01\x00\x01\x00\x00\x40\x00\x00\x00\x8C\x06\x0E\x01\x00\x02\x00\x00\x69\x00\x40\x10\x00\x00\x40\x00\x00\x00";
		
//        if (Zend_Registry::get('ua') == 1) {
//			//docomo some mobile can only use POST
//			$agent = $_SERVER['HTTP_USER_AGENT'];
//			info_log($agent, 'ralf_vars');
//			info_log('act='.$act, 'ralf_vars');
//		}
		echo $this->swf_wrapper_source($vars, $paramArr);
		exit(0);
    }
    */

    
    
    
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
        return $this->_redirect($this->_baseUrl . '/mobile/error/notfound');
    }
}