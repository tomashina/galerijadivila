<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    &gt; <a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><button type="button" class="btn btn-warning" id="clear_cache"><?php echo $text_clear_webp_cache; ?></button><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td>
              <select name="webp_image_status" id="input-status" class="form-control">
                <?php if ($webp_image_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_quality; ?></td>
            <td>
              <input type="text" class="form-control" name="webp_image_quality" value="<?php echo $webp_image_quality; ?>" placeholder="90">
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script>
  var token = '<?php echo $token; ?>';
  $("#clear_cache").on('click', function(){
    Swal.fire({
      title: 'Are you sure?',
      text: "Delete all cached images?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes'
    }).then((result) => {
      if (result.value) {
        $.post('index.php?route=module/webp_image/clearWebpCache&'+token, {}, function(data){
          Swal.fire(
            'Deleted!',
            'Removed '+data.count+' files.',
            'success'
          )
        });
      }
    });
    
  });
</script>
<?php echo $footer; ?>