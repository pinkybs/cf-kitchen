<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile School Controller(modules/mobile/controllers/KitchenController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class KitchenController extends MyLib_Zend_Controller_Action_Mobile
{
    protected $_pageSize = 10;

    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * dispatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        $userName = $this->_user->getDisplayName();
        $this->view->app_name = 'kitchen';
        $this->view->uid = $uid;
        $this->view->userName = $userName;

        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();

        //mine
        require_once 'Mdal/Kitchen/User.php';
        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
        $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $rowMyRes = $mdalRes->getActiveRestaurant($uid);
        $rowMyPro = $mdalProfile->getUser($uid);
        $rowMyRes['total_level'] = $rowMyPro['total_level'];
        $this->view->myRes = $rowMyRes;
    }

    /**
     * index action -- welcome page
     *
     */
    public function indexAction()
    {
        $uid = $this->_user->getId();
        $this->_redirect($this->_baseUrl . '/mobile/kitchen/home');
        return;
        //$this->render();
    }

    /**
     * home action
     *
     */
    public function homeAction()
    {
        //add by xial 2010-04-27
        $result = $this->_request->getParam('result');
        if ($result == 'success') {
            //23:ask recipe communication feed配信UU; 24:ask recipe communication feed配信数
            Mbll_Kitchen_Access::insertAccess($uid, 23, 24);
        }

        $_SESSION['kitchen_kit_selectfood'] = null;
        unset($_SESSION['kitchen_kit_selectfood']);

        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid', $uid);
        require_once 'Bll/User.php';
        require_once 'Bll/Friend.php';
        require_once 'Mdal/Kitchen/User.php';
        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
        $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
        require_once 'Mdal/Kitchen/Gift.php';
        $mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        require_once 'Mdal/Kitchen/Visit.php';
        $mdalVisit = Mdal_Kitchen_Visit::getDefaultInstance();
        require_once 'Mdal/Kitchen/InviteSuccess.php';
        $mdalInvite = Mdal_Kitchen_InviteSuccess::getDefaultInstance();

        /* add by shenhw 2010/04/01 */
        $sendFriends = $mdalGift->getSendFriends(date('Y-m-d'), $uid);
        if (!$sendFriends) {
            $mdalProfile->updateUser(array('daily_gift' => 1), $uid);
        }
        /* add by shenhw 2010/04/01 */

		
        if (empty($profileUid) || $uid == $profileUid) {
            //my home
            $profileUid = $uid;
            $isSelf = 1;
            $this->view->isMyself = 1;
            
            //has invite success
            $inviteSuccessCnt = $mdalInvite->getInviteSuccessCount($uid);
            $this->view->hasInviteSuccess = $inviteSuccessCnt;
            
			$rowUserPro = $mdalProfile->getUser($profileUid);
        }
        else {
            //other's home
            $isSelf = 0;
            $this->view->isMyself = 0;
            //$this->view->isFriend = Bll_Friend::isFriend($uid, $profileUid);
            //$this->view->hasGift = $mdalGift->hasGiftByUid($uid);

            $rowUserPro = $mdalProfile->getUser($profileUid);
            $rowSelfPro = $mdalProfile->getUser($uid);

            $isFriend = Bll_Friend::isFriend($uid, $profileUid);
            if ($isFriend) {
            	$this->view->canAccess = 1;
            }
        	else {
        		if ($rowUserPro['friend_only'] == 0 && $rowSelfPro['friend_only'] == 0) {
        			$this->view->canAccess = 1;
        		}
            	else if ($rowUserPro['friend_only'] == 1) {
            		//target id friend_only and isnotfriend
            		$this->view->canAccess = 2;
            	}
            	else {
            		//self is friend_only and isnotfriend
            		$this->view->canAccess = 3;
            	}
            }
        }

        $rowUserRes = $mdalRes->getActiveRestaurant($profileUid);

        if (empty($rowUserRes) || empty($rowUserPro)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }

        //1:洋食 2:リストランテ 3:日本料理 4:中華料理
        if (1 == $rowUserRes['genre']) {
            $rowUserRes['genre_name'] = '洋食';
        }
        else if (2 == $rowUserRes['genre']) {
            $rowUserRes['genre_name'] = 'ﾘｽﾄﾗﾝﾃ';
        }
        else if (3 == $rowUserRes['genre']) {
            $rowUserRes['genre_name'] = '和食';
        }
        else if (4 == $rowUserRes['genre']) {
            $rowUserRes['genre_name'] = '中華';
        }

        require_once 'Mdal/Kitchen/NbLevel.php';
		$mdalLevel = Mdal_Kitchen_NbLevel::getDefaultInstance();
		$rowLevel = $mdalLevel->getNbLevelExp($rowUserRes['level']);
		$remainNext = $rowLevel['exp'] - $rowUserRes['exp'];
		$rowUserRes['remainNext'] = $remainNext;
		$rowUserRes['name'] = MyLib_String::unescapeString($rowUserRes['name']);

		//if (0 == $this->view->isMyself) {
	        //get neighber
	        $fids = Bll_Friend::getFriends($uid);
	        if (empty($fids)) {
	            $prevId = $nextId = $uid;
	        }
	        else {
	            $prevId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'prev', $fids);
	            if (empty($prevId)) {
	                $prevId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'last', $fids);
	            }
	            $nextId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'next', $fids);
	            if (empty($nextId)) {
	                $nextId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'first', $fids);
	            }
	        }
	        $rowUserPro['prev_uid'] = $prevId;
	        $rowUserPro['next_uid'] = $nextId;
		//}
        Bll_User::appendPerson($rowUserPro, 'uid');
        $this->view->userPro = $rowUserPro;
        $this->view->userRes = $rowUserRes;

        //get rest and recipe info
        $rest = $mdalRes->getUserAllRestaurant($profileUid);

        require_once 'Mbll/Emoji.php';
        $mbllEmoji = new Mbll_Emoji();

        $userRecipeCount = 0;
        $c = count($rest);
        require_once 'Mdal/Kitchen/Recipe.php';
        $mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();

        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRes = new Mbll_Kitchen_Restaurant();

        for ($i = 0; $i < $c; $i++) {
            //set restuarant img
            //\estate\40x40\y
            $imgPath = "/estate/40x40/" . $mbllKitchenRes->converGenreNum2Alp($rest[$i]['genre'])
                        . '/' . sprintf("%02d", ($rest[$i]['estate'] - 1)) . '.gif';
            $rest[$i]['img_path'] = $imgPath;

            //$userRecipeCount += $rest[$i]['recipe_count'];
            $recipeCountByGenre = $mdalRecipe->getUserRecipeCountByGenre($rest[$i]['uid'], $rest[$i]['genre']);
            $rest[$i]['recipe_count'] = $recipeCountByGenre;
            $userRecipeCount += $recipeCountByGenre;
            $rest[$i]['name'] = $mbllEmoji->unescapeEmoji($rest[$i]['name']);
        }

        $this->view->allUserRecipe = $userRecipeCount;
        $this->view->rowUserPro = $rowUserPro;
        $this->view->rest = $rest;
		$nbRestaurant = Mbll_Kitchen_Cache::getNbGenreList();
		$maxSysRecipe = 0;
        foreach ($nbRestaurant as $nbR) {
        	$maxSysRecipe += $nbR['recipe_count'];
        }
        $this->view->maxSysRecipe = $maxSysRecipe;
		//get rest and recipe info OVER

        //get restaurant flash
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swfFile = Mbll_Kitchen_FlashCache::getRestaurant($uid, $profileUid);
        $this->view->swfFile = $swfFile;

        require_once 'Mbll/Kitchen/Cache.php';
        require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $lstKitchen = $mdalKitchen->getUserKitchenAll($profileUid);
        if ($isSelf) {
        	//#1  get rabbit comment
        	//normal 表情 /sad 表情  / happy 表情  /boo 表情
	        $msgText = '';
	        $express = 'normal';
	    	$rcpName = '';
	    	$hasFly = false;
	    	$needSpice = false;
	    	$allCooking = true;
	        foreach ($lstKitchen as $key=>$kData) {
	        	if (!empty($kData['cooking_recipe_id']) && !empty($kData['cooking_start_time'])) {
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


            //初回表示のみ
			if (0 == $rowUserPro['total_exp'] && 1 == $rowUserPro['total_level']) {
				$msgText = 'オーナー！今日から一流のお店を目指して一緒にがんばっていこうね♪';
				$express = 'normal';
			}
			/*
			//招待した友人がアプリを追加
			else if ($inviteSuccessCnt > 0) {
				$msgText = "招待したマイミクさんがレストランを開店したよ～。遊びに行ってみようよぉ～";
				$express = 'happy';
			}
			*/
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
	        $this->view->msgText = $msgText;
	        $this->view->express = $express;


	        //#2  get access info(来店履歴)
            $lstVisit = $mdalVisit->listVisitFoot($profileUid, 1, 1);
            foreach ($lstVisit as $key => $vdata) {
                if (1 == (int)$vdata['action']) {
                    $lstVisit[$key]['action_name'] = 'ハエを撃退しました';
                }
                else if (2 == (int)$vdata['action']) {
                    $lstVisit[$key]['action_name'] = 'ハエを投入しました';
                }
                else if (3 == (int)$vdata['action']) {
                    $lstVisit[$key]['action_name'] = '味付けしました';
                }
                else if (4 == (int)$vdata['action']) {
                    $lstVisit[$key]['action_name'] = '味見しました';
                }
            	else if (5 == (int)$vdata['action']) {
                    $lstVisit[$key]['action_name'] = 'ナデナデしました';
                }
                else {
                    $lstVisit[$key]['action_name'] = '';
                }
                $lstVisit[$key]['format_time'] = strftime('%m月 %d日 %H:%M', $vdata['update_time']);
            }
            if (!empty($lstVisit) && count($lstVisit) > 0) {
                Bll_User::appendPeople($lstVisit, 'visit_uid');
            }
            $cntVisit = $mdalVisit->getVisitFootCountAll($profileUid);
            /*
            $now = getdate();
            $year = (int)$now['year'];
            $month = (int)$now['mon'];
            $day = (int)$now['mday'];
            //$yesterday = strftime('%Y-%m-%d', mktime(0, 0, 0, $month, $day, $year) - 60 * 60 * 24);
            //$cntVisitToday = $mdalVisit->getVisitFootCountByDate($profileUid, $year . '-' . $month . '-' . $day);
            //$cntVisitYesterday = $mdalVisit->getVisitFootCountByDate($profileUid, $yesterday);
            //$this->view->cntVisitToday = $cntVisitToday;
            //$this->view->cntVisitYesterday = $cntVisitYesterday;
            */
            $this->view->lstVisit = $lstVisit;
            $this->view->cntVisit = $cntVisit;

            //#3 get open alert info
        	$listOpen = $mdalVisit->getOpenAlertAll($uid);
        	if ($listOpen) {
        		$this->view->listOpen = $listOpen;
        	}
        	
        	//#4 handle daily links
        	require_once 'Mdal/Kitchen/Daily.php';
            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();

            $dailyInfo = $mdalDaily->getDaily($uid);
            $myGiftCount = $mdalGift->getMyGiftCount($uid);
            if (!$dailyInfo) {

            	$dailyInfo = array('uid' => $uid, 'renew_time' => time(),
            					   'gift' => $myGiftCount,'history' => 0,'board' => 0,'announce' => 1);
            	$mdalDaily->insertDaily($dailyInfo);
            }

            //#4-1 has received gift
            $this->view->hasGift = $myGiftCount;

            //#4-2 has visit History
            $this->view->hasHistory = $dailyInfo['history'];

        	//#4-3 has board
            $this->view->hasBoard = $dailyInfo['board'];

            //#4-4 has announce
            $this->view->hasAnnounce = Mbll_Kitchen_Cache::getSysAnnounce($uid);

            //#5 cook status friend
            require_once 'Bll/Friend.php';
	        $fids = Bll_Friend::getFriends($uid);

	        if (empty($fids)) {
	            $this->view->cntCookStatus = 0;
	        }
	        else {
	        	$firstMaxUid = $mdalKitchen->getMaxCookingTime($fids);
	        	if ($firstMaxUid) {
		        	$keyFirst = array_search($firstMaxUid, $fids);
		        	array_splice($fids, $keyFirst, 1);

		        	if (!empty($fids)) {
			        	$secondMaxUid = $mdalKitchen->getMaxCookingTime($fids);
			        	$listFriendKitchen = $mdalKitchen->getListFriendKitchen(array($firstMaxUid,$secondMaxUid));
			        	$cntCookStatus = 2;
		        	}
		        	else {
		        		$listFriendKitchen = $mdalKitchen->getUserKitchenAll($firstMaxUid);
		        		$cntCookStatus = 1;
		        	}
	        	}
	        	else {
	        		$cntCookStatus = 0;
	        	}

	        	if ($cntCookStatus == 0) {
	        		$this->view->cntCookStatus = 0;
	        	}
	        	else if ($cntCookStatus == 1) {
	        		$teamOne = array();
	        		$nowTime = time();
	        		foreach ($listFriendKitchen as $cs) {
	        			$needTime = $cs['cooking_start_time'] + ((int)$cs['cooking_part1'] + (int)$cs['cooking_part2'] + (int)$cs['cooking_part3'])*60;
	        			if ($needTime > $nowTime) {
	        				$teamOne['cntCooking'] += 1;
	        			}
	        			else if (!empty($cs['cooking_recipe_id'])){
	        				$teamOne['cntFinish'] += 1;
	        			}
	        		}
	        		$this->view->cntCookStatus = 1;
	        		$teamOne['uid'] = $firstMaxUid;
		        	Bll_User::appendPerson($teamOne, 'uid');
		        	$this->view->teamOne = $teamOne;
	        	}
	        	else {
		        	$baseUid = $listFriendKitchen[0]['uid'];
		        	$maxStartTime = 0;
		        	$maxStartUid = 0;

		        	$teamOne = array();
		        	$teamTwo = array();
		        	$nowTime = time();
	        		foreach ($listFriendKitchen as $cs) {
		        		$needTime = $cs['cooking_start_time'] + ((int)$cs['cooking_part1'] + (int)$cs['cooking_part2'] + (int)$cs['cooking_part3'])*60;
		        		if ($cs['uid'] == $baseUid) {
		        			$teamOne[] = $cs;
		        			if ($needTime > $nowTime) {
		        				$teamOne['cntCooking'] += 1;
		        			}
		        			else if (!empty($cs['cooking_recipe_id'])){
		        				$teamOne['cntFinish'] += 1;
		        			}
		        		}
		        		else {
		        			$teamTwo[] = $cs;
		        			if ($needTime > $nowTime) {
		        				$teamTwo['cntCooking'] += 1;
		        			}
		        			else if (!empty($cs['cooking_recipe_id'])){
		        				$teamTwo['cntFinish'] += 1;
		        			}
		        		}

	        			if ($cs['cooking_start_time'] >= $maxStartTime) {
		        			$maxStartTime = $cs['cooking_start_time'];
		        			$maxStartUid = $cs['uid'];
		        		}
		        	}

		        	if ($maxStartUid != $teamOne[0]['uid']) {
		        		 $temp = $teamOne;
		        		 $teamOne = $teamTwo;
		        		 $teamTwo = $temp;
		        	}

		        	$teamOne['uid'] = $teamOne[0]['uid'];
		        	Bll_User::appendPerson($teamOne, 'uid');
		        	$this->view->teamOne = $teamOne;
		        	if (empty($teamTwo)) {
		        		$cntCookStatus = 1;

		        	}
		        	else {
		        		$cntCookStatus = 2;
		        		$teamTwo['uid'] = $teamTwo[0]['uid'];
		        		Bll_User::appendPerson($teamTwo, 'uid');
		        		$this->view->teamTwo = $teamTwo;
		        	}

		        	$this->view->cntCookStatus = $cntCookStatus;
	        	}
	        }

	        //#6 board message
	        require_once 'Mdal/Kitchen/Board.php';
            $mdalBoard = Mdal_Kitchen_Board::getDefaultInstance();
            $board = $mdalBoard->getNewestBoard($uid);
            if ($board) {
	            Bll_User::appendPerson($board, 'uid');
		        $board['datetime'] = strftime("%m月 %d日 %H:%M", $board['create_time']);

		        //$board['is_f'] = 1;
		        $board['is_f'] = Bll_Friend::isFriend($uid, $board['uid']) ? 1 : 0;
	            $this->view->board = $board;
	        }

            //#7 minifeed
            require_once 'Mdal/Kitchen/Activity.php';
            $mdalActivity = Mdal_Kitchen_Activity::getDefaultInstance();
            $actSize = 1;
            $lstMiniFeed = $mdalActivity->getActivity($uid, $actSize);
            foreach ($lstMiniFeed as $key => $vdata) {
                $lstMiniFeed[$key]['format_time'] = strftime('%m月 %d日 %H:%M', $vdata['create_time']);
            }
            if (!empty($lstMiniFeed) && count($lstMiniFeed) > 0) {
                Bll_User::appendPeople($lstMiniFeed, 'send_uid');
                $lstMiniFeed[0]['is_f'] = Bll_Friend::isFriend($uid, $lstMiniFeed[0]['send_uid']) ? 1 : 0;
            }
            //$cntMiniFeed = count($lstMiniFeed);

            $this->view->lstMiniFeed = $lstMiniFeed;
            $this->view->actSize = $actSize;
            
            //#8 cook status self
            $listMyKitchen = $mdalKitchen->getUserKitchenAll($uid);
            $teamSelf = array();
        	$nowTime = time();
        	foreach ($listMyKitchen as $lmk) {
        		$needTime = $lmk['cooking_start_time'] + ((int)$lmk['cooking_part1'] + (int)$lmk['cooking_part2'] + (int)$lmk['cooking_part3'])*60;
        		if ($needTime > $nowTime) {
        			$teamSelf['cntCooking'] += 1;
        		}
        		else if (!empty($lmk['cooking_recipe_id'])){
        			$teamSelf['cntFinish'] += 1;
        		}
        	}
        	$this->view->teamSelf = $teamSelf;
            
        }
        else {
        	//friend
        	//#1 get kitchen_recipe info
        	foreach ($lstKitchen as $key=>$kData) {
        		if (!empty($kData['cooking_recipe_id']) && !empty($kData['cooking_start_time'])) {
	        		$nowTime = time();
	        		$needSeconds = ((int)$kData['cooking_part1'] + (int)$kData['cooking_part2'] + (int)$kData['cooking_part3'])*60;
					if (($kData['cooking_start_time'] + (int)$kData['cooking_part1'] * 60 <= $nowTime) &&
					    ($kData['cooking_start_time'] + $needSeconds > $nowTime)) {
					    $lstKitchen[$key]['cooking'] = 1;
	        			$rowKitchenSpice = $mdalKitchen->getKitchenSpice($profileUid, $kData['kitchen_id'], $uid);
						if (empty($rowKitchenSpice)) {
							$lstKitchen[$key]['need_spice'] = 1;
						}
	        		}

	        		if ($kData['cooking_start_time'] + $needSeconds <= $nowTime) {
	        			$lstKitchen[$key]['cookover'] = 1;
	        			$rowKitchenTaste = $mdalKitchen->getKitchenTaste($profileUid, $kData['kitchen_id'], $uid);
						if (empty($rowKitchenTaste)) {
							$lstKitchen[$key]['can_taste'] = 1;
						}
	        		}
	        		else {
		        		if (1 == $kData['has_fly']) {
		        			$lstKitchen[$key]['has_fly'] = 1;
		        			$rowFly = $mdalKitchen->getKitchenFlySet($profileUid, $kData['kitchen_id']);
		        			if ($rowFly['set_fly_uid'] != $uid) {
		        				$lstKitchen[$key]['can_remove'] = 1;
		        			}
		        		}
	        		}

	        		$nbRecipe = Mbll_Kitchen_Cache::getRecipe($kData['cooking_recipe_id']);
				    $lstKitchen[$key]['rcpName'] = $nbRecipe['recipe_name'];
				    $lstKitchen[$key]['pic'] = $nbRecipe['f'] . '/' . $nbRecipe['n'] . '.gif';
	        	}
	        	else {
	        		$lstKitchen[$key]['pic'] = 'null_cookingstove.gif';
	        		$lstKitchen[$key]['blank'] = 1;
	        		$rowOrder = $mdalKitchen->getKitchenOrder($profileUid, $kData['kitchen_id'], $uid);
	        		$lstKitchen[$key]['can_order'] = empty($rowOrder) ? 1 : 0;
	        	}

        		if ($this->view->canAccess != 1) {
	        		$lstKitchen[$key]['has_fly'] = 1;
	        		$lstKitchen[$key]['can_remove'] = 0;
	        		$lstKitchen[$key]['can_taste'] = 0;
	        		$lstKitchen[$key]['need_spice'] = 0;
	        		$lstKitchen[$key]['can_order'] = 0;
	        	}
        	}
        	$this->view->lstKitchen = $lstKitchen;

        	//#2　can cheertwo or not
        	$nowDate = date('Y-m-d');
        	$rowCheerTwo = $mdalKitchen->getCheerTwo($uid, $profileUid, $nowDate);
        	if (empty($rowCheerTwo['act_date']) || $nowDate != $rowCheerTwo['act_date']) {
				$this->view->canCheerTwo = 1;
			}

            //#3 get open alert info
        	if (1 == $this->getParam('CF_open', 0)) {
        		$mdalVisit->deleteOpenAlert($uid, $profileUid);
        	}

        	//#4 get inviteby info and open days
            //zhaoxh modify at 0326
            $inviteByInfo = $mdalInvite->getTargetInviteSuccess($profileUid);
            $this->view->inviteByUid = empty($inviteByInfo) ? 0 : $inviteByInfo['uid'];
            if ($this->view->inviteByUid) {
            	$inviteByPro = array('uid' => $inviteByInfo['uid']);
            	Bll_User::appendPerson($inviteByPro);
            	$this->view->inviteByName = $inviteByPro['displayName'];
            }
            $this->view->openDays = (int)((time() - $rowUserPro['create_time'])/86400);

            //#5 UUs
            require_once 'Mdal/Kitchen/Access.php';
            $mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
            try {
            	$mdalAccess->insertUu(array('type' => 9, 'create_time'=> time()));
            }
            catch (Exception $e){
            }
            //access analyse -uu
		    require_once 'Mbll/Kitchen/Access.php';
	        $insertUu = Mbll_Kitchen_Access::tryInsert($this->_USER_ID, 10);

	        //#6 boardinfo
	        require_once 'Mdal/Kitchen/Board.php';
            $mdalBoard = Mdal_Kitchen_Board::getDefaultInstance();
            //$board = $mdalBoard->getNewestBoard($uid);
            $boardtwo = $mdalBoard->getNewestBoardTwo($profileUid, 2);
            Bll_User::appendPeople($boardtwo, 'uid');
	        foreach ($boardtwo as $key=>$board) {
            	$boardtwo[$key]['datetime'] = strftime("%m月 %d日 %H:%M", $boardtwo[$key]['create_time']);
            	if ($uid != $board['uid']) {
            		$boardtwo[$key]['is_f'] = Bll_Friend::isFriend($uid, $board['uid']) ? 1 : 0;
            	}
            	else {
            		$boardtwo[$key]['is_f'] = 1;
	        	}
	        }
            $this->view->boardtwo = $boardtwo;

			/*
            //#7 minifeed
            require_once 'Mdal/Kitchen/Activity.php';
            $mdalActivity = Mdal_Kitchen_Activity::getDefaultInstance();
            $actSize = 2;
            $lstMiniFeed = $mdalActivity->getActivity($profileUid, $actSize);
            foreach ($lstMiniFeed as $key => $vdata) {
                $lstMiniFeed[$key]['format_time'] = strftime('%m月 %d日 %H:%M', $vdata['create_time']);
                if ($uid != $vdata['send_uid']) {
            		$lstMiniFeed[$key]['is_f'] = Bll_Friend::isFriend($uid, $vdata['send_uid']) ? 1 : 0;
            	}
            	else {
            		$lstMiniFeed[$key]['is_f'] = 1;
	        	}
            }
            if (!empty($lstMiniFeed) && count($lstMiniFeed) > 0) {
                Bll_User::appendPeople($lstMiniFeed, 'send_uid');
            }
            //$cntMiniFeed = count($lstMiniFeed);

            $this->view->lstMiniFeed = $lstMiniFeed;
            $this->view->actSize = $actSize;
			*/
        }

        $this->render();
    }

    /**
     * setfly action
     *
     */
    public function setflyAction()
    {

        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        $kitchenId = $this->getParam('CF_kitchen_id');
        if (empty($profileUid) || empty($kitchenId)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }
        //check allow to set fly
        require_once 'Mbll/Kitchen/Kitchen.php';
        $mbllKitchen = new Mbll_Kitchen_Kitchen();
        $rst = $mbllKitchen->setFly($uid, $profileUid, $kitchenId);
        if (!$rst) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }


    	require_once 'Mdal/Kitchen/User.php';
        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
        $rowUserPro = $mdalProfile->getUser($profileUid);
        if ($rowUserPro['friend_only'] == 1 && $uid != $profileUid) {
           	$isFriend = Bll_Friend::isFriend($uid, $profileUid);
           	if (!$isFriend) {
           		$timeNow = time();
           		info_log($timeNow . 'setfly-' . $uid . '-' . $profileUid, 'kitchen_try_food');
           	    $this->_redirect($this->_baseUrl . '/mobile/error/error');
           	}
        }



        require_once 'Bll/User.php';
        $rowProfile = array('uid' => $profileUid);
        Bll_User::appendPerson($rowProfile, 'uid');
        $this->view->profileInfo = $rowProfile;

       //zhaoxh20100206   activity 2
        require_once 'Mbll/Kitchen/Activity.php';
        $activity = Mbll_Kitchen_Activity::getActivity(2, $rowProfile['displayName']);
        $aryActivity = explode('|', $activity);

        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

        $this->render();
    }

    /**
     * removefly action
     *
     */
    public function removeflyAction()
    {

        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        $kitchenId = $this->getParam('CF_kitchen_id');

        if (empty($kitchenId)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }

        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
            $this->view->isMyself = 1;
        }


        require_once 'Mdal/Kitchen/User.php';
        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
        $rowUserPro = $mdalProfile->getUser($profileUid);
        if ($rowUserPro['friend_only'] == 1 && $uid != $profileUid) {
           	$isFriend = Bll_Friend::isFriend($uid, $profileUid);
           	if (!$isFriend) {
           		$timeNow = time();
           		info_log($timeNow . 'removefly-' . $uid . '-' . $profileUid, 'kitchen_try_food');
           	    $this->_redirect($this->_baseUrl . '/mobile/error/error');
           	}
        }


        //remove fly
        require_once 'Mbll/Kitchen/Kitchen.php';
        $mbllKitchen = new Mbll_Kitchen_Kitchen();
        $rst = $mbllKitchen->removeFly($uid, $profileUid, $kitchenId);
        if (!$rst) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }

        //is level up
        if (1 == $rst || 3 ==$rst ) {
            require_once 'Mdal/Kitchen/Restaurant.php';
            $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
            $rowRes = $mdalRes->getActiveRestaurant($uid);
            require_once 'Mdal/Kitchen/NbLevel.php';
            $mdalLevel = Mdal_Kitchen_NbLevel::getDefaultInstance();
            $rowLevel = $mdalLevel->getNbLevelExp($rowRes['level']);
            $remainNext = $rowLevel['exp'] - $rowRes['exp'];
            $this->view->remainNext = $remainNext;
            if (3 == $rst) {
            	$this->view->isGainZeroExp = 1;
            }
        }
        else {
            $this->view->isLevUp = 1;
            require_once 'Mdal/Kitchen/Restaurant.php';
            $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
            $maxLevel = $dalRest->getMaxLevel($uid);
            $this->view->step = $maxLevel;

            $rowUserRes = $dalRest->getActiveRestaurant($uid);

	        //1:洋食 2:リストランテ 3:日本料理 4:中華料理
	        if (1 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '洋食';
	        }
	        else if (2 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = 'ﾘｽﾄﾗﾝﾃ';
	        }
	        else if (3 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '和食';
	        }
	        else if (4 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '中華';
	        }
        	//zhaoxh20100421 minifeed 99
	        $miniFeed = Mbll_Kitchen_Activity::getMiniFeed(99, $rowUserRes['genre_name'] . 'ﾚﾍﾞﾙが' . $rowUserRes['level']);
	        $aryMiniFeed = explode('|', $miniFeed);

	        $fids = Bll_Friend::getFriends($this->_USER_ID);
	        require_once 'Mdal/Kitchen/Activity.php';
	        $mdalActivity = Mdal_Kitchen_Activity::getDefaultInstance();
	        try {
		        foreach ($fids as $value) {
		        	$arrInsert = array('send_uid' => $this->_USER_ID,
		        	                   'rcv_uid' => $value,
		        	                   'feed' => $aryMiniFeed[0],
		        	                   'create_time' => time());
		        	$mdalActivity->insertMiniFeed($arrInsert);
		        }
	        }
	        catch (Exception $e) {
	        }
        }

        require_once 'Bll/User.php';
        $rowProfile = array('uid' => $profileUid);
        Bll_User::appendPerson($rowProfile, 'uid');
        $this->view->profileInfo = $rowProfile;


        //zhaoxh20100206   activity 1
        require_once 'Mbll/Kitchen/Activity.php';
        $activity = Mbll_Kitchen_Activity::getActivity(1, $rowProfile['displayName']);
        $aryActivity = explode('|', $activity);

        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

        //access analyse -uu
	    require_once 'Mbll/Kitchen/Access.php';
        $insertUu = Mbll_Kitchen_Access::tryInsert($uid, 7);
        $this->render();
    }

    /**
     * add spice action
     *
     */
    public function addspiceAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        $kitchenId = $this->getParam('CF_kitchen_id');
        if (empty($kitchenId)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }

        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
            $this->view->isMyself = 1;
        }


        require_once 'Mdal/Kitchen/User.php';
        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
        $rowUserPro = $mdalProfile->getUser($profileUid);
        if ($rowUserPro['friend_only'] == 1 && $uid != $profileUid) {
           	$isFriend = Bll_Friend::isFriend($uid, $profileUid);
           	if (!$isFriend) {
           		$timeNow = time();
           		info_log($timeNow . 'addspice-' . $uid . '-' . $profileUid, 'kitchen_try_food');
           	    $this->_redirect($this->_baseUrl . '/mobile/error/error');
           	}
        }


        //add spice
        require_once 'Mbll/Kitchen/Kitchen.php';
        $mbllKitchen = new Mbll_Kitchen_Kitchen();
        $rst = $mbllKitchen->addSpice($uid, $profileUid, $kitchenId);
        if (!$rst) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }

        //is level up
        if (1 == $rst) {
            require_once 'Mdal/Kitchen/Restaurant.php';
            $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
            $rowRes = $mdalRes->getActiveRestaurant($uid);
            require_once 'Mdal/Kitchen/NbLevel.php';
            $mdalLevel = Mdal_Kitchen_NbLevel::getDefaultInstance();
            $rowLevel = $mdalLevel->getNbLevelExp($rowRes['level']);
            $remainNext = $rowLevel['exp'] - $rowRes['exp'];
            $this->view->remainNext = $remainNext;
        }
        else {
            $this->view->isLevUp = 1;
            require_once 'Mdal/Kitchen/Restaurant.php';
            $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
            $maxLevel = $dalRest->getMaxLevel($uid);
            //require_once 'Mdal/Kitchen/User.php';
            //$mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
            //$rowMyPro = $mdalProfile->getUser($uid);
            $this->view->step = $maxLevel;

            $rowUserRes = $dalRest->getActiveRestaurant($uid);

	        //1:洋食 2:リストランテ 3:日本料理 4:中華料理
	        if (1 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '洋食';
	        }
	        else if (2 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = 'ﾘｽﾄﾗﾝﾃ';
	        }
	        else if (3 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '和食';
	        }
	        else if (4 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '中華';
	        }
        	//zhaoxh20100421 minifeed 99
	        $miniFeed = Mbll_Kitchen_Activity::getMiniFeed(99, $rowUserRes['genre_name'] . 'ﾚﾍﾞﾙが' . $rowUserRes['level']);
	        $aryMiniFeed = explode('|', $miniFeed);

	        $fids = Bll_Friend::getFriends($this->_USER_ID);
	        require_once 'Mdal/Kitchen/Activity.php';
	        $mdalActivity = Mdal_Kitchen_Activity::getDefaultInstance();
	        try {
		        foreach ($fids as $value) {
		        	$arrInsert = array('send_uid' => $this->_USER_ID,
		        	                   'rcv_uid' => $value,
		        	                   'feed' => $aryMiniFeed[0],
		        	                   'create_time' => time());
		        	$mdalActivity->insertMiniFeed($arrInsert);
		        }
	        }
	        catch (Exception $e) {
	        }
        }

        //recipe info
        require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $rowKitchen = $mdalKitchen->getUserKitchen($profileUid, $kitchenId);
        if (!empty($rowKitchen)) {
            require_once 'Mbll/Kitchen/Cache.php';
            $nbRecipe = Mbll_Kitchen_Cache::getRecipe($rowKitchen['cooking_recipe_id']);
            $this->view->rcpName = $nbRecipe['recipe_name'];
        }
        require_once 'Bll/User.php';
        $rowProfile = array('uid' => $profileUid);
        Bll_User::appendPerson($rowProfile, 'uid');
        $this->view->profileInfo = $rowProfile;

        //zhaoxh20100206   activity 3
        if ($uid != $profileUid) {
	        require_once 'Mbll/Kitchen/Activity.php';
	        $activity = Mbll_Kitchen_Activity::getActivity(3, $rowProfile['displayName']);
	        $aryActivity = explode('|', $activity);

	        require_once 'Bll/Restful.php';
	        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
	        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');
	    }

	    //access analyse -uu
	    require_once 'Mbll/Kitchen/Access.php';
        $insertUu = Mbll_Kitchen_Access::tryInsert($uid, 6);

        //xial 2010-04-15 friend 味付 uu,pv
        if ($profileUid != $uid) {
            try {
                $mdalKitchenAccess = Mdal_Kitchen_Access::getDefaultInstance();
            	//friend に対する味付け人数
                $mdalKitchenAccess->insertUu(array('type' => 16, 'create_time' => time()));
            }
            catch (Exception $e){
            }
            //friend に対する味付けUU
        	$insertUu = Mbll_Kitchen_Access::tryInsert($uid, 15);
        }

        $this->render();
    }

    /**
     * try food action
     *
     */
    public function tryfoodAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        $kitchenId = $this->getParam('CF_kitchen_id');
        if (empty($profileUid) || $uid == $profileUid || empty($kitchenId)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }

        require_once 'Mdal/Kitchen/User.php';
        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
        $userInfo = $mdalProfile->getUser($uid);

        require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $rowKitchen = $mdalKitchen->getUserKitchen($profileUid, $kitchenId);
        //can not use spoon when has this recipe
        require_once 'Mdal/Kitchen/Recipe.php';
        $mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();

        if (!empty($rowKitchen['cooking_recipe_id'])) {
        	$hasThisRecipe = $mdalRecipe->hasRecipe($uid, $rowKitchen['cooking_recipe_id']);
        }

    	require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
		$isLearnGenre = $mdalRest->hasRest($uid, $rowKitchen['genre']);

        if (!$this->getParam('CF_spoonOver') && $userInfo['try_rate_add'] == 0 && !$hasThisRecipe && $isLearnGenre){
	        require_once 'Mdal/Kitchen/Item.php';
	        $mdalItem = Mdal_Kitchen_Item::getDefaultInstance();
	        $userSpoon = $mdalItem->getUserSpoon($uid);
	        if (count($userSpoon) == 2 && $userSpoon[0]['item_count'] > 0 && $userSpoon[1]['item_count'] > 0) {
	        	$this->_redirect($this->_baseUrl . '/mobile/kitchenitem/useitem/CF_step/spoonList/CF_uid/' . $profileUid . '/CF_kitchen_id/' . $kitchenId);
	        }
	        else if ($userSpoon[0]['item_count'] > 0 || $userSpoon[1]['item_count'] > 0) {
	        	if ($userSpoon[0]['item_count'] > 0) {
	        		$item_id = $userSpoon[0]['item_id'];
	        	}
	        	else {
	        		$item_id = $userSpoon[1]['item_id'];
	        	}
	        	$this->_redirect($this->_baseUrl . '/mobile/kitchenitem/useitem/CF_itemId/' . $item_id . '/CF_uid/' . $profileUid . '/CF_kitchen_id/' . $kitchenId);
	        }
        }

        $rowUserPro = $mdalProfile->getUser($profileUid);
        if ($rowUserPro['friend_only'] == 1 && $uid != $profileUid) {
           	$isFriend = Bll_Friend::isFriend($uid, $profileUid);
           	if (!$isFriend) {
           		$timeNow = time();
           		info_log($timeNow . 'trtfood-' . $uid . '-' . $profileUid, 'kitchen_try_food');
	        	if ($userInfo['try_rate_add'] != 0) {
	        		require_once 'Mbll/Kitchen/Item.php';
		            $mbllItem = new Mbll_Kitchen_Item();

		            $mbllItem->reSpoon($uid, $userInfo['try_rate_add']);
	        	}
           	    $this->_redirect($this->_baseUrl . '/mobile/error/error');
           	}
        }


        //try food
        require_once 'Mbll/Kitchen/Kitchen.php';
        $mbllKitchen = new Mbll_Kitchen_Kitchen();
        $rst = $mbllKitchen->tryFood($uid, $profileUid, $kitchenId);
        if (!$rst) {
        	$userInfo = $mdalProfile->getUser($uid);
        	if ($userInfo['try_rate_add'] != 0) {
        		require_once 'Mbll/Kitchen/Item.php';
	            $mbllItem = new Mbll_Kitchen_Item();

	            $mbllItem->reSpoon($uid, $userInfo['try_rate_add']);
        	}
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }
        $aryRst = explode('|', $rst);
        $isUp = $aryRst[0];
        $learn = $aryRst[1];

        //xial 2010-04-15 friend 味見 uu,pv
        if ($profileUid != $uid && !empty($rst)) {
            try {
                $mdalKitchenAccess = Mdal_Kitchen_Access::getDefaultInstance();
            	//friendに対する味見け人数 pv
                $mdalKitchenAccess->insertUu(array('type' => 18, 'create_time' => time()));
            }
            catch (Exception $e){
            }
            //friendに対する味見けUU
        	$insertUu = Mbll_Kitchen_Access::tryInsert($uid, 17);
        }

        //is level up
        if (1 == $isUp) {
            require_once 'Mdal/Kitchen/Restaurant.php';
            $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
            $rowRes = $mdalRes->getActiveRestaurant($uid);
            require_once 'Mdal/Kitchen/NbLevel.php';
            $mdalLevel = Mdal_Kitchen_NbLevel::getDefaultInstance();
            $rowLevel = $mdalLevel->getNbLevelExp($rowRes['level']);
            $remainNext = $rowLevel['exp'] - $rowRes['exp'];
            $this->view->remainNext = $remainNext;
        }
        else {
            $this->view->isLevUp = 1;
            require_once 'Mdal/Kitchen/Restaurant.php';
            $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
            $maxLevel = $dalRest->getMaxLevel($uid);
            //require_once 'Mdal/Kitchen/User.php';
            //$mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
            //$rowMyPro = $mdalProfile->getUser($uid);
            $this->view->step = $maxLevel;

        	$rowUserRes = $dalRest->getActiveRestaurant($uid);

	        //1:洋食 2:リストランテ 3:日本料理 4:中華料理
	        if (1 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '洋食';
	        }
	        else if (2 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = 'ﾘｽﾄﾗﾝﾃ';
	        }
	        else if (3 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '和食';
	        }
	        else if (4 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '中華';
	        }
        	//zhaoxh20100421 minifeed 99
	        $miniFeed = Mbll_Kitchen_Activity::getMiniFeed(99, $rowUserRes['genre_name'] . 'ﾚﾍﾞﾙが' . $rowUserRes['level']);
	        $aryMiniFeed = explode('|', $miniFeed);

	        $fids = Bll_Friend::getFriends($this->_USER_ID);
	        require_once 'Mdal/Kitchen/Activity.php';
	        $mdalActivity = Mdal_Kitchen_Activity::getDefaultInstance();
	        try {
		        foreach ($fids as $value) {
		        	$arrInsert = array('send_uid' => $this->_USER_ID,
		        	                   'rcv_uid' => $value,
		        	                   'feed' => $aryMiniFeed[0],
		        	                   'create_time' => time());
		        	$mdalActivity->insertMiniFeed($arrInsert);
		        }
	        }
	        catch (Exception $e) {
	        }
        }

        require_once 'Bll/User.php';
        $rowProfile = array('uid' => $profileUid);
        Bll_User::appendPerson($rowProfile, 'uid');
        $this->view->profileInfo = $rowProfile;
        require_once 'Mbll/Kitchen/Activity.php';

        require_once 'Mbll/Kitchen/Cache.php';
        //recipe info
        //require_once 'Mdal/Kitchen/Kitchen.php';
        //$mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $rowKitchen = $mdalKitchen->getUserKitchen($profileUid, $kitchenId);
        if (!empty($rowKitchen)) {
            $nbRecipe = Mbll_Kitchen_Cache::getRecipe($rowKitchen['cooking_recipe_id']);
            $this->view->recipeInfo = $nbRecipe;

            if ($learn) {
                require_once 'Mdal/Kitchen/Food.php';
                $mdalFood = Mdal_Kitchen_Food::getDefaultInstance();
                //get recipe info
                $aryBaseFood = array();
                $rowFood = Mbll_Kitchen_Cache::getFood($nbRecipe['food1']);
                $aryBaseFood[0]['food_id'] = $rowFood['food_id'];
                $aryBaseFood[0]['food_name'] = $rowFood['food_name'];
                $aryBaseFood[0]['food_picture'] = $rowFood['food_picture'];
                $aryBaseFood[0]['food_got'] = $mdalFood->hasFood($uid, $rowFood['food_id']);
                $rowFood = Mbll_Kitchen_Cache::getFood($nbRecipe['food2']);
                $aryBaseFood[1]['food_id'] = $rowFood['food_id'];
                $aryBaseFood[1]['food_name'] = $rowFood['food_name'];
                $aryBaseFood[1]['food_picture'] = $rowFood['food_picture'];
                $aryBaseFood[1]['food_got'] = $mdalFood->hasFood($uid, $rowFood['food_id']);
                if (!empty($nbRecipe['food3'])) {
                    $rowFood = Mbll_Kitchen_Cache::getFood($nbRecipe['food3']);
                    $aryBaseFood[2]['food_id'] = $rowFood['food_id'];
                    $aryBaseFood[2]['food_name'] = $rowFood['food_name'];
                    $aryBaseFood[2]['food_picture'] = $rowFood['food_picture'];
                    $aryBaseFood[2]['food_got'] = $mdalFood->hasFood($uid, $rowFood['food_id']);
                }

                $this->view->lstBaseFood = $aryBaseFood;
                $this->view->isLearned = 1;

                //zhaoxh20100206   activity 4
                $activity = Mbll_Kitchen_Activity::getActivity(4, $rowProfile['displayName'], $rowKitchen['cooking_recipe_id']);
            }
            else {
            	//zhaoxh20100206   activity 5
		        $activity = Mbll_Kitchen_Activity::getActivity(5, $rowProfile['displayName'], $rowKitchen['cooking_recipe_id']);
            }
            $aryActivity = explode('|', $activity);

            require_once 'Bll/Restful.php';
	        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
	        $restful->createActivityWithPic(array('title' => $aryActivity[0]), $aryActivity[1], 'image/gif');

        }

        $this->render();
    }

    /**
     * cook finish action
     *
     */
    public function cookfinishAction()
    {
        $uid = $this->_user->getId();
        $kitchenId = $this->getParam('CF_kitchen_id');
        if (empty($kitchenId)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }

        //cook finish
        require_once 'Mbll/Kitchen/Kitchen.php';
        $mbllKitchen = new Mbll_Kitchen_Kitchen();
        $rst = $mbllKitchen->cookFinish($uid, $kitchenId);

        $aryRst = explode('|', $rst);
        $isUp = $aryRst[0];
        $point = $aryRst[1];
        $exp = $aryRst[2];
        $rcpId = $aryRst[3];

    	if (!$rst) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }
        else {
            //access analyse
	        require_once 'Mdal/Kitchen/Access.php';
        	$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
        	try {
        		$mdalAccess->insertMoney(array('uid' => $uid,
        									   'amount' => $point,
        									   'type' => 2,
        									   'description' => 'cook_finish',
        									   'create_time' => time()));
        	}
        	catch (Exception $e){
	        }
        }

        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $rowRes = $mdalRes->getActiveRestaurant($uid);

        //is level up
        if (1 == $rst) {
            require_once 'Mdal/Kitchen/NbLevel.php';
            $mdalLevel = Mdal_Kitchen_NbLevel::getDefaultInstance();
            $rowLevel = $mdalLevel->getNbLevelExp($rowRes['level']);
            $remainNext = $rowLevel['exp'] - $rowRes['exp'];
            $this->view->remainNext = $remainNext;
            $this->view->step = "";
            if (1 == $rowRes['level']) {
                $this->view->step = "1";
            }
        }
        else {
            $this->view->isLevUp = 1;
            require_once 'Mdal/Kitchen/Restaurant.php';
            $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
            $maxLevel = $dalRest->getMaxLevel($uid);
            $this->view->step = $maxLevel;

            if ($maxLevel == 2) {
	            //access analyse -uu
		        require_once 'Mdal/Kitchen/Access.php';
	        	$mdalAccess = Mdal_Kitchen_Access::getDefaultInstance();
	        	try {
	        		$mdalAccess->insertUu(array('type' => 4,
	        							        'create_time' => time()));
	        	}
	        	catch (Exception $e){
		        }
            }

        	$rowUserRes = $dalRest->getActiveRestaurant($uid);

	        //1:洋食 2:リストランテ 3:日本料理 4:中華料理
	        if (1 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '洋食';
	        }
	        else if (2 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = 'ﾘｽﾄﾗﾝﾃ';
	        }
	        else if (3 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '和食';
	        }
	        else if (4 == $rowUserRes['genre']) {
	            $rowUserRes['genre_name'] = '中華';
	        }
        	//zhaoxh20100421 minifeed 99
	        $miniFeed = Mbll_Kitchen_Activity::getMiniFeed(99, $rowUserRes['genre_name'] . 'ﾚﾍﾞﾙが' . $rowUserRes['level']);
	        $aryMiniFeed = explode('|', $miniFeed);

	        $fids = Bll_Friend::getFriends($this->_USER_ID);
	        require_once 'Mdal/Kitchen/Activity.php';
	        $mdalActivity = Mdal_Kitchen_Activity::getDefaultInstance();
	        try {
		        foreach ($fids as $value) {
		        	$arrInsert = array('send_uid' => $this->_USER_ID,
		        	                   'rcv_uid' => $value,
		        	                   'feed' => $aryMiniFeed[0],
		        	                   'create_time' => time());
		        	$mdalActivity->insertMiniFeed($arrInsert);
		        }
	        }
	        catch (Exception $e) {
	        }

        }

        //recipe info
        require_once 'Mbll/Kitchen/Cache.php';
        $nbRecipe = Mbll_Kitchen_Cache::getRecipe($rcpId);
        $this->view->rcpName = $nbRecipe['recipe_name'];
        $this->view->gainPoint = $point;
        $this->view->gainExp = $exp;

        //access analyse -uu
        require_once 'Mbll/Kitchen/Access.php';
        $insertUu = Mbll_Kitchen_Access::tryInsert($uid, 3);

//        if (Zend_Registry::get('ua') == 1) {
//			//docomo some mobile can only use POST
//			$agent = $_SERVER['HTTP_USER_AGENT'];
//			info_log($agent, 'ralf_urls');
//			info_log('cookfinish'.$uid, 'ralf_urls');
//		}
        $this->render();
    }

    /**
     * flash callback action
     *
     */
    public function flashcallbackAction()
    {
        $uid = $this->_user->getId();
        $fromFlash = $this->getParam('CF_flash');

        if ('kitchen' == $fromFlash) {
            //1	レシピ選択   2	味付けする   3	ハエを飛ばす   4	ハエを撃退   5	どうぐを使う   6	料理を運ぶ   7	味見する
            $act = (int)$this->getParam('act');
            $kitchenId = (int)$this->getParam('place');
            $profileUid = $this->getParam('owner');

            if (1 == $act) {
                $this->_redirect($this->_baseUrl . '/mobile/kitchenrecipe/choice?CF_kitchen_id=' . $kitchenId);
            }
            else if (2 == $act) {
                $this->_redirect($this->_baseUrl . '/mobile/kitchen/addspice?CF_uid=' . $profileUid . '&CF_kitchen_id=' . $kitchenId);
            }
            else if (3 == $act) {
                $this->_redirect($this->_baseUrl . '/mobile/kitchen/setfly?CF_uid=' . $profileUid . '&CF_kitchen_id=' . $kitchenId);
            }
            else if (4 == $act) {
                $this->_redirect($this->_baseUrl . '/mobile/kitchen/removefly?CF_uid=' . $profileUid . '&CF_kitchen_id=' . $kitchenId);
            }
            else if (5 == $act) {
                $this->_redirect($this->_baseUrl . '/mobile/kitchenitem/item?CF_kitchen_id=' . $kitchenId);
            }
            else if (6 == $act) {
                $this->_redirect($this->_baseUrl . '/mobile/kitchen/cookfinish?CF_kitchen_id=' . $kitchenId);
            }
            else if (7 == $act) {
                $this->_redirect($this->_baseUrl . '/mobile/kitchen/tryfood?CF_uid=' . $profileUid . '&CF_kitchen_id=' . $kitchenId);
            }
            return;
        }

        else {
            $this->_redirect($this->_baseUrl . '/mobile/kitchen/home');
            return;
        }
    }

    //zhaoxh 20100310 added
    /**
     * wrapper flash callback action
     */
	public function wrapdispatchAction()
    {
        $uid = $this->_user->getId();
        $act = $this->getParam('mode');
		$kitchenId = $this->getParam('pos');
        $profileUid = $this->getParam('CF_uid', $uid);

		//select: レシピ選択    item: どうぐを使う  finish: 料理を運ぶ  cancel: 調理をやめる evt:levelup or stealrecipe
        if ('select' == $act) {
            $this->_redirect($this->_baseUrl . '/mobile/kitchenrecipe/choice?CF_kitchen_id=' . $kitchenId);
        }
        else if ('item' == $act) {
            $this->_redirect($this->_baseUrl . '/mobile/kitchenitem/item?CF_kitchen_id=' . $kitchenId);
        }
        else if ('finish' == $act) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchen/cookfinish?CF_kitchen_id=' . $kitchenId);
        }
        else if ('cancel' == $act) {
            $this->_redirect($this->_baseUrl . '/mobile/kitchen/cancelconfirm?CF_kitchen_id=' . $kitchenId);
        }
        else if ('evt' == $act){
        	$evtStr = $this->getParam('event');
        	$this->_redirect($this->_baseUrl . '/mobile/kitchen/varsevt/event/' . $evtStr . '/CF_uid/' . $profileUid);
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchen/home');
        }
        return;
    }

    //zhaoxh 20100310 add over
    public function cancelconfirmAction()
    {
        $uid = $this->_user->getId();
        $kitchenId = $this->getParam('CF_kitchen_id');
        if (empty($kitchenId)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $kitchenRow = $mdalKitchen->getUserKitchen($uid, $kitchenId);
		$rst = 1;
        if (!$kitchenRow['cooking_recipe_id'] || !$kitchenRow['cooking_start_time']) {
			$rst = 0;
		}

        $nbRecipe = Mbll_Kitchen_Cache::getRecipe($kitchenRow['cooking_recipe_id']);

		$this->view->nbRecipe = $nbRecipe;
        $this->view->kitchenId = $kitchenId;
        $this->view->rst = $rst;

        $this->render();
    }

    public function cancelsubmitAction()
    {
        $uid = $this->_user->getId();
        $kitchenId = $this->getParam('CF_kitchen_id');
        if (empty($kitchenId)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
            return;
        }
		require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
        $kitchenRow = $mdalKitchen->getUserKitchen($uid, $kitchenId);
    	$rst = 1;
        if (!$kitchenRow['cooking_recipe_id'] || !$kitchenRow['cooking_start_time']) {
			$rst = 0;
		}
		else {
			$nbRecipe = Mbll_Kitchen_Cache::getRecipe($kitchenRow['cooking_recipe_id']);

			$this->view->nbRecipe = $nbRecipe;
			//cook finish
	        require_once 'Mbll/Kitchen/Kitchen.php';
	        $mbllKitchen = new Mbll_Kitchen_Kitchen();
	        $re = $mbllKitchen->cookCancel($uid, $kitchenId);
			if (!re) {
	            $this->_redirect($this->_baseUrl . '/mobile/error/error');
			}
		}

        $this->view->rst = $rst;

        $this->render();
    }

	public function varsevtAction()
    {
        $uid = $this->_user->getId();
        $event = $this->getParam('event', '');
        $profileUid = $this->getParam('CF_uid', $uid);
        if (empty($event)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
        $actNum = substr($event,0,1);
        $specialLv = substr($event,2,1);

        if ($actNum == 1) {
        	$recipeId = substr($event,4,3);
        	$nowLv = substr($event,8);

        	$rcpInfo = Mbll_Kitchen_Cache::getRecipe($recipeId);
        	$this->view->rcpInfo = $rcpInfo;
        	$this->view->recipeGot = 0;
        }
        else if ($actNum == 3) {
        	$nowLv = substr($event,4);

        	$proArr = array('uid' => $profileUid);
        	Bll_User::appendPerson($proArr);
        	$this->view->displayName = $proArr['displayName'];

        	$this->view->recipeGot = 0;
        }
        else if ($actNum == 4) {
        	$recipeGot = substr($event,4,1);
        	$recipeId = substr($event,6,3);
        	$nowLv = substr($event,10);

        	$rcpInfo = Mbll_Kitchen_Cache::getRecipe($recipeId);
        	$this->view->rcpInfo = $rcpInfo;
        	$this->view->recipeGot = $recipeGot;

        	$proArr = array('uid' => $profileUid);
        	Bll_User::appendPerson($proArr);
        	$this->view->displayName = $proArr['displayName'];
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
        $this->view->actNum = $actNum;
        $this->view->nowLv = $nowLv;
        $this->view->specialLv = $specialLv;

        $this->view->profileUid = $profileUid;
        $this->render();
    }
    //********************** add by shenhw **************************************


    /**
     * ranking list action
     *
     */
    public function rankinglistAction()
    {

        $pageIndex = $this->getParam('CF_page');
        $orderType = $this->getParam('CF_ordertype', "nakano_order");
        $order = "DESC";
        $pageSize = 10;

        require_once 'Mdal/Kitchen/Rank.php';
        $mdalRank = Mdal_Kitchen_Rank::getDefaultInstance();

        //$type 1->mixifriend, 2->all
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($this->_user->getId());

        if (!$pageIndex) {
            //get user rank number
            $userRankNm = $mdalRank->getUserRankNum($this->_user->getId(), $fids, $orderType, $order);
            $pageIndex = ceil($userRankNm / $pageSize);
        }

        //get rank list
        $rankList = $mdalRank->getRankList($this->_user->getId(), $fids, $pageIndex, $pageSize, $orderType, $order);
        $rankCount = $mdalRank->getRankCount($this->_user->getId(), $fids);

        //add by zhaoxh 20100412 , add cooking_dishes_count and cookover_dishes_count
        $cntCut = count($rankList);
        $fidsCut = array();
        $fidsInSetStr = "'";
        for ($i = 0; $i < $cntCut;$i++) {
        	$fidsCut[] = $rankList[$i]['uid'];
        	$fidsInSetStr .= $rankList[$i]['uid'] . ",";
        }
        $fidsInSetStr = substr($fidsInSetStr, 0, strlen($fidsInSetStr)-1);
        $fidsInSetStr .= "'";

        $cookingDish = $mdalRank->getCookingDish($fidsCut, $fidsInSetStr);
        $cookOverDish = $mdalRank->getCookOverDish($fidsCut, $fidsInSetStr);

        for ($i = 0; $i < count($cookingDish); $i++ ) {
        	for ($j = 0; $j < $cntCut; $j++) {
        		if ($cookingDish[$i]['uid'] == $rankList[$j]['uid']) {
        			$rankList[$j]['cooking_dish'] = $cookingDish[$i]['cooking_dish'];
        			$i++;
        			if ($i == count($cookingDish)) {
        				break 2;
        			}
        		}
        	}
        }

        for ($i = 0; $i < count($cookOverDish); $i++ ) {
        	for ($j = 0; $j < $cntCut; $j++) {
        		if ($cookOverDish[$i]['uid'] == $rankList[$j]['uid']) {
        			$rankList[$j]['cookover_dish'] = $cookOverDish[$i]['cookover_dish'];
        			$i++;
        			if ($i == count($cookOverDish)) {
        				break 2;
        			}
        		}
        	}
        }
        //zhaoxh 20100412 over

        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankList, 'uid');

        $this->view->rankList = $rankList;
        $this->view->rankCount = $rankCount;
        $this->view->orderType = $orderType;

        //get total recipe count
        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRestaurant = new Mbll_Kitchen_Restaurant();

        $genreList = $mbllKitchenRestaurant->getGenreList();
        $totalRecipeCount = 0;
        foreach ($genreList as $genre) {
            $totalRecipeCount += $genre['recipe_count'];
        }
        $this->view->totalRecipeCount = $totalRecipeCount;

        //get pager info
        $this->view->pager = array('count' => $rankCount, 'pageIndex' => $pageIndex, 'requestUrl' => '/mobile/kitchen/rankinglist', 'pageSize' => $pageSize, 'maxPager' => ceil($rankCount / $pageSize), 'pageParam' => '&CF_ordertype=' . $orderType);

        $this->render();
    }

    /**
     * access list action
     *
     */
    public function accesslistAction()
    {
        $uid = $this->getParam('CF_uid', $this->_user->getId());
        $pageIndex = $this->getParam('CF_page', 1);
        $pageSize = 10;

        $clearDaily = $this->getParam('CF_clearDaily', 0);
        if ($clearDaily) {
        	require_once 'Mdal/Kitchen/Daily.php';
            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
            //update daily param
            $mdalDaily->updateDaily(array('history'=>0), $this->_user->getId());
        }

        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();

        require_once 'Mdal/Kitchen/Visit.php';
        $mdalVisit = Mdal_Kitchen_Visit::getDefaultInstance();

        //get active restaurant info
        $activeResInfo = $mdalRes->getActiveRestaurant($uid);
        $this->view->activeResInfo = $activeResInfo;

        //get access list
        $accessList = $mdalVisit->getVisitList($uid, $pageIndex, $pageSize);
        $accessCount = $mdalVisit->getVisitCount($uid);

        $userInfo = array('uid' => $uid);
        require_once 'Bll/User.php';
        Bll_User::appendPeople($accessList, 'visit_uid');
        Bll_User::appendPerson($userInfo, 'uid');

        $this->view->userInfo = $userInfo;
        $this->view->accessList = $accessList;
        $this->view->accessCount = $accessCount;

        //get start number and end number
        $start = ($pageIndex - 1) * $pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $pageSize) > $accessCount ? $accessCount : ($start + $pageSize);

        //get pager info
        $this->view->pager = array('count' => $accessCount, 'pageIndex' => $pageIndex, 'requestUrl' => '/mobile/kitchen/accesslist', 'pageSize' => $pageSize, 'maxPager' => ceil($accessCount / $pageSize), 'pageParam' => '&CF_uid=' . $uid);

        $this->render();
    }

    public function minifeedlistAction()
    {
        $uid = $this->getParam('CF_uid', $this->_user->getId());
        $pageIndex = $this->getParam('CF_page', 1);
        $pageSize = 10;

        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();

        require_once 'Mdal/Kitchen/Activity.php';
        $mdalActivity = Mdal_Kitchen_Activity::getDefaultInstance();

        //get minifeed list
        $minifeedList = $mdalActivity->getMiniFeedList($uid, $pageIndex, $pageSize);
        $minifeedCount = $mdalActivity->getMiniFeedCount($uid);

        $userInfo = array('uid' => $uid);
        require_once 'Bll/User.php';
        Bll_User::appendPeople($minifeedList, 'send_uid');
        Bll_User::appendPerson($userInfo, 'uid');

        foreach ($minifeedList as $key=>$feed) {
            $minifeedList[$key]['datetime'] = strftime("%m月 %d日 %H:%M", $minifeedList[$key]['create_time']);
            if ($this->_USER_ID != $feed['send_uid']) {
            	$minifeedList[$key]['is_f'] = Bll_Friend::isFriend($this->_USER_ID, $feed['send_uid']) ? 1 : 0;
            }
            else {
            	$minifeedList[$key]['is_f'] = 1;
        	}
        }

        $this->view->userInfo = $userInfo;
        $this->view->minifeedList = $minifeedList;
        $this->view->minifeedCount = $minifeedCount;

        //get start number and end number
        $start = ($pageIndex - 1) * $pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $pageSize) > $minifeedCount ? $minifeedCount : ($start + $pageSize);

        //get pager info
        $this->view->pager = array('count' => $minifeedCount, 'pageIndex' => $pageIndex, 'requestUrl' => '/mobile/kitchen/minifeedlist', 'pageSize' => $pageSize, 'maxPager' => ceil($minifeedCount / $pageSize), 'pageParam' => '&CF_uid=' . $uid);

        $this->render();
    }

    //**********************add by hch*****************************//
    /**
     * top action
     *
     */
    public function topAction()
    {
    	$this->_redirect($this->_baseUrl . '/mobile/kitchen/home');
        $uid = $this->_USER_ID;

        //get ranking
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($uid);

        require_once 'Mdal/Kitchen/Kitchen.php';
        $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();

        if (empty($fids)) {
            $this->view->friendCookingCnt = 0;
        }
        else {
            $this->view->friendCookingCnt = $mdalKitchen->friendCookingCnt($fids);
        }

        require_once 'Mdal/Kitchen/Rank.php';
        $mdalRank = Mdal_Kitchen_Rank::getDefaultInstance();
        //$userRankNm = $mdalRank->getUserRankNum($uid, $fids, 'total_level', 'DESC');

//        if ($userRankNm > 10) {
//            $ranking = $mdalRank->getRankList($uid, $fids, 1, 9, 'total_level', 'DESC');
//            $selfRank = $mdalRank->getSelfRank($uid, $userRankNm);
//
//            $ranking = array_merge($ranking, $selfRank);
//        }
//        else {
            $ranking = $mdalRank->getRankList($uid, $fids, 1, 10, 'total_level', 'DESC');
//        }

        require_once 'Bll/User.php';
        Bll_User::appendPeople($ranking, 'uid');
        $this->view->ranking = $ranking;

        //get total recipe count
        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRestaurant = new Mbll_Kitchen_Restaurant();

        $genreList = $mbllKitchenRestaurant->getGenreList();
        $totalRecipeCount = 0;
        foreach ($genreList as $genre) {
            $totalRecipeCount += $genre['recipe_count'];
        }
        $this->view->totalRecipeCount = $totalRecipeCount;

        $this->render();
    }

    /**
     * profile action
     *
     */
    public function profileAction()
    {
    	$uid = $this->getParam('CF_uid', $this->_USER_ID);

        //get user profile
        require_once 'Mdal/Kitchen/User.php';
        $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
        $user = $mdalUser->getUser($uid);
        require_once 'Bll/User.php';
        Bll_User::appendPerson($user);

        //add user lv and up exp
        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $rowUserRes = $mdalRes->getActiveRestaurant($uid);
        require_once 'Mdal/Kitchen/NbLevel.php';
        $mdalLevel = Mdal_Kitchen_NbLevel::getDefaultInstance();
        $rowLevel = $mdalLevel->getNbLevelExp($rowUserRes['level']);
        $user['upexp'] = $rowLevel['exp'] - $rowUserRes['exp'];
        $user['lv'] = $rowLevel['level']-1;

        $this->view->user = $user;

        //get user restaurant
        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $rest = $mdalRest->getUserAllRestaurant($uid);

        require_once 'Mbll/Emoji.php';
        $mbllEmoji = new Mbll_Emoji();

        $userRecipeCount = 0;
        $c = count($rest);
        require_once 'Mdal/Kitchen/Recipe.php';
        $mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();

        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRes = new Mbll_Kitchen_Restaurant();

        for ($i = 0; $i < $c; $i++) {
            //set restuarant img
            //\estate\40x40\y
            $imgPath = "/estate/40x40/" . $mbllKitchenRes->converGenreNum2Alp($rest[$i]['genre'])
                        . '/' . sprintf("%02d", ($rest[$i]['estate'] - 1)) . '.gif';
            $rest[$i]['img_path'] = $imgPath;

            //$userRecipeCount += $rest[$i]['recipe_count'];
            $recipeCountByGenre = $mdalRecipe->getUserRecipeCountByGenre($rest[$i]['uid'], $rest[$i]['genre']);
            $rest[$i]['recipe_count'] = $recipeCountByGenre;
            $userRecipeCount += $recipeCountByGenre;
            $rest[$i]['name'] = $mbllEmoji->unescapeEmoji($rest[$i]['name']);
        }

        $this->view->allUserRecipe = $userRecipeCount;
        $this->view->rest = $rest;

        //get total recipe count
        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRestaurant = new Mbll_Kitchen_Restaurant();

        $genreList = $mbllKitchenRestaurant->getGenreList();
        $totalRecipeCount = 0;
        foreach ($genreList as $genre) {
            $totalRecipeCount += $genre['recipe_count'];
        }
        $this->view->totalRecipeCount = $totalRecipeCount;

        //require_once 'Mbll/Kitchen/FlashCache.php';
        //$this->view->swfFile = Mbll_Kitchen_FlashCache::getProfile($uid);

        $this->view->ismine = $uid == $this->_USER_ID ? 1 : 0;

        require_once 'Mdal/Kitchen/Chef.php';
        $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
        $chefArr = $mdalChef->getCfChef($uid);

        if (empty($chefArr)) {
        	info_log($uid, 'http_build_query');
        }

        $paramStr = http_build_query($chefArr);
    	$this->view->paramStr = $paramStr;

        $this->render();
    }

    public function profiletwoAction()
    {
        $uid = $this->_USER_ID;

        //get user profile
        require_once 'Mdal/Kitchen/User.php';
        $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
        $user = $mdalUser->getUser($uid);
        require_once 'Bll/User.php';
        Bll_User::appendPerson($user);

        //add user lv and up exp
        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $rowUserRes = $mdalRes->getActiveRestaurant($uid);
        require_once 'Mdal/Kitchen/NbLevel.php';
        $mdalLevel = Mdal_Kitchen_NbLevel::getDefaultInstance();
        $rowLevel = $mdalLevel->getNbLevelExp($rowUserRes['level']);
        $user['upexp'] = $rowLevel['exp'] - $rowUserRes['exp'];
        $user['lv'] = $rowLevel['level']-1;



        //get user restaurant
        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $rest = $mdalRest->getUserAllRestaurant($uid);

        require_once 'Mbll/Emoji.php';
        $mbllEmoji = new Mbll_Emoji();

        $userRecipeCount = 0;
        $c = count($rest);
        require_once 'Mdal/Kitchen/Recipe.php';
        $mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();

        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRes = new Mbll_Kitchen_Restaurant();

        for ($i = 0; $i < $c; $i++) {
            //set restuarant img
            //\estate\40x40\y
            $imgPath = "/estate/40x40/" . $mbllKitchenRes->converGenreNum2Alp($rest[$i]['genre'])
                        . '/' . sprintf("%02d", ($rest[$i]['estate'] - 1)) . '.gif';
            $rest[$i]['img_path'] = $imgPath;

            //$userRecipeCount += $rest[$i]['recipe_count'];
            $recipeCountByGenre = $mdalRecipe->getUserRecipeCountByGenre($rest[$i]['uid'], $rest[$i]['genre']);
            $rest[$i]['recipe_count'] = $recipeCountByGenre;
            $userRecipeCount += $recipeCountByGenre;
            $rest[$i]['name'] = $mbllEmoji->unescapeEmoji($rest[$i]['name']);
        }

        $this->view->allUserRecipe = $userRecipeCount;
        $this->view->rest = $rest;

        //get total recipe count
        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRestaurant = new Mbll_Kitchen_Restaurant();

        $genreList = $mbllKitchenRestaurant->getGenreList();
        $totalRecipeCount = 0;
        foreach ($genreList as $genre) {
            $totalRecipeCount += $genre['recipe_count'];
        }
        $this->view->totalRecipeCount = $totalRecipeCount;

        require_once 'Mdal/Kitchen/Chef.php';
        $mdalChef = Mdal_Kitchen_Chef::getDefaultInstance();
        $chefArr = $mdalChef->getCfChef($uid);

        if (empty($chefArr)) {
        	info_log($uid, 'http_build_query');
        }

        $paramStr = http_build_query($chefArr);
    	$this->view->paramStr = $paramStr;


    	//add by zhaoxh 100318
    	if ($this->getParam('CF_editspeech') == 1) {
    		$speechText = $this->getPost('CF_speech', '');
    		$speechText = mb_ereg_replace( "^(　| |\t|\n|\r|\0|\x0B)*|(　| |\t|\n|\r|\0|\x0B)*$", "", $speechText);
    		if (mb_strlen($speechText,'utf-8') > 32) {
	            $error = 2;
	        }

	        /*
	        else {
	            $escapeEmojiName = $mbllEmoji->escapeEmoji($speechText, true);
	            if (strlen($escapeEmojiName) != strlen($speechText)) {
	                $error = 3;
	            }
	        }
	    	*/


	        $speechText = $mbllEmoji->escapeEmoji($speechText);

	        if (empty($error) && $user['speech'] != $speechText) {
	        	$mdalUser->updateUser(array('speech' => $speechText), $uid);
	        	$user['speech'] = $speechText;
	        }
    	}
    	else {
        	$speechText = $user['speech'];
    	}

	    $this->view->speechText = $speechText;
        $this->view->user = $user;
        $this->view->error = $error;
        $this->render();
    }

    /**
     * set restaurant in use action
     *
     */
    public function changerestaurantAction()
    {
        $step = $this->getParam('CF_step', "confirm");
        if (!$step) {
            $step = $this->getPost('CF_step', "confirm");
        }

        $genre = $this->getParam('CF_genre', 1);
        if (!$genre) {
            $genre = $this->getPost('CF_genre', 1);
        }

        $uid = $this->_user->getId();

        require_once 'Mbll/Kitchen/Restaurant.php';
        $mbllKitchenRes = new Mbll_Kitchen_Restaurant();

        if ("confirm" == $step) {
            require_once 'Mdal/Kitchen/Restaurant.php';
            $mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
            $res = $mdalRes->getOneRestaurant($uid, $genre);
            $restName = $res['name'];

            $genreName = $mbllKitchenRes->getGenreNameById($genre);

            $changeRestInfo = $mbllKitchenRes->getUserRestaurantByGenre($uid, $genre);
            $changeRestInfo['genre_alp'] = $mbllKitchenRes->converGenreNum2Alp($changeRestInfo['genre']);
            $changeRestInfo['estate_pic'] = sprintf("%02d", ($changeRestInfo['estate'] - 1));

            $this->view->changeRestInfo = $changeRestInfo;
            $this->view->restName = $restName;
            $this->view->genre = $genre;
            $this->view->genreName = $genreName;
        }
        else if ("complete" == $step) {
            //change restaurant
            $mbllKitchenRes->changeRestaurant($uid, $genre);
        }

        $this->view->uid = $uid;
        $this->view->step = $step;
        $this->render();
    }

    public function chefpngAction()
    {
    	//$uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');

        $express = $this->getParam('CF_express');

        require_once 'Mbll/Kitchen/FlashCache.php';
        $stream = Mbll_Kitchen_FlashCache::getChefImage($profileUid, $express);

        echo $stream;
        exit(0);
    }

    /*************** add by shenhw ************************/
    public function inviteguideAction()
    {
        $this->render();
    }

    public function invitefinishAction()
    {
        $this->render();
    }

    //zhaoxh add 20100409
    public function boardlistAction()
    {
        $uid = $this->getParam('CF_uid', $this->_user->getId());
        $pageIndex = $this->getParam('CF_page', 1);
        $pageSize = 10;

        $clearDaily = $this->getParam('CF_clearDaily', 0);
        if ($clearDaily) {
        	require_once 'Mdal/Kitchen/Daily.php';
            $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
            //update daily param
            $mdalDaily->updateDaily(array('board'=>0), $this->_user->getId());
        }

        require_once 'Mdal/Kitchen/Board.php';
        $mdalBoard = Mdal_Kitchen_Board::getDefaultInstance();
        $boardList = $mdalBoard->getBoardList($uid, $pageIndex, $pageSize);
        $boardCount = $mdalBoard->getBoardCount($uid);

        if ($uid != $this->_user->getId() && count($boardList) > 2) {
        	$this->view->showJump = 1;
        }
    	if ($uid != $this->_user->getId()) {
        	$this->view->showWrite = 1;
        	$this->view->profileUid = $uid;
        }

        $userInfo = array('uid' => $uid);
        require_once 'Bll/User.php';
        Bll_User::appendPeople($boardList, 'uid');
        Bll_User::appendPerson($userInfo, 'uid');

        foreach ($boardList as $key=>$data) {
	        if ($this->_USER_ID != $data['uid']) {
	            $boardList[$key]['is_f'] = Bll_Friend::isFriend($this->_USER_ID, $data['uid']) ? 1 : 0;
	        }
	        else {
	            $boardList[$key]['is_f'] = 1;
	        }
        }

        $this->view->userInfo = $userInfo;
        $this->view->boardList = $boardList;
        $this->view->boardCount = $boardCount;

        //get start number and end number
        $start = ($pageIndex - 1) * $pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $pageSize) > $boardCount ? $boardCount : ($start + $pageSize);

        //get pager info
        $this->view->pager = array('count' => $boardCount, 'pageIndex' => $pageIndex, 'requestUrl' => '/mobile/kitchen/boardlist', 'pageSize' => $pageSize, 'maxPager' => ceil($boardCount / $pageSize), 'pageParam' => '&CF_uid=' . $uid);

        $this->render();
    }

    public function writeboardAction()
    {
    	$uid = $this->_user->getId();

        $profileUid = $this->getParam('CF_uid', $uid);

        if ($uid == $profileUid) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $step = $this->getParam('CF_step', 'confirm');

        $txt = $this->getPost('CF_txt','');

        //maxlength=50  &&  emoji  deal
		$txt = mb_ereg_replace( "^(　| |\t|\n|\r|\0|\x0B)*|(　| |\t|\n|\r|\0|\x0B)*$", "", $txt);
		if (mb_strlen($txt,'utf-8') > 50) {
            $error = '50文字超えます、50以降無視された!';
			$txtShow = mb_substr($txt, 0, 50, 'utf-8');
			$txt = $txtShow;
        }
        else if (mb_strlen($txt,'utf-8') == 0){
            $error = 'おカキコミください';
            $this->view->opera = 1;
        }
        else {
        	$txtShow = $txt;
        }

        //maxlength=50  &&  emoji  deal

        $this->view->alertTxt = $error;
        //$this->view->opera = $error == '' ? 0 : 1;
        $this->view->txt = $txt;
        $this->view->txtShow = $txtShow;

        $this->view->step = $step;
        $this->view->profileUid = $profileUid;
        /*
        $board = $mdalBoard->getNewestBoard($uid);
        Bll_User::appendPerson($board, 'uid');
        $board['datetime'] = strftime("%m月 %d日 %H:%M", $board['create_time']);

        $this->view->board = $board;
        */
        require_once 'Mdal/Kitchen/Board.php';
        $mdalBoard = Mdal_Kitchen_Board::getDefaultInstance();
        $mdalBoard->writeTmp($uid, $profileUid, $txt);
        
        $proArr = array('uid' => $profileUid);
        Bll_User::appendPerson($proArr);
        
        
        $picurl = Zend_Registry::get('static') . '/apps/kitchen/mobile/img/write.gif';

        $commFeedTitle = 'マイミク' . $proArr['displayName'] . 'にカキコミました!';
        $commFeedUrl = urlencode($picurl) . ',image/gif';

        $this->view->commFeedTitle = $commFeedTitle;
        $this->view->commFeedUrl = $commFeedUrl;
        $this->view->appId = $this->_APP_ID;
        
        $this->render();
    }

    public function writeboardfinishAction()
    {
        $uid = $this->_user->getId();
        
        require_once 'Mdal/Kitchen/Board.php';
        $mdalBoard = Mdal_Kitchen_Board::getDefaultInstance();
        $txtArr = $mdalBoard->readTmp($uid);
		$txt = $txtArr['txt'];
		
    	$profileUid = $txtArr['fid'];

        if ($uid == $profileUid || empty($profileUid)) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }
        
        require_once 'Mbll/Emoji.php';
        $mbllEmoji = new Mbll_Emoji();
        $txt = $mbllEmoji->escapeEmoji($txt);

        require_once 'Mdal/Kitchen/Board.php';
        $mdalBoard = Mdal_Kitchen_Board::getDefaultInstance();
        $mdalBoard->insertBoard(array('uid' => $uid, 'target_uid' => $profileUid, 'txt' => $txt, 'create_time' => time()));

        //update target`s board
        //update daily param
        require_once 'Mdal/Kitchen/Daily.php';
        $mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
        $dailyInfo = $mdalDaily->getDaily($profileUid);
        if ($dailyInfo) {
            $mdalDaily->updateDaily(array('board' => $dailyInfo['board'] + 1), $profileUid);
        }

        $profileUidInfo = array('uid' => $profileUid);
        Bll_User::appendPerson($profileUidInfo, 'uid');
        $this->view->profileUidInfo = $profileUidInfo;

        //get neighbor
        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
        $fids = Bll_Friend::getFriends($uid);
        if (empty($fids)) {
            $prevId = $nextId = $uid;
        }
        else {
            $prevId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'prev', $fids);
            if (empty($prevId)) {
                $prevId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'last', $fids);
            }
            $nextId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'next', $fids);
            if (empty($nextId)) {
                $nextId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'first', $fids);
            }
        }

        $this->view->prev_uid = $prevId;
        $this->view->next_uid = $nextId;


        $this->view->step = $step;
        $this->view->profileUid = $profileUid;
        /*
        $board = $mdalBoard->getNewestBoard($uid);
        Bll_User::appendPerson($board, 'uid');
        $board['datetime'] = strftime("%m月 %d日 %H:%M", $board['create_time']);

        $this->view->board = $board;
        */

        $this->view->commFeedTitle = $profileUidInfo['displayName'] . "さんにカキコミしました!";
        $this->view->commFeedUrl = urlencode($this->_baseUrl . "/mobile/index/getpic?CF_uid=" . $uid) . ',image/gif';;
        //$this->view->commFeedUrl = urlencode("http://kitchen.mixitest.communityfactory.net/static/apps/kitchen/mobile/img/recipe/kitchen.gif") . ',image/gif';;
        $this->view->appId = $this->_APP_ID;

        //xial 2010-04-21 : カキコミ数
        try {
            $mdalKitchenAccess = Mdal_Kitchen_Access::getDefaultInstance();
		    $mdalKitchenAccess->insertUu(array('type' => 21, 'create_time' => time()));
        } catch (Exception $e) {

        }//カキコミUU
		$insertUu = Mbll_Kitchen_Access::tryInsert($uid, 22);

        $this->render();
    }

    /**
     * cheer v2 action
     *
     */
    public function cheertwoAction()
    {
    	$uid = $this->_user->getId();

        $profileUid = $this->getParam('CF_uid', $this->_user->getId());

        if ($uid == $profileUid) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $profileUidInfo = array('uid' => $profileUid);
        Bll_User::appendPerson($profileUidInfo, 'uid');
        $this->view->profileUidInfo = $profileUidInfo;

	    $nowDate = date('Y-m-d');
	    require_once 'Mbll/Kitchen/Kitchen.php';
        $mbllKitchen = new Mbll_Kitchen_Kitchen();
        $re = $mbllKitchen->cheertwo($uid, $profileUid, $nowDate);

        if (!$re) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        //get neighbor
        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
        $fids = Bll_Friend::getFriends($uid);
        if (empty($fids)) {
            $prevId = $nextId = $uid;
        }
        else {
            $prevId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'prev', $fids);
            if (empty($prevId)) {
                $prevId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'last', $fids);
            }
            $nextId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'next', $fids);
            if (empty($nextId)) {
                $nextId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'first', $fids);
            }
        }

        $this->view->prev_uid = $prevId;
        $this->view->next_uid = $nextId;

        $this->render();
    }
    
    public function orderconfirmAction()
    {
        $uid = $this->_user->getId();

        $profileUid = $this->getParam('CF_uid', $this->_user->getId());
        $kitchenId = $this->getParam('CF_kitchen_id');
        
        if ($uid == $profileUid || !$kitchenId) {
            $this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $profileUidInfo = array('uid' => $profileUid);
        Bll_User::appendPerson($profileUidInfo, 'uid');
        $this->view->profileUidInfo = $profileUidInfo;
        $this->view->profileUid = $profileUid;
        $this->view->kitchenId = $kitchenId;

        $this->view->commFeedTitle = $profileUidInfo['displayName'] . "さんに料理の注文が入っています！";
        $this->view->appId = $this->_APP_ID;
        
        $this->render();
    }
    
	public function orderfinishAction()
    {
    	$uid = $this->_user->getId();

        $profileUid = $this->getParam('CF_uid', $this->_user->getId());
		$kitchenId = $this->getParam('CF_kitchen_id');
        
        if ($uid == $profileUid || !$kitchenId) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        $profileUidInfo = array('uid' => $profileUid);
        Bll_User::appendPerson($profileUidInfo, 'uid');
        $this->view->profileUidInfo = $profileUidInfo;

	    $nowDate = date('Y-m-d');
	    require_once 'Mbll/Kitchen/Kitchen.php';
        $mbllKitchen = new Mbll_Kitchen_Kitchen();
        $re = $mbllKitchen->order($uid, $profileUid, $kitchenId);

        if (!$re) {
        	$this->_redirect($this->_baseUrl . '/mobile/error/error');
        }

        //get neighbor
        $mdalProfile = Mdal_Kitchen_User::getDefaultInstance();
        $fids = Bll_Friend::getFriends($uid);
        if (empty($fids)) {
            $prevId = $nextId = $uid;
        }
        else {
            $prevId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'prev', $fids);
            if (empty($prevId)) {
                $prevId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'last', $fids);
            }
            $nextId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'next', $fids);
            if (empty($nextId)) {
                $nextId = $mdalProfile->getNeighberFriendUid($uid, $profileUid, 'first', $fids);
            }
        }

        $this->view->prev_uid = $prevId;
        $this->view->next_uid = $nextId;

        $this->view->commFeedTitle = $profileUidInfo['displayName'] . "さんに料理の注文が入っています！";
        //$this->view->commFeedUrl = urlencode($this->_baseUrl . "/mobile/index/getpic?CF_uid=" . $uid) . ',image/gif';
        $this->view->appId = $this->_APP_ID;
        
        $this->render();
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
        return $this->_redirect($this->_baseUrl . '/mobile/error/notfound');
    }
}