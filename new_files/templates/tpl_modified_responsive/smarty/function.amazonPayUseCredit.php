<?php

function smarty_function_amazonPayUseCredit(){
    global $order_total_modules;
    $return = '';
    $oldValue = isset($_SESSION['cot_gv'])?$_SESSION['cot_gv']:null;
    $creditSelectionResult = $order_total_modules->credit_selection();
    $_SESSION['cot_gv'] = $oldValue;
    if($creditSelectionResult){
        $return = '<div><label><input type="checkbox" name="amazon_pay_use_credit" value="1" '.(!empty($_SESSION['cot_gv'])?'checked':'').' /> '.TEXT_AMAZON_PAY_USE_CREDIT.'</label></div>';
    }
    return $return;
}