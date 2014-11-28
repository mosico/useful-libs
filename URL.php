<?php
/**
 * Created by PhpStorm.
 * User: mmo
 * Date: 11/28/2014
 * Time: 2:37 PM
 */

class URL
{
    public static function encodeSiteMapUrl($url)
    {
        $encodeUrl = '';
        $url = trim($url);
        $length = strlen($url);
        if ($length < 0) return $encodeUrl;

        for ($i = 0; $i < $length; $i++) {
            $char = $url{$i};
            $charOrd = ord($char);
            if ($charOrd > 32 && $charOrd < 127) {
                $encodeUrl .= $char;
            } else {
                $encodeUrl .= urlencode($char);
            }
        }

        $encodeUrl = htmlspecialchars($encodeUrl, ENT_QUOTES | ENT_XHTML, 'UTF-8');
        return $encodeUrl;
    }
}

//encode ', ", <, > to utf-8 entity
$url = 'http://www.example.com/ümlat.php&q=name&m="quote';
$encodeUrl = URL::encodeSiteMapUrl($url);