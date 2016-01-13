<?php
use Tygh\Registry;

function fn_everypay_convert_amount($price, $from_currency, $to_currency)
{
    $currencies = Registry::get('currencies');
    $symbol = $currencies[$to_currency]['symbol'];
    if ($to_currency == $from_currency) {
        return array('price' => $price, 'symbol' => $symbol);
    }
    if (array_key_exists($to_currency, $currencies)) {
        $price = fn_format_price($price / $currencies[$to_currency]['coefficient']);
        $symbol = $currencies[$to_currency]['symbol'];
    } else {
        return false;
    }
    return array('price' => $price, 'symbol' => $symbol);
}

function fn_everypay_place_order()
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
    
    return $order_id;
}

function fn_send_everypay_payment()
{
    global $order_id;
    $view = Registry::get('view');
    $view->assign('order_action', __('placing_order'));
    $view->display('views/orders/components/placing_order.tpl');
    fn_flush();
    
    if (!isset($_REQUEST['everypayToken']) || empty($_REQUEST['everypayToken'])){
        fn_set_notification('E', __('error'), __('text_evp_failed_order'));
        fn_order_placement_routines('checkout_redirect');
    }
    
    $everypay_token = $_REQUEST['everypayToken'];
    $merchant_order_id = fn_everypay_place_order();
    
    if (!empty($merchant_order_id)) {
        if (fn_check_payment_script('everypay.php', $merchant_order_id, $processor_data)) {
            $secret_key = $processor_data['processor_params']['secret_key'];
            $order_info = fn_get_order_info($merchant_order_id);
            $amount = fn_everypay_convert_amount($order_info['total'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency']);

            $description = $_SERVER['SERVER_NAME']
                . ' - '
                . __('privilege_sections.cart') . ' #' . $merchant_order_id
                . ' - '
                . $order_info['total'] . ' ' . $amount['symbol'];

            $test_mode = $processor_data['processor_params']['test_mode'];

            $theURL = "https://" . ($test_mode ? 'sandbox-' : '')
                . "api.everypay.gr/payments";

            $everypayParams = array(
                'token' => $everypay_token,
                'amount' => intval($amount['price'] * 100),
                'description' => $description,
                'payee_email' => $order_info['email'],
                'payee_phone' => $order_info['phone'],
            );
            
            if (false !== $max = fn_everypay_get_installments($order_info['total'], 
                $processor_data['processor_params']['everypay_installments'])) {
                    $everypayParams['max_installments'] = $max;
            }

            $response = array();
            $success = false;
            $error = "";

            try {
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_TIMEOUT, 60);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($curl, CURLOPT_USERPWD, $secret_key . ':');
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($everypayParams, null, '&'));
                curl_setopt($curl, CURLOPT_URL, $theURL);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($curl);
                $info = curl_getinfo($curl);

                if ($result === false) {
                    $success = false;
                    $error = 'Curl error: ' . curl_error($curl);
                } else {
                    $response_array = json_decode($result, true);
                    //Check success response
                    if (isset($response_array['error']) === false) {
                        $success = true;
                    } else {
                        $success = false;

                        if (!empty($response_array['error']['code'])) {
                            $error = $response_array['error']['code'] . ":" . $response_array['error']['message'];
                        } else {
                            $error = "EVERYPAY_ERROR:Invalid Response <br/>" . $result;
                        }
                    }
                }

                curl_close($curl);
            } catch (Exception $e) {
                $success = false;
                $error = "CSCART_ERROR:Request to Everypay Failed";
            }

            if ($success === true) {
                $response['order_status'] = 'P';
                $response['reason_text'] = fn_get_lang_var('text_evp_success');
                $response['transaction_id'] = @$order;
                $response['client_id'] = $everypay_token;
                fn_finish_payment($merchant_order_id, $response);
                fn_order_placement_routines('route', $merchant_order_id);
            } else {
                $response['order_status'] = 'O';
                $response['reason_text'] = fn_get_lang_var('text_evp_pending') . $everypay_token . ' (EveryPay: ' . $error . ')';
                $response['transaction_id'] = @$order;
                $response['client_id'] = $everypay_token; //$everypay_payment_id;
                fn_finish_payment($merchant_order_id, $response);
                fn_set_notification('E', __('error'), __('text_evp_pending') . $everypay_token . ' (EveryPay: ' . $error . ')');
                fn_order_placement_routines('checkout_redirect');
            }
        }
    } else {
        fn_set_notification('E', __('error'), __('text_evp_failed_order'));
        fn_order_placement_routines('checkout_redirect');
    }

    exit;
}

function fn_everypay_get_installments($total, $ins)
{
    $inst = htmlspecialchars_decode($ins);
    if ($inst) {
        $installments = json_decode($inst, true);
        $counter = 1;
        $max = 0;
        $max_installments = 0; 
        foreach ($installments as $i) {           
            if ($i['to'] > $max){
                $max = $i['to'];
                $max_installments = $i['max'];
            }
            
            if(($counter == (count($installments)) && $total >= $max)){
                return $max_installments;
            }
            
            if ($total >= $i['from'] && $total <= $i['to']) {
                return $i['max'];
            }
            $counter++;
        }
    }
    return false;
}
