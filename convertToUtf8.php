<?php

class Common
{
    public static function convertToUtf8($var)
    {
        $isObject = is_object($var);
        $isArray = is_array($var);
        if ($isObject) {
            $className = get_class($var);
            $converted = new $className();
        } elseif ($isArray) {
            $converted = array();
        } elseif (is_string($var)) {
            $converted = self::_convertCore($var);
        } else {
            $converted = $var;
        }
        if ($isObject || $isArray) {
            foreach ($var as $key => $value) {
                if (is_object($value) || is_array($value)) {
                    $utf8Val = self::convertToUtf8($value);
                } else {
                    $utf8Val = self::_convertCore($value);
                }
                if ($isObject) {
                    $converted->$key = $utf8Val;
                } elseif ($isArray) {
                    $converted[$key] = $utf8Val;
                } else {
                    $converted = $utf8Val;
                }
            }
        }
        return $converted;
    }

    private static function _convertCore($string)
    {
        $utf8Val = '';
        if (function_exists('mb_detect_encoding')) {
            $encoding = mb_detect_encoding($string, 'auto', true);
            if ($encoding == 'UTF-8') {
                $utf8Val = $string;
            } else {
                $encoding = $encoding ?: 'auto';
                $utf8Val = mb_convert_encoding($string, "UTF-8", $encoding);
            }
        } else {
            $utf8Val = iconv(false, "UTF-8//IGNORE", $string);
        }
        return $utf8Val;
    }

}