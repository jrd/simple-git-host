<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="/<?php echo $gitwebroot;?>style.css" type="text/css" />
    <link rel="shortcut icon" href="/<?php echo $gitwebroot;?>favicon.png" type="image/png"/>
    <!--[if lt IE 9]>
    <script src="/<?php echo $gitwebroot;?>ie7/IE9.js"></script>
    <![endif]-->
  </head>
  <body>
    <h1><?php echo $pageTitle; ?></h1>
<?php require('nav.inc.php'); ?>
