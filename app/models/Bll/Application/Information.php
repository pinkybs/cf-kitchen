<?php

class Bll_Application_Information
{
    /**
     * get app name by app id
     *
     * @param int $appId
     * @return string
     */
    public static function getAppName($appId)
    {
        $appList = array(
            16235   => 'kitchen'
            );

        if (isset($appList[$appId])) {
            return $appList[$appId];
        }

        return null;
    }
}
