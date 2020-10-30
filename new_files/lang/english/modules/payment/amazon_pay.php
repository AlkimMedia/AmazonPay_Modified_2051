<?php

$texts = [
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
'MODULE_PAYMENT_AMAZON_PAY_ALLOWED_DESC' => 'Geben Sie <b>einzeln</b> die Zonen an => welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer => werden alle Zonen erlaubt))'
];

foreach($texts as $k=>$v){
    define($k, $v);
}
