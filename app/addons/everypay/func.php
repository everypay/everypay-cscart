<?php
/* * *************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 * ***************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 * ************************************************************************** */
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Http;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

function fn_everypay_delete_payment_processors()
{
    db_query("DELETE FROM ?:payment_processors WHERE addon = 'everypay'");
}

function fn_update_everypay_settings($settings)
{
    return false;
}

function fn_everypay_update_payment_pre($settings)
{
//return false;
}

function fn_everypay_prepare_checkout_payment_methods($cart)
{
    $amount = fn_everypay_convert_amount($cart['total'], CART_PRIMARY_CURRENCY, 
        $cart['payment_method_data']['processor_params']['currency']);
        
    $values = array(
        'amount' => intval($amount['price'] * 100),
        'currency' => $cart['payment_method_data']['processor_params']['currency'],
        'key' => $cart['payment_method_data']['processor_params']['public_key'],
        'locale' => $cart['payment_method_data']['lang_code'],
        'sandbox' => $cart['payment_method_data']['processor_params']['test_mode'],
    );

    //************   Content of the iframe will be here ***************/
    if (isset($_GET['dispatch']) && $_GET['dispatch'] == 'checkout.process_payment' 
        && $cart['payment_method_data']['processor'] == 'Everypay'
    ) {
        $response = '<script type="text/javascript">parent.postMessage(\'init_everypay:'
            . json_encode($values) . '\',"*");</script>';
        die($response);
    }
    
    if (isset($_GET['everypayToken'])){
        fn_send_everypay_payment();
    }

    Tygh::$app['view']->display('../../../backend/templates/addons/everypay/hooks/everypay.tpl');
}

function fn_everypay_get_checkout_payment_buttons()
{
    
}

function fn_everypay_get_checkout_payment_buttons_pre()
{
    
}

function fn_everypay_payment_url()
{
    
}

function fn_everypay_process_payments()
{
    
}

function fn_everypay_convert_amount($price, $from_currency, $to_currency)
{
    $currencies = Registry::get('currencies');
    $symbol = $currencies[$to_currency]['symbol'];
    if ($to_currency == $from_currency){
        return array($price, $symbol);
    }
    if (array_key_exists($to_currency, $currencies)) {
        $price = fn_format_price($price / $currencies[$to_currency]['coefficient']);
        $symbol = $currencies[$to_currency]['symbol'];
    } else {
        return false;
    }
    return array('price' => $price, 'symbol' => $symbol);
}

function fn_send_everypay_payment()
{
    if ($mode == 'return' && !empty($_REQUEST['merchant_order_id'])) {
        $view = Registry::get('view');
        $view->assign('order_action', __('placing_order'));
        $view->display('views/orders/components/placing_order.tpl');
        fn_flush();
        $merchant_order_id = fn_evp_place_order($_REQUEST['merchant_order_id']);
        $everypay_token = $_REQUEST['everypayToken']; //ctn_...
        if (!empty($merchant_order_id) and ! empty($everypay_token)) {
            if (fn_check_payment_script('everypay.php', $merchant_order_id, $processor_data)) {
                $secret_key = $processor_data['processor_params']['secret_key'];
                $order_info = fn_get_order_info($merchant_order_id);
                $amount_and_symbol = fn_evp_adjust_amount($order_info['total'], $processor_data['processor_params']['currency']);
                $amount = $amount_and_symbol[0] * 100;
                $symbol = $amount_and_symbol[1];
                $host = $_SERVER['HTTP_HOST'];
                $description = $host
                    . ' - '
                    . __('privilege_sections.cart') . ' #' . $merchant_order_id
                    . ' - '
                    . $order_info['total'] . ' ' . $symbol;
                $test_mode = $processor_data['processor_params']['test_mode'];
                $pp_response = array();
                $success = false;
                $error = "";
                $everypay_payment_id = ""; //pmt_...
                $everypayParams = array(
                    'token' => $everypay_token,
                    'amount' => $amount,
                    'description' => $description
                );
                $query = http_build_query($everypayParams, null, '&');
                try {
                    if ($test_mode) {
                        $url = 'https://sandbox-api.everypay.gr/payments';
                    } else {
                        $url = 'https://api.everypay.gr/payments';
                    }
                    //cURL Request
                    $ch = curl_init();
                    //set the url, number of POST vars, POST data
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ":");
                    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    //execute post
                    $result = curl_exec($ch);
                    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if ($result === false) {
                        $success = false;
                        $error = 'Curl error: ' . curl_error($ch);
                    } else {
                        $response_array = json_decode($result, true);
                        //Check success response
                        if ($http_status === 200 and isset($response_array['error']) === false) {
                            $success = true;
                            $everypay_payment_id = $response_array['token'];
                        } else {
                            $success = false;
                            if (!empty($response_array['error']['code'])) {
                                $error = $response_array['error']['code'] . ":" . $response_array['error']['message'];
                            } else {
                                $error = "EVERYPAY_ERROR:Invalid Response <br/>" . $result;
                            }
                        }
                    }
                    //close connection
                    curl_close($ch);
                } catch (Exception $e) {
                    $success = false;
                    $error = "CSCART_ERROR:Request to Everypay Failed";
                }
                if ($success === true) {
                    $pp_response['order_status'] = 'P';
                    $pp_response['reason_text'] = fn_get_lang_var('text_evp_success');
                    $pp_response['transaction_id'] = @$order;
                    $pp_response['client_id'] = $everypay_payment_id;
                    fn_finish_payment($merchant_order_id, $pp_response);
                    fn_order_placement_routines('route', $merchant_order_id);
                } else {
                    $pp_response['order_status'] = 'O';
                    $pp_response['reason_text'] = fn_get_lang_var('text_evp_pending') . $everypay_token . ' (EveryPay: ' . $error . ')';
                    $pp_response['transaction_id'] = @$order;
                    $pp_response['client_id'] = $everypay_token; //$everypay_payment_id;
                    fn_finish_payment($merchant_order_id, $pp_response);
                    fn_set_notification('E', __('error'), __('text_evp_pending') . $everypay_token . ' (EveryPay: ' . $error . ')');
                    fn_order_placement_routines('checkout_redirect');
                }
            }
        } else {
            fn_set_notification('E', __('error'), __('text_evp_failed_order') . $_REQUEST['merchant_order_id']);
            fn_order_placement_routines('checkout_redirect');
        }
    }
    exit;
}

function fn_everypay_place_order($original_order_id)
{
    $cart = & $_SESSION['cart'];
    $auth = & $_SESSION['auth'];
    list($order_id, $process_payment) = fn_place_order($cart, $auth);
    $data = array(
        'order_id' => $order_id,
        'type' => 'S',
        'data' => TIME,
    );
    db_query('REPLACE INTO ?:order_data ?e', $data);
    $data = array(
        'order_id' => $order_id,
        'type' => 'E', // extra order ID
        'data' => $original_order_id,
    );
    db_query('REPLACE INTO ?:order_data ?e', $data);
    return $order_id;
}

function fn_get_everypay_settings($lang_code = DESCR_SL)
{
    $pp_settings = Settings::instance()->getValues('everypay', 'ADDON');

    return $pp_settings['general'];
}
