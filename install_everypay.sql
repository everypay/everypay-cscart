REPLACE INTO cscart_payment_processors (`processor`,`processor_script`,`processor_template`,`admin_template`,`callback`,`type`) VALUES ('Everypay','everypay.php', 'views/orders/components/payments/cc_outside.tpl','everypay.tpl', 'Y', 'P');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','public_key','Δημόσιο Κλειδί');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','secret_key','Ιδιωτικό Κλειδί');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','test_mode','Δοκιμαστική Λειτουργία');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','text_evp_failed_order','No response from Everypay has been received. Please contact the store staff and tell them the order ID:');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','text_evp_pending','No response from Everypay. Please check the payment using Payment Token on Everypay dashboard. ');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','text_evp_success','Payment Sucessful. You can check the payment under Payments on Everypay dashboard. ');