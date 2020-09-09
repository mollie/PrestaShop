$('table.table tr td:nth-child(1)').each(function () {
        var $textField = $(this);
        var $input = $($textField.next().next()).find(':input');
        $input.val($textField.html())
    }
)
