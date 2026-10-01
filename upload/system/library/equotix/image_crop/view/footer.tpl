<footer class="mt-auto">
  <div class="container text-center">
    <?php echo $copyright; ?><br>
    <?php echo $opencart; ?> | <?php echo $version; ?>
  </div>
</footer>
<?php if ($license_key) { ?>
<div class="modal" id="modal-license" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-body">
        <table class="table">
          <tr>
            <td><?php echo $text_license_holder; ?></td>
            <td><?php echo $license['name']; ?></td>
          </tr>
          <tr>
            <td><?php echo $text_license_order_id; ?></td>
            <td><?php echo $license['order_id']; ?></td>
          </tr>
          <tr>
            <td><?php echo $text_license_purchased; ?></td>
            <td><?php echo $license['date_purchased']; ?></td>
          </tr>
          <tr>
            <td<?php echo $license['expired'] ? ' class="table-danger"' : ''; ?>><?php echo $text_support_expiry; ?></td>
            <td<?php echo $license['expired'] ? ' class="table-danger"' : ''; ?>><?php echo $license['date_expired']; ?></td>
          </tr>
          <tr>
            <td><?php echo $text_extension_version; ?></td>
            <td><?php echo $license['version']; ?> <span class="badge bg-<?php echo $license['updates'] ? 'danger' : 'success'; ?>"><?php echo $license['updates'] ? $text_updates_available : $text_latest; ?></span></td>
          </tr>
          <tr>
            <td><?php echo $text_licensed_domain; ?></td>
            <td>
              <?php if (isset($license['domains'])) { ?>
              <?php foreach ($license['domains'] as $domain) { ?>
              <i class="fa fa-check"></i>
              <a href="http://<?php echo $domain['domain']; ?>" target="_blank"><?php echo $domain['domain']; ?></a>
              (<?php echo $domain['date_added']; ?>)<br>
              <?php } ?>
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td colspan="2" class="text-center">
              <button type="button" class="btn btn-danger btn-sm" onclick="unlinkLicense('<?php echo $license['license_key']; ?>', this);"><?php echo $button_unlink; ?></button>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
function unlinkLicense(license_key, element) {
    $.ajax({
        url: 'index.php?route=extension/<?php echo $equotix_folder; ?>/<?php echo $equotix_code; ?>/unlinkLicense<?php echo $equotix_token; ?>&license_key=' + license_key,
        type: 'get',
        dataType: 'json',
        beforeSend: function () {
            $(element).prop('disabled', true);
            $(element).html('<i class="fa fa-spinner fa-spin"></i>');
        },
        success: function (json) {
            if (json['success']) {
                alert('<?php echo $text_success_unlink; ?>');

                location.reload();
            } else {
                alert(json['error']);

                $(element).prop('disabled', false);
                $(element).html('<?php echo $button_unlink; ?>');
            }
        }
    });
}
</script>
<?php } else { ?>
<div class="modal" id="modal-license" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <div>
          <label class="mb-2"><?php echo $entry_platform; ?> <a href="https://marketinsg.zendesk.com/hc/en-us/articles/205251588-How-should-I-register-the-extension-I-purchased-" target="_blank"><i class="fa fa-question-circle"></i></a></label>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="license_type" id="input-radio-opencart" value="opencart" checked="checked">
            <label class="form-check-label" for="input-radio-opencart">
              OpenCart
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="license_type" id="input-radio-others" value="others">
            <label class="form-check-label" for="input-radio-others">
              Others
            </label>
          </div>
        </div>
        <div id="license-opencart" style="display:none;">
          <div class="alert alert-info">
            <?php echo $text_opencart_first_time; ?>
          </div>
          <div class="mb-3">
            <label for="input-license-name" class="form-label required" data-bs-toggle="tooltip" title="<?php echo $text_name; ?>"><?php echo $entry_name; ?></label>
            <input type="text" name="name" id="input-license-name" class="form-control" value="">
          </div>
          <div class="mb-3">
            <label for="input-license-email" class="form-label required" data-bs-toggle="tooltip" title="<?php echo $text_email; ?>"><?php echo $entry_email; ?></label>
            <input type="email" name="email" id="input-license-email" class="form-control" value="">
          </div>
          <div class="mb-3">
            <label for="input-license-order-id" class="form-label required" data-bs-toggle="tooltip" title="<?php echo $text_order_id; ?>"><?php echo $entry_order_id; ?></label>
            <input type="text" name="order_id" id="input-license-order-id" class="form-control" value="">
          </div>
          <div class="mb-3">
            <label for="input-license-username" class="form-label required" data-bs-toggle="tooltip" title="<?php echo $text_username; ?>"><?php echo $entry_username; ?></label>
            <input type="text" name="username" id="input-license-username" class="form-control" value="">
          </div>
        </div>
        <div id="license-others" style="display:none;">
          <div class="mb-3">
            <label for="input-license-key" class="form-label required" data-bs-toggle="tooltip" title="<?php echo $text_license_key; ?>"><?php echo $entry_license_key; ?></label>
            <input type="text" name="license_key" id="input-license-key" class="form-control" value="" placeholder="XXXXXXX-XXXXXXX-XXXXXXX-XXXXXXX-XXXXXXX-XXXXXXX">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="button-license-add" class="btn btn-success"><?php echo $button_license_key; ?></button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
let licenseModal = new bootstrap.Modal(document.getElementById('modal-license'), {
    backdrop: 'static',
    keyboard: false
});

licenseModal.show();

$('input[name=\'license_type\']').change(function() {
    if ($('input[name=\'license_type\']:checked').val() == 'opencart') {
        $('#license-opencart').slideDown();
        $('#license-others').slideUp();
    } else {
        $('#license-opencart').slideUp();
        $('#license-others').slideDown();
    }
});

$('input[name=\'license_type\']').trigger('change');

$('#button-license-add').click(function() {
    $.ajax({
        url: 'index.php?route=extension/<?php echo $equotix_folder; ?>/<?php echo $equotix_code; ?>/linkLicense<?php echo $equotix_token; ?>',
        type: 'post',
        data: $('#modal-license input[type=\'text\'], #modal-license input[type=\'email\'], #modal-license input:checked'),
        dataType: 'json',
        beforeSend: function () {
            $('#button-license-add').prop('disabled', true);
            $('#button-license-add').html('<i class="fa fa-spinner fa-spin"></i>');
        },
        success: function (json) {
            $('#button-license-add').prop('disabled', false);
            $('#button-license-add').html('<?php echo $button_license_key; ?>');

            if (json['success']) {
                alert('<?php echo $text_success_register; ?>');

                location.reload();
            } else {
                alert(json['error']);
            }
        }
    });
});
</script>
<?php } ?>
<script type="text/javascript">
let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
});

$(document).on('submit', '[data-eq-toggle=\'ajax-form\']', function(e) {
    e.preventDefault();

    let button = $(this).attr('data-eq-target');
    let button_content = $(button).html();
    let form = $(this);

    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        beforeSend: function () {
            $(button).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        },
        complete: function () {
            $(button).prop('disabled', false).html(button_content);
        },
        success: function (json) {
            $('#alert').html('');
            form.find('.text-danger').remove();
            form.find('.is-invalid').removeClass('is-invalid');

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['success']) {
                $('#alert').html('<div class="alert alert-success alert-dismissible"><i class="fas fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }

            if (typeof json['error'] == 'string') {
                $('#alert').html('<div class="alert alert-danger alert-dismissible"><i class="fas fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }

            if (typeof json['error'] == 'object') {
                if (json['error']['warning']) {
                    $('#alert').html('<div class="alert alert-danger alert-dismissible"><i class="fas fa-exclamation-circle"></i> ' + json['error']['warning'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                }

                $.each(json['error'], function(key, value) {
                    form.find('[name=\'' + key + '\']').parent().append('<div class="text-danger">' + value + '</div>');
                    form.find('[name=\'' + key + '\']').addClass('is-invalid');
                });
            }
        }
    });
});
</script>
</body>
</html>