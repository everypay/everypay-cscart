(function (_, $) {
    function add_everypay_css() {
        if (Tygh.$('#everypay-css').length){
            return;
        }
        var my_css = '.payments-form .everypay-button {float: right;font-size: 1.3em !important;margin: 21px;padding: 15px 20px !important}';
        var head = document.head || document.getElementsByTagName('head')[0];
        var my_style = document.createElement('style');
        my_style.id = 'everypay-css';
        my_style.type = 'text/css';
        if (my_style.styleSheet) {
            my_style.styleSheet.cssText = my_css;
        } else {
            my_style.appendChild(document.createTextNode(my_css));
        }

        head.appendChild(my_style);
    }

    function init_button(INIT_DATA) {
        add_everypay_scripts();
        add_everypay_css();
        
        var $form = jQuery('.payments-form:visible');
        var $iframe = $form.find("iframe[id^='order_iframe']");
        $form.append('<input type="hidden" value="1" name="dispatch[checkout.place_order]">');

        var loadButton = setInterval(function () {
            try {
                $iframe.hide();
                $iframe.parent().hide();
                EverypayButton.jsonInit(INIT_DATA, $form);
                clearInterval(loadButton);
            } catch (err) {
                //console.log(err);
            }
        }, 301);
    }

    
    function add_everypay_scripts() {
        if (Tygh.$('#everypay-javascript').length){
            return;
        }
        var head = document.head || document.getElementsByTagName('head')[0];
        var $btn_script = document.createElement('script');
        $btn_script.id = 'everypay-javascript';
        $btn_script.type = 'text/javascript';
        $btn_script.src = 'https://button.everypay.gr/js/button.js';
        head.appendChild($btn_script);
    }

    init_button(EVERYPAY_DATA);
}(Tygh, Tygh.$));

