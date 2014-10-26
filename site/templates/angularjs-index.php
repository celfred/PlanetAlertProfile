<!doctype html>
<html lang="en" ng-app="myApp">
<head>
  <meta charset="utf-8">
  <title>My AngularJS App</title>
  <link rel="stylesheet" href="<?php echo $config->urls->templates?>css/app.css"/>
</head>
<body>
  <ul class="menu">
    <li><a href="#/view1">view1</a></li>
    <li><a href="#/view2">view2</a></li>
  </ul>

  <div ng-view></div>

  <div>Angular seed app: v<span app-version></span></div>

  <!-- In production use:
  <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.0.7/angular.min.js"></script>
  -->
  <script src="<?php echo $config->urls->templates?>lib/angular/angular.js"></script>
  <script src="<?php echo $config->urls->templates?>js/app.js"></script>
  <script src="<?php echo $config->urls->templates?>js/services.js"></script>
  <script src="<?php echo $config->urls->templates?>js/controllers.js"></script>
  <script src="<?php echo $config->urls->templates?>js/filters.js"></script>
  <script src="<?php echo $config->urls->templates?>js/directives.js"></script>
</body>
</html>
