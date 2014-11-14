<?php

function convertToUtf8($array) {
    $converted = array();
    foreach($array as $key => $value) {
        if(is_array($value)) {
            $utf8Val = $this->convertToUtf8($value);
        } else {
            if (function_exists('mb_detect_encoding')) {
                $encoding = mb_detect_encoding($value, 'auto', true);
                if ($encoding == 'UTF-8') {
                    $utf8Val = $value;
                } else {
                    $encoding = $encoding ?: 'auto';
                    $utf8Val = mb_convert_encoding($value, "UTF-8", $encoding);
                }
            } else {
                $utf8Val = iconv(false, "UTF-8//IGNORE", $value);
            }
        }
        $converted[$key] = $utf8Val;
    }
    return $converted;
}