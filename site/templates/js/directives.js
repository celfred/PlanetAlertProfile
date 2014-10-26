'use strict';

/* Directives */


angular.module('myApp.directives', []).
  directive('appVersion', ['version', function(version) {
    return function(scope, elm, attrs) {
      elm.text(version);
    };
  }]).
  directive('ngFocus', function() {
    return function(scope, element, attrs) {
      element.bind('focus', function(){
        scope.$apply(function () {
          scope.$eval(attrs.ngFocus);
        });
      });
    }
  }).
  directive('checkboxAll', function () {
    return function(scope, iElement, iAttrs) {
      var parts = iAttrs.checkboxAll.split('.');
      iElement.attr('type','checkbox');
      iElement.bind('change', function (evt) {
        scope.$apply(function () {
          var setValue = iElement.prop('checked');
          angular.forEach(scope.$eval(parts[0]), function (v) {
            v[parts[1]] = setValue;
          });
        });
      });
      scope.$watch(parts[0], function (newVal) {
        var hasTrue, hasFalse;
        angular.forEach(newVal, function (v) {
          if (v[parts[1]]) {
            hasTrue = true;
          } else {
            hasFalse = true;
          }
        });
        if (hasTrue && hasFalse) {
          iElement.attr('checked', false);
          iElement.addClass('greyed');
        } else {
          iElement.attr('checked', hasTrue);
          iElement.removeClass('greyed');
        }
      }, true);
    }
  }).
  directive('onKeyup', function() {
    return function(scope, elm, attrs) {
      function applyKeyup() {
        scope.$apply(attrs.onKeyup);
      };           
        
      var allowedKeys = scope.$eval(attrs.keys);
      elm.bind('keyup', function(evt) {
        //if no key restriction specified, always fire
        if (!allowedKeys || allowedKeys.length == 0) {
          applyKeyup();
        } else {
          angular.forEach(allowedKeys, function(key) {
            if (key == evt.which) {
              applyKeyup();
            }
          });
        }
      });
    }
  });
