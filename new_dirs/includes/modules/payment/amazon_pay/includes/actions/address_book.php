<?php
if ($_SESSION['checkout_with_incomplete_account_started']) {
    if (!$_SESSION['customer_default_address_id']) {
        $q  = "SELECT * FROM " . TABLE_ADDRESS_BOOK . " WHERE customers_id = " . (int)$_SESSION['customer_id'] . " AND entry_street_address != '' AND entry_street_address IS NOT NULL";
        $rs = xtc_db_query($q);
        if ($r = xtc_db_fetch_array($rs)) {
            xtc_db_perform(TABLE_CUSTOMERS, ['customers_default_address_id' => (int)$r['address_book_id']], 'update', 'customers_id = ' . (int)$_SESSION['customer_id']);
            $_SESSION['customer_default_address_id'] = (int)$r['address_book_id'];
        }
    }
    unset($_SESSION['checkout_with_incomplete_account_started']);
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}