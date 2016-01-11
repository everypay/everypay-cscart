var installments = [];
var row = "<tr data-id=\"{{id}}\">"
+"<td><input type=\"text\" name=\"amount_{{id}}_from\" value=\"{{from}}\" class=\"form-control\" /></td>"
+"<td><input type=\"text\" name=\"amount_{{id}}_to\" value=\"{{to}}\" class=\"form-control\" /></td>"
+"<td><input type=\"text\" name=\"max_{{id}}\" value=\"{{max}}\" class=\"form-control\" /></td>"
+"<td><a class=\"btn btn-danger remove-installment\" href=\"#\"><i class=\"fa fa-minus-circle\"></i></a></td>"
+"</tr>";
$(function() {
    //console.log("installments.js loaded all right 27");
    var table = Tygh.$('#installment-table').html();
    //console.log(table);
    Mustache.parse(table);
    var renderedTable = Mustache.render(table, {});
    Tygh.$('#installments').html(renderedTable);

    var input = $('#everypay-installments').val();
    if (input) {
        //alert(input);
        installments = JSON.parse(input);
        createElements();
    }

    Tygh.$('#add-installment').click(function (e) {
        e.preventDefault();
        var maxRows = maxElementIndex();

        //var row = Tygh.$('#installment-row').html();
        //var row = "<tr data-id=\"{{id}}\">"
//+"<td><input type=\"text\" name=\"amount_{{id}}_from\" value=\"{{from}}\" class=\"form-control\" /></td>"
//+"<td><input type=\"text\" name=\"amount_{{id}}_to\" value=\"{{to}}\" class=\"form-control\" /></td>"
//+"<td><input type=\"text\" name=\"max_{{id}}\" value=\"{{max}}\" class=\"form-control\" /></td>"
//+"<td><a class=\"btn btn-danger remove-installment\" href=\"#\"><i class=\"fa fa-minus-circle\"></i></a></td>"
//+"</tr>";
        //console.log(row);
        Mustache.parse(row);
        var element = {id: maxRows, from: 0, to: 100, max: 12};
        var renderedRow = Mustache.render(row, element);
        $row = Tygh.$(renderedRow);
        addInstallment($row);
        $row.find('input').change(function (e){
            //console.log("hey<br/>");
            addInstallment(Tygh.$(this).parent().parent());
        });
        //console.log("go on");
        Tygh.$('#installments table tbody').append($row);
        $row.find('.remove-installment').click(function (e){
            e.preventDefault();
            removeInstallment(Tygh.$(this).parent().parent());
            Tygh.$(this).parent().parent().remove();
        });
    });
});

var addInstallment = function (row) {
    var element = {
        id: row.attr('data-id'),
        from: row.find('input[name$="from"]').val(),
        to:  row.find('input[name$="to"]').val(),
        max:  row.find('input[name^="max"]').val(),
    };

    index = elementExists(element.id);
    if (false !== index) {
        installments[index] = element;
    } else {
        installments.push(element);
    }
    Tygh.$('#everypay-installments').val(JSON.stringify(installments));
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
    Tygh.$('#everypay-installments').val(JSON.stringify(installments));
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

var createElements = function () {
    //var row = Tygh.$('#installment-row').html();
    Mustache.parse(row);
    for (var i = 0, l = installments.length; i < l; i++) {
        var element = installments[i];
        var renderedRow = Mustache.render(row, element);
        $row = Tygh.$(renderedRow);
        $row.find('input').change(function (e){
            addInstallment($(this).parent().parent());
        });
        Tygh.$('#installments table tbody').append($row);
        $row.find('.remove-installment').click(function (e){
            e.preventDefault();
            removeInstallment(Tygh.$(this).parent().parent());
            Tygh.$(this).parent().parent().remove();
        });
    }
}
