'use strict';


// Declare app level module which depends on filters, and services
angular.module('myApp', ['ngRoute', 'checklist-model', 'ui.bootstrap', 'myApp.filters', 'myApp.services', 'myApp.directives', 'myApp.controllers']).
  config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    //$routeProvider.when('/', {templateUrl: '../private/ProcessWire/site/templates/partials/partial1.php', controller: 'MyCtrl1'});
    //$routeProvider.when('/', {templateUrl: 'index.php', controller: ''});
    //$routeProvider.when('/', {templateUrl: 'site/templates/home.php', controller: ''});
    //$routeProvider.when('/players/:team', {templateUrl: 'site/templates/partials/partial1.php', controller: 'teamCtrl'});
    //$routeProvider.when('/players/:team', {templateUrl: 'site/templates/partials/partial1.php', controller: 'teamCtrl'});
    //$routeProvider.when('/player/:player', {templateUrl: 'site/templates/partials/partial2.php', controller: 'playerCtrl'});
    //$routeProvider.otherwise({redirectTo: '/'});
    //$locationProvider.html5Mode(true);
  }]);
