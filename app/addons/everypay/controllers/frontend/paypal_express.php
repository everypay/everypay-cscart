<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

if (!defined('BOOTSTRAP')) {
    require './../../../../payments/init_payment.php';
}

if ($mode == 'in_context_order') {
    $token = $_REQUEST['token'];
    $payment_id = $_REQUEST['payment_id'];

    $payment_method_data = fn_get_payment_method_data($payment_id);
    $everypay_checkout_details = fn_everypay_get_express_checkout_details($payment_method_data, $token);

    if (fn_everypay_ack_success($everypay_checkout_details)) {
        fn_everypay_user_login($everypay_checkout_details);

        list($order_id, $status) = fn_place_order($_SESSION['cart'], $_SESSION['auth']);
        fn_everypay_mark_in_context_payment_started($order_id);

        $order_info = fn_get_order_info($order_id);
        $processor_data = fn_get_processor_data($payment_id);

        fn_pp_save_mode($order_info);
        fn_everypay_complete_checkout($token, $processor_data, $order_info);
    } else {
        fn_everypay_get_error($everypay_checkout_details);
        fn_order_placement_routines('checkout_redirect');
    }

} elseif ($mode == 'express_return') {

    $token = $_REQUEST['token'];
    $payment_id = $_REQUEST['payment_id'];

    $processor_data = fn_get_payment_method_data($payment_id);
    $everypay_checkout_details = fn_everypay_get_express_checkout_details($processor_data, $token);

    if (fn_everypay_ack_success($everypay_checkout_details)) {
        fn_everypay_user_login($everypay_checkout_details);

        $everypay_express_details = array(
            'token' => $token,
            'payment_id' => $payment_id
        );
        $_SESSION['pp_express_details'] = $everypay_express_details;
        $_SESSION['cart']['payment_id'] = $payment_id;
    } else {
        fn_everypay_get_error($everypay_checkout_details);
    }

    fn_order_placement_routines('checkout_redirect');

} elseif ($mode == 'express') {

    $_SESSION['cart'] = empty($_SESSION['cart']) ? array() : $_SESSION['cart'];

    $payment_id = (empty($_REQUEST['payment_id']) ? $_SESSION['cart']['payment_id'] : $_REQUEST['payment_id']);

    $is_in_context_order = isset($_REQUEST['in_context_order']) && isset($_REQUEST['in_context']);

    $result = fn_everypay_set_express_checkout($payment_id, 0, array(), $_SESSION['cart'], AREA, $is_in_context_order);
    $useraction = 'continue';

    $processor_data = fn_get_payment_method_data($payment_id);
    $in_context_checkout = ($processor_data['processor_params']['in_context'] == 'Y');

    if (fn_everypay_ack_success($result) && !empty($result['TOKEN'])) {

        if ($in_context_checkout && isset($_REQUEST['in_context'])) {
            header('Content-type: application/json');
            echo json_encode(array('token' => $result['TOKEN']));
            exit;
        } else {
            fn_everypay_payment_form($processor_data, $result['TOKEN']);
        }
    } else {
        // create notification
        fn_everypay_get_error($result);

        if ($in_context_checkout && isset($_REQUEST['in_context'])) {
            header('Content-type: application/json');
            echo json_encode(array('error' => true));
            exit;
        } else {
            fn_order_placement_routines('checkout.cart');
        }
    }
}
