'use strict';

/* Filters */

angular.module('myApp.filters', []).
  filter('interpolate', ['version', function(version) {
    return function(text) {
      return String(text).replace(/\%VERSION\%/mg, version);
    }
  }]).
  filter('isOwned', function() {
    return function(id, owners) {
      for (var i=0; i<owners.length; i++){
        for (var j=0; j<owners[i].places.length; j++) {
          if (id === owners[i].places[j].id) {
            return true;
          }
        }
      }      
      return false;
    }
  }).
  filter('owned', function() {
    return function(input, status) {
      var out = [];
      if (input) {
      for (var i = 0; i < input.length; i++){
        if(input[i].owners.length > status)
            out.push(input[i]);
      }      
      }
      return out;
    }
  }).
  filter('ownersList', function() {
    return function(input, status) {
      var out = [];
      for (var i = 0; i < input.length; i++){
        if(input[i].places.length > status)
            out.push(input[i]);
      }      
      return out;
    }
  })
  .filter('unsafe', function($sce) {
      return function(val) {
          return $sce.trustAsHtml(val);
      };
  })
.filter('filterHtmlChars', function(){
   return function(html) {
       var filtered = angular.element('<div>').html(html).text(); 
       return filtered;
   }
});
