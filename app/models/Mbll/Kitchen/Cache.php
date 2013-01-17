<?php

/**
 * Mobile kitchen cache logic
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  hch  2010-1-7
 */
require_once 'Bll/Cache.php';

class Mbll_Kitchen_Cache
{
	/**
	 * mdal kitchen recipe
	 *
	 * @var Mdal_Kitchen_Recipe
	 */
    private static $_mdalRecipe = null;

    /**
     * mdal kitchen goods
     *
     * @var Mdal_Kitchen_Goods
     */
    private static $_mdalGoods = null;
    
    /**
     * mdal kitchen item
     *
     * @var Mdal_Kitchen_Item
     */
    private static $_mdalItem = null;
    
    /**
     * mdal kitchen item
     *
     * @var Mdal_Kitchen_Item
     */
    private static $_mdalGift = null;
    
    /**
     * mdal kitchen food
     *
     * @var Mdal_Kitchen_Food
     */
    private static $_mdalFood = null;
    
    /**
     * mdal kitchen restaurant
     *
     * @var Mdal_Kitchen_Restaurant
     */
    private static $_mdalRestaurant = null;
    
    private static $_mdalDaily = null;

    private static $_prefix = 'Mbll_Kitchen_Cache';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get mdal recipe
     *
     * @return Mdal_Kitchen_Recipe
     */
    public static function getMdalRecipe()
    {
        if (self::$_mdalRecipe === null) {
            require_once 'Mdal/Kitchen/Recipe.php';
            self::$_mdalRecipe = Mdal_Kitchen_Recipe::getDefaultInstance();
        }

        return self::$_mdalRecipe;
    }

    /**
     * get mdal recipe
     *
     * @return Mdal_Kitchen_Goods
     */
    public static function getMdalGoods()
    {
        if (self::$_mdalGoods === null) {
            require_once 'Mdal/Kitchen/Goods.php';
            self::$_mdalGoods = Mdal_Kitchen_Goods::getDefaultInstance();
        }

        return self::$_mdalGoods;
    }
    
    /**
     * get mdal recipe
     *
     * @return Mdal_Kitchen_Item
     */
    public static function getMdalItem()
    {
        if (self::$_mdalItem === null) {
            require_once 'Mdal/Kitchen/Item.php';
            self::$_mdalItem = Mdal_Kitchen_Item::getDefaultInstance();
        }

        return self::$_mdalItem;
    }

    /**
     * get mdal gift
     *
     * @return Mdal_Kitchen_Gift
     */
    public static function getMdalGift()
    {
        if (self::$_mdalGift === null) {
            require_once 'Mdal/Kitchen/Gift.php';
            self::$_mdalGift = Mdal_Kitchen_Gift::getDefaultInstance();
        }

        return self::$_mdalGift;
    }

    /**
     * get mdal food
     *
     * @return Mdal_Kitchen_Food
     */
    public static function getMdalFood()
    {
        if (self::$_mdalFood === null) {
            require_once 'Mdal/Kitchen/Food.php';
            self::$_mdalFood = Mdal_Kitchen_Food::getDefaultInstance();
        }

        return self::$_mdalFood;
    }
    
    /**
     * get recipe
     *
     * @param string $key
     * @return array
     */
    public static function getRecipe($recipe_id="")
    {
        $key = self::getCacheKey('getRecipe0601v1');

        if (!$result = Bll_Cache::get($key)) {

            $mdalRecipe = self::getMdalRecipe();
            $result = $mdalRecipe->getAllRecipe();

            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_MAX );
            }
        }

        if (empty($recipe_id)) {
            return $result;
        }
        else {
            foreach ($result as $item) {
                if ($item['recipe_id'] == $recipe_id) {
                    return $item;
                }
            }
        }
    }

    /**
     * clear recipe
     */
    public static function clearRecipe()
    {
        Bll_Cache::delete(self::getCacheKey('getRecipe0601v1'));
    }
    
	/**
	 * get goods
	 *
	 * @param string $good_id
	 * @return array
	 */
    public static function getGoods($good_id="")
    {
    	$key = self::getCacheKey('getGoods0601v1');

        if (!$result = Bll_Cache::get($key)) {

            $mdalGoods = self::getMdalGoods();
            $result = $mdalGoods->getAllGoods();
             
            if ($result) {
	            Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_MAX );
            }
        }

        if (empty($good_id)) {
            return $result;
        }
        else {
            foreach ($result as $item) {
                if ($item['goods_id'] == $good_id) {
                    return $item;
                }
            }
        }
    }
    
    /**
     * clear recipe
     */
    public static function clearGoods()
    {
        Bll_Cache::delete(self::getCacheKey('getGoods0601v1'));
    }
    
    /**
     * get item
     *
     * @param string $item_id
     * @return array
     */
    public static function getItem($item_id="")
    {
    	$key = self::getCacheKey('getItem0601v1');

        if (!$result = Bll_Cache::get($key)) {

            $mdalItem = self::getMdalItem();
            $result = $mdalItem->getAllItem();

            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_MAX );
            }
        }

        if (empty($item_id)) {
            return $result;
        }
        else {
            foreach ($result as $item) {
                if ($item['item_id'] == $item_id) {
                    return $item;
                }
            }
        }
    }
    
    /**
     * clear item
     */
    public static function clearItem()
    {
        Bll_Cache::delete(self::getCacheKey('getItem0601v1'));
    }
    

	public static function getFood($food_id)
    {
        $key = self::getCacheKey('getFood0601v1', $food_id);

        if (!$result = Bll_Cache::get($key)) {
            
            $mdalFood = self::getMdalFood();
            $result = $mdalFood->getAllFood();

            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_MAX );
            }
        }
        
        if (empty($food_id)) {
            return $result;
        }
        else {
            foreach ($result as $food) {
                if ($food['food_id'] == $food_id) {
                    return $food;
                }
            }
        }
    }

   	public static function clearFood($food_id)
    {
        Bll_Cache::delete(self::getCacheKey('getFood0601v1', $food_id));
    }

    /**
     * if uid_cache_value == sys_value  {return $daily['announce']}
     * else  {return 1}
     *
     *
     */
	public static function getSysAnnounce($uid)
    {
    	//change this when update system announcement.  format:yymmdd01 ---- yymmdd99
    	$sysVal = 2010060101;
    	
    	$sysKey = self::getCacheKey('sysAnnounce');
    	
    	$cachedVal = Bll_Cache::get($sysKey);
    	if (!$cachedVal || $cachedVal != $sysVal) {
            Bll_Cache::set($sysKey, $sysVal,  Bll_Cache::LIFE_TIME_MAX );
        }
    	
    	$userKey = self::getCacheKey('sysAncUser', $uid);
    	$userVal = Bll_Cache::get($userKey);
    	
    	
        $mdalDaily = self::getMdalDaily();
    	$dailyInfo = $mdalDaily->getDaily($uid);
    	
    	if (!$userVal || $userVal != $sysVal) {
    		if ($dailyInfo['announce'] == 0) {
        		$mdalDaily->updateDaily(array('announce' => 1), $uid);
    		}
    		
    		$result = 1;
    		
        	Bll_Cache::set($userKey, $sysVal,  Bll_Cache::LIFE_TIME_MAX );
    	}
    	else {
    		$result = $dailyInfo['announce'];
    	}
        
    	return $result;
    }
    
    public static function getMdalDaily()
    {
        if (self::$_mdalDaily === null) {
            require_once 'Mdal/Kitchen/Daily.php';
            self::$_mdalDaily = Mdal_Kitchen_Daily::getDefaultInstance();
        }

        return self::$_mdalDaily;
    }
    
    /**************** add by shenhw *****************/
    /**
     * get mdal restaurant
     *
     * @return Mdal_Ship_Rank
     */
    public static function getMdalRestaurant()
    {
        if (self::$_mdalRestaurant === null) {
            require_once 'Mdal/Kitchen/Restaurant.php';
            self::$_mdalRestaurant = Mdal_Kitchen_Restaurant::getDefaultInstance();
        }

        return self::$_mdalRestaurant;
    }


    /**
     * get recipe
     *
     * @param string $key
     * @return array
     */
    public static function getNbGenreList()
    {
        $key = self::getCacheKey('getNbGenreList0601v1');

        if (!$result = Bll_Cache::get($key)) {

            $mdalRestaurant = self::getMdalRestaurant();
            $result = $mdalRestaurant->getNbGenreList();

            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_MAX );
            }
        }

        return $result;
    }

    /**
     * clear place
     */
    public static function clearNbGenreList()
    {
        Bll_Cache::delete(self::getCacheKey('getNbGenreList0601v1'));
    }
    
    /**
     * get recipe
     *
     * @param string $key
     * @return array
     */
    public static function getGift($giftId = "", $type = 1)
    {
        if (1 == $type) {
            $key = self::getCacheKey('getGift');
        } else if (2 == $type) {
            $key = self::getCacheKey('getLevelupGift');
        } else if (3 == $type) {
            $key = self::getCacheKey('getVisitGift');
        } else if (4 == $type) {
            $key = self::getCacheKey('getFreeGift');
        } else if (5 == $type) {
            $key = self::getCacheKey('getCampainGift2');
        }
            
        if (!$result = Bll_Cache::get($key)) {

            $mdalGift = self::getMdalGift();
            $result = $mdalGift->listGift(0, 500, $type);

            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_MAX );
            }
        }

        if (empty($giftId)) {
            return $result;
        }
        else {
            foreach ($result as $item) {
                if ($item['gift_id'] == $giftId) {
                    return $item;
                }
            }
        }
    }

    /**
     * clear recipe
     */
    public static function clearGift($type = 1)
    {
        if (1 == $type) {
            Bll_Cache::delete(self::getCacheKey('getGift'));
        } else if (2 == $type) {
            Bll_Cache::delete(self::getCacheKey('getLevelupGift'));
        } else if (3 == $type) {
            Bll_Cache::delete(self::getCacheKey('getVisitGift'));
        } else if (4 == $type) {
            Bll_Cache::delete(self::getCacheKey('getFreeGift'));
        } else if (5 == $type) {
            Bll_Cache::delete(self::getCacheKey('getCampainGift2'));
        }
    }
    
}