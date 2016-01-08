{* $Id$ *}

<div class="control-group">
    <label class="control-label" for="public_key">{__("public_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][public_key]" id="public_key" value="{$processor_params.public_key}" class="input-text" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="secret_key">{__("secret_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][secret_key]" id="secret_key" value="{$processor_params.secret_key}" class="input-text" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>{__("currency_code_eur")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test_mode">{__("test_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test_mode]" id="test_mode">
            <option value="1" {if $processor_params.test_mode == "1"}selected="selected"{/if}>{__("yes")}</option>
            <option value="0" {if $processor_params.test_mode == "0"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="iframe_mode_{$payment_id}">{__("iframe_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][iframe_mode]" id="iframe_mode_{$payment_id}">
            <option value="Y" {if $processor_params.iframe_mode == "Y"}selected="selected"{/if}>{__("enabled")}</option>
            <option value="N" {if $processor_params.iframe_mode == "N"}selected="selected"{/if}>{__("disabled")}</option>
        </select>
    </div>
</div>