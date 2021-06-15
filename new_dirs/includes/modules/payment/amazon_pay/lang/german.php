/**
 * @package AlkimMediaAmazonPayCV2Global
 */
<?php

$texts = [
    //default payment module texts
    'MODULE_PAYMENT_AMAZON_PAY_TEXT_DESCRIPTION' => 'Amazon Pay',
    'MODULE_PAYMENT_AMAZON_PAY_TEXT_TITLE' => 'Amazon Pay',
    'MODULE_PAYMENT_AMAZON_PAY_TEXT_INFO' => '',
    'MODULE_PAYMENT_AMAZON_PAY_STATUS_TITLE' => 'Amazon Pay aktivieren',
    'MODULE_PAYMENT_AMAZON_PAY_STATUS_DESC' => 'M&ouml;chten Sie Zahlungen per Amazon Pay akzeptieren?',
    'MODULE_PAYMENT_AMAZON_PAY_SORT_ORDER_TITLE' => 'Anzeigereihenfolge',
    'MODULE_PAYMENT_AMAZON_PAY_SORT_ORDER_DESC' => 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.',
    'MODULE_PAYMENT_AMAZON_PAY_ZONE_TITLE' => 'Zahlungszone',
    'MODULE_PAYMENT_AMAZON_PAY_ZONE_DESC' => 'Wenn eine Zone ausgew&auml;hlt ist => gilt die Zahlungsmethode nur f&uuml;r diese Zone.',
    'MODULE_PAYMENT_AMAZON_PAY_ALLOWED_TITLE' => 'Erlaubte Zonen',
    'MODULE_PAYMENT_AMAZON_PAY_ALLOWED_DESC' => 'Geben Sie <b>einzeln</b> die Zonen an => welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer => werden alle Zonen erlaubt))',

    //config texts
    'APC_MERCHANT_ID_TITLE'=>'Amazon H&auml;ndler ID',
    'APC_CLIENT_ID_TITLE'=>'Amazon Store ID',
    'APC_PUBLIC_KEY_ID_TITLE'=>'Public Key ID',
    'APC_PUBLIC_KEY_TITLE'=>'Mein Public Key',
    'APC_IPN_URL_TITLE'=>'URL f&uuml;r Amazon IPN',
    'APC_CRON_STATUS_TITLE'=>'Cronjob aktivieren',
    'AMZ_JS_ORIGIN_TITLE'=>'Erlaubte Javascript Urspr&uuml;nge',
    'APC_IS_LIVE_TITLE'=>'Live/Sandbox',
    'APC_IS_DEBUG_TITLE'=>'Debug-Modus (Buttons verstecken)',
    'APC_CHECKOUT_BUTTON_COLOR_TITLE'=>'Farbe des Amazon-Checkout-Buttons',
    'APC_LOGIN_BUTTON_COLOR_TITLE'=>'Farbe des Amazon-Login-Buttons',
    'APC_ORDER_STATUS_AUTHORIZED_TITLE'=>'Status f&uuml;r autorisierte Bestellungen',
    'APC_ORDER_STATUS_DECLINED_TITLE'=>'Status f&uuml;r Bestellungen mit abgelehnter Zahlung',
    'APC_ORDER_STATUS_CAPTURED_TITLE'=>'Status f&uuml;r Bestellungen mit eingezogener Zahlung',
    'APC_CAPTURE_MODE_TITLE'=>'Art des Zahlungseinzugs',
    'APC_ORDER_STATUS_SHIPPED_TITLE'=>'Status f&uuml;r versendete Bestellungen',

    'HEADING_CREDENTIALS_TITLE'=>'Amazon Pay Konto',
    'HEADING_GENERAL_TITLE'=>'Allgemeine Einstellungen',
    'HEADING_STYLE_TITLE'=>'Design Einstellungen',

    //shop
    'TEXT_AMAZON_PAY_ERROR'=>'Ihre Zahlung war nicht erfolgreich. Bitte verwenden Sie eine andere Zahlungsart',
    'TEXT_AMAZON_PAY_PENDING'=>'Ihre Zahlung mit Amazon Pay ist derzeit noch in Pr&uuml;fung. Bitte beachten Sie, dass wir uns mit Ihnen in K&uuml;rze per Email in Verbindung setzen werden, falls noch Unklarheiten bestehen sollten.',
    'TEXT_AMAZON_PAY_ACCOUNT_EDIT_INFORMATION'=>'Um den Checkout zu starten, ben&ouml;tigen wir noch folgende Informationen von Ihnen',
    'TEXT_AMAZON_PAY_ADDRESS_INFORMATION'=>'Bitte geben Sie Ihre Versandadresse ein'
];

foreach($texts as $k=>$v){
    define($k, $v);
}
