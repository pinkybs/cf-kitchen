<?php

require_once 'Mdal/Abstract.php';

class Mdal_Kitchen_Recipe extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_recipe = 'res_user_recipe';

    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Kitchen_Recipe
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * insert into kitchen
     *
     * @param array $info
     * @return integer
     */
    public function insert($info)
    {
        $this->_wdb->insert($this->table_recipe, $info);
    }

	/**
	 * update
	 *
	 * @param integer $uid
	 * @param integer $recipe_id
	 * @param array $info
	 */
    public function update($uid, $recipe_id, $info)
    {
    	$where = array($this->_wdb->quoteInto('uid=?', $uid),
                       $this->_wdb->quoteInto('recipe_id=?', $recipe_id));
        $this->_wdb->update($this->table_recipe, $info, $where);
    }
    
    /**
     * get all recipe
     *
     * @return unknown
     */
    public function getAllRecipe()
    {
        $sql = "SELECT *,part1_time+part2_time+part3_time AS total_time,LEFT(recipe_id,1) AS f,RIGHT(recipe_id,2) AS n FROM res_nb_recipe";
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get use recipe
     *
     * @param integer $uid
     * @return array
     */
    public function getUserRecipe($uid, $pageIndex, $pageSize, $orderType, $order="DESC")
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $sql = "SELECT * FROM
                (SELECT nb.recipe_id,nb.recipe_name,CAST(nb.point+c.lucky_flag*0.5*nb.point AS SIGNED) as point,part1_time+part2_time+part3_time AS total_time,LEFT(nb.recipe_id,1) AS f,RIGHT(nb.recipe_id,2) AS n
                FROM res_user_restaurant AS r,res_user_recipe AS c,res_nb_recipe AS nb
                WHERE  c.recipe_id=nb.recipe_id AND r.uid=c.uid AND r.genre=c.genre AND r.in_use=1 AND r.uid=:uid) AS x
                ORDER BY $orderType $order LIMIT $start, $pageSize";
        
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    /**
     * get current restaurant recipe count
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserRecipeCount($uid)
    {
        $sql = "SELECT recipe_count FROM res_user_restaurant WHERE in_use=1 AND uid=:uid";
        
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    /**
     * check if ha recipe
     *
     * @param integer $uid
     * @param integer $recipe_id
     * @return boolean
     */
    public function hasRecipe($uid, $recipe_id)
    {
    	$sql = "SELECT COUNT(1) FROM res_user_recipe WHERE uid=:uid AND recipe_id=:recipe_id";
    	
    	$result = $this->_rdb->fetchOne($sql, array('uid' => $uid, 'recipe_id' => $recipe_id));
    	
    	return $result > 0 ? true : false;
    }

    /***************** add by shenhw*********************
    
    /**
     * get use recipe by genre
     *
     * @param integer $genre :洋食 2:リストランテ 3:日本料理 4:中華料理
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @param string $orderType
     * @param string $order
     * @return array
     */
    public function getUserRecipeByGenre($uid, $genre, $pageIndex, $pageSize = 9, $orderType = 'allow_level', $order="ASC")
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $sql = "SELECT rnr.*, if(rur.recipe_id is null, 0, 1) AS isdisplay, LEFT(rnr.recipe_id,1) AS f, RIGHT(rnr.recipe_id,2) AS n
                FROM res_nb_recipe rnr
                LEFT JOIN res_user_recipe rur
                ON rur.uid = :uid
                AND rnr.recipe_id = rur.recipe_id AND rnr.genre = rur.genre
                WHERE rnr.genre = :genre
                ORDER BY rnr.$orderType $order
                LIMIT $start, $pageSize";
        
        return $this->_rdb->fetchAll($sql, array('uid' => $uid, 'genre' => $genre));
    }
    
    /**
     * get use recipe count by genre
     *
     * @param integer $genre :洋食 2:リストランテ 3:日本料理 4:中華料理
     * @param integer $uid
     * @return integer
     */
    public function getUserRecipeCountByGenre($uid, $genre)
    {
        $sql = "SELECT count(1) FROM $this->table_recipe WHERE uid = :uid AND genre = :genre";
        
        return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'genre' => $genre));
    }
    
    /**
     * get recipe by id
     *
     * @return array
     */
    public function getRecipeById($recipeId)
    {
        $sql = "SELECT *, LEFT(recipe_id,1) AS f, RIGHT(recipe_id,2) AS n FROM res_nb_recipe WHERE recipe_id = :recipe_id";
        return $this->_rdb->fetchRow($sql, array('recipe_id' => $recipeId));
    }
    
    /**
     * get user don't have recipe
     *
     * @param integer $uid
     * @param integer $genre
     * @return array
     */
    public function getUserRecipeNot($uid, $genre)
    {
    	$sql = "SELECT recipe_id FROM res_nb_recipe WHERE recipe_id NOT IN
				(SELECT recipe_id FROM res_user_recipe WHERE genre=:genre AND uid=:uid) AND genre=:genre ORDER BY allow_level";
    	
    	return $this->_rdb->fetchAll($sql, array('genre' => $genre, 'uid'=>$uid));
    }
    
    /**
     * get recipe by id
     *
     * @return array
     */
    public function getUserRecipeById($uid, $recipeId)
    {
        $sql = "SELECT *, LEFT(recipe_id,1) AS f, RIGHT(recipe_id,2) AS n
                FROM res_user_recipe
                WHERE recipe_id = :recipe_id AND uid = :uid";
        return $this->_rdb->fetchRow($sql, array('recipe_id' => $recipeId, 'uid' => $uid));
    }
    
    
    public function getEasyUserRecipeByGenre($uid, $genre)
    {
    	$sql = "SELECT recipe_id,lucky_flag FROM res_user_recipe WHERE genre=:genre AND uid=:uid";
    	return $this->_rdb->fetchAll($sql, array('genre' => $genre, 'uid'=>$uid));
    }
    
    public function getGachaBasedRecipes($genre)
    {
    	$sql = "select recipe_id from res_nb_recipe where (food1 in (select food_id from res_shop_food where `type`=0) 
				OR food2 in (select food_id from res_shop_food where `type`=0) 
				OR food3 in (select food_id from res_shop_food where `type`=0))
				and res_nb_recipe.genre=:genre;";
    	return $this->_rdb->fetchCol($sql, array('genre' => $genre));
    }
    
    public function hasTargetRecipe($profileUid, $kitchenId)
    {
    	$sql = "SELECT COUNT(1) FROM res_user_recipe WHERE uid=:uid AND recipe_id=:recipe_id";
    	
    	$result = $this->_rdb->fetchOne($sql, array('uid' => $uid, 'recipe_id' => $recipe_id));
    	
    	return $result > 0 ? true : false;
    }
}