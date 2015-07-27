<!DOCTYPE html>
<!--[if IEMobile 7 ]> <html dir="ltr" class="no-js iem7" <?php language_attributes(); ?>> <![endif]-->
<!--[if lt IE 7 ]> <html dir="ltr" class="no-js ie6 oldie" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>    <html dir="ltr" class="no-js ie7 oldie" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html dir="ltr" class="no-js ie8 oldie" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|(gt IEMobile 7)|!(IEMobile)|!(IE)]><!--><html dir="ltr" class="no-js" <?php language_attributes(); ?>><!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
  <meta name="apple-mobile-web-app-title" content="<?php echo bloginfo( "name" ); ?>">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">

  <title><?php wp_title('|', true, 'left'); ?></title>
  
  <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico" />
  <link rel="icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon_16px.png" sizes="16x16" type="image/png">
  <link rel="icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon_32px.png" sizes="32x32" type="image/png">
  <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.png">

  <link href="<?php echo get_stylesheet_directory_uri(); ?>/icon_57.png" sizes="57x57" rel="apple-touch-icon">
  <link href="<?php echo get_stylesheet_directory_uri(); ?>/icon_72.png" sizes="72x72" rel="apple-touch-icon">
  <link href="<?php echo get_stylesheet_directory_uri(); ?>/icon_114px.png" sizes="114x114" rel="apple-touch-icon">
  <link href="<?php echo get_stylesheet_directory_uri(); ?>/icon_144px.png" sizes="144x144" rel="apple-touch-icon">

  <link href="<?php echo get_stylesheet_directory_uri(); ?>/touch-icon-ipad-startup-768x1004.png" media="(device-width: 768px) and (device-height: 1024px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 1)" rel="apple-touch-startup-image">
  <link href="<?php echo get_stylesheet_directory_uri(); ?>/touch-icon-ipad-startup-1024x748.png" media="(device-width: 768px) and (device-height: 1024px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 1)" rel="apple-touch-startup-image">

  <link href="<?php echo get_stylesheet_directory_uri(); ?>/touch-icon-iphone-retina-startup-640x920.png" media="(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">
  <link href="<?php echo get_stylesheet_directory_uri(); ?>/touch-icon-iphone-retina-startup-640x1096.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">

  <link href="<?php echo get_stylesheet_directory_uri(); ?>/touch-icon-ipad-retina-startup-1536x2008.png" media="(device-width: 768px) and (device-height: 1024px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">
  <link href="<?php echo get_stylesheet_directory_uri(); ?>/touch-icon-ipad-retina-startup-2048x1496.png" media="(device-width: 768px) and (device-height: 1024px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">

  <?php wp_head(); ?>

  <link rel="alternate" type="application/rss+xml" title="<?php echo get_bloginfo('name'); ?> Feed" href="<?php echo esc_url(get_feed_link()); ?>">
</head>
