<?php
use Tygh\Registry;

include_once ('everypay/everypay_common.inc');

if ( !defined('AREA') ) { die('Access denied'); }

// Return from payment
if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'return' && !empty($_REQUEST['merchant_order_id'])) {
        $view = Registry::get('view');
        $view->assign('order_action', __('placing_order'));
        $view->display('views/orders/components/placing_order.tpl');
        fn_flush();

        $merchant_order_id = fn_evp_place_order($_REQUEST['merchant_order_id']);
        $everypay_token = $_REQUEST['everypayToken']; //ctn_...

        if(!empty($merchant_order_id) and !empty($everypay_token)){
            if (fn_check_payment_script('everypay.php', $merchant_order_id, $processor_data)) {
                $secret_key = $processor_data['processor_params']['secret_key'];
                $order_info = fn_get_order_info($merchant_order_id);
                $amount_and_symbol = fn_evp_adjust_amount($order_info['total'], $processor_data['processor_params']['currency']);
                $amount = $amount_and_symbol[0] * 100 ;
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
                if (false !== $max = getInstallments($amount, $processor_data['processor_params']['everypay_installments'])) {
                    $everypayParams['max_installments'] = $max;
                }
                $query = http_build_query($everypayParams, null, '&');

                try {
                    if($test_mode){
                        $url = 'https://sandbox-api.everypay.gr/payments';
                    }else{
                        $url = 'https://api.everypay.gr/payments';
                    }

                    //cURL Request
                    $ch = curl_init();

                    //set the url, number of POST vars, POST data
                    curl_setopt($ch,CURLOPT_URL, $url);
                    curl_setopt($ch,CURLOPT_USERPWD, $secret_key . ":");
                    curl_setopt($ch,CURLOPT_TIMEOUT, 60);
                    curl_setopt($ch,CURLOPT_POST, 1);
                    curl_setopt($ch,CURLOPT_POSTFIELDS, $query);
                    curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);

                    //execute post
                    $result = curl_exec($ch);
                    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    if($result === false) {
                        $success = false;
                        $error = 'Curl error: ' . curl_error($ch);
                    }
                    else {
                        $response_array = json_decode($result, true);

                        //Check success response
                        if($http_status === 200 and isset($response_array['error']) === false){
                            $success = true;
                            $everypay_payment_id = $response_array['token'];
                        }
                        else {
                            $success = false;

                            if(!empty($response_array['error']['code'])) {
                                $error = $response_array['error']['code'].":".$response_array['error']['message'];
                            }
                            else {
                                $error = "EVERYPAY_ERROR:Invalid Response <br/>".$result;
                            }
                        }
                    }

                    //close connection
                    curl_close($ch);
                }
                catch (Exception $e) {
                    $success = false;
                    $error ="CSCART_ERROR:Request to Everypay Failed";
                }

                if($success === true){
                    $pp_response['order_status'] = 'P';
                    $pp_response['reason_text'] = fn_get_lang_var('text_evp_success');
                    $pp_response['transaction_id'] = @$order;
                    $pp_response['client_id'] = $everypay_payment_id;

                    fn_finish_payment($merchant_order_id, $pp_response);
                    fn_order_placement_routines('route', $merchant_order_id);
                }
                else {
                    $pp_response['order_status'] = 'O';
                    $pp_response['reason_text'] = fn_get_lang_var('text_evp_pending').$everypay_token.' (EveryPay: '.$error.')';
                    $pp_response['transaction_id'] = @$order;
                    $pp_response['client_id'] = $everypay_token;//$everypay_payment_id;

                    fn_finish_payment($merchant_order_id, $pp_response);
                    fn_set_notification('E', __('error'), __('text_evp_pending').$everypay_token.' (EveryPay: '.$error.')');
                    fn_order_placement_routines('checkout_redirect');
                }

            }
        }
        else {
            fn_set_notification('E', __('error'), __('text_evp_failed_order').$_REQUEST['merchant_order_id']);
            fn_order_placement_routines('checkout_redirect');
        }
    }
    exit;
}
else { //load the payment form
    $url = fn_url("payment_notification.return?payment=everypay", AREA, 'current');
    $urlButton = "https://button.everypay.gr/js/button.js";
    $test_mode = $processor_data['processor_params']['test_mode'];

    $fields = array(
        'key' => $processor_data['processor_params']['public_key'],
        'amount' => fn_evp_adjust_amount($order_info['total'], $processor_data['processor_params']['currency'])[0]*100,
        'currency' => $processor_data['processor_params']['currency'],
        'description' => "Order# ".$order_id,
        'name' => Registry::get('settings.Company.company_name'),
        'customer_name' => $order_info['b_firstname']." ".$order_info['b_lastname'],
        'customer_email' => $order_info['email'],
        'customer_phone' => $order_info['phone'],
        'order_id' => $order_id
    );
    $installments = getInstallments($fields['amount'], $processor_data['processor_params']['everypay_installments']);

    $html = '<form class="payment-card-form" method="POST" action="'.$url.'" target="_parent" id="payment-card-form">
                <input type="hidden" name="merchant_order_id" id="order_id" value="'.$fields['order_id'].'"/>
                <div class="button-holder" style="float: right;display:none;"></div>';
    if (1 == $test_mode){
        $html .= '<p style="text-align:right;"><strong style="color: #ff0000">'.__("test_mode").'</strong></p>';
    }
    $html  .= '</form>';

    $jsButton = '<script type="text/javascript" src="https://button.everypay.gr/js/button.js"></script>';

    $jsForm = '<script type="text/javascript">';

    $jsForm .= "var EVERYPAY_DATA = {
                amount: '".$fields['amount']."',
                description: 'Order# ".$fields['order_id']."',
                key: '".$fields['key']."',
                locale: 'el-GR',
                callback: '',"
                .($installments !== false ? 'max_installments:'.$installments.',' : '')
                ."sandbox: ".$test_mode."
            };
            var loadButton = setInterval(function () {
                  try {
                    EverypayButton.jsonInit(EVERYPAY_DATA, '#payment-card-form');
                    document.getElementsByClassName('everypay-button')[0].click();
                    clearInterval(loadButton);
                  } catch (err) { console.log(err)}
            }, 100);
            ";

    $jsForm .= '</script>';

    if (!$fields['amount']) {
        echo __('text_unsupported_currency');
        exit;
    }

echo <<<EOT
    {$jsButton}
    {$jsForm}
    {$html}
</body>
</html>
EOT;
exit;
}

?>