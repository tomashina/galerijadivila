jQuery(function ($) {
    $(document).ready(function () {
        $(document).delegate('a[data-toggle=\'attachmanager\']', 'click', function (e) {
            e.preventDefault();

            var element = this;

            $(element).popover({
                html: true,
                placement: 'right',
                trigger: 'manual',
                content: function () {
                    return '<button type="button" id="button-object" class="btn btn-primary"><i class="fa fa-pencil"></i></button> <button type="button" id="button-clear" class="btn btn-danger"><i class="fa fa-trash-o"></i></button>';
                }
            });

            $(element).popover('toggle');

            $('#button-object').on('click', function () {
                $('#modal-object').remove();
                var di_url = '';
                var current_image = $(this).closest('td').children('input').val();
                if (current_image !== '') {
                    var directories = current_image.split('/');
                    var directory = current_image.replace('/' + directories[directories.length - 1], '');
                    di_url = '&directory=' + encodeURIComponent(directory);
                }

                $.ajax({
                    url: 'index.php?route=extension/module/mmos_popup_attachmanager&user_token=' + getURLVar('user_token') + '&target=' + $(element).parent().find('input').attr('id') + '&thumb=' + $(element).attr('id'),
                    dataType: 'html',
                    beforeSend: function () {
                        $('#button-object i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
                        $('#button-object').prop('disabled', true);
                    },
                    complete: function () {
                        $('#button-object i').replaceWith('<i class="fa fa-upload"></i>');
                        $('#button-object').prop('disabled', false);
                    },
                    success: function (html) {
                        $('body').append('<div id="modal-object" class="modal">' + html + '</div>');

                        $('#modal-object').modal('show');
                    }
                });

                $(element).popover('hide');
            });

            $('#button-clear').on('click', function () {
                $(element).find('img').attr('src', $(element).find('img').attr('data-placeholder'));

                $(element).parent().find('input').attr('value', '');

                $(element).popover('hide');
            });
        });
    });
});