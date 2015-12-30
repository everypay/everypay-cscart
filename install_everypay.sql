REPLACE INTO cscart_payment_processors (`processor`,`processor_script`,`processor_template`,`admin_template`,`callback`,`type`) VALUES ('Everypay','everypay.php', 'views/orders/components/payments/cc_outside.tpl','everypay.tpl', 'Y', 'P');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','public_key','Public Key');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','secret_key','Secret Key');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','mode','Mode');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','text_everypay_failed_order','No response from Everypay has been received. Please contact the store staff and tell them the order ID:');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','text_everypay_pending','No response from Everypay. Please check the payment using the Token on everypay dashboard. ');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','text_everypay_success','Payment Sucessful. You can check the payment using Payment Token on everypay dashboard. ');