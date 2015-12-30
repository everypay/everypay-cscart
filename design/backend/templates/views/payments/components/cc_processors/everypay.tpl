{* $Id$ *}

<div class="form-field"> 
    <label for="key_id">{__("public_key")}:</label>
    <input type="text" name="payment_data[processor_params][public_key]" id="public_key" value="{$processor_params.public_key}" class="input-text" />
</div>
<div class="form-field">
    <label for="key_secret">{__("secret_key")}:</label>
    <input type="text" name="payment_data[processor_params][secret_key]" id="secret_key" value="{$processor_params.secret_key}" class="input-text" />
</div>
<div class="form-field">
    <label for="mode">{__("mode")}:</label>
    <select name="payment_data[processor_params][mode]">
        <option {if $processor_params.mode == "LIVE"}selected="selected"{/if}>LIVE</option>
        <option {if $processor_params.mode == "SANDBOX"}selected="selected"{/if}>SANDBOX</option>
        </select>
    <input type="text" name="payment_data[processor_params][mode]" id="mode" value="{$processor_params.mode}" class="input-text" />
</div>
<div class="form-field">
    <label for="currency">{__("currency")}:</label>
    <select name="payment_data[processor_params][currency]" id="currency">
        <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>{__("currency_code_eur")}</option>
    </select>
</div>
<div class="form-field">
    <label for="iframe_mode_{$payment_id}">{__("iframe_mode")}:</label>
    <select name="payment_data[processor_params][iframe_mode]" id="iframe_mode_{$payment_id}">
        <option value="Y" selected="selected">{__("enabled")}</option>
        <option value="N" >{__("disabled")}</option>
    </select> 
</div>