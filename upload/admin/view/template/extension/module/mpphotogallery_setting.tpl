<?php echo $header; ?><?php echo $column_left; ?>
<div id="content" class="m-menu-wrap">
  <div class="container-fluid">
    <div class="page-header">
      <h1><i class="fa fa-file-image-o"></i> <?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
      <div class="text-right">
        <button type="submit" form="form-account" class="btn btn-success"><i class="fa fa-save"></i> <?php echo $button_save; ?></button>
        <a href="<?php echo $cancel; ?>" class="btn btn-default"><i class="fa fa-times"></i> <?php echo $button_cancel; ?></a>
      </div>
    </div>
    <?php echo $mtabs; ?>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> <?php echo $success; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div>
    <?php } ?>

    <div class="alert alert-info"><i class="fa fa-info-circle info-collapse" data-toggle="collapse" data-target="#list-layouts"></i> <?php echo $text_add_layouts; ?>
      <ul class="list list-unstyled list-layouts collapse" id="list-layouts">
        <?php foreach ($layouts_info as $layout_info) { ?>
        <li><strong class="label <?php if ($layout_info['exists']) { ?>label-success<?php } else { ?>label-danger<?php } ?>"><?php echo $text_layout_name; ?></strong>&nbsp;<?php echo $layout_info['name']; ?></li>
        <li><strong><?php echo $text_layout_route; ?></strong> <?php echo $layout_info['path']; ?></li>
        <?php } ?>
      </ul>
    </div>
    <?php /* // 17-march-2023: improvements start */ ?>
    <?php if ($disable_events) { ?>
    <div class="activate_evs">
      <div class="alert alert-warning"><?php echo $text_disable_events; ?></div>
      <script type="text/javascript">
        $('.mpphotogallery_setting_activate_evs').on('click', function() {
          let $this = $(this);
          $.ajax({
            url: 'index.php?route=<?php echo $extension_path; ?>module/mpphotogallery_setting/activateEvents&<?php echo $get_token; ?>=<?php echo $token; ?>',
            type: 'get',
            data: 'ae=1',
            dataType: 'json',
            beforeSend: function() {
              $('.alert-dismissible').remove();
              $this.button('loading');
            },
            complete: function() {
              $this.button('reset');
            },
            success: function(json) {
              if (json['success']) {
                $this.parent('.alert').after('<div class="alert alert-success alert-dismissible"><i class="fa fa-exclamation-circle"></i> '+ json['success'] +' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                setTimeout(() => {
                  $('.activate_evs').remove();
                }, 5000);
              }
              if (json['error']) {
                if (json['error']['warning']) {
                  $this.parent('.alert').after('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> '+ json['error']['warning'] +' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }
              }
            },
            error: function(xhr, ajaxOptions, thrownError) {
              if (xhr.responseText) {
              }
            }
          });
        });
      </script>
    </div>
    <?php } ?>
    <?php /* // 17-march-2023: improvements end */ ?>
    <?php if ($layouts) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $text_add_layouts; ?> <button type="button" class="close" data-dismiss="alert">&times;</button> <button type="button" class="btn btn-primary" id="addlayouts"><i class="fa fa-plus-circle" data-class="fa fa-plus-circle"></i> <?php echo $button_add_layouts; ?></button></div>
    <script type="text/javascript">
    $('#addlayouts').on('click', function() {
      var el = this;
      $.ajax({
        url: 'index.php?route=<?php echo $extension_path; ?>module/mpphotogallery_setting/updateLayouts&<?php echo $get_token; ?>=<?php echo $token; ?>',
        type: 'get',
        data: '',
        dataType: 'json',
        beforeSend: function() {
          $(el).attr('disabled','disabled');
          $(el).find('i').attr('class', 'fa fa-refresh fa-spin');
        },
        complete: function() {
          $(el).removeAttr('disabled');
          $(el).find('i').attr('class', $(el).find('i').attr('data-class'));
        },
        success: function(json) {
          $('.alert-dismissible, .text-danger').remove();

          if (json['redirect']) {
            location = json['redirect'];
          }

          if (json['success']) {
            $('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
        }
        },
        error: function(xhr, ajaxOptions, thrownError) {
          console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    });
    </script>
    <?php } ?>
    <?php /* // 17-march-2023: improvements start */ ?>
    <?php if ($dump) { ?>
      <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> <?php echo $text_mpphotogallery_dump; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <br/>
        <?php echo $text_mpphotogallery_info_dump; ?>
        <br/>
        <button type="button" class="btn btn-primary" id="adddump"><i class="fa fa-plus-circle" data-class="fa fa-plus-circle"></i> <?php echo $button_mpphotogallery_dump; ?></button>
      </div>
      <script type="text/javascript">
        $('#adddump').on('click', function() {
          if (confirm(`<?php echo $text_mpphotogallery_alert_dump; ?>`)) {
            var button = this;
            $.ajax({
              url: 'index.php?route=<?php echo $extension_path; ?>module/mpphotogallery_setting/addDump&<?php echo $get_token; ?>=<?php echo $token; ?>',
              type: 'get',
              data: '',
              dataType: 'json',
              beforeSend: function() {
                $(button).attr('disabled','disabled');
                $(button).find('i').attr('class', 'fa fa-refresh fa-spin');
              },
              complete: function() {
                $(button).removeAttr('disabled');
                $(button).find('i').attr('class', $(button).find('i').attr('data-class'));
              },
              success: function(json) {
                $('.alert-dismissible, .text-danger').remove();

                if (json['redirect']) {
                  location = json['redirect'];
                }

                if (json['success']) {
                  $('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }
              },
              error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
              }
            });

          }

        });
      </script>
    <?php } ?>
    <?php /* // 17-march-2023: improvements end */ ?>
    <?php if ($files) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $text_files_permission; ?> <button type="button" class="close" data-dismiss="alert">&times;</button><button type="button" class="btn btn-primary" id="addfiles"><i class="fa fa-plus-circle" data-class="fa fa-plus-circle"></i> <?php echo $button_files_permission; ?></button></div>
    <script type="text/javascript">
      $('#addfiles').on('click', function() {
        var button = this;
        $.ajax({
          url: 'index.php?route=<?php echo $extension_path; ?>module/mpphotogallery_setting/updatePermissions&<?php echo $get_token; ?>=<?php echo $token; ?>',
          type: 'get',
          data: '',
          dataType: 'json',
          beforeSend: function() {
            $(button).attr('disabled','disabled');
            $(button).find('i').attr('class', 'fa fa-refresh fa-spin');
          },
          complete: function() {
            $(button).removeAttr('disabled');
            $(button).find('i').attr('class', $(button).find('i').attr('data-class'));
          },
          success: function(json) {
            $('.alert-dismissible, .text-danger').remove();

            if (json['redirect']) {
              location = json['redirect'];
            }

            if (json['success']) {
              $('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
            }
          },
          error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
        });
      });
    </script>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-cog"></i> <?php echo $text_edit; ?></h3>
        <div class="pull-right">
          <label class="control-label"><?php echo $entry_store; ?></label>
          <select onchange="location=this.value">
            <?php foreach ($stores as $store) { ?>
            <option value="<?php echo $store['href']; ?>" <?php if ($store_id == $store['store_id']) { ?>selected="selected"<?php } ?>><?php echo $store['name']; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="panel-body">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $text_gsetting; ?></a></li>
          <li><a href="#tab-seo" data-toggle="tab"><?php echo $text_seo; ?></a></li>
          <li><a href="#tab-colors" data-toggle="tab"><?php echo $text_colors; ?></a></li>
          <!-- // gallery for product task starts -->
          <li><a href="#tab-product" data-toggle="tab"><?php echo $text_product; ?></a></li>
          <!-- // gallery for product task ends -->
        </ul>

        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-account" class="form-horizontal">
          <div class="tab-content">
            <div class="tab-pane active" id="tab-general">
            <fieldset>
              <h3><?php echo $fieldset_general; ?></h3>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_status)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_status" value="1" <?php if (!empty($module_mpphotogallery_status)) { ?>checked="checked"<?php } ?> /> <?php echo $text_enabled; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_status)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_status" value="0" <?php if (empty($module_mpphotogallery_status)) { ?>checked="checked"<?php } ?> /> <?php echo $text_disabled; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_social_status; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_social_status)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_social_status" value="1" <?php if (!empty($module_mpphotogallery_social_status)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_social_status)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_social_status" value="0" <?php if (empty($module_mpphotogallery_social_status)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_menu_at; ?></label>
                <div class="col-sm-10">
                  <div class="checkbox">
                    <?php foreach ($menu_at as $menuat) { ?>
                    <div class="checkbox-inline">
                      <label><input type="checkbox" name="module_mpphotogallery_menu_at[]" value="<?php echo $menuat['value']; ?>" <?php if (in_array($menuat['value'], $module_mpphotogallery_menu_at)) { ?>checked="checked"<?php } ?> /> <?php echo $menuat['text']; ?></label>
                    </div>
                   <?php } ?>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_menu_at_header; ?></label>
                <div class="col-sm-10">
                  <div class="radio">
                    <?php foreach ($gallery_menus_at as $gallery_menu_at) { ?>
                    <div class="radio-inline">
                      <label><input type="radio" name="module_mpphotogallery_menu_at_header" value="<?php echo $gallery_menu_at['value']; ?>" <?php if ($gallery_menu_at['value'] == $module_mpphotogallery_menu_at_header) { ?>checked="checked"<?php } ?> /> <?php echo $gallery_menu_at['text']; ?></label>
                    </div>
                   <?php } ?>
                  </div>
                </div>
              </div>
              <div class="form-group gallery_album_header " id="gallery_album_header" style="<?php if ($module_mpphotogallery_menu_at_header != 'selected_album') { ?>display: none;<?php } ?>">
                <label class="col-sm-2 control-label"><?php echo $entry_menu_at_header; ?></label>
                <div class="col-sm-10">
                  <select name="module_mpphotogallery_menuheader_album" class="form-control">
                    <option value=""><?php echo $text_select; ?></option>
                    <?php foreach ($mpgallery_albums as $mpgallery_album) { ?>
                    <option value="<?php echo $mpgallery_album['mpgallery_id']; ?>" <?php if ($module_mpphotogallery_menuheader_album == $mpgallery_album['mpgallery_id']) { ?>selected="selected"<?php } ?>><?php echo $mpgallery_album['title']; ?> - <?php echo $mpgallery_album['status']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_menu_at_footer; ?></label>
                <div class="col-sm-10">
                  <div class="radio">
                    <?php foreach ($gallery_menus_at as $gallery_menu_at) { ?>
                    <div class="radio-inline">
                      <label><input type="radio" name="module_mpphotogallery_menu_at_footer" value="<?php echo $gallery_menu_at['value']; ?>" <?php if ($gallery_menu_at['value'] == $module_mpphotogallery_menu_at_footer) { ?>checked="checked"<?php } ?> /> <?php echo $gallery_menu_at['text']; ?></label>
                    </div>
                   <?php } ?>
                  </div>
                </div>
              </div>
              <div class="form-group gallery_album_footer" style="<?php if ($module_mpphotogallery_menu_at_footer != 'selected_album') { ?>display: none;<?php } ?>" id="gallery_album_footer" >
                <label class="col-sm-2 control-label"><?php echo $entry_menu_at_footer; ?></label>
                <div class="col-sm-10">
                  <select name="module_mpphotogallery_menufooter_album" class="form-control">
                    <option value=""><?php echo $text_select; ?></option>
                    <?php foreach ($mpgallery_albums as $mpgallery_album) { ?>
                    <option value="<?php echo $mpgallery_album['mpgallery_id']; ?>" <?php if ($module_mpphotogallery_menufooter_album == $mpgallery_album['mpgallery_id']) { ?>selected="selected"<?php } ?>><?php echo $mpgallery_album['title']; ?> - <?php echo $mpgallery_album['status']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-popup_width"><?php echo $entry_popup; ?></label>
                <div class="col-sm-10">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_popup_width" value="<?php echo $module_mpphotogallery_popup_width; ?>" placeholder="<?php echo $entry_width; ?>" id="input-popup_width" class="form-control" />
                        <span class="input-group-btn">
                        <button type="button" class="btn btn-primary"><i class="fa fa-arrows-h"></i></button>
                        </span>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_popup_height" value="<?php echo $module_mpphotogallery_popup_height; ?>" placeholder="<?php echo $entry_height; ?>" id="input-popup_height" class="form-control" />
                        <span class="input-group-btn">
                        <button type="button" class="btn btn-primary"><i class="fa fa-arrows-v"></i></button>
                        </span></div>
                    </div>
                  </div>
                  <span class="help"><?php echo $help_popup; ?></span>
                  <?php if ($error_popup_size) { ?>
                  <div class="text-danger"><?php echo $error_popup_size; ?></div>
                  <?php } ?>
                </div>
              </div>
              <!-- // 07-05-2022: updation task start -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_album_photo; ?></label>
                <div class="col-sm-10">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_albumphoto_width" value="<?php echo $module_mpphotogallery_albumphoto_width; ?>" placeholder="<?php echo $entry_width; ?>" class="form-control" />
                        <span class="input-group-btn"><button type="button" class="btn btn-primary"><i class="fa fa-arrows-h"></i></button></span>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_albumphoto_height" value="<?php echo $module_mpphotogallery_albumphoto_height; ?>" placeholder="<?php echo $entry_height; ?>" class="form-control" />
                        <span class="input-group-btn"><button type="button" class="btn btn-primary"><i class="fa fa-arrows-v"></i></button></span>
                      </div>
                    </div>
                  </div>
                  <span class="help"><?php echo $help_album_photo; ?></span>
                  <?php if ($error_albumphoto_size) { ?>
                  <div class="text-danger"><?php echo $error_albumphoto_size; ?></div>
                  <?php } ?>
                </div>
              </div>
              <!-- // 07-05-2022: updation task end -->
            </fieldset>
            <br/><br/><br/><br/>
            <fieldset>
              <h3><?php echo $fieldset_album_page; ?></h3>
              <div class="form-group hide mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_album_description; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_album_description)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_album_description" value="1" <?php if (!empty($module_mpphotogallery_album_description)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_album_description)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_album_description" value="0" <?php if (empty($module_mpphotogallery_album_description)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_album_limit; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="module_mpphotogallery_album_limit" value="<?php echo $module_mpphotogallery_album_limit; ?>" placeholder="<?php echo $entry_album_limit; ?>"class="form-control" />
                  <?php if ($error_album_limit) { ?>
                  <div class="text-danger"><?php echo $error_album_limit; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_album; ?></label>
                <div class="col-sm-10">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_album_width" value="<?php echo $module_mpphotogallery_album_width; ?>" placeholder="<?php echo $entry_width; ?>" class="form-control" />
                        <span class="input-group-btn"><button type="button" class="btn btn-primary"><i class="fa fa-arrows-h"></i></button></span>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_album_height" value="<?php echo $module_mpphotogallery_album_height; ?>" placeholder="<?php echo $entry_height; ?>" class="form-control" />
                        <span class="input-group-btn"><button type="button" class="btn btn-primary"><i class="fa fa-arrows-v"></i></button></span>
                      </div>
                    </div>
                  </div>
                  <span class="help"><?php echo $help_album; ?></span>
                  <?php if ($error_album_size) { ?>
                  <div class="text-danger"><?php echo $error_album_size; ?></div>
                  <?php } ?>
                </div>
              </div>
            </fieldset>
            <br/><br/><br/><br/>
            <fieldset>
              <h3><?php echo $fieldset_photo_page; ?></h3>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_cursive_font; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_photo_cursive_font)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_photo_cursive_font" value="1" <?php if (!empty($module_mpphotogallery_photo_cursive_font)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_photo_cursive_font)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_photo_cursive_font" value="0" <?php if (empty($module_mpphotogallery_photo_cursive_font)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_show_videos; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_show_videos)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_show_videos" value="1" <?php if (!empty($module_mpphotogallery_show_videos)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_show_videos)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_show_videos" value="0" <?php if (empty($module_mpphotogallery_show_videos)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
            </fieldset>
            <br/><br/><br/><br/>
            <fieldset>
              <h3><?php echo $fieldset_albumn_photo; ?></h3>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_cursive_font; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_albumphoto_cursive_font)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_albumphoto_cursive_font" value="1" <?php if (!empty($module_mpphotogallery_albumphoto_cursive_font)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_albumphoto_cursive_font)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_albumphoto_cursive_font" value="0" <?php if (empty($module_mpphotogallery_albumphoto_cursive_font)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_albumphoto_description; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_albumphoto_description)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_albumphoto_description" value="1" <?php if (!empty($module_mpphotogallery_albumphoto_description)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_albumphoto_description)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_albumphoto_description" value="0" <?php if (empty($module_mpphotogallery_albumphoto_description)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_albumphoto_limit; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="module_mpphotogallery_albumphoto_limit" value="<?php echo $module_mpphotogallery_albumphoto_limit; ?>" placeholder="<?php echo $entry_albumphoto_limit; ?>" class="form-control" />
                  <?php if ($error_albumphoto_limit) { ?>
                  <div class="text-danger"><?php echo $error_albumphoto_limit; ?></div>
                  <?php } ?>
                </div>
              </div>
            </fieldset>
            <fieldset>
              <h3><?php echo $fieldset_albumn_video; ?></h3>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_cursive_font; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_albumvideo_cursive_font)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_albumvideo_cursive_font" value="1" <?php if (!empty($module_mpphotogallery_albumvideo_cursive_font)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_albumvideo_cursive_font)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_albumvideo_cursive_font" value="0" <?php if (empty($module_mpphotogallery_albumvideo_cursive_font)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_albumvideo_description; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_albumvideo_description)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_albumvideo_description" value="1" <?php if (!empty($module_mpphotogallery_albumvideo_description)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_albumvideo_description)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_albumvideo_description" value="0" <?php if (empty($module_mpphotogallery_albumvideo_description)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_albumvideo_limit; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="module_mpphotogallery_albumvideo_limit" value="<?php echo $module_mpphotogallery_albumvideo_limit; ?>" placeholder="<?php echo $entry_albumphoto_limit; ?>" class="form-control" />
                  <?php if ($error_albumvideo_limit) { ?>
                  <div class="text-danger"><?php echo $error_albumvideo_limit; ?></div>
                  <?php } ?>
                </div>
              </div>
              <!-- // 07-05-2022: updation task start -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_album_video; ?></label>
                <div class="col-sm-10">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_albumvideo_width" value="<?php echo $module_mpphotogallery_albumvideo_width; ?>" placeholder="<?php echo $entry_width; ?>" class="form-control" />
                        <span class="input-group-btn"><button type="button" class="btn btn-primary"><i class="fa fa-arrows-h"></i></button></span>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_albumvideo_height" value="<?php echo $module_mpphotogallery_albumvideo_height; ?>" placeholder="<?php echo $entry_height; ?>" class="form-control" />
                        <span class="input-group-btn"><button type="button" class="btn btn-primary"><i class="fa fa-arrows-v"></i></button></span>
                      </div>
                    </div>
                  </div>
                  <span class="help"><?php echo $help_album_video; ?></span>
                  <?php if ($error_albumvideo_size) { ?>
                  <div class="text-danger"><?php echo $error_albumvideo_size; ?></div>
                  <?php } ?>
                </div>
              </div>
              <!-- // 07-05-2022: updation task end -->
            </fieldset>
            </div>
            <!-- // gallery for product task starts -->
            <div class="tab-pane" id="tab-product">
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_galleryproduct_status)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_status" value="1" <?php if (!empty($module_mpphotogallery_galleryproduct_status)) { ?>checked="checked"<?php } ?> /> <?php echo $text_enabled; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_galleryproduct_status)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_status" value="0" <?php if (empty($module_mpphotogallery_galleryproduct_status)) { ?>checked="checked"<?php } ?> /> <?php echo $text_disabled; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label"><?php echo $entry_product_title; ?></label>
                <div class="col-sm-10">
                  <?php foreach ($languages as $language) { ?>
                  <div class="input-group">
                    <span class="input-group-addon"><img src="<?php echo $language['lang_flag']; ?>" title="<?php echo $language['name']; ?>" /></span>
                    <input type="text" name="module_mpphotogallery_galleryproduct_description[<?php echo $language['language_id']; ?>][title]" value="<?php if (isset($module_mpphotogallery_galleryproduct_description[$language['language_id']])) { ?><?php echo $module_mpphotogallery_galleryproduct_description[$language['language_id']]['title']; ?><?php } ?>" placeholder="<?php echo $entry_product_title; ?>" class="form-control" />
                  </div>
                  <?php if (isset($error_galleryproduct_description[$language['language_id']]['title'])) { ?>
                  <div class="text-danger"><?php echo $error_galleryproduct_description[$language['language_id']]['title']; ?></div>
                  <?php } ?>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_galleryproduct_viewas; ?></label>
                <div class="col-sm-5">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_galleryproduct_viewas || $module_mpphotogallery_galleryproduct_viewas == 'GA')) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_viewas" value="GA" <?php if (!empty($module_mpphotogallery_galleryproduct_viewas || $module_mpphotogallery_galleryproduct_viewas == 'GA')) { ?>checked="checked"<?php } ?> /> <?php echo $text_mpphotogallery_album; ?></label>
                    <label class="btn btn-primary <?php if (($module_mpphotogallery_galleryproduct_viewas == 'GAP')) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_viewas" value="GAP" <?php if (($module_mpphotogallery_galleryproduct_viewas == 'GAP')) { ?>checked="checked"<?php } ?> /> <?php echo $text_mpphotogallery_album_photos; ?></label>
                  </div>
                </div>
              </div>
              <!-- // 07-05-2022: updation task start -->
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_override_video; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_galleryproduct_override_video)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_override_video" value="1" <?php if (!empty($module_mpphotogallery_galleryproduct_override_video)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_galleryproduct_override_video)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_override_video" value="0" <?php if (empty($module_mpphotogallery_galleryproduct_override_video)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_override_image; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_galleryproduct_override_image)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_override_image" value="1" <?php if (!empty($module_mpphotogallery_galleryproduct_override_image)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_galleryproduct_override_image)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_override_image" value="0" <?php if (empty($module_mpphotogallery_galleryproduct_override_image)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
              <!-- // 07-05-2022: updation task end -->
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-galleryproduct_width"><?php echo $entry_galleryproduct_size; ?></label>
                <div class="col-sm-10">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_galleryproduct_width" value="<?php echo $module_mpphotogallery_galleryproduct_width; ?>" placeholder="<?php echo $entry_width; ?>" id="input-galleryproduct_width" class="form-control" />
                        <span class="input-group-btn"><button type="button" class="btn btn-primary"><i class="fa fa-arrows-h"></i></button></span>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_galleryproduct_height" value="<?php echo $module_mpphotogallery_galleryproduct_height; ?>" placeholder="<?php echo $entry_height; ?>" id="input-galleryproduct_height" class="form-control" />
                        <span class="input-group-btn"><button type="button" class="btn btn-primary"><i class="fa fa-arrows-v"></i></button></span>
                      </div>
                    </div>
                  </div>
                  <span class="help"><?php echo $help_galleryproduct_size; ?></span>
                  <?php if ($error_galleryproduct_size) { ?>
                  <div class="text-danger"><?php echo $error_galleryproduct_size; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-galleryproduct_photo_width"><?php echo $entry_galleryproduct_photo_size; ?></label>
                <div class="col-sm-10">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_galleryproduct_photo_width" value="<?php echo $module_mpphotogallery_galleryproduct_photo_width; ?>" placeholder="<?php echo $entry_width; ?>" id="input-galleryproduct_photo_width" class="form-control" />
                        <span class="input-group-btn"><button type="button" class="btn btn-primary"><i class="fa fa-arrows-h"></i></button></span>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_mpphotogallery_galleryproduct_photo_height" value="<?php echo $module_mpphotogallery_galleryproduct_photo_height; ?>" placeholder="<?php echo $entry_height; ?>" id="input-galleryproduct_photo_height" class="form-control" />
                        <span class="input-group-btn"><button type="button" class="btn btn-primary"><i class="fa fa-arrows-v"></i></button></span>
                      </div>
                    </div>
                  </div>
                  <span class="help"><?php echo $help_galleryproduct_photo_size; ?></span>
                  <?php if ($error_galleryproduct_photo_size) { ?>
                  <div class="text-danger"><?php echo $error_galleryproduct_photo_size; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_carousel; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_galleryproduct_carousel)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_carousel" value="1" <?php if (!empty($module_mpphotogallery_galleryproduct_carousel)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_galleryproduct_carousel)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_carousel" value="0" <?php if (empty($module_mpphotogallery_galleryproduct_carousel)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label"><?php echo $entry_extitle; ?></label>
                <div class="col-sm-3">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php if (!empty($module_mpphotogallery_galleryproduct_extitle)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_extitle" value="1" <?php if (!empty($module_mpphotogallery_galleryproduct_extitle)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                    <label class="btn btn-primary <?php if (empty($module_mpphotogallery_galleryproduct_extitle)) { ?>active<?php } ?>"><input type="radio" name="module_mpphotogallery_galleryproduct_extitle" value="0" <?php if (empty($module_mpphotogallery_galleryproduct_extitle)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-seo">
              <div class="alert alert-warning"><b><?php echo $text_alert_seo; ?></b></div>
              <fieldset>
                <legend><?php echo $text_mpphotogallery_album_page; ?></legend>
                <h3><?php echo $text_seo_mpphotogallery_album_page; ?></h3>
                <?php echo $module_mpphotogallery_album_page; ?>
              </fieldset>
              <fieldset>
                <legend><?php echo $text_mpphotogallery_albumphoto_page; ?></legend>
                <h3><?php echo $text_seo_mpphotogallery_albumphoto_page; ?></h3>
                <?php echo $module_mpphotogallery_albumphoto_page; ?>
              </fieldset>
              <fieldset>
                <legend><?php echo $text_mpphotogallery_albumvideo_page; ?></legend>
                <h3><?php echo $text_seo_mpphotogallery_albumvideo_page; ?></h3>
                <?php echo $module_mpphotogallery_albumvideo_page; ?>
              </fieldset>
              <fieldset>
                <legend><?php echo $text_mpphotogallery_photo_page; ?></legend>
                <h3><?php echo $text_seo_mpphotogallery_photo_page; ?></h3>
                <?php echo $module_mpphotogallery_photo_page; ?>
              </fieldset>
            </div>
            <!-- // gallery for product task ends -->
            <div class="tab-pane" id="tab-colors">
              <fieldset>
                <h3><?php echo $fieldset_albumn_allphoto; ?></h3>
                <div class="form-group">
                  <div class="col-sm-6">
                    <label class="control-label" for="input-color_title_text"><?php echo $entry_color_title_text; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[title_text]" value="<?php if (!empty($module_mpphotogallery_color['title_text'])) { ?><?php echo $module_mpphotogallery_color['title_text']; ?><?php } ?>" placeholder="<?php echo $entry_color_title_text; ?>" id="input-color_title_text" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-color_title_text"><?php echo $entry_albumtitle_bg; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[album_title_bg]" value="<?php if (!empty($module_mpphotogallery_color['album_title_bg'])) { ?><?php echo $module_mpphotogallery_color['album_title_bg']; ?><?php } ?>" placeholder="<?php echo $entry_albumtitle_bg; ?>" id="input-color_album_title_bg" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-color_title_text"><?php echo $entry_color_albumtitle_text; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[albumtitle_text]" value="<?php if (!empty($module_mpphotogallery_color['albumtitle_text'])) { ?><?php echo $module_mpphotogallery_color['albumtitle_text']; ?><?php } ?>" placeholder="<?php echo $entry_color_albumtitle_text; ?>" id="input-color_albumtitle_text" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-photo_tilte_color"><?php echo $entry_photo_tilte_color; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[photo_tilte_color]" value="<?php if (!empty($module_mpphotogallery_color['photo_tilte_color'])) { ?><?php echo $module_mpphotogallery_color['photo_tilte_color']; ?><?php } ?>" placeholder="<?php echo $entry_photo_tilte_color; ?>" id="input-photo_tilte_color" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-photo_zoomicon_color"><?php echo $entry_photo_zoomicon_color; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[photo_zoomicon_color]" value="<?php if (!empty($module_mpphotogallery_color['photo_zoomicon_color'])) { ?><?php echo $module_mpphotogallery_color['photo_zoomicon_color']; ?><?php } ?>" placeholder="<?php echo $entry_photo_zoomicon_color; ?>" id="input-photo_zoomicon_color" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-photo_hoverbg_color"><?php echo $entry_photo_hoverbg_color; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[photo_hoverbg_color]" value="<?php if (!empty($module_mpphotogallery_color['photo_hoverbg_color'])) { ?><?php echo $module_mpphotogallery_color['photo_hoverbg_color']; ?><?php } ?>" placeholder="<?php echo $entry_photo_hoverbg_color; ?>" id="input-photo_hoverbg_color" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                </div>
              </fieldset>
              <fieldset>
                <h3><?php echo $fieldset_albumns; ?></h3>
                <div class="form-group">
                  <div class="col-sm-6">
                    <label class="control-label" for="input-albumsapge_title_text"><?php echo $entry_albumsapge_title_text; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[albumsapge_title_text]" value="<?php if (!empty($module_mpphotogallery_color['albumsapge_title_text'])) { ?><?php echo $module_mpphotogallery_color['albumsapge_title_text']; ?><?php } ?>" placeholder="<?php echo $entry_albumsapge_title_text; ?>" id="input-albumsapge_title_text" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-albums_detail_text"><?php echo $entry_albums_detail_text; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[albums_detail_text]" value="<?php if (!empty($module_mpphotogallery_color['albums_detail_text'])) { ?><?php echo $module_mpphotogallery_color['albums_detail_text']; ?><?php } ?>" placeholder="<?php echo $entry_albums_detail_text; ?>" id="input-albums_detail_text" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-albums_wrapbg"><?php echo $entry_albums_wrapbg; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[albums_wrapbg]" value="<?php if (!empty($module_mpphotogallery_color['albums_wrapbg'])) { ?><?php echo $module_mpphotogallery_color['albums_wrapbg']; ?><?php } ?>" placeholder="<?php echo $entry_albums_wrapbg; ?>" id="input-albums_wrapbg" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                </div>
              </fieldset>
              <fieldset>
                <h3><?php echo $fieldset_sharethis; ?></h3>
                <div class="form-group">
                  <div class="col-sm-6">
                    <label class="control-label" for="input-sharethis_bg"><?php echo $entry_sharethis_bg; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[sharethis_bg]" value="<?php if (!empty($module_mpphotogallery_color['sharethis_bg'])) { ?><?php echo $module_mpphotogallery_color['sharethis_bg']; ?><?php } ?>" placeholder="<?php echo $entry_sharethis_bg; ?>" id="input-sharethis_bg" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-sharethis_bg"><?php echo $entry_sharethis_color; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[sharethis_color]" value="<?php if (!empty($module_mpphotogallery_color['sharethis_color'])) { ?><?php echo $module_mpphotogallery_color['sharethis_color']; ?><?php } ?>" placeholder="<?php echo $entry_sharethis_color; ?>" id="input-sharethis_color" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                </div>
              </fieldset>
              <fieldset>
                <h3><?php echo $fieldset_extension; ?></h3>
                <div class="form-group">
                  <div class="col-sm-6">
                    <label class="control-label" for="input-extitle_bgcolor"><?php echo $entry_extitle_bgcolor; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[extitle_bgcolor]" value="<?php if (!empty($module_mpphotogallery_color['extitle_bgcolor'])) { ?><?php echo $module_mpphotogallery_color['extitle_bgcolor']; ?><?php } ?>" placeholder="<?php echo $entry_extitle_bgcolor; ?>" id="input-extitle_bgcolor" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-extitle_textcolor"><?php echo $entry_extitle_textcolor; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[extitle_textcolor]" value="<?php if (!empty($module_mpphotogallery_color['extitle_textcolor'])) { ?><?php echo $module_mpphotogallery_color['extitle_textcolor']; ?><?php } ?>" placeholder="<?php echo $entry_extitle_textcolor; ?>" id="input-extitle_textcolor" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-extitle_bordercolor"><?php echo $entry_extitle_bordercolor; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[extitle_bordercolor]" value="<?php if (!empty($module_mpphotogallery_color['extitle_bordercolor'])) { ?><?php echo $module_mpphotogallery_color['extitle_bordercolor']; ?><?php } ?>" placeholder="<?php echo $entry_extitle_bordercolor; ?>" id="input-extitle_bordercolor" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-exview_all_color"><?php echo $entry_exview_all_color; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[exview_all_color]" value="<?php if (!empty($module_mpphotogallery_color['exview_all_color'])) { ?><?php echo $module_mpphotogallery_color['exview_all_color']; ?><?php } ?>" placeholder="<?php echo $entry_exview_all_color; ?>" id="input-exview_all_color" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-carousel_arrow_bgcolor"><?php echo $entry_carousel_arrow_bgcolor; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[carousel_arrow_bgcolor]" value="<?php if (!empty($module_mpphotogallery_color['carousel_arrow_bgcolor'])) { ?><?php echo $module_mpphotogallery_color['carousel_arrow_bgcolor']; ?><?php } ?>" placeholder="<?php echo $entry_carousel_arrow_bgcolor; ?>" id="input-carousel_arrow_bgcolor" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label" for="input-carousel_arrow_color"><?php echo $entry_carousel_arrow_color; ?></label>
                    <div class="colorpicker colorpicker-component input-group">
                      <input type="text" name="module_mpphotogallery_color[carousel_arrow_color]" value="<?php if (!empty($module_mpphotogallery_color['carousel_arrow_color'])) { ?><?php echo $module_mpphotogallery_color['carousel_arrow_color']; ?><?php } ?>" placeholder="<?php echo $entry_carousel_arrow_color; ?>" id="input-carousel_arrow_color" class="form-control" />
                      <span class="input-group-addon"><i></i></span>
                    </div>
                  </div>
                </div>
              </fieldset>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--

$('input[name="module_mpphotogallery_menu_at_header"]').on('change', function() {
  $('.gallery_album_header').hide();

  if (this.value == 'selected_album') {
    $('.gallery_album_header').show();
  }
});

$('input[name="module_mpphotogallery_menu_at_footer"]').on('change', function() {
  $('.gallery_album_footer').hide();

  if (this.value == 'selected_album') {
    $('.gallery_album_footer').show();
  }
});

$(function() {
  $('input[name="module_mpphotogallery_menu_at_header"]:checked').trigger('change');
  $('input[name="module_mpphotogallery_menu_at_footer"]:checked').trigger('change');
});

$(function() { $('.colorpicker').colorpicker(); });
//--></script>
<?php echo $footer; ?>