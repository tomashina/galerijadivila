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
        <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
        <div class="pull-right">
        <button type="button" class="btn btn-danger" onclick="confirm('<?php echo $text_confirm; ?>') ? $('#form-gallery').submit() : false;"><i class="fa fa-trash-o"></i> <?php echo $button_delete; ?></button>
        </div>
      </div>
      <div class="panel-body">
        <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form-gallery">
          <?php if ($error_warning) { ?>
          <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
          <?php } ?>
          <?php if ($success) { ?>
          <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
          <?php } ?>
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
              <thead>
                <tr>
                  <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
                  <td class="text-left"><?php echo $column_image; ?></td>
                  <td class="text-left"><?php if ($sort == 'gd.title') { ?>
                    <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'g.viewed') { ?>
                    <a href="<?php echo $sort_viewed; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_viewed; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_viewed; ?>"><?php echo $column_viewed; ?></a>
                    <?php } ?></td>
                  <td class="text-right"><?php if ($sort == 'g.sort_order') { ?>
                    <a href="<?php echo $sort_sort_order; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_sort_order; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_sort_order; ?>"><?php echo $column_sort_order; ?></a>
                    <?php } ?></td>
                  <td class="text-right"><?php if ($sort == 'g.status') { ?>
                    <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
                    <?php } ?></td>
                  <td class="text-right"><?php echo $column_action; ?></td>
                </tr>
              </thead>
              <tbody>
                <?php if ($galleries) { ?>
                <?php foreach ($galleries as $gallery) { ?>
                <tr>
                  <td class="text-center"><?php if (in_array($gallery['mpgallery_id'], $selected)) { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $gallery['mpgallery_id']; ?>" checked="checked" />
                    <?php } else { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $gallery['mpgallery_id']; ?>" />
                    <?php } ?> <br/>#<?php echo $gallery['mpgallery_id']; ?></td>
                  <td class="text-left"><img src="<?php echo $gallery['thumb']; ?>" class="img-responsive" alt="<?php echo $gallery['title']; ?>" /></td>
                  <td class="text-left"><?php echo $gallery['title']; ?></td>
                  <td class="text-left"><?php echo $gallery['viewed']; ?></td>
                  <td class="text-right"><?php echo $gallery['sort_order']; ?></td>

                  <td class="text-right"><label class="label <?php if ($gallery['status']) { ?>label-success<?php } else { ?>label-danger<?php } ?>"><?php echo $gallery['status_str']; ?></label></td>
                  <td class="text-right"><a href="<?php echo $gallery['edit']; ?>" class="btn btn-primary"><i class="fa fa-pencil"></i> <?php echo $button_edit; ?></a> <a target="_blank" href="<?php echo $gallery['view']; ?>" class="btn btn-primary"><i class="fa fa-eye"></i> <?php echo $button_view; ?></a></td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                  <td class="text-center" colspan="7"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </form>
        <div class="row">
          <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
          <div class="col-sm-6 text-right"><?php echo $results; ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>