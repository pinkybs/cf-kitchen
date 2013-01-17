<?php
/**
 * Mobile kitchen activity logic
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-2-6
 */


class Mbll_Kitchen_Activity
{
	/**
	 * kitchen activity
	 *
	 * @param string $tpId = template id
	 * @param string $param = displayName or targetName
	 * @param string $paramtwo = image path
	 * @return string
	 */
	public static function getActivity($tpId, $param, $paramtwo='', $paramthree = '')
	{
		switch ($tpId) {
			case 1 :
				$template = $param . "のお店のハエを撃退しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/remove.gif';
				break;
			case 2 :
				$template = $param . "のお店にハエを飛ばしました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/fly.gif';
				break;
			case 3 :
				$template = $param . "のお店で味付けをしました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/spice.gif';
				break;
			case 4 :
				$template = $param . "のお店の味を盗みました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/meal/40x40/' . substr($paramtwo,0,1) . '/' . substr($paramtwo,1) . '.gif';
				break;
			case 5 :
				$template = $param . "のお店の料理を味見しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/meal/40x40/' . substr($paramtwo,0,1) . '/' . substr($paramtwo,1) . '.gif';
				break;
			case 6 :
				$template = "洋食屋" . $param . "が新装オープン!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/estate/40x40/y/01.gif';
				break;
			case 7 :
				$template = $param . "の調理を開始しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/meal/40x40/' . substr($paramtwo,0,1) . '/' . substr($paramtwo,1) . '.gif';
				break;
			case 8 :
				$template = $param . "の開発に成功しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/meal/40x40/' . substr($paramtwo,0,1) . '/' . substr($paramtwo,1) . '.gif';
				break;
			case 9 :
				$template = $param . "を購入しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/' . $paramtwo . '.gif';
				break;
			case 10 :
				$template = "デイリーギフトを開封しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/gift/40x40/' . $paramtwo;
				break;
			case 11 :
				$template = "ごほうびギフトを開封しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/gift/40x40/' . $paramtwo;
				break;
			case 12 :
				$template = $param . "を使用しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/yorozu/40x40/' . $paramtwo . '.gif';
				break;
			case 13 :
				$template = "お店の模様替えをしました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/zakka/40x40/' . $paramtwo . '.gif';
				break;
			case 14 :
				$template = $param . "のお店にざっかを飾りました｡";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/zakka/40x40/' . $paramtwo . '.gif';
				break;
			case 15 :
				$template = $param . "にギフトを贈りました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/gift/40x40/' . $paramtwo;
				break;
			case 16 :
				$template = $param . "のギフトを開封しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/gift/40x40/' . $paramtwo;
				break;
            case 17 :
                $template =  "マイミク" . $param . "人に" . $paramthree . "を贈りました!";
                $template .= '|' . $paramtwo;
                break;
		  default:;
		}
        
        return $template;
	}
	
	public static function getMiniFeed($tpId, $param, $paramtwo='', $paramthree = '')
	{
		switch ($tpId) {
			case 1 :
				$template = $param . "のお店のハエを撃退しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/remove.gif';
				break;
			case 2 :
				$template = $param . "のお店にハエを飛ばしました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/fly.gif';
				break;
			case 3 :
				$template = $param . "のお店で味付けをしました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/spice.gif';
				break;
			case 4 :
				$template = $param . "のお店の味を盗みました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/meal/40x40/' . substr($paramtwo,0,1) . '/' . substr($paramtwo,1) . '.gif';
				break;
			case 5 :
				$template = $param . "のお店の料理を味見しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/meal/40x40/' . substr($paramtwo,0,1) . '/' . substr($paramtwo,1) . '.gif';
				break;
			case 6 :
				$template = "洋食屋" . $param . "が新装オープン!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/estate/40x40/y/01.gif';
				break;
			case 7 :
				$template = $param . "の調理を開始しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/meal/40x40/' . substr($paramtwo,0,1) . '/' . substr($paramtwo,1) . '.gif';
				break;
			case 8 :
				$template = $param . "の開発に成功しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/meal/40x40/' . substr($paramtwo,0,1) . '/' . substr($paramtwo,1) . '.gif';
				break;
			case 9 :
				$template = $param . "を購入しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/' . $paramtwo . '.gif';
				break;
			case 10 :
				$template = "デイリーギフトを開封しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/gift/40x40/' . $paramtwo;
				break;
			case 11 :
				$template = "ごほうびギフトを開封しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/gift/40x40/' . $paramtwo;
				break;
			case 12 :
				$template = $param . "を使用しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/yorozu/40x40/' . $paramtwo . '.gif';
				break;
			case 13 :
				$template = "お店の模様替えをしました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/zakka/40x40/' . $paramtwo . '.gif';
				break;
			case 14 :
				$template = $param . "のお店にざっかを飾りました｡";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/zakka/40x40/' . $paramtwo . '.gif';
				break;
			case 15 :
				$template = $param . "にギフトを贈りました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/gift/40x40/' . $paramtwo;
				break;
			case 16 :
				$template = $param . "のギフトを開封しました!";
				$template .= '|' . Zend_Registry::get('static') . '/apps/kitchen/mobile/img/gift/40x40/' . $paramtwo;
				break;
            case 17 :
                $template =  "マイミク" . $param . "人に" . $paramthree . "を贈りました!";
                $template .= '|' . $paramtwo;
                break;
                
            case 99 :
            	$template = $param . 'になりました!';
            	$template .= '|' . 'nakano';
            	break;
		  default:;
		}
        
        return $template;
	}
	
}