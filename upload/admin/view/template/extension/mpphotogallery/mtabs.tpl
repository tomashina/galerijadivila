<div class="m-menu-tabs">
  <ul class="list-inline clearfix">
    <?php foreach ($menus['children'] as $children) { ?>
      <?php if ($children['children']) { ?>
        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="li" aria-haspopup="true" aria-expanded="true"><?php if (!empty($children['icon'])) { ?><i class="fa <?php echo $children['icon']; ?>" aria-hidden="true"></i> <?php } ?><?php echo $children['name']; ?> </a>
          <div class="dropdown-menu">
            <?php foreach ($children['children'] as $children2key => $children2) { ?>
            <a class="dropdown-item" href="<?php echo $children2['href']; ?>"><?php echo $children2['name']; ?></a>
            <?php if ($children2key == '0') { ?><div class="dropdown-divider"></div><?php } ?>
            <?php } ?>
          </div>
        </li>
      <?php } else { ?>
      <li><a href="<?php echo $children['href']; ?>" class=""><?php if (!empty($children['icon'])) { ?><i class="fa <?php echo $children['icon']; ?>" aria-hidden="true"></i> <?php } ?><span><?php echo $children['name']; ?></span></a></li>
      <?php } ?>
    <?php } ?>
  </ul>
</div>
<script type="text/javascript">

  // Set last page opened on the menu
  $('.m-menu-tabs a[href]').on('click', function() {
    if ($(this).attr('href') != '#') {
      sessionStorage.setItem('menu', $(this).attr('href'));
    }
  });
  if (sessionStorage.getItem('menu') && sessionStorage.getItem('menu') != '#') {
    $('.m-menu-tabs a[href=\'' + sessionStorage.getItem('menu') + '\']').parent().addClass('active');

    $('.m-menu-tabs a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('li > a').removeClass('collapsed');

    $('.m-menu-tabs a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('ul').addClass('in');

    $('.m-menu-tabs a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('li').addClass('active');
  }
</script>