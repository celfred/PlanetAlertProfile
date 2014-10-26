'use strict';

/* Controllers */
angular.module('myApp.controllers', []) .
  controller('teamCtrl', ['$sce', '$scope', '$route', '$filter', 'Team', 'Equipment', 'Task', 'Places', '$timeout', function($sce, $scope, $route, $filter, Team, Equipment, Task, Places, $timeout) {
    
    $scope.selected = 1;
    $scope.allChecked = false;
    $scope.anyChecked = false;
    $scope.adminTableAnyChecked = false;
    $scope.predicate = 'karma';
    $scope.reverse = true;
    $scope.startEdit = false; // No player checked
    $scope.allCategories = [];
    $scope.alreadyCat = [];
    $scope.search = {};

    $scope.inArray = function (list, needed) {
      return !!~list.indexOf(needed);
    }

    $scope.setSearch = function (tag, type) {
      $scope.search.category = tag;
      return false;
    }

    $scope.setComment = function (player, task) {
      
    }

    $scope.setCommonComment = function(task) {
      if ($scope.adminTableAnyChecked == true) {
        angular.forEach($scope.players, function(player){
          if (task.showComment) {
            if (player.checkedTasks.indexOf(task.id) != -1) {
              player.checkedComments[task.id] = task.commonComment;
            }
          } else {
            if (player.checkedTasks.indexOf(task.id) != -1) {
              player.checkedComments[task.id] = ' ';
            }
          }
        });
      }
    }

    $scope.showComment = function(task) {
      task.showComment = !task.showComment;
    }

    $scope.selectAll = function(task) {
      //console.log(selectedTask.id);
      if (task.checkedAll == false) {
        angular.forEach($scope.players, function(player){
          if (player.checkedTasks.indexOf(task.id) == -1 ) {
            player.checkedTasks.push(task.id);
            player.checkedComments[task.id] = ' ';
          }
        });
        $scope.adminTableAnyChecked = true;
      } else {
        angular.forEach($scope.players, function(player){
          player.checkedTasks.splice(player.checkedTasks.indexOf(task.id), 1);
          delete player.checkedComments[task.id];
          if ( player.checkedTasks.length == 0) {
            $scope.adminTableAnyChecked = false;
          }
        });
      }
      task.checkedAll = !task.checkedAll;
    }

    $scope.onCheck = function(player, task) {
      $scope.adminTableAnyChecked = true;
      task.checkedAll = false;
      if (player.checkedComments[task.id]) {
        delete player.checkedComments[task.id];
      } else {
        player.checkedComments[task.id] = ' ';
      }
    }

    // Load data according to the team
    $scope.loadData = function(team) {
      if (team) {
        // Store sanitizedTeam for further display
        $scope.sanitizedTeam = team;
        // Manipulation for GET request
        team = team.replace('-', ' ');
        team = '&team='+team;

        //$scope.team = '&team='+team;
        
        // Load ALL possible equipments and items
        Equipment.load().then( function(data) {
          $scope.items = data;
        });
        // Load ALL possible tasks
        Task.load().then( function(data) {
          $scope.tasks = data;
          angular.forEach($scope.tasks, function(task){
            task.checkedAll = false;
            task.showComment = false;
            task.commonComment = '';
            if ( task.HP<0 ) {
              task.type = 'négative';
            } else {
              task.type = 'positive';
            }
            // Make a unique list of categories used in Tasks;
            if (!$scope.inArray($scope.alreadyCat, task.category.title)) {
              $scope.allCategories.push(task.category);
              $scope.alreadyCat += task.category.title;
            }
            });
        });
        
        // Load ALL team players
        Team.load(team).then( function(data) {
          $scope.players = data;
          // Prepare data
          angular.forEach( $scope.players, function(player) {
            player.isChecked = false; 
            player.task = '';
            player.checkedTasks = [];
            player.checkedComments = {};
            player.htask = '';
            player.customTask = '';
            player.rawHP = player.HP;
            player.rawGC = player.GC;
            player.rawXP = player.XP;
            player.rawlevel = player.level;
            player.equipmentList = '';
            player.deltaHP = 0;
            player.deltaXP = 0;
            player.xpWidth = 150*player.XP/(player.level*10+90);
            player.hpWidth = 150*player.HP/50;
                        
            // Get delta from all equipment list
            angular.forEach(player.equipment, function(item, index) {
              var mainItem = $filter('filter')($scope.items, { id:item.id});
              if (mainItem && mainItem.length > 0) {
                item.HP = mainItem[0].HP;
                item.GC = mainItem[0].GC;
                item.XP = mainItem[0].XP;
                player.deltaHP += item.HP;
                player.deltaXP += item.XP;
              }
              
              if (index == player.equipment.length-1) { // Last item
                player.equipmentList += item.title;
              } else {
                player.equipmentList += item.title + ' / ';
              }
              
            });

            // Set Karma value
            if (!player.karma) player.karma = 0;

          });

          // Load ALL possible places
          Places.load().then( function(data) {
            $scope.places = data;
            $scope.allPlaces = $scope.places.length;
            // Prepare data
            angular.forEach( $scope.places, function(place) {
              place.owners = [];
              place.freedomRate = 0;
              place.freedomRateWidth = 0;
              //place.completed = 0;
            });
            
            // Set team places stats (owners, fredom rates...)
            var ownersList = $filter('ownersList')($scope.players, 0);
            // Set stats
            Places.setOwned($scope.places, ownersList);
            // Get owned
            $scope.placesOwned = $filter('owned')($scope.places, 0);
            // Get completed
            $scope.completed = $filter('filter')($scope.places, { freedomRate:100}).length;
            $scope.completedRate = Math.round(100*$scope.completed/$scope.allPlaces);
          });
        });
      } else {
        $scope.team = '';
      }
    }

    $scope.fillTitles = function(e) {
      var el = e.target;
      var options = el.querySelectorAll("option");
      if (options) {
        for (var i=0; i<options.length; i++) {
          // Replace quotes
          options[i].textContent = options[i].textContent.replace("&#039;", "'");
          // Set title
          if ($scope.tasks[i-1]) {
            options[i].title = $scope.tasks[i-1].summary;
            // Replace quotes
            options[i].title = options[i].title.replace("&#039;", "'");
          }
        }
      }
    }

    $scope.getPos = function(event) {
      var mouseX;
      var mouseY;
      if ( event.offsetX == null ) { // Firefox
        mouseX = event.layerX;
        mouseY = event.layerY;
      } else {                       // Other browsers
        mouseX = event.offsetX;
        mouseY = event.offsetY;
      }
      $scope.posLeft = mouseX;
      return mouseX;
    }

    $scope.placesOwned = function() {
      var allPlaces = angular.copy($scope.places);
      var filteredItems = {};
      var already = false;
      var history = [];
      var owned = [];
      // Limit to players owning places
      var any = $filter('owners')($scope.players, 0);
      // Group by places
      for (var i=0; i<any.length; i++) { // Go through all owners
        angular.forEach(any[i].places, function(place) {
          if (history.indexOf(place.id) == -1) { // New place
            // Test if place is owned by other players
            var isOwned = $filter('isOwned')(place.id, any);
            if (isOwned) {
              place.owners = [];
              place.owners.push = any;
              owned.push(place);
            }
            history.push(place.id);
          } else { // Already owned, add an owner then
            place.owners.push.any;
          }

          /*
          if (history.indexOf(place.id) == -1) { // New place
            var isOwned = $filter('ownedPlaces')(allPlaces, place.id, any[i]);
            owned.push(isOwned);
          } else { // New Owner
          }
          */
        });
      }
            //console.log(owned);
            return owned;

      /*
      angular.forEach($scope.players, function(player){
        if (player.places.length > 0) {
          angular.forEach(player.places, function(place){
            if (history.indexOf(place.id) == -1) {
              //place.owners = 1;
              //this[place.id] = place;
              for (var i=0; i<$scope.places.length; i++) { // TODO : Lighter code instead of all those loops...
                if ($scope.places[i].id === place.id) {
                  this[place.id] = $scope.places[i];
                  this[place.id].owners = [];
                  this[place.id].owners.push(player);
                  this[place.id].freedomRate = Math.round(100/$scope.places[i].maxOwners);
                  this[place.id].freedomRateWidth = Math.round(100/$scope.places[i].maxOwners)*150/100;

                  if (this[place.id].freedomRate == 100) {
                    this[place.id].completed = 1;
                    $scope.completed++;
                  } else {
                    this[place.id].completed = 0;
                  }
                }
              }
              history.push(place.id);
            } else {
              this[place.id].owners.push(player);
              this[place.id].freedomRate = this[place.id].owners.length*100/this[place.id].maxOwners;
              if (this[place.id].freedomRate == 100) {
                this[place.id].completed = 1;
                $scope.completed++;
              } else {
                this[place.id].completed = 0;
              }
            }
          }, filteredItems);
          //console.log(filteredItems);
        }
      });
      */
      return filteredItems;
    }

    $scope.setCommonTask = function() {
      var any = $filter('filter')($scope.players, { isChecked:true});
      angular.forEach( any, function(player) {
        player.task = $scope.commonTask;
        $scope.updateStats(player);
      });
    }

    $scope.setCustomCommonTask = function() {
      if ($scope.anyChecked == true) {
        angular.forEach( $scope.players, function(player) {
          if (player.isChecked == true) {
            player.customTask = $scope.customCommonTask;
            $scope.manualEdit(player, 'customTask', $scope.customCommonTask);
          }
        });
      }
    }

    $scope.nextTab = function(type, index) {
      var id = type+'['+index+']';
      if (document.getElementById(id)) {
        document.getElementById(id).focus();
      }
    }

    $scope.findCheckedPlayers = function() {
      var any = $filter('filter')($scope.players, { isChecked:true});
      any && any.length > 0 ? $scope.anyChecked = true : $scope.anyChecked = false;
    }

    $scope.commonSet = function(field, value) {
      if ($scope.anyChecked == true) {
        angular.forEach( $scope.players, function(player) {
          if (player.isChecked == true) {
            if (value != '') {
              player[field] = value;
              if (field == 'XP' || field == 'HP') { // Check level (or death)
                $scope.checkLevel(player);
              }
            } else {
              var reset = 'raw'+field;
              player[field] = player[reset];
              if (field == 'XP' && player.level != player.rawlevel) { player.level = player.rawlevel; }
            }
          }
        });
      }
    }

    $scope.manualEdit = function(player, field, value) {
      $scope.commonTask = '';
      if (player.task != '' && field != 'customTask') {
        player.task = '';
        player.customTask = '';
        $scope.initStats(player);
      }
      if (field != 'customTask') {  // HP, GC, XP, Level new value is kept
        player[field] = value;
      }
      if (field == 'XP' || field == 'HP') { // Check level (or death)
        $scope.checkLevel(player);
      }
    }

    $scope.initStats = function(player) {
      player.HP = player.rawHP;
      player.GC = player.rawGC;
      player.XP = player.rawXP;
      player.level = player.rawlevel;
    }

    $scope.checkLevel = function(player) {
      // Test if level changes
      // TODO : Limit to level 100 and find a new way to reward player?
      var threshold = player.level*10+90;
      if (player.XP >= threshold) {
        var new_level = player.level+1;
        var new_threshold = new_level*10+90;
        player.XP = player.XP-threshold;
        player.level = new_level;
        //player.XP = new_threshold-player.XP;
      }
      // Death test
      if ( player.HP <= 0) { // player dies
        player.customTask = "[Joueur mort!]";
        // Loose 1 level (TODO : Loose 1 equipment?)
        player.level > 1 ? player.level-- : player.level = 1;
        // Init stats
        player.HP = 50;
        player.GC = 0;
        player.XP = 0;
      } else {
        player.customTask = '';
      }
    }

    $scope.updateStats = function(player) {
      $scope.initStats(player);
      if (player.task) {
        player.htask = player.task;
        var selectedTask = $filter('filter')($scope.tasks, {id:player.task.id});
        // Add delta according to player's equipment
        if (selectedTask[0].HP < 0) { // Negative task
          // Loss of 1 minimum whatever the equipment
          if (parseInt(player.deltaHP) > selectedTask[0].HP) {
            player.deltaHP = -selectedTask[0].HP-1;
          }
          player.HP = player.HP + selectedTask[0].HP + parseInt(player.deltaHP);
          player.XP = player.XP + selectedTask[0].XP;
        } else { // Positive task
          player.HP = player.HP + selectedTask[0].HP;
          player.XP = player.XP + selectedTask[0].XP + parseInt(player.deltaXP);
        }
        player.GC = player.GC + selectedTask[0].GC;
        if (selectedTask[0].category) {
          player.taskType = selectedTask[0].category.id;
        } else {
          player.taskType = '';
        }

        $scope.checkLevel(player);
        }
    }
  }]).
  controller('playerCtrl', ['$scope', '$http', function($scope, $http) {

    $scope.loadedHistory = false;

    $scope.init = function(rate) {
      $scope.rate = rate;
    }
    $scope.loadHistory = function (playerHistoryId) {
      if (playerHistoryId) {
        $http({ // Get player details
          url: "service-pages/?template=event&sort=-created&parent_id="+playerHistoryId,
            method: "GET",
        }).success(function(data, status, headers, config) {
          $scope.history = data.matches;
          $scope.loadedHistory = true;
        }).error(function(data, status, headers, config) {
          $scope.status = status;
        });
      }
    }

  }]).
  controller('taskCtrl', ['$scope', 'Task', '$http', function($scope, Task, $http) {

    $scope.allCategories = [];
    $scope.alreadyCat = [];
    $scope.search = {};
    $scope.search.type = '';
    
    $scope.inArray = function (list, needed) {
      return !!~list.indexOf(needed);
    }

    $scope.loadTasks = function() {
      Task.load().then( function(data) {
        $scope.tasks = data;
        angular.forEach( $scope.tasks, function(task) {
          if ( task.HP<0 ) {
            task.type = 'négative';
          } else {
            task.type = 'positive';
          }
          // Make a unique list of categories used in Tasks;
          if (!$scope.inArray($scope.alreadyCat, task.category.title)) {
            $scope.allCategories.push(task.category);
            $scope.alreadyCat += task.category.title;
          }
        });
      });
    }

    $scope.setSearch = function (tag, type) {
      $scope.search.category = tag;
      $scope.search.type = type;
      return false;
    }
  }]).
  controller('shopCtrl', ['$sce', '$scope', 'Equipment', '$filter', function($sce, $scope, Equipment, $filter) {
    $scope.tempPlayer=false;
    $scope.validOrders=[];
    $scope.minPrice = 20; // TODO : Find it by looping through items?

    $scope.search = '';
    $scope.getPos = function(event) {
      var mouseX;
      var mouseY;
      if ( event.offsetX == null ) { // Firefox
        mouseX = event.layerX;
        mouseY = event.layerY;
      } else {                       // Other browsers
        mouseX = event.offsetX;
        mouseY = event.offsetY;
      }
      $scope.posLeft = mouseX;
      return mouseX;
    }

    $scope.setFilter = function(filter){
      $scope.search = filter;
    }

    $scope.loadItems = function() { // Outside call (from template)
      Equipment.load().then( function(data) {
        $scope.items = data;
      });
    }

    $scope.filteredPlayers = function() {
      var filteredPlayers = [];
      angular.forEach($scope.players, function(player){
        if(player.GC > $scope.minPrice){
          this.push(player);
        }
      },filteredPlayers);
      return filteredPlayers;
    }

    $scope.availableItems = function() {
      var filteredItems = [];
      var already = false;
      if ($scope.tempPlayer) {
        angular.forEach($scope.items, function(item) {
          // Check if not already in validOrders
          angular.forEach($scope.validOrders, function(alreadyItem){
            if ( alreadyItem.equipmentId == item.id && alreadyItem.playerId == $scope.tempPlayer.id) {
              already = true;
            }
          });
          if(item.GC <= $scope.tempPlayer.GC && item.GC != 0 && item.level <= $scope.tempPlayer.level){
            angular.forEach($scope.tempPlayer.equipment, function(alreadyItem){
              if (alreadyItem.id === item.id) {
                already = true;
              }
            });
            if (already === false) {
              this.push(item); // New possible item
            } else {
              already = false;
            }
          }
        },filteredItems);
      }
      return filteredItems;
    }

    $scope.validOrder = function() {
      if ($scope.tempEquipment && $scope.tempPlayer) {
        $scope.validOrders.push({
          player: $scope.tempPlayer.title,
          playerId: $scope.tempPlayer.id,
          equipment: $scope.tempEquipment.title,
          equipmentId: $scope.tempEquipment.id,
          GC: parseInt($scope.tempPlayer.GC-$scope.tempEquipment.GC)
        });

        angular.forEach($scope.players, function(player){
          if (player.id == $scope.tempPlayer.id) {
            player.GC = parseInt($scope.tempPlayer.GC-$scope.tempEquipment.GC);
          }
        });
      }
      $scope.tempPlayer = '';
      $scope.tempEquipment = '';
    }

    $scope.cancelOrder = function(index) {
      $scope.validOrders.splice(index, 1);
    }
  }]).
  controller('mapCtrl', ['$scope', 'Places', '$filter', function($scope, Places, $filter) {
    $scope.tempPlayer=false;
    $scope.validOrders=[];
    $scope.predicate = 'freedomRate';
    $scope.reverse = 'reverse';
    //$scope.completed = 0;
    $scope.minPrice = 20; // TODO : Find it by looping through items?

    /*
    $scope.initPlaces = function() {
      $scope.placesOwned();
    }
    */
    
    $scope.filteredPlayers = function() {
      var filteredPlayers = [];
      angular.forEach($scope.players, function(player){
        if(player.GC > $scope.minPrice){
          this.push(player);
        }
      },filteredPlayers);
      return filteredPlayers;
    }


    $scope.availableItems = function() {
      var filteredItems = [];
      var already = false;
      if ($scope.tempPlayer) {
        angular.forEach($scope.places, function(place) {
          // Check if not already in validOrders
          angular.forEach($scope.validOrders, function(alreadyItem){
            if ( alreadyItem.placeId == place.id) { // No check for player's id because place is now unavailable to any player in the team
              already = true;
            }
          });
          if(place.freedomRate < 100 && place.GC <= $scope.tempPlayer.GC && place.level <= $scope.tempPlayer.level){
            if ($scope.tempPlayer.places.length > 0) {
              angular.forEach($scope.tempPlayer.places, function(alreadyItem){ // If place has already been freed by selected player, it disappears from the list
                if (alreadyItem.id == place.id) {
                  already = true;
                }
              });
            }
            if (already == false) {
              this.push(place); // New possible item
            } else {
              already = false;
            }
          }
        },filteredItems);
      }
      return filteredItems;
    }

    $scope.validOrder = function() {
      if ($scope.tempPlace && $scope.tempPlayer) {
        $scope.validOrders.push({
          player: $scope.tempPlayer.title,
          playerId: $scope.tempPlayer.id,
          place: $scope.tempPlace.title,
          placeId: $scope.tempPlace.id,
          level: $scope.tempPlace.level,
          GC: parseInt($scope.tempPlayer.GC-$scope.tempPlace.GC)
        });

        angular.forEach($scope.players, function(player){
          if (player.id == $scope.tempPlayer.id) {
            player.GC = parseInt($scope.tempPlayer.GC-$scope.tempPlace.GC);
          }
        });
      }
      $scope.tempPlayer = '';
      $scope.tempPlace = '';
    }

    $scope.cancelOrder = function(index) {
      $scope.validOrders.splice(index, 1);
    }
  }]).
  controller('placesCtrl', ['$scope', '$http', function($scope, $http) {
    $scope.thumbView = true;

    $scope.loadPlaces = function(pageNum, param) {
      if (pageNum > 1) {
        var root = "service-pages/page"+pageNum+"/?";
      } else {
        var root = "service-pages/?";
      }
      $http({ // Get all places
          url: root+"template=place"+param+"&name!=places&sort=name&limit=30",
          method: "GET",
      }).success(function(data, status, headers, config) {
          $scope.places = data.matches;
      }).error(function(data, status, headers, config) {
          $scope.status = status;
      });
    }
  }]);

