$(document).ready(function () {
    $('[data-click-input-name]').on('click', function () {
        $('[name="' + $(this).attr('data-click-input-name') + '"]').val($(this).attr('data-click-input-value'));
    });
    $('[data-submit-on-click]').on('click', function () {
        $(this).parents('form').submit();
    });
    $('[data-submit-on-change]').on('change', function () {
        $(this).parents('form').submit();
    });
    $('[data-waterfall-to]').on('change', function () {
        var target = $($(this).attr('data-waterfall-to'));
        var val = $(this).find('option:selected').attr('data-waterfall-value');
        if (val) {
            target.show();
            target.find('option[data-waterfall-value]').hide();
            target.find('option[data-waterfall-value="' + val + '"]').show();
            var opt = target.find("option:selected");
            if (opt.attr('data-waterfall-value') != val) {
                opt.prop("selected", false);
            }
        }
        else {
            target.hide();
        }
    });
    $('[data-waterfall-to]').trigger('change');
    $('[data-expando]').on('click', function () {
        var target = $($(this).attr('data-expando'));
        target.toggle();
        target.removeClass('hide');
        if (target.is(':visible')) {
            $(this).text('less');
        }
        else {
            $(this).text('more');
        }
    });
});
