<?php

$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:32.0) android Gecko/20100101 Firefox/32.0';

function isMobile() {
    $isMobile = false;
    $devicesRegex = array(
        '/(Android|Googlebot-Mobile|BNTV400|BlackBerry)/si',
        '/(iPad|iPhone|AppleTV)/si',
        '/^Mozilla.*Windows.*(Phone|Tablet|IEMobile|HP)/si',
        '/^Mozilla.*AppleWebKit.*(Mobile|Kindle|LG|Espial|QtCarBrowser|Viera|Puffin)/si',
        '/^Mozilla.*(Tablet|hp-tablet|hpwOS|TouchPad|KFJWI|Tizen)/si',
        '/(Opera Tablet|PlayBook|RIM Tablet|PalmSourceNETTV|Opera TV|HbbTV)/si',
        '/^Mozilla.*(WebTV|Silk|GoogleTV|PalmOS|Nitro|Maple|Kylo|Escape|SmartHub|SMART\-TV|SMARTTV)/si',
        '/(HbbTV|Roku|Teleca|Obigo|Bada|Kindle Fire|InettvBrowser|CFNetwork|Playstation|Nintendo|xbox|j2me)/si',
    );
    foreach ($devicesRegex as $pattern) {
        if (preg_match($pattern, $_SERVER['HTTP_USER_AGENT'])) {
            $isMobile = true;
            break;
        }
    }
    return $isMobile;
}

$isMobile = inv_is_mobile();
var_dump($str, $isMobile, $_SERVER['HTTP_USER_AGENT']);