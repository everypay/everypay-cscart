/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function (_, $) {
    $(document).ready(function () {
        var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
        var eventer = window[eventMethod];
        var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

        // Listen to message from child window
        eventer(messageEvent, function (e) {
            var key = e.message ? "message" : "data";
            var data = e[key];
            //console.log(data);
            var myData = data.split("init_everypay:");
            if (myData.length == 2) {
                init_button(myData[1]);
            }
        }, false);

        add_everypay_css();
    });
    
    function add_everypay_css(){
        var my_css = '.payments-form .everypay-button {float: right;font-size: 1.3em !important;margin: 21px;padding: 15px 20px !important}';
        var head = document.head || document.getElementsByTagName('head')[0];
        var my_style = document.createElement('style');

        my_style.type = 'text/css';
        if (my_style.styleSheet){
            my_style.styleSheet.cssText = my_css;
        } else {
            my_style.appendChild(document.createTextNode(my_css));
        }

        head.appendChild(my_style);
    }
    
    function init_button(INIT_DATA) {
        add_everypay_scripts();
        var $form = Tygh.$('.payments-form');
        var $iframe = $form.find('iframe');
        var $iframe_wrapper = $form.find('.ty-payment-method-iframe__box');
        
        /*
        Tygh.$($form).bind('submit', function (){
            alert(1);
             if (Tygh.$('.everypay-button.disabled').length){
                 alert(2);
                Tygh.$('.everypay-button.disabled')
                    .after('<div class="ep-loading" style="float: right;font-size:1.2em;\n\
                    margin-top: 20px;">Processing. Please wait...</div>');
            }
        })*/
        
        var loadButton = setInterval(function () {
            try {
                $iframe.hide();
                $iframe_wrapper.hide();
                var DATA = Tygh.$.parseJSON(INIT_DATA);                
                EverypayButton.jsonInit(DATA, $form);
                clearInterval(loadButton);
            } catch (err) {
                //console.log(err);
            }
        }, 301);
    }
    
    var ADDED_BUTTON_JS = false;
    function add_everypay_scripts() {
        if(ADDED_BUTTON_JS){
            return;
        }     
        var head = document.head || document.getElementsByTagName('head')[0];
        var $btn_script = document.createElement('script');
        $btn_script.type = 'text/javascript';
        $btn_script.src = 'https://button.everypay.gr/js/button.js';
        head.appendChild($btn_script);
        ADDED_BUTTON_JS = true;
    }
})(Tygh, Tygh.$);