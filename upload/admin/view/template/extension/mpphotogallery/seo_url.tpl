<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $text_keyword; ?></div>
<?php if (VERSION >= '3.0.0.0') { ?>
<div class="table-responsive">
  <table class="table table-bordered table-hover">
    <thead>
      <tr>
        <td class="text-left"><?php echo $entry_store; ?></td>
        <td class="text-left"><?php echo $entry_keyword; ?></td>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($stores as $store) { ?>
    <tr>
      <td class="text-left"><?php echo $store['name']; ?></td>
      <td class="text-left"><?php foreach ($languages as $language) { ?>
        <div class="input-group"><span class="input-group-addon"><img src="<?php echo $language['lang_flag']; ?>" title="<?php echo $language['name']; ?>" /></span>
          <input type="text" name="<?php echo $input; ?>[<?php echo $store['store_id']; ?>][<?php echo $language['language_id']; ?>]" value="<?php if (isset($seourl[$store['store_id']][$language['language_id']])) { ?><?php echo $seourl[$store['store_id']][$language['language_id']]; ?><?php } ?>" placeholder="<?php echo $entry_keyword; ?>" class="form-control" />
        </div>
        <?php if (isset($error_seourl[$store['store_id']][$language['language_id']])) { ?>
        <div class="text-danger"><?php echo $error_seourl[$store['store_id']][$language['language_id']]; ?></div>
        <?php } ?>
        <?php } ?></td>
    </tr>
    <?php } ?>
      </tbody>
  </table>
</div>
<?php } else { ?>
<div class="form-group">
  <label class="col-sm-2 control-label" for="input-keyword-<?php echo $input; ?>"><?php echo $entry_keyword; ?></label>
  <div class="col-sm-10">
    <input type="text" name="module_mpphotogallery_albumphoto_page" value="<?php echo $seourl; ?>" placeholder="<?php echo $entry_keyword; ?>" id="input-keyword-<?php echo $input; ?>" class="form-control" />
    <?php if ($error_seorul) { ?>
    <div class="text-danger"><?php echo $error_seorul; ?></div>
    <?php } ?>
  </div>
</div>
<?php } ?>