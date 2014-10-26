'use strict';

/* Services */


// Demonstrate how to register services
// In this case it is a simple value service.
angular.module('myApp.services', []).
  value('version', '0.1').
  factory('Team', function($http) {
    return {
      load: function(team) {
        if (team) {
          var url = "service-pages/?template=player&sort=title"+team;
          var promise = $http.get(url).then(function (response) {
            return response.data.matches;
          });
        }
        return promise;
    }
  }
  }).
  factory('Places', function($http, $filter) {
    return {
      load: function(team) {
        var url = "service-pages/?template=place&name!=places&sort=country.name";
        var promise = $http.get(url).then(function (response) {
          return response.data.matches;
        });
        return promise;
      },
      setOwned: function(allPlaces, owners) {
        // List all owners
        for (var i=0; i<owners.length; i++) {
          owners[i].owners = [];
          // List all places owned by player
          for (var j=0; j<owners[i].places.length; j++) {
            // Set stats for the place
            var placeId = owners[i].places[j].id;
            var already = $filter('filter')(allPlaces, {id:placeId});
            if (already[0]) {
              already[0].owners.push(owners[i]);
              already[0].freedomRate = Math.round(100*already[0].owners.length/already[0].maxOwners);
              already[0].freedomRateWidth = Math.round(100*already[0].owners.length/already[0].maxOwners)*150/100;
            }
          }
        }
        return false;
      }
  }
  }).
  factory('Equipment', function($http) {
    return {
      load: function(team) {
        var url = "service-pages/?template=equipment|item&sort=level";
        var promise = $http.get(url).then(function (response) {
          return response.data.matches;
        });
        return promise;
    }
  }
  }).
  factory('Task', function($http) {
    return {
      load: function(team) {
        var url = "service-pages/?template=task&name!=tasks&sort=category.name";
        var promise = $http.get(url).then(function (response) {
          return response.data.matches;
        });
        return promise;
    }
  }
  });
