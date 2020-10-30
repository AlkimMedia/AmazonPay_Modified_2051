<?php
include __DIR__ . '/../amazon_pay.php';
$orderId = (int)$_GET['oID'];


$originalTotal = 0;
$capturedTotal = 0;
$hasOpenCharge = false;
$chargePermissionId = null;
$q = "SELECT * FROM amazon_pay_transactions WHERE order_id = ".$orderId;
$rs = xtc_db_query($q);
?>
<style>
    #amazon-pay-panel{
        margin:10px 0;
        border:1px solid #999;
        background:#f4f4f4;
        padding:8px;
    }

    #amazon-pay-panel h2{
        margin:0;
        padding:0 0 5px 0;
        font-size: 1.4em;
    }

    #amazon-pay-panel h3{
        margin:0;
        padding: 5px 0;
        font-size: 1.2em;
    }
</style>
<div id="amazon-pay-panel">
<h3>Transaktionen</h3>
<table class="main" cellpadding="4">
    <tr>
        <th style="text-align:left;">Type</th>
        <th style="text-align:left;">ID</th>
        <th style="text-align:left;">Status</th>
        <th style="text-align:left;">Amount</th>
        <th style="text-align:left;">Captured</th>
        <th style="text-align:left;">Refunded</th>
        <th style="text-align:left;">Actions</th>
    </tr>
    <?php
while($r = xtc_db_fetch_array($rs)){
    $amazonPayHelper = new \AlkimAmazonPay\AmazonPayHelper();
    $transactionHelper = new \AlkimAmazonPay\Helpers\TransactionHelper();
    $apiClient = $amazonPayHelper->getClient();
    $transaction = new \AlkimAmazonPay\Models\Transaction($r);
    if($transaction->type === 'Refund' && $transaction->status === \AmazonPayExtendedSdk\Struct\StatusDetails::REFUND_INITIATED){
        $refund = $apiClient->getRefund($transaction->reference);
        $transactionHelper->updateRefund($refund);
        $transaction = $transactionHelper->getTransaction($refund->getRefundId());
    }

    if($transaction->type === 'Charge' && $transaction->status === \AmazonPayExtendedSdk\Struct\StatusDetails::AUTHORIZATION_INITIATED){
        $charge = $apiClient->getCharge($transaction->reference);
        $transactionHelper->updateCharge($charge);
        $transaction = $transactionHelper->getTransaction($charge->getChargeId());
    }

    if($transaction->type === 'ChargePermission'){
        $originalTotal = $transaction->charge_amount;
        $chargePermissionId = $transaction->reference;
    }

    if($transaction->type === 'Charge'){
        $capturedTotal += $transaction->captured_amount;
        if($transaction->status === \AmazonPayExtendedSdk\Struct\StatusDetails::OPEN || $transaction->status === \AmazonPayExtendedSdk\Struct\StatusDetails::AUTHORIZATION_INITIATED){
            $hasOpenCharge = true;
        }
    }

    echo '<tr>
            <td>'.$transaction->type.'</td>
            <td>'.$transaction->reference.'</td>
            <td>'.$transaction->status.'</td>
            <td>'.number_format($transaction->charge_amount, 2, ',', '.').' '.$transaction->currency.'</td>
            <td>'.($transaction->type === 'Charge'?number_format($transaction->captured_amount, 2, ',', '.').' '.$transaction->currency:'').'</td>
            <td>'.($transaction->type === 'Charge'?number_format($transaction->refunded_amount, 2, ',', '.').' '.$transaction->currency:'').'</td>
            <td>';
    if($transaction->type === 'Charge' && $transaction->status === \AmazonPayExtendedSdk\Struct\StatusDetails::AUTHORIZED){
        echo '<form method="POST" action="'.xtc_href_link('orders.php', 'oID='.$orderId.'&action=edit&amazon_pay_action=capture&charge_id='.$transaction->reference, 'SSL').'">
                <input type="number" name="amount" step="0.01" max="'.$transaction->charge_amount.'" value="'.$transaction->charge_amount.'" />
                <button class="button">Zahlung einziehen</button>
              </form>';
    }
    if($transaction->type === 'Charge' && $transaction->status === \AmazonPayExtendedSdk\Struct\StatusDetails::CAPTURED && round($transaction->captured_amount*1.15, 2) - $transaction->refunded_amount > 0){
        $amount = $transaction->captured_amount - $transaction->refunded_amount;
        $maxAmount = round($transaction->captured_amount*1.15, 2) - $transaction->refunded_amount;
        echo '<form method="POST" action="'.xtc_href_link('orders.php', 'oID='.$orderId.'&action=edit&amazon_pay_action=refund&charge_id='.$transaction->reference, 'SSL').'">
                <input type="number" name="amount" step="0.01" max="'.$maxAmount.'" value="'.$amount.'" />
                <button class="button">Zahlung erstatten</button>
              </form>';
    }
    echo '</tr>';
}
?>
</table>
<?php
if($capturedTotal < $originalTotal && !$hasOpenCharge && $chargePermissionId !== null){
    $amount = $originalTotal - $capturedTotal;
    echo '<h3>Weitere Zahlung autorisieren</h3>
              <form method="POST" action="'.xtc_href_link('orders.php', 'oID='.$orderId.'&action=edit&amazon_pay_action=create_charge&charge_permission_id='.$chargePermissionId, 'SSL').'">
                <input type="number" name="amount" step="0.01" max="'.$amount.'" value="'.$amount.'" />
                <button class="button">Autorisieren</button>
              </form>';
}
?>
</div>
