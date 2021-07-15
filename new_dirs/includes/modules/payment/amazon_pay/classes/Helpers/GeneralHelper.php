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

    public static function log($level, $msg, $data = null)
    {
       $fileName = $level.'_'.date('m-Y').'.log';
        $path = DIR_FS_CATALOG . 'includes/modules/payment/amazon_pay/logs/' . $fileName;

        if (file_exists($path) && filesize($path) > 4000000) {
            rename($path, $path . '.' . date('Y-m-d_H-i-s') . '.log');
        }
        if (file_exists($path)) {
            @chmod($path, 0777);
        }

        file_put_contents($path, '['.date('Y-m-d H:i:s').'] '.str_pad($_SERVER['REMOTE_ADDR'], 18, ' ', STR_PAD_RIGHT).$msg."\n", 8);
        xtc_db_perform('amazon_pay_log', [
            'time'=>'now()',
            'msg'=>$msg,
            'ip'=>$_SERVER['REMOTE_ADDR'],
            'data'=>serialize($data)
        ]);
    }

}
