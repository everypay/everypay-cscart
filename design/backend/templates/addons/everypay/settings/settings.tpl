<div id="text_paypal_status_map" class="in collapse">
    <h2>Configuration</h2>
    <p><ol>
        <li>Navigate to <a href="{$smarty.server.SCRIPT_NAME}?dispatch=payments.manage" target="_blank">Administration > Payment Methods.</a></li>
        <li>Click the "+" to add a new payment method.</li>
        <li>Choose EveryPay from the list and choose "cc_outside.tpl" in the template filed</li>
        <li>Click the 'Configure' tab.</li>
        <li>Enter your Public Key, Private Key, and choose the test mode (Test or not). You can find these in your settings menu (<a href="https://dashboard.everypay.gr/settings/api-keys" target="_blank">https://dashboard.everypay.gr/settings/api-keys</a>)</li>
        <li>Enter your desired installment ranges, e.g. [0->100:2 installments], [100->500 : 5 installments], etc.</li>
        <li>Click 'Save'</li>
    </ol></p>
</div>