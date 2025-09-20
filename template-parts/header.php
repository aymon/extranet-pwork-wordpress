<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
    <?php $title = get_bloginfo( 'name' ); ?>
    <title><?php echo esc_html($title); ?></title>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <?php do_action('pwork_head'); ?>
  </head>
  <body id="pwork">
  <div id="pwork-overlay"></div>