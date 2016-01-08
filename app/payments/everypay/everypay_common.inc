<?php
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_evp_adjust_amount($price, $payment_currency)
{
    $currencies = Registry::get('currencies');
    $symbol = ENV['CART_PRIMARY_CURRENCY'];

    if (array_key_exists($payment_currency, $currencies)) {
        if ($currencies[$payment_currency]['is_primary'] != 'Y') {
            $price = fn_format_price($price / $currencies[$payment_currency]['coefficient']);
        }
        $symbol = $currencies[$payment_currency]['symbol'];
    } else {
        return false;
    }

    return array($price, $symbol);
}

function fn_evp_place_order($original_order_id)
{
    $cart = & $_SESSION['cart'];
    $auth = & $_SESSION['auth'];

    list($order_id, $process_payment) = fn_place_order($cart, $auth);

    $data = array (
        'order_id' => $order_id,
        'type' => 'S',
        'data' => TIME,
    );
    db_query('REPLACE INTO ?:order_data ?e', $data);

    $data = array (
        'order_id' => $order_id,
        'type' => 'E', // extra order ID
        'data' => $original_order_id,
    );
    db_query('REPLACE INTO ?:order_data ?e', $data);

    return $order_id;
}

function getInstallments($total, $ins)
{
    $inst = htmlspecialchars_decode($ins);//$processor_data['processor_params']['everypay_installments']);
    if ($inst) {
        $installments = json_decode($inst, true);
        foreach ($installments as $i) {
            if ($total >= $i['from'] && $total <= $i['to']) {
                return $i['max'];
            }
        }
    }


    return false;
}
