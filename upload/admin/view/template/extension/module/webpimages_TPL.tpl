<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-module-webp" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit;?></h3>
      </div>
      <div class="panel-body">
        <div class="content">
            <form action="<?php echo $action;?>" method="post" enctype="multipart/form-data" id="form-module" class="form-horizontal">
              <div class="col-sm-8">
                 <div class="form-group">
                  <label class="col-sm-4 control-label" for="input-status">PHP GD Wep Status</label>
                  <div class="col-sm-8"><?php echo $text_alert;?>
                  </div>
                </div>
            <div class="form-group">
        		<label class="col-sm-4 control-label" for="input-status"><?php echo $entry_status;?></label>
        		<div class="col-sm-8">
        		<label class="switch">
        			<input type="checkbox" name=" module_webpimages_status" <?php if ($module_webpimages_status){?>checked<?php }?>>
        			<span class="slider round"></span>
        		</label>
        		</div>
        	</div>
                <div class="form-group">
                  <label class="col-sm-4 control-label" for="input-quality"><?php echo $entry_quality;?></label>
                  <div class="col-sm-4">
                    <div class="input-group rangeslidecontainer">
                      <input type="range" min="1" max="100" value="<?php if ($module_webpimages_quality > 0) { echo $module_webpimages_quality; }?>" class="rangeslider" id="myRange">
                      <input type="hidden" value="<?php echo $module_webpimages_quality;?>" name="module_webpimages_quality"/>
                      <span id="range" class="pull-right"><?php echo $module_webpimages_quality;?>%</span>
                      </div>
                  </div>
                </div>
                 <div class="form-group">
        		<label class="col-sm-4 control-label" for="input-cookie"><?php echo $entry_cookie;?></label>
        		<div class="col-sm-8">
        		<label class="switch">
        			<input type="checkbox" name=" module_webpimages_cookie" <?php if ($module_webpimages_cookie){?>checked<?php }?>>
        			<span class="slider round"></span>
        		</label>
        		</div>
        	</div>
              </div>
               <div class="col-sm-4"><?php echo $help_gd;?><br/><br/><h3>GD Info</h3><pre><?php print_r($gdinfo);?></pre></div>
            </form>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript">
  $('#myRange').val();
  
  
  $('#myRange').change(function(){
    var myVar = $(this).val();
    var output = document.getElementById("range");
    $('input[name=\'module_webpimages_quality\']').val(myVar);
    output.innerHTML = myVar+'%' ;
   
});
</script>
</div>
<?php echo $footer; ?> 