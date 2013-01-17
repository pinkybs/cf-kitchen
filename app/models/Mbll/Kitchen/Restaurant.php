<?php

/**
 * Mobile kitchen restaurant logic
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  hch  2010-1-11
 */
require_once 'Mbll/Abstract.php';

class Mbll_Kitchen_Restaurant extends Mbll_Abstract
{
	/**
	 * set restaurant in use
	 *
	 * @param integer $uid
	 * @param integer $genre
	 * @return boolean
	 */
	public function changeRestaurant($uid, $genre)
	{
		$result = false;
    	$this->_wdb->beginTransaction();
    	
    	try {
    		require_once 'Mdal/Kitchen/Restaurant.php';
    		$mdalRes = Mdal_Kitchen_Restaurant::getDefaultInstance();
            
            //get active restaurant
            $activeRes = $mdalRes->getActiveRestaurant($uid);
            $oldKitchenCount = $activeRes["estate"];
    		
    		//set all not in use first
    		$mdalRes->setAllToUnuse($uid);
    		
    		//set in use by genre
    		$mdalRes->updateRestaurant(array('in_use'=>1), $uid, $genre);
    		
    		//get active restaurant
    		$activeRes = $mdalRes->getActiveRestaurant($uid);
    		$newKitchenCount = $activeRes["estate"];
    		
            require_once 'Mdal/Kitchen/Kitchen.php';
            $mdalKitchen = Mdal_Kitchen_Kitchen::getDefaultInstance();
            
            //delete old kitchen info
            for ($i = 1; $i <= $oldKitchenCount; $i++) {
                //delete old kitchen
                $mdalKitchen->delete($uid, $i);
                
                //delete old kitchen fly set
                $mdalKitchen->deleteKitchenFlySet($uid, $i);
                
                //delete old kitchen spice
                $mdalKitchen->deleteKitchenSpice($uid, $i);
                                
                //delete old kitchen taste
                $mdalKitchen->deleteKitchenTaste($uid, $i);
            }
                        
            //insert new kitchen info
            for ($i = 1; $i <= $newKitchenCount; $i++) {
                $kitchen = array('uid' => $uid, 'kitchen_id' => $i, 'genre' => $genre);
                $mdalKitchen->insert($kitchen);
            }
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
	}
	
    /**
     * get genre list
     *
     * @return array
     */
	public function getGenreList()
	{
        require_once 'Mbll/Kitchen/Cache.php';
        $mbllKitchenCache = new Mbll_Kitchen_Cache();;
        $result = $mbllKitchenCache->getNbGenreList();
        
        return $result;
	}
	
    /**
     * get genre list
     *
     * @param integer $genreId
     * @return string
     */
    public function getGenreNameById($genreId)
    {
        $genreList = $this->getGenreList();
        $name = "";
        
        foreach ($genreList as $genre) {
            if ($genre['genre'] == $genreId) {
                $name = $genre['name'];
                break;
            }
        }
        
        return $name;
    }
	
    public function getUserRestaurantByGenre($uid, $genre)
    {
        //get user restaurant
        require_once 'Mdal/Kitchen/Restaurant.php';
        $mdalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
        $rest = $mdalRest->getOneRestaurant($uid, $genre);
        
        return $rest;
    }
    
    /**
     * get genre list
     *
     * @param integer $genreId
     * @return string
     */
    public function converGenreNum2Alp($genreId)
    {
        $genreAlp = '';

        if (1 == $genreId) {
            $genreAlp = 'y';
        } else if (2 == $genreId) {
            $genreAlp = 'r';
        }
        
        return $genreAlp;
    }
    
}