<?php
use AlkimAmazonPay\ConfigHelper;require __DIR__ . '/includes/application_top.php';
require_once DIR_FS_CATALOG . 'includes/modules/payment/amazon_pay/amazon_pay.php';
$configHelper = new ConfigHelper();
if(isset($_POST['action'])){
    $action = $_POST['action'];
}elseif(isset($_GET['action'])){
    $action = $_GET['action'];
}else{
    $action = null;
}

if ($action) {
    switch ($action) {
        case 'save_amazon_pay_configuration':
            foreach (array_map('trim', $_POST["configuration"]) as $k => $v) {
                if ($configHelper->getConfigurationValue($k) === null) {
                    $configHelper->addConfigurationValue($k, $v);
                } else {
                    $configHelper->updateConfigurationValue($k, $v);
                }
            }
            xtc_redirect(xtc_href_link('amazon_pay_configuration.php'));
            break;
        case 'reset_key':
            $configHelper->resetKey();
            xtc_redirect(xtc_href_link('amazon_pay_configuration.php'));
            break;
    }
}
require (DIR_WS_INCLUDES.'head.php');
?>
    <style>
        .alert{
            padding:5px;
            margin:5px 0;
        }
        .alert-error{
            background: #f59090;
            border:2px solid red;
        }
        
        .amz-heading{
            background:#680f0e;
            color:#fff;
            padding:10px;
            font-size:1.2em;
            font-weight:bold;
        }

        #amz-config-table td{
            border:none !important;
        }

        #amz-config-table tr:nth-child(2n + 1) td{
            background:#f4f4f4;
        }
    </style>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<table border="0" width="100%" cellspacing="2" cellpadding="2">
    <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
            <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
                <!-- left_navigation //-->
                <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
                <!-- left_navigation_eof //-->
            </table>
        </td>
        <!-- body_text //-->
        <td valign="top" class="amzConfWr">
            <?php
            if(!is_writable($configHelper->getPrivateKeyPath()) || !is_writable($configHelper->getPublicKeyPath()) || !is_writable(dirname($configHelper->getPublicKeyPath()))){
                ?>
                    <div class="alert alert-error main">
                        Die Schreibrechte f&uuml;r das Schl&uuml;sselverzeichnis unter <?php echo dirname($configHelper->getPublicKeyPath());?> sind nicht ausreichend. Bitte setzen Sie die Rechte so, dass der Webserver auf das Verzeichnis und die beinhalteten Dateien vollen Zugriff hat.
                    </div>
                <?php
            }

            if(!is_writable(DIR_FS_CATALOG.'includes/modules/payment/amazon_pay/logs')){
                ?>
                <div class="alert alert-error main">
                    Die Schreibrechte f&uuml;r das Logverzeichnis unter includes/modules/payment/amazon_pay/logs sind nicht ausreichend. Bitte setzen Sie die Rechte so, dass der Webserver auf das Verzeichnis und die beinhalteten Dateien vollen Zugriff hat.
                </div>
                <?php
            }

            ?>
            <?php echo xtc_draw_form('configuration', 'amazon_pay_configuration.php'); ?>
                <input type="hidden" name="action" value="save_amazon_pay_configuration"/>
                <table width="100%" border="0" cellspacing="0" cellpadding="8" class="configurationTable main" id="amz-config-table">
                    <?php
                    $configHelper = new ConfigHelper();
                    foreach ($configHelper->getConfigurationFields() as $field => $fieldInfo) {
                        if($fieldInfo['type'] === ConfigHelper::FIELD_TYPE_HEADING){
                            ?>
                            <tr>
                                <td style="padding:0;" colspan="3"><div class="amz-heading"><?php echo constant($field . '_TITLE'); ?></div></td>
                            </tr>
                            <?php
                        }else{
                            ?>
                            <tr>
                                <td class="dataTableContent"><b><?php echo constant($field . '_TITLE'); ?></b></td>
                                <td class="dataTableContent"><?php
                                    switch ($fieldInfo['type']) {
                                        case ConfigHelper::FIELD_TYPE_STRING:
                                            echo renderInputField($field);
                                            break;
                                        case ConfigHelper::FIELD_TYPE_SELECT:
                                            echo renderSelectField($field, $fieldInfo['options']);
                                            break;
                                        case ConfigHelper::FIELD_TYPE_BOOL:
                                            echo renderSelectField($field, [['text' => 'ja', 'id' => 'True'], ['text' => 'nein', 'id' => 'False']]);
                                            break;
                                        case ConfigHelper::FIELD_TYPE_READ_ONLY:
                                            echo $fieldInfo['value'];
                                            break;
                                        case ConfigHelper::FIELD_TYPE_STATUS:
                                            echo renderStatusSelectField($field, $fieldInfo['options']);
                                            break;
                                    }
                                    ?></td>
                                <td class="dataTableContent"><?php echo defined($field . '_DESC') ? constant($field . '_DESC') : ''; ?></td>
                            </tr>
                            <?php
                        }

                    }
                    /*
                    ?>
                    <tr>
                        <td><b><?php echo MODULE_PAYMENT_AM_APA_ORDER_STATUS_OK_TITLE; ?></b></td>
                        <td><?php echo xtc_cfg_pull_down_order_statuses(MODULE_PAYMENT_AM_APA_ORDER_STATUS_OK, 'MODULE_PAYMENT_AM_APA_ORDER_STATUS_OK'); ?></td>
                        <td><?php echo MODULE_PAYMENT_AM_APA_ORDER_STATUS_OK_DESC; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo AMZ_AUTHORIZATION_CONFIG_TITLE; ?></b></td>
                        <td><?php echo renderAuthorizationSelect('configuration[AMZ_AUTHORIZATION_MODE]', AMZ_AUTHORIZATION_MODE); ?></td>
                        <td><?php echo AMZ_AUTHORIZATION_CONFIG_DESC; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo AMZ_CAPTURE_CONFIG_TITLE; ?></b></td>
                        <td><?php echo renderCaptureSelect('configuration[AMZ_CAPTURE_MODE]', AMZ_CAPTURE_MODE); ?></td>
                        <td><?php echo AMZ_CAPTURE_CONFIG_DESC; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo AMZ_SHIPPED_STATUS_TITLE; ?></b></td>
                        <td><?php echo xtc_cfg_pull_down_order_statuses(AMZ_SHIPPED_STATUS, 'AMZ_SHIPPED_STATUS'); ?></td>
                        <td><?php echo AMZ_SHIPPED_STATUS_DESC; ?></td>
                    </tr>
                    */
                    ?>
                </table>
                <button type="submit" class="amzButton" onclick="this.blur();">Speichern</button>
            </form>

        </td>
        <!-- body_text_eof //-->
    </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php
if ($_GET["mode"] != 'excludeProducts') {
    require(DIR_WS_INCLUDES . 'footer.php');
} else {
    echo '</div>';
}
?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php');

function renderAuthorizationSelect($name, $value, $params = '')
{

    $arr = [['id' => 'fast_auth', 'text' => AMZ_FAST_AUTH], ['id' => 'after_checkout', 'text' => AMZ_AUTH_AFTER_CHECKOUT], ['id' => 'manually', 'text' => AMZ_MANUALLY]];

    return xtc_draw_pull_down_menu($name, $arr, $value, $params);

}

function renderCaptureSelect($name, $value, $params = '')
{

    $arr = [['id' => 'after_auth', 'text' => AMZ_CAPTURE_AFTER_AUTH], ['id' => 'after_shipping', 'text' => AMZ_AFTER_SHIPPING], ['id' => 'manually', 'text' => AMZ_MANUALLY]];

    return xtc_draw_pull_down_menu($name, $arr, $value, $params);

}

function renderInputField($key)
{
    return '<input name="configuration[' . $key . ']" value="' . constant($key) . '" style="width:350px;"/>';
}

function renderSelectField($key, $values, $params = '')
{
    return xtc_draw_pull_down_menu('configuration[' . $key . ']', $values, constant($key), $params);
}

function renderStatusSelectField($key, $params = '')
{
    $values = [['id' => '-1', 'text' => '']];
    $q = xtc_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . $_SESSION['languages_id'] . "' order by orders_status_name");
    while ($r = xtc_db_fetch_array($q)) {
        $values[] = ['id' => $r['orders_status_id'], 'text' => $r['orders_status_name']];
    }
    return renderSelectField($key, $values, $params);
}
