<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title><?php echo $heading_title; ?></title>
  <base href="<?php echo $base; ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://license.marketinsg.com/download/external/stylesheet.css">
</head>
<body class="d-flex flex-column h-100">
<nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="<?php echo $home; ?>"><img src="https://license.marketinsg.com/download/external/opencart.png"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav" aria-controls="main-nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="main-nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $cancel; ?>"><i class="fa fa-caret-left"></i> <?php echo $button_back_admin; ?></a>
        </li>
      </ul>
      <?php if ($license_key) { ?>
      <div class="me-3">
        <?php if ($documentation) { ?>
        <a href="<?php echo $documentation; ?>" target="_blank" class="btn btn-outline-secondary me-3"><i class="fa fa-fw fa-book"></i> <?php echo $button_docs; ?></a>
        <?php } ?>
        <a href="https://marketinsg.zendesk.com/hc/en-us/requests/new" target="_blank" class="btn btn-outline-secondary me-3"><i class="fa fa-fw fa-users-cog"></i> <?php echo $button_support; ?></a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-license"><i class="fa fa-fw fa-user-circle"></i> <?php echo $button_license_info; ?></button>
      </div>
      <?php } ?>
    </div>
  </div>
</nav>