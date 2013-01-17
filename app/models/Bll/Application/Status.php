<?php

class Bll_Application_Status
{
    const PC_DEFAULT = 1;
    const PC_AJAX = 2;
    const MOBILE = 3;
    
    public static function check($type, $uid = 0)
    {
        $stop = (defined('APP_STATUS') && APP_STATUS == 0);
        $dev = (defined('APP_STATUS_DEV') && APP_STATUS_DEV == 1);
        
        if ($stop && $dev && $uid > 0) {
             $developers = array(
                '22677405', //communityfactory.com
                '23815088', //
                '23815089', //
                '23815090', //
                '23815091', //
                '23815092', //
                '23815093', //
                '23815094', //
                '23815095', //
                '23815096', //
                '23815097', //
                '23815098', //
                '23815099', //
                '23815100', //
                '23815101', //
                '23815102', //
                '23815103', //
                '23815104', //
                '23815105', //
                '23815106', //
                '23815107', //
                //'21224066', //
            );
            if (in_array($uid, $developers)) {
                $stop = false;
            }
        }
        
        if ($stop) {
            if ($type == Bll_Application_Status::PC_DEFAULT) {
                echo self::getMsg4PCDefault();
            } else if ($type == Bll_Application_Status::MOBILE) {
                if (Zend_Registry::isRegistered('ua')) {
                    $ua = Zend_Registry::get('ua');
                    $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" dir="ltr"><head>';
                    //docomo
                    if ($ua == 1) {
                        $content .= '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=Shift_JIS" />';
                    } else {
                        $content .= '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />';
                    }
                    info_log($uid.'_'.time(),'mantanceuid');
                    $content .= '</head><body>' . self::getMsg4Mobile() . '</body></html>';
                    if ($ua == 1) {
                        header('Content-type: application/xhtml+xml; charset=Shift_JIS');
                        $content = mb_convert_encoding($content, 'SJIS-win', 'UTF-8');
                    }
                    
                    echo $content;
                }
            }
            
            exit;
        }
    }
    
    public static function checkOnCallback($view, &$message, $uid = 0)
    {
        $stop = (defined('APP_STATUS') && APP_STATUS == 0);
        $dev = (defined('APP_STATUS_DEV') && APP_STATUS_DEV == 1);
        
        if ($stop && $dev && $uid > 0) {
             $developers = array(
                '22677405', //communityfactory.com
                '23815088', //
                '23815089', //
                '23815090', //
                '23815091', //
                '23815092', //
                '23815093', //
                '23815094', //
                '23815095', //
                '23815096', //
                '23815097', //
                '23815098', //
                '23815099', //
                '23815100', //
                '23815101', //
                '23815102', //
                '23815103', //
                '23815104', //
                '23815105', //
                '23815106', //
                '23815107', //
                '21224066', //
            );
            if (in_array($uid, $developers)) {
                $stop = false;
            }
        }
        
        $message = '';
        
        if ($stop) {
            if ($view == 'canvas') {
                $message = self::getMsg4PCCanvas();
            } else {
                $message = self::getMsg4PCGadget();
            }
        }
        
        return $stop;
    }
    
    public static function getMsg4PCDefault()
    {
        $file = CONFIG_DIR . '/status/pc.default.msg';
        return @file_get_contents($file);
    }
    
    public static function getMsg4PCGadget()
    {
        $file = CONFIG_DIR . '/status/pc.gadget.msg';
        return @file_get_contents($file);
    }
    
    public static function getMsg4PCCanvas()
    {
        $file = CONFIG_DIR . '/status/pc.canvas.msg';
        return @file_get_contents($file);
    }
    
    public static function getMsg4Mobile()
    {
        $file = CONFIG_DIR . '/status/mobile.msg';
        return @file_get_contents($file);
    }
}
