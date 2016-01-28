<?php
require_once dirname(__FILE__) . "/controllers/common/lib.php";

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

function fn_everypay_delete_payment_processors()
{
    db_query("DELETE FROM ?:payment_processors WHERE addon = 'everypay'");
}

function fn_everypay_find_payment_processor(){
    $processor_data = db_get_row("SELECT * FROM ?:payment_processors WHERE addon = ?s", 'everypay');
    
    if (empty($processor_data)) {
        return false;
    }
    return $processor_data;
}

function fn_everypay_prepare_checkout_payment_methods($cart, $sec, $payment_tabs)
{   
    
    if (!isset($_GET['dispatch']) || $_GET['dispatch'] != 'checkout.process_payment') {
        return;
    }   
    
    $ev_id = 0;
    $processor_data = array();
    foreach ($payment_tabs as $tab) {
        foreach ($tab as $payment_id => $payment_data) {
            $processor_data = fn_get_payment_method_data($payment_id);            
            if ($processor_data['processor'] == 'Everypay') {
                $ev_id = $payment_data['payment_id'];
            }
        }
    }
    
    if ($cart['payment_id'] != $ev_id){
        exit();
    }    
    
    $processor_data = fn_get_processor_data($ev_id);
    $processor_data = $processor_data['processor_params'];    
    
    $amount = fn_everypay_convert_amount($cart['total'], CART_PRIMARY_CURRENCY, $processor_data['currency']);
    
    $jsonInit = array(
        'amount'   => intval($amount['price'] * 100),
        'currency' => $processor_data['currency'],
        'key'      => $processor_data['public_key'],
        'locale'   => $cart['user_data']['lang_code'],
        'sandbox'  => $processor_data['test_mode'],
    );

    $max_installments = fn_everypay_get_installments($amount['price'], $cart['payment_method_data']['processor_params']['everypay_installments']);

    $jsonInit['max_installments'] = $max_installments ? : 0;
    $time = time();
    $response = '<script type="text/javascript">parent.init' . $time . ' = function(data){'
        . ' var element =  document.getElementById(\'everypay_added_script\');
            //if (typeof(element) == \'undefined\' || element == null)
            //{             
                var fileref = document.createElement("script")
                fileref.setAttribute("type","text/javascript")
                fileref.setAttribute("id","everypay_added_script")
                fileref.setAttribute("src", "/js/addons/everypay/everypay.js");
                parent.document.getElementsByTagName("head")[0].appendChild(fileref)
            //}
                parent.EVERYPAY_DATA = ' . json_encode($jsonInit) . '
            };
            parent.init' . $time . '(' . json_encode($jsonInit) . ');
            '
        . '</script>'
        . 'Loading. Please Wait...'

    ;
    
    die($response);
}