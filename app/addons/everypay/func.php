<?php
require_once dirname(__FILE__) . "/controllers/common/lib.php";

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

function fn_everypay_delete_payment_processors()
{
    db_query("DELETE FROM ?:payment_processors WHERE addon = 'everypay'");
}

function fn_everypay_find_payment_processor()
{
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

    if ($cart['payment_id'] != $ev_id) {
        exit();
    }

    $processor_data = fn_get_processor_data($ev_id);
    $processor_data = $processor_data['processor_params'];

    $amount = fn_everypay_convert_amount($cart['total'] + $cart['payment_surcharge'], CART_PRIMARY_CURRENCY, $processor_data['currency']);
    $lang = strtolower($cart['user_data']['lang_code']);
    $jsonInit = array(
        'amount' => intval(strval($amount['price'] * 100)),
        'currency' => $processor_data['currency'],
        'key' => $processor_data['public_key'],
        'locale' => $lang == 'el' ? $lang : 'en',
        'sandbox' => $processor_data['test_mode'],
        'callback' => 'handleToken'
    );

    $max_installments = fn_everypay_get_installments($amount['price'], $cart['payment_method_data']['processor_params']['everypay_installments']);

    $jsonInit['max_installments'] = $max_installments ? : 0;
    $time = time();
    
    $btn_text = 'Πληρωμή με κάρτα';
    if ($cart['user_data']['lang_code'] != 'el'){
        $btn_text = 'Pay with card';
    }
    //ouput
    ?>
    <html>
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
            <script type="text/javascript">
                var loadOutter = setInterval(function () {
                    try {
                        parent.init<?php echo $time ?> = function (data) {
                            var fileref = document.createElement("script")
                            fileref.setAttribute("type", "text/javascript")
                            fileref.setAttribute("id", "everypay_added_script_<?php echo $time ?>")
                            fileref.setAttribute("src", "<?php echo fn_get_storefront_url(fn_get_storefront_protocol()) ?>/js/addons/everypay/everypay.js");
                            parent.document.getElementsByTagName("head")[0].appendChild(fileref)

                            parent.EVERYPAY_DATA = data;
                        };
                        parent.init<?php echo $time ?>(<?php echo json_encode($jsonInit) ?>);

                        clearInterval(loadOutter);
                    } catch (err) {
                        console.log(err);
                    }
                }, 301);
                var trigger_button = function () {
                    parent.trigger_outer_button();
                }
            </script>
            <link type="text/css" rel="stylesheet" href="https://button.everypay.gr/css/button-external.css?version=1.76">
            <style type="text/css">
                .everypay-button {
                    font-family: Open Sans,sans-serif,Tahoma,Verdana;
                    font-size: 18px !important;
                    padding: 12px 20px !important;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div style="text-align:center">
                <button onclick="trigger_button();" class="everypay-button"><?php echo $btn_text ?></button>
            </div>
        </body>
    </html>
    <?php
    die();
}
