<?php
if (!empty($_SESSION['checkout_with_incomplete_account_started'])) {
    unset($_SESSION['checkout_with_incomplete_account_started']);
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}