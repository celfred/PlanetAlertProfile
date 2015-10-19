var exerciseApp = angular.module('exerciseApp', []);

exerciseApp.controller('TranslateCtrl', function ($scope, $http, $timeout) {
  $scope.monsterHP = 100;
  $scope.playerHP = 100;
  $scope.hit = 10; // 10 correct answers to win
  $scope.exType = '';
  $scope.exData = '';
  $scope.showInput = false; // Hide input field at first
  $scope.isFocused = false;
  
  $scope.init = function(exerciseId) {
    $http.get('service-pages/?template=exercise&id='+exerciseId).then(function(response){ //make a get request to mock json file.
      $scope.exData = response.data.matches[0].exData; //Assign data received to $scope.data
      $scope.exType = response.data.matches[0].type.name;
      $scope.pickQuestion($scope.exType);
    })
  }

  $scope.pickQuestion = function(exType) {
    $scope.showCorrection = '';
    switch(exType) {
      case 'translate' :
        // Build data array
        var lines = $scope.exData.split("\n");
        // Pick a random line and build words array
        var randLine = lines[Math.floor(Math.random()*lines.length)];
        var randWords = randLine.split(",");

        // Pick a random word
        var randIndex = Math.round(Math.random());
        if (randIndex == 0) { randOpp = 1; } else { randOpp = 0; }
        $scope.word = randWords[randIndex].trim();
        $scope.correction = randWords[randOpp].trim();
        console.log('Word:'+$scope.word+'-Correction:'+$scope.correction);
        break;
      default:
        console.log('Unknown exType');
        break;
    }
  }

  $scope.attack = function() {
    // First click : I know!
    // Show text input for player answer
    if (!$scope.showInput) {
      $scope.showInput = true;
      // Wait for input to be displayed to set focus
      $timeout($scope.focusInput, 300);
    } else { // 2nd click : let's check the answer
      console.log('Player\'s answer : ' + $scope.playerAnswer);
      $scope.checkAnswer($scope.playerAnswer);
    }
  }

  $scope.checkAnswer = function(submitted) {
    if (submitted == $scope.correction ) {
      console.log('CORRECT');
      if ($scope.showCorrection != '') { // Previous wrong answer, just copy correction, half loss for monster
        // TODO : Check time to copy and change loss accordingly
        $scope.monsterHP = $scope.monsterHP - 2;
      } else { // First time hit, full loss for monster
        $scope.monsterHP = $scope.monsterHP - $scope.hit;
      }
      if ($scope.monsterHP <= 0) {
        console.log('PLAYER WINS THE FIGHT! MONSTER LOOSES!');
      } else {
        // Empty & Hide input field
        $scope.playerAnswer = '';
        $scope.isFocused = false;
        $scope.showInput = false;
        // Pick another question
        $scope.pickQuestion($scope.exType);
      }
    } else {
      console.log('WRONG');
      $scope.playerHP = $scope.playerHP - $scope.hit;
      if ($scope.playerHP <= 0) {
        console.log('MONSTER WINS THE FIGHT! PLAYER LOOSES!');
      } else {
        $scope.showCorrection = $scope.correction;
        console.log();
      }
    }
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
            }
        });
    };
});
