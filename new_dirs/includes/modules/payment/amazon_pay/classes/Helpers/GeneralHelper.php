<?php

namespace AlkimAmazonPay;

class GeneralHelper
{
    public static function autoDecode($str)
    {
        if (strtolower($_SESSION["language_charset"]) == 'utf-8') {
            return self::autoEncode($str);
        } elseif (self::isUTF8($str)) {
            return utf8_decode($str);
        }

        return $str;
    }

    public static function autoEncode($str)
    {
        if (self::isUTF8($str)) {
            return $str;
        }

        return utf8_encode($str);
    }

    public static function isUTF8($str)
    {
        if ($str === mb_convert_encoding(mb_convert_encoding($str, "UTF-32", "UTF-8"), "UTF-8", "UTF-32")) {
            return true;
        } else {
            return false;
        }
    }

}