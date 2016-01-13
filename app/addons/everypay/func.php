<?php
require_once dirname(__FILE__) . "/controllers/common/lib.php";

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

function fn_everypay_delete_payment_processors()
{
    db_query("DELETE FROM ?:payment_processors WHERE addon = 'everypay'");
}

function fn_everypay_prepare_checkout_payment_methods($cart)
{

    //iframe content
    if (isset($_GET['dispatch']) 
        && $_GET['dispatch'] == 'checkout.process_payment' 
        && $cart['payment_method_data']['processor'] == 'Everypay') {
        $amount = fn_everypay_convert_amount($cart['total'], CART_PRIMARY_CURRENCY, $cart['payment_method_data']['processor_params']['currency']);

        $jsonInit = array(
            'amount' => intval($amount['price'] * 100),
            'currency' => $cart['payment_method_data']['processor_params']['currency'],
            'key' => $cart['payment_method_data']['processor_params']['public_key'],
            'locale' => $cart['payment_method_data']['lang_code'],
            'sandbox' => $cart['payment_method_data']['processor_params']['test_mode'],
        );
        
        $max_installments = fn_everypay_get_installments($amount['price'], 
            $cart['payment_method_data']['processor_params']['everypay_installments']);
        
        $jsonInit['max_installments'] = $max_installments ? : 0;
        $response = '<script type="text/javascript">parent.postMessage(\'init_everypay:'
            . json_encode($jsonInit) . '\',"*");</script>';

        die($response);
    }

    //All the rest pages just include the script file
    Tygh::$app['view']->display('../../../backend/templates/addons/everypay/hooks/everypay.tpl');
}
