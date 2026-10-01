<?php echo $header; ?><?php echo $column_left; ?>
<div id="content" class="m-menu-wrap">
  <div class="container-fluid">
    <div class="page-header">
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
    <?php echo $mtabs; ?>
  </div>
  <div class="container-fluid">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
        <div class="pull-right">
        <button type="submit" form="form-mpgallery" class="btn btn-success"><i class="fa fa-save"></i> <?php echo $button_save; ?></button>
        <a href="<?php echo $cancel; ?>" class="btn btn-default"><i class="fa fa-times"></i> <?php echo $button_cancel; ?></a></div>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-mpgallery" class="form-horizontal">
          <?php if ($error_warning) { ?>
          <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
          <?php } ?>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-name"><?php echo $entry_name; ?></label>
            <div class="col-sm-10">
              <input type="text" name="name" value="<?php echo $name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" />
              <?php if ($error_name) { ?>
              <div class="text-danger"><?php echo $error_name; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group required">
            <label class="col-sm-2 control-label"><?php echo $entry_title; ?></label>
            <div class="col-sm-10">
              <?php foreach ($languages as $language) { ?>
              <div class="input-group">
                <span class="input-group-addon"><img src="<?php echo $language['lang_flag']; ?>" title="<?php echo $language['name']; ?>" /></span>
                <input type="text" name="gall_description[<?php echo $language['language_id']; ?>][title]" value="<?php echo isset($gall_description[$language['language_id']]) ? $gall_description[$language['language_id']]['title'] : ''; ?>" placeholder="<?php echo $entry_title; ?>" class="form-control" />
              </div>
              <?php if (isset($error_title[$language['language_id']])) { ?>
              <div class="text-danger"><?php echo $error_title[$language['language_id']]; ?></div>
              <?php } ?>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-gallery"><span data-toggle="tooltip" title="<?php echo $help_gallery; ?>"><?php echo $entry_gallery; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="mpgallery_name" value="" placeholder="<?php echo $entry_gallery; ?>" id="input-gallery" class="form-control" />
              <div id="gallery-gallery" class="well well-sm" style="height: 150px; overflow: auto;">
                <?php foreach ($galleries as $gallery) { ?>
                <div id="gallery-gallery<?php echo $gallery['mpgallery_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $gallery['title']; ?>
                  <input type="hidden" name="gallery[]" value="<?php echo $gallery['mpgallery_id']; ?>" />
                </div>
                <?php } ?>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-limit"><?php echo $entry_limit; ?></label>
            <div class="col-sm-10">
              <input type="text" name="limit" value="<?php echo $limit; ?>" placeholder="<?php echo $entry_limit; ?>" id="input-limit" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-width"><?php echo $entry_width; ?></label>
            <div class="col-sm-10">
              <input type="text" name="width" value="<?php echo $width; ?>" placeholder="<?php echo $entry_width; ?>" id="input-width" class="form-control" />
              <?php if ($error_width) { ?>
              <div class="text-danger"><?php echo $error_width; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-height"><?php echo $entry_height; ?></label>
            <div class="col-sm-10">
              <input type="text" name="height" value="<?php echo $height; ?>" placeholder="<?php echo $entry_height; ?>" id="input-height" class="form-control" />
              <?php if ($error_height) { ?>
              <div class="text-danger"><?php echo $error_height; ?></div>
              <?php } ?>
            </div>
          </div>          
          <div class="form-group mp-buttons">
            <label class="col-sm-2 control-label"><?php echo $entry_carousel; ?></label>
            <div class="col-sm-3">
              <div class="btn-group btn-group-justified" data-toggle="buttons">
                <label class="btn btn-primary <?php if (!empty($carousel)) { ?>active<?php } ?>"><input type="radio" name="carousel" value="1" <?php if (!empty($carousel)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                <label class="btn btn-primary <?php if (empty($carousel)) { ?>active<?php } ?>"><input type="radio" name="carousel" value="0" <?php if (empty($carousel)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
              </div>
            </div>
          </div>
          <div class="form-group mp-buttons">
            <label class="col-sm-2 control-label"><?php echo $entry_extitle; ?></label>
            <div class="col-sm-3">
              <div class="btn-group btn-group-justified" data-toggle="buttons">
                <label class="btn btn-primary <?php if (!empty($extitle)) { ?>active<?php } ?>"><input type="radio" name="extitle" value="1" <?php if (!empty($extitle)) { ?>checked="checked"<?php } ?> /> <?php echo $text_yes; ?></label>
                <label class="btn btn-primary <?php if (empty($extitle)) { ?>active<?php } ?>"><input type="radio" name="extitle" value="0" <?php if (empty($extitle)) { ?>checked="checked"<?php } ?> /> <?php echo $text_no; ?></label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="status" id="input-status" class="form-control">
                <?php if ($status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script type="text/javascript"><!--
$('input[name=\'mpgallery_name\']').autocomplete({
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=<?php echo $extension_path; ?>mpphotogallery/mpgallery/autocomplete&<?php echo $get_token; ?>=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['title'],
						value: item['mpgallery_id']
					}
				}));
			}
		});
	},
	select: function(item) {
		$('input[name=\'mpgallery_name\']').val('');
		
		$('#gallery-gallery' + item['value']).remove();
		
		$('#gallery-gallery').append('<div id="gallery-gallery' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="gallery[]" value="' + item['value'] + '" /></div>');	
	}
});
	
$('#gallery-gallery').delegate('.fa-minus-circle', 'click', function() {
	$(this).parent().remove();
});
//--></script></div>
<?php echo $footer; ?>
