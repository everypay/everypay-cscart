DELETE FROM ?:payment_processors WHERE addon = 'everypay';
REPLACE INTO ?:payment_processors (`processor`,`processor_script`,`processor_template`,`admin_template`,`callback`,`type`,`addon`) 
VALUES ('Everypay','everypay.php', 'views/orders/components/payments/cc_outside.tpl','everypay.tpl', 'Y', 'P', 'everypay');

REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','public_key','Public Key');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','secret_key','Private Key');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','test_mode','Test Mode');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','text_evp_failed_order','No response from Everypay has been received. Please contact the store staff and tell them the order ID:');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','text_evp_pending','Payment failed! Please check EveryPay dashboard using Payment Token ');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','text_evp_success','Payment Sucessful. You can check the payment under Payments on Everypay dashboard. ');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','From (€)','From (€)');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','To (€)','To (€)');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','Installments','Installments');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','Add','Add');

REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EL','public_key','Δημόσιο Κλειδί');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EL','secret_key','Ιδιωτικό Κλειδί');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EL','test_mode','Δοκιμαστική Λειτουργία');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EL','text_evp_failed_order','Καμία απάντηση από την Everypay. Παρακαλώ επικοινωνήστε με τα κεντρικά γραφεία της εταιρείας και δώστε τους τον κωδικό παραγγελίας: ');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EL','text_evp_pending','Η συναλλαγή απέτυχε! Παρακαλώ ελέγξτε την πληρωμή στον λογαριαμό σας στο Everypay dashboard χρησιμοποιώντας τον κωδικό πληρωμής ');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EL','text_evp_success','Επιτυχής πληρωμή. Μπορείτε να ελέγξτε την πληρωμή στον λογαριαμό σας στο Everypay dashboard. ');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','From (€)','Από (€)');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','To (€)','Εως (€)');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','Installments','Δόσεις');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('EN','Add','Προσθήκη');