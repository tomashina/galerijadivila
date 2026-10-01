$(document).ready(function() {
  var gkUserToken = (location.search.split('user_token=')[1] || '').split('&')[0];
  
  $('body').on('click', '.rbClearCache', function (e) {
    e.stopPropagation();
    
    $('.rocketBoostWidget button').attr('disabled', true);
    var rbPreloadCacheCurrentBtn = $(this);
    var currentHtml = rbPreloadCacheCurrentBtn.html();
    rbPreloadCacheCurrentBtn.html('<i class="fa fa-cog fa-spin"></i>');
    
     $.ajax({
      url: 'index.php?route=extension/module/rocket_boost/clear_cache&user_token='+gkUserToken,
      type: 'POST',
      dataType: 'json',
      success: function(data){
        rbPreloadCacheCurrentBtn.html('<i class="fa fa-check"></i>');
        $('button').attr('disabled', false);
      },
      error: function(xhr) {
        alert(xhr.responseText);
        rbPreloadCacheCurrentBtn.html(currentHtml);
        $('button').attr('disabled', false);
      }
    });
  });
  
  $('body').on('click', '.rbChangeStatus', function (e) {
    e.stopPropagation();
    
    $.ajax({
      url: 'index.php?route=extension/module/rocket_boost/status&user_token='+gkUserToken,
      type: 'POST',
      dataType: 'json',
      success: function(data){
        if (!data) {
          $('.boostStatusOn').hide();
          $('.boostStatusOff').show();
          $('.activeButtons button').attr('disabled', true);
          $('.rocketBoostWidget img').addClass('off');
          $('.rocketBoostWidget .badge').addClass('disabled');
        } else {
          $('.boostStatusOff').hide();
          $('.boostStatusOn').show();
          $('.activeButtons button').attr('disabled', false);
          $('.rocketBoostWidget img').removeClass('off');
          $('.rocketBoostWidget .badge').removeClass('disabled');
        }
      },
      error: function(xhr) {
        alert(xhr.responseText);
      }
    });
  });


  var rbPreloadCachePause = 1;
  var currentProgress = 0;
  var currentBtn = '';
  var currentHtml = '';
    
  function processQueue(start) {
    var rbPreloadCacheCurrentBtn = $('.rocketBoostWidget .rbPreloadCache');
    
    $.ajax({
      url: 'index.php?route=extension/module/rocket_boost/process&start='+start+'&user_token='+gkUserToken,
      type: 'POST',
      //data: $('#update-form :input').serialize(),
      dataType: 'json',
      success: function(data){
        if(data.success) {
          $('.rocketBoostWidget .progress-bar').css('width',data.progress + '%').html(data.progress + ' %');
          $('.rocketBoostWidget .processProgress').html(data.progress + ' %');
          if (!rbPreloadCachePause && !data.finished) {
            processQueue(data.processed);
          } else {
            currentProgress = data.processed;
            $('.rocketBoostWidget .progress-bar').removeClass('active');
            rbPreloadCacheCurrentBtn.removeClass('rbPreloadCachePause').addClass('rbPreloadCache');
            $('button').attr('disabled', false);
            
            if (data.finished) {
              rbPreloadCacheCurrentBtn.html('<i class="fa fa-check"></i>');
              $('button').attr('disabled', false);
            }
          }
        }
      },
      error: function(xhr) {
        $('.rocketBoostWidget .progress-bar').css('width','100%');
        $('.rocketBoostWidget .rbPreloadCachePause').html('<i class="fa fa-check"></i> 100%');
        $('.rocketBoostWidget .progress-bar').removeClass('active');
        
        $('button').attr('disabled', false);
        rbPreloadCacheCurrentBtn.html('<i class="fa fa-exclamation-triangle"></i>');
        rbPreloadCacheCurrentBtn.removeClass('btn-success').addClass('btn-danger');
        rbPreloadCacheCurrentBtn.attr('disabled', true);
        alert(xhr.responseText);
      }
    });
  }

  var first_run = true;
    
  $('body').on('click', '.rbPreloadCache', function(e) {
    e.stopPropagation();
    
    $('button').attr('disabled', true);
    
    $('.fa-pause').parent().removeClass('btn-success').addClass('btn-primary').html('<i class="fa fa-refresh"></i>');
    
    rbPreloadCacheCurrentBtn = $(this);
    
    currentHtml = rbPreloadCacheCurrentBtn.html();
    
    if (rbPreloadCacheCurrentBtn.html().indexOf('fa-pause') < 0) {
      rbPreloadCacheCurrentBtn.html('<i class="fa fa-refresh fa-spin"></i>&nbsp;&nbsp;<span class="processProgress">0 %</span>');
    } else {
      rbPreloadCacheCurrentBtn.html(rbPreloadCacheCurrentBtn.html().replace('fa-pause', 'fa-refresh fa-spin'));
    }
    rbPreloadCacheCurrentBtn.removeClass('btn-primary').addClass('btn-success');
    rbPreloadCacheCurrentBtn.attr('disabled', false);
    rbPreloadCacheCurrentBtn.removeClass('rbPreloadCache').addClass('rbPreloadCachePause');
    
    $('.rocketBoostWidget .progress').show();
    $('.rocketBoostWidget .progress-bar').addClass('active');
    
    rbPreloadCachePause = 0;
    if (!currentProgress) {
      processQueue('init', rbPreloadCacheCurrentBtn.data('page'));
    } else {
      processQueue(currentProgress, rbPreloadCacheCurrentBtn.data('page'));
    }
  });

  $('body').on('click', '.rbPreloadCachePause', function(e) {
    e.stopPropagation();
    rbPreloadCachePause = 1;
    rbPreloadCacheCurrentBtn.html(rbPreloadCacheCurrentBtn.html().replace('fa-refresh fa-spin', 'fa-pause'));
  });
});