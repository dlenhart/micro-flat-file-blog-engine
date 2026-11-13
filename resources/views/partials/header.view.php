<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/assets/css/styles.css" rel="stylesheet" />

    <title><?= config('app.app_name'); ?></title>
  </head>
  <body>

<div class="container">
  <div class="header">
    <h2><?= config('app.app_name'); ?></h2>
  </div>

  <?php require('navigation.view.php'); ?>