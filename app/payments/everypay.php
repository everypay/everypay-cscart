<?php
use Tygh\Registry;

include_once ('everypay/everypay_common.inc');

if (!defined('AREA')) {
    die('Access denied');
}

// Return from payment
if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'return' && !empty($_REQUEST['merchant_order_id'])) {
        $view->assign('order_action', __('placing_order'));
        $view->display('views/orders/components/placing_order.tpl');
        fn_flush();

        $merchant_order_id = fn_everypay_place_order($_REQUEST['merchant_order_id']);
        $everypay_payment_id = $_REQUEST['everypay_payment_id'];

        if (!empty($merchant_order_id) and ! empty($everypay_payment_id)) {
            if (fn_check_payment_script('everypay.php', $merchant_order_id, $processor_data)) {
                $public_key = $processor_data['processor_params']['public_key'];
                $secret_key = $processor_data['processor_params']['secret_key'];
                $order_info = fn_get_order_info($merchant_order_id);
                $amount = fn_everypay_adjust_amount($order_info['total'], $processor_data['processor_params']['currency']) * 100;

                $pp_response = array();
                $success = false;
                $error = "";



                $success = true;
                $error = "";

                try {

                    $theURL = "https://" . ($params['mode'] == 'LIVE' ? '' : 'sandbox-')
                        . "api.everypay.gr/payments";
                    $everypayParams = array(
                        'token' => $everypay_token,
                        'amount' => $converted_amount,
                        'description' => $CONFIG['CompanyName'] . " Invoice #" . $merchant_order_id,
                        'payee_email' => $_POST["payee_email"],
                        'payee_phone' => $_POST["payee_phone"],
                    );

                    $curl = curl_init();
                    $query = http_build_query($everypayParams, null, '&');

                    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($curl, CURLOPT_USERPWD, $params['SecretKey'] . ':');
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
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
                    //close connection
                    curl_close($curl);
                } catch (Exception $e) {
                    $success = false;
                    $error = "WHMCS_ERROR:Request to Everypay Failed";
                }

                if ($success === true) {
                    $pp_response['order_status'] = 'P';
                    $pp_response['reason_text'] = fn_get_lang_var('text_everypay_success');
                    $pp_response['transaction_id'] = @$order;
                    $pp_response['client_id'] = $everypay_payment_id;

                    fn_finish_payment($merchant_order_id, $pp_response);
                    fn_order_placement_routines('route', $merchant_order_id);
                } else {
                    $pp_response['order_status'] = 'O';
                    $pp_response['reason_text'] = fn_get_lang_var('text_everypay_pending') . $error;
                    $pp_response['transaction_id'] = @$order;
                    $pp_response['client_id'] = $everypay_payment_id;

                    fn_finish_payment($merchant_order_id, $pp_response);
                    fn_set_notification('E', __('error'), __('text_everypay_failed_order') . $merchant_order_id);
                    fn_order_placement_routines('checkout_redirect');
                }
            }
        } else {
            fn_set_notification('E', __('error'), __('text_everypay_failed_order') . $_REQUEST['merchant_order_id']);
            fn_order_placement_routines('checkout_redirect');
        }
    }
    exit;
} else {
    $url = fn_url("payment_notification.return?payment=everypay", AREA, 'current');
    $checkout_url = "https://checkout.everypay.com/v1/checkout.js"; 

    $fields = array(
        'key' => $processor_data['processor_params']['public_key'],
        'amount' => fn_everypay_adjust_amount($order_info['total'], $processor_data['processor_params']['currency']) * 100,
        'currency' => $processor_data['processor_params']['currency'],
        'description' => "Order# " . $order_id,
        'name' => Registry::get('settings.Company.company_name'),
        'customer_name' => $order_info['b_firstname'] . " " . $order_info['b_lastname'],
        'customer_email' => $order_info['email'],
        'customer_phone' => $order_info['phone'],
        'order_id' => $order_id
    );

    $html = '<form name="everypay-form" id="everypay-form" action="' . $url . '" target="_parent" method="POST">
                <input type="hidden" name="everypay_payment_id" id="everypay_payment_id" />
                <input type="hidden" name="merchant_order_id" id="order_id" value="' . $fields['order_id'] . '"/>
            </form>';

    $js = '<script>';

    $js .= "var everypay_options = {
                'key': '" . $fields['key'] . "',
                'amount': '" . $fields['amount'] . "',
                'name': '" . $fields['name'] . "',
                'description': 'Order# " . $fields['order_id'] . "',
                'currency': '" . $fields['currency'] . "',
                'handler': function (transaction) {
                    document.getElementById('everypay_payment_id').value = transaction.everypay_payment_id;
                    document.getElementById('everypay-form').submit();
                },
                'prefill': {
                    'name': '" . $fields['customer_name'] . "',
                    'email': '" . $fields['customer_email'] . "',
                    'contact': '" . $fields['customer_phone'] . "'
                },
                notes: {
                    'cs_order_id': '" . $fields['order_id'] . "'
                },
                netbanking: true
            };
            
            function everypaySubmit(){                  
                var everypay1 = new Everypay(everypay_options);
                everypay1.open();
                everypay1.modal.options.backdropClose = false;
            }    
            
            var everypay_interval = setInterval(function(){
                if (typeof window[\"Everypay\"] != \"undefined\")
                {
                    setTimeout(function(){ everypaySubmit(); }, 500);
                    clearInterval(everypay_interval);
                }
            }, 500);
            ";

    $js .= '</script>';

    if (!$fields['amount']) {
        echo __('text_unsupported_currency');
        exit;
    }

    echo <<<EOT
    <script src="{$checkout_url}"></script>
    {$html}
    {$js}
</body>
</html>
EOT;
    exit;
}

?>