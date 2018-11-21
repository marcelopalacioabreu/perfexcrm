<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
  <title><?php if (isset($title)){ echo $title; } ?></title>
  <?php echo app_compile_css('customers-area-default'); ?>
  <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
  <?php echo app_stylesheet('assets/css','bs-overides.css'); ?>
  <?php echo app_stylesheet(template_assets_path().'/css','style.css'); ?>
  <?php if(file_exists(FCPATH.'assets/css/custom.css')){ ?>
  <link href="<?php echo base_url('assets/css/custom.css'); ?>" rel="stylesheet" type='text/css' id="custom-css">
  <?php } ?>
  <?php render_custom_styles(array('general','tabs','buttons','customers','modals')); ?>
  <?php $isRTL = (is_rtl(true) ? 'true' : 'false'); ?>
  <?php // DO NOT REMOVE THIS LINE ?>
  <?php do_action('app_customers_head',array('language'=>$language)); ?>
</head>
<body class="customers<?php if(isset($bodyclass)){echo ' ' . $bodyclass; } ?>" <?php if($isRTL == 'true'){ echo 'dir="rtl"';} ?>>
