var exerciseApp = angular.module('exerciseApp', ['ngAnimate', 'ngSanitize']);

exerciseApp.controller('TranslateCtrl', function ($scope, $http, $timeout, $interval, $window) {
  $scope.history = new Array();
  $scope.waitForStart = true;
  $scope.monsterHP = 100;
  $scope.wonFight = false;
  $scope.playerHP = 100;
  $scope.hit = 10; // 10 correct answers to win, variable according to player's equipment
  $scope.monsterPower = 10;
  $scope.playerPower = 10;
  $scope.counter = 0; // # of tries to copy correction
  $scope.exType = '';
  $scope.exData = '';
  $scope.correct = false;
  $scope.nbAttacks = 0;
  $scope.showInput = false; // Hide input field at first
  $scope.isFocused = false;
  $scope.runningInterval = false;
  $scope.questionClass = "bubble-left";
  
  $scope.init = function(exerciseId, redirectUrl, playerId, weaponRatio, protectionRatio, submitUrl) {
    console.log('weaponRatio:'+weaponRatio);
    console.log('protectionRatio:'+protectionRatio);
    $scope.playerPower += parseInt(weaponRatio);
    $scope.monsterPower -= parseInt(protectionRatio);
    console.log('playerPower:'+$scope.playerPower);
    console.log('monsterPower:'+$scope.monsterPower);
    $http.get('service-pages/?template=exercise&id='+exerciseId).then(function(response){
      var newLines = new Array();
      $scope.exType = response.data.matches[0].type.name;
      $scope.exData = response.data.matches[0].exData;
      // Replace some escaped characters
      var tmp = $scope.exData;
      $scope.exData = tmp.replace(/&#039;/g, "'");
      // Build data array
      $scope.allLines = $scope.exData.split("\n");
      // Manage priorities
      var pattern = /^{(\d+)}/i; // {n} at the beginning of a line
      for (var i=0; i<$scope.allLines.length; i++) {
        var str = $scope.allLines[i];
        if (str.search(pattern) != -1 ) {
          // Get n and copy the line accordingly
          var n = str.match(pattern);
          // Clean original line
          $scope.allLines[i] = str.replace(pattern, '');
          for (var j=0; j<n[1]; j++) {
            newLines.push($scope.allLines[i]);
          }
        }
      }
      // Add new lines
      for (var i=0; i<newLines.length; i++) {
        $scope.allLines.push(newLines[i]);
      }
      // Enable start Fight button
      $scope.waitForStart = false;
      $scope.submitUrl = submitUrl;
    })

    $scope.exerciseId = exerciseId;
    $scope.redirectUrl = redirectUrl;
    $scope.playerId = playerId;
  }

  $scope.startFight = function() {
    $scope.pickQuestion($scope.exType);
  }

  $scope.pickQuestion = function(exType) {
    $scope.correct = false;
    $scope.questionClass = "bubble-left";
    $scope.showCorrection = '';
    switch(exType) {
      case 'translate' :
        // Pick a random line and build words array
        var randLine = $scope.allLines[Math.floor(Math.random()*$scope.allLines.length)];
        var randWords = randLine.split(",");
        // Pick a random word
        var randIndex = Math.round(Math.random());
        if (randIndex == 0) { randOpp = 1; } else { randOpp = 0; }
        // Test for multiple possible words and answers
        $scope.allWords = randWords[randIndex].trim().split("|");
        $scope.allCorrections = randWords[randOpp].trim().split("|");
        // Pick 1 random word (different from previous word)
        while ( $scope.word == $scope.history[$scope.history.length-1]) {
          $scope.word = chance.pick($scope.allWords);
        }
        // Add word to history
        $scope.history.push($scope.word);
        // console.log($scope.history);
        // $scope.correction = chance.pick($scope.allCorrections);
        $scope.nbAttacks += 1;
        // console.log('Word:'+$scope.word+'-Correction:'+$scope.correction);
        $scope.throwQuestion();
        break;
      default:
        console.log('Unknown exType');
        break;
    }
  }

  $scope.throwQuestion = function() {
    // Set focus on input field
    $timeout($scope.focusInput, 300);
    // Player loses 1HP every second
    if ($scope.runningInterval == false ) {
      $scope.promise = $interval($scope.loseHP, 1000);
    }
  }

  $scope.attack = function() {
    // Make it a Dodge action if player's answer is empty (means player hit the Enter key)
    if (!$scope.playerAnswer) {
      $scope.dodge();
    } else {
      $scope.checkAnswer($scope.playerAnswer);
    }
  }

  $scope.dodge = function() {
    $scope.showCorrection = $scope.allCorrections.join(', ');
    // Player admits "I don't know" : reduced loss speed
    // Stop animation and start again slowly
    $interval.cancel($scope.promise);
    $scope.promise = $interval($scope.loseHP, 1500);
    // Reduce monster loss
    $scope.counter = 10;
  }

  $scope.checkAnswer = function(submitted) {
    if ($scope.allCorrections.indexOf(submitted) != -1 ) { // Correct answer
      // Trigger explode animation
      $scope.correct = true;
      $scope.questionClass = 'bubble-left explode';
      // Stop HP loss
      $interval.cancel($scope.promise);
      $scope.runningInterval = false;
      if ($scope.showCorrection != '') { // Previous wrong answer, just copy correction, partial loss for monster
        // Check time and change loss accordingly
        if ($scope.counter <= 5) {
          $scope.monsterHP = $scope.monsterHP - Math.round($scope.playerPower/2);
        }
        if ($scope.counter > 5 && $scope.counter <= 10) {
          $scope.monsterHP = $scope.monsterHP - Math.round($scope.playerPower/3);
        }
        if ($scope.counter > 10) {
          $scope.monsterHP = $scope.monsterHP - 1;
        }
        $scope.counter = 0;
      } else { // First time hit, full loss for monster
        $scope.monsterHP = $scope.monsterHP - $scope.playerPower;
      }
      if ($scope.monsterHP <= 0) { // Player wins, monster loses
        $scope.winFight();
      } else {
        $scope.playerAnswer = '';
        $scope.isFocused = false;
        // Pick another question (timeout workaround so animation starts from 0)
        $timeout(function() { $scope.pickQuestion($scope.exType); }, 550);
        //$scope.pickQuestion($scope.exType);
      }
    } else { // Wrong answer
      // Full HP loss
      $scope.playerHP = $scope.playerHP - $scope.monsterPower;
      if ($scope.playerHP <= 0) { // Monster wins, player loses
        $scope.loseFight();
      } else { // Player must copy the correct translation
        $scope.showCorrection = $scope.allCorrections.join(', ');
      }
    }
  }

  $scope.loseHP = function() {
    $scope.runningInterval = true;
    $scope.playerHP = $scope.playerHP - 1;
    $scope.counter += 1;
    if ($scope.playerHP <= 0) { // Monster wins, player loses
        //console.log('MONSTER WINS THE FIGHT! PLAYER LOSES!');
        $scope.loseFight();
    }
  }

  $scope.winFight = function () {
    // Stop HP loss
    $interval.cancel($scope.promise);
    $scope.runningInterval = false;
    // Get rid of monster
    $scope.wonFight = true;
    // $scope.saveData();
    swal({
      title: "Congratulations! You won!",
      text: "The monster ran away! But beware, he may come back in a few weeks ;) This excellent fight credited your player with XP and GC.",
      type: "success",
      confirmButtonText: "Cool! Let's see my updated profile!"
    }, function() {
      $window.location.href = $scope.redirectUrl;
    });;
  }

  $scope.loseFight = function () {
    // Stop animation
    // $scope.questionClass = 'bubble-left';
    $interval.cancel($scope.promise);
    $scope.runningInterval = false;
    // $scope.saveData();
    swal({
      title: "You lost!",
      text: "You need to revise more and fight back against this monster! Don't give up!",
      type: "error",
      confirmButtonText: "Ok, I'll do better next time..."
    }, function() {
      $window.location.href = $scope.redirectUrl;
    });
  }

  $scope.saveData = function () {
    // $scope.monsterHP = correct answers
    // $scope.playerHP = wrong answers, needed time
    // $scope.nbAttacks = nb of words, the more = a lot of copying
    // Get some quality indicator
    if ($scope.playerHP <= 0) { // Lost fight : very bad exercise
      if ($scope.monsterHP > 50) { // Very bad result
        $scope.result = 'RR';
      } else { // Bad result
        $scope.result = 'R';
      }
    }
    if ($scope.monsterHP <= 0) { // Won fight : Good exercise
      if ($scope.playerHP > 50 && $scope.nbAttacks < 15) { // Very good result
        $scope.result = 'VV';
      } else { // Good result
        $scope.result = 'V';
      }
    }
    // Save result
    $http({
      url: $scope.submitUrl,
      // url: 'service-pages/?name=submitFight',
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      //data : myData
      data: $.param({
        exerciseId : $scope.exerciseId,
        playerId : $scope.playerId,
        playerHP : $scope.playerHP,
        monsterHP : $scope.monsterHP,
        nbAttacks : $scope.nbAttacks,
        result : $scope.result
      })
      }).then(function(data, status, headers, config){ //make a get request to mock json file.
      $scope.saved = 'Result saved!';
    }, function(data, status, headers, config) {
      $scope.saved = 'Error! Please contact the administrator.';
    })
  }

  $scope.focusInput = function() {
    $scope.isFocused = !$scope.isFocused;
  }

});

exerciseApp.directive('syncFocusWith', function($timeout, $rootScope) {
    return {
        restrict: 'A',
        scope: {
            focusValue: "=syncFocusWith"
        },
        link: function($scope, $element, attrs) {
            $scope.$watch("focusValue", function(currentValue, previousValue) {
                if (currentValue === true && !previousValue) {
                    $element[0].focus();
                } else if (currentValue === false && previousValue) {
                    $element[0].blur();
                }
            })
        }
    }
});

exerciseApp.directive('myEnter', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if(event.which === 13) {
                scope.$apply(function (){
                    scope.$eval(attrs.myEnter);
                });

                event.preventDefault();
 ;           }
        });
    };
});
