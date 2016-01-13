{* $Id$ *}
{script src="js/addons/everypay/mustache.min.js"}
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
    <label class="control-label" for="everypay-installments">{__("installments")}:</label>
    <div class="controls">
        <div id="installments"></div>
        <input type="hidden" name="payment_data[processor_params][everypay_installments]" id="everypay-installments" value="{$processor_params.everypay_installments}"/>
    </div>
</div>

<div id="installment-table" style="display:none">
    <table class="table">
        <thead>
            <tr>
                <th>{__("From (€)")}</th>
                <th>{__("To (€)")}</th>
                <th>{__("installments")}</th>
                <th>
                    <a class="btn btn-default" href="#" id="add-installment" style="width:101px;">                        
                        <i class="icon icon-plus-sign"></i> {__("add")}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<!-- Force iframe mode -->
<input type="hidden" name="payment_data[processor_params][iframe_mode]" value="Y">

<style type="text/css">{literal}
    #installments table{max-width: 401px;}
    #installments table input[type="number"] {width: 99px;}
    .remove-installment{color:#ee5f5b}
    {/literal}
    </style>

    <script type="text/javascript">{literal}
        var installments = [];
        var row = "<tr data-id=\"{{id}}\">"
                + "<td><input type=\"number\" step=\"0.01\" min=\"0\" name=\"amount_{{id}}_from\" value=\"{{from}}\" class=\"form-control\" /></td>"
                + "<td><input type=\"number\" step=\"0.01\" min=\"0\" name=\"amount_{{id}}_to\" value=\"{{to}}\" class=\"form-control\" /></td>"
                + "<td><input type=\"number\" step=\"1\" max=\"72\" min=\"0\" name=\"max_{{id}}\" value=\"{{max}}\" class=\"form-control\" /></td>"
                + "<td><a class=\"btn btn-danger remove-installment\" href=\"#\"><i class=\"icon-trash\"></i></a></td>"
                + "</tr>";

        var loadButton = setInterval(function () {
            try {
                var table = $('#installment-table').html();
                Mustache.parse(table);
                var renderedTable = Mustache.render(table, {});
                $('#installments').html(renderedTable);

                var input = $('#everypay-installments').val();
                if (input) {
                    //console.log(input);
                    installments = JSON.parse(input);
                    createElements();
                }

                $('#add-installment').click(function (e) {
                    e.preventDefault();
                    var maxRows = maxElementIndex();

                    Mustache.parse(row);
                    var element = {id: maxRows, from: 0, to: 100, max: 12};
                    var renderedRow = Mustache.render(row, element);
                    $row = $(renderedRow);

                    var max = findMaxAmount();

                    $row.find("input[name$='from']").val((max + 0.01).toFixed(2))
                    $row.find("input[name$='to']").val((parseInt(max.toFixed(0)) + 50).toFixed(2))

                    addInstallment($row);

                    $row.find('input').change(function (e) {
                        addInstallment($(this).parent().parent());
                    });

                    $('#installments table tbody').append($row);
                    $row.find('.remove-installment').click(function (e) {
                        e.preventDefault();
                        removeInstallment($(this).parent().parent());
                        $(this).parent().parent().remove();
                    });
                });
                clearInterval(loadButton);
            } catch (err) {
                //console.log(err);
            }
        }, 500);
        
        var addInstallment = function (row) {
            var element = {
                id: row.attr('data-id'),
                from: row.find('input[name$="from"]').val(),
                to: row.find('input[name$="to"]').val(),
                max: row.find('input[name^="max"]').val(),
            };

            index = elementExists(element.id);
            if (false !== index) {
                installments[index] = element;
            } else {
                installments.push(element);
            }
            $('#everypay-installments').val(JSON.stringify(installments));
        };

        var removeInstallment = function (row) {
            var index = false;
            var id = row.attr('data-id');
            for (var i = 0, l = installments.length; i < l; i++) {
                if (installments[i].id == id) {
                    index = i;
                }
            }

            if (false !== index) {
                installments.splice(index, 1);
            }
            $('#everypay-installments').val(JSON.stringify(installments));
        };

        var elementExists = function (id) {
            for (var i = 0, l = installments.length; i < l; i++) {
                if (installments[i].id == id) {
                    return i;
                }
            }

            return false;
        }

        var maxElementIndex = function (row) {
            var length = $('#installments table tbody tr').length;
            if (0 == length) {
                return 1;
            }

            length = $('#installments table tbody tr:last').attr('data-id');
            length = parseInt(length);

            return length + 1;
        }

        var findMaxAmount = function () {
            var max = 0;
            for (var i = 0, l = installments.length; i < l; i++) {
                if (parseFloat(installments[i].to) > parseFloat(max)) {
                    max = parseFloat(installments[i].to)
                }
            }

            return max;
        }


        var createElements = function () {
            Mustache.parse(row);

            for (var i = 0, l = installments.length; i < l; i++) {
                var element = installments[i];
                var renderedRow = Mustache.render(row, element);
                $row = $(renderedRow);
                $row.find('input').change(function (e) {
                    addInstallment($(this).parent().parent());
                });
                $('#installments table tbody').append($row);
                $row.find('.remove-installment').click(function (e) {
                    e.preventDefault();
                    removeInstallment($(this).parent().parent());
                    $(this).parent().parent().remove();
                });
            }
        }
        {/literal}
        </script>
