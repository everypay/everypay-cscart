DElETE FROM ?:payment_processors WHERE processor = 'Everypay';
REPLACE INTO ?:payment_processors (`processor`,`processor_script`,`processor_template`,`admin_template`,`callback`,`type`) 
VALUES ('Everypay','everypay.php', 'views/orders/components/payments/cc_outside.tpl','everypay.tpl', 'Y', 'P');

REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','public_key','Public Key');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','secret_key','Private Key');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','test_mode','Test Mode');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','text_evp_failed_order','No response from Everypay has been received. Please contact the store staff and tell them the order ID:');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','text_evp_pending','Payment failed! Please check EveryPay dashboard using Payment Token ');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','text_evp_success','Payment Sucessful. You can check the payment under Payments on Everypay dashboard. ');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','From (€)','From (€)');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','To (€)','To (€)');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','installments','Installments');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','add','Add');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('en','ttc_installments_everypay','Choose max installments number, according to the total amount of the order');

REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','public_key','Δημόσιο Κλειδί');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','secret_key','Ιδιωτικό Κλειδί');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','test_mode','Δοκιμαστική Λειτουργία');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','text_evp_failed_order','Καμία απάντηση από την Everypay. Παρακαλώ επικοινωνήστε με τα κεντρικά γραφεία της εταιρείας και δώστε τους τον κωδικό παραγγελίας: ');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','text_evp_pending','Η συναλλαγή απέτυχε! Παρακαλούμε βεβαιωθείτε οτι τα στοιχεία της κάρτας είναι σωστά και οτι η κάρτα αυτή έχει επαρκές υπόλοιπο.');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','text_evp_success','Επιτυχής πληρωμή. Μπορείτε να ελέγξτε την πληρωμή στον λογαριαμό σας στο Everypay dashboard. ');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','From (€)','Από (€)');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','To (€)','Εως (€)');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','installments','Δόσεις');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','ttc_installments_everypay','Επιλέξτε το πλήθος μέγιστων δόσεων, ανάλογα με το ύψος της παραγγελίας');
REPLACE INTO ?:language_values (`lang_code`,`name`,`value`) VALUES ('el','add','Προσθήκη');