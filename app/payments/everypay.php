<?php
use Tygh\Registry;

include_once ('everypay/everypay_common.inc');

if ( !defined('AREA') ) { die('Access denied'); }

// Return from payment
if (defined('PAYMENT_NOTIFICATION')) {
//print_r($_REQUEST);echo "<br/><br/>";
    if ($mode == 'return' && !empty($_REQUEST['merchant_order_id'])) {
        //echo "<br/>merchant order id is ok<br/>";
        //$view->assign('order_action', __('placing_order'));
        //$view->display('views/orders/components/placing_order.tpl');
        //fn_flush();

        $merchant_order_id = fn_rzp_place_order($_REQUEST['merchant_order_id']);
        $everypay_token = $_REQUEST['everypayToken'];

        if(!empty($merchant_order_id) and !empty($everypay_token)){
            if (fn_check_payment_script('everypay.php', $merchant_order_id, $processor_data)) {
                $secret_key = $processor_data['processor_params']['secret_key'];
                $order_info = fn_get_order_info($merchant_order_id);
                //$amount = fn_rzp_adjust_amount($order_info['total'], $processor_data['processor_params']['currency'])*100;
                $amount = $order_info['total'];
                $description = "#".$merchant_order_id;
                $test_mode = $processor_data['processor_params']['test_mode'];

                $pp_response = array();
                $success = false;
                $error = "";
                $everypay_payment_id = "";

                $everypayParams = array(
                    'token' => $everypay_token,
                    'amount' => $amount,
                    'description' => $description
                );
                $query = http_build_query($everypayParams, null, '&');

                try {
                    if($test_mode){
                        $url = 'https://sandbox-api.everypay.gr/payments';
                    }else{
                        $url = 'https://api.everypay.gr/payments';
                    }
                    //$field_description="description='".$description."'";

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
//echo "curl: ";
//print_r($result);
//exit;
                    if($result === false) {
                        $success = false;
                        $error = 'Curl error: ' . curl_error($ch);
                    }
                    else {
                        $response_array = json_decode($result, true);
//print_r($response_array);
//exit;
                        //Check success response
                        if($http_status === 200 and isset($response_array['error']) === false){
                            $success = true;
                            $everypay_payment_id = $response_array['token'];
                        }
                        else {
                            $success = false;

                            if(!empty($response_array['error']['code'])) {
                                $error = $response_array['error']['code'].":".$response_array['error']['description'];
                            }
                            else {
                                $error = "RAZORPAY_ERROR:Invalid Response <br/>".$result;
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
                    $pp_response['reason_text'] = fn_get_lang_var('text_evp_pending').$error;
                    $pp_response['transaction_id'] = @$order;
                    $pp_response['client_id'] = $everypay_payment_id;

                    fn_finish_payment($merchant_order_id, $pp_response);
                    fn_set_notification('E', __('error'), __('text_evp_failed_order').$merchant_order_id);
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
else {
    $url = fn_url("payment_notification.return?payment=everypay", AREA, 'current');
    //$checkout_url = "https://sandbox-button.everypay.gr/js/button.js";
    $urlButton = "https://button.everypay.gr/js/button.js";
    $test_mode = $processor_data['processor_params']['test_mode'];

    $fields = array(
        'key' => $processor_data['processor_params']['public_key'],
        'amount' => fn_rzp_adjust_amount($order_info['total'], $processor_data['processor_params']['currency'])*100,
        'currency' => $processor_data['processor_params']['currency'],
        'description' => "Order# ".$order_id,
        'name' => Registry::get('settings.Company.company_name'),
        'customer_name' => $order_info['b_firstname']." ".$order_info['b_lastname'],
        'customer_email' => $order_info['email'],
        'customer_phone' => $order_info['phone'],
        'order_id' => $order_id
    );

    //$html = '<form name="everypay-form" id="razorpay-form" action="'.$url.'" target="_parent" method="POST">
    //            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id" />
    //            <input type="hidden" name="merchant_order_id" id="order_id" value="'.$fields['order_id'].'"/>
    //        </form>';

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
                callback: '',
                sandbox: 0,
                max_installments: 0,
                sandbox: ".$test_mode."
            };

            var loadButton = setInterval(function () {
                  try {
                    console.log('trying');
                    EverypayButton.jsonInit(EVERYPAY_DATA, $('#payment-card-form'));
                    clearInterval(loadButton);
                  } catch (err) { }
                  $('.everypay-button').trigger('click');
            }, 1000);
            ";

    $jsForm .= '</script>';

    if (!$fields['amount']) {
        echo __('text_unsupported_currency');
        exit;
    }

echo <<<EOT
    <script data-no-defer="" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script data-no-defer="" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
    <script type="text/javascript" src="http://cscart.local/js/lib/jquery/jquery.min.js?ver=4.3.5"></script>
    <script type="text/javascript" src="http://cscart.local/js/lib/jqueryui/jquery-ui.custom.min.js?ver=4.3.5"></script>
    {$jsButton}
    {$jsForm}
    {$html}
</body>
</html>
EOT;
exit;
}

?>