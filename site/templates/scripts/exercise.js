var exerciseApp = angular.module('exerciseApp', ['ngAnimate', 'ngSanitize']);

exerciseApp.controller('TranslateCtrl', function ($scope, $http, $timeout, $interval, $window) {
  $scope.waitForStart = true;
  $scope.history = new Array();
  $scope.monsterHP = 100;
  $scope.playerHP = 100;
  $scope.monsterPower = 10; // 10 wrong answers = to lose, depending on player's equipment
  $scope.playerPower = 10; // 10 correct answers to win, depending on player's equipment
  $scope.nbAttacks = 0; // # of words
  $scope.counter = 0; // # of tries to copy correction
  $scope.exType = '';
  $scope.exData = '';
  $scope.correct = false;
  $scope.wrong = false;
  $scope.wonFight = false;
  $scope.isFocused = false; // Automatic focus on input field
  $scope.runningInterval = false;
  
	$window.addEventListener("beforeunload", function (e) {
		if (!$scope.waitForStart) {
			var confirmationMessage = "Do you really want to quit the page?";

			(e || window.event).returnValue = confirmationMessage; //Gecko + IE
			return confirmationMessage;                            //Webkit, Safari, Chrome
		} else {
			return false;
		}
	});

  $scope.init = function(exerciseId, redirectUrl, playerId, weaponRatio, protectionRatio, submitUrl) {
    $scope.playerPower += parseInt(weaponRatio);
    $scope.monsterPower -= parseInt(protectionRatio);
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
    $scope.wrong = false;
    $scope.showCorrection = '';
    switch(exType) {
      case 'translate' :
        // Pick a random line and build words array
				// TODO : Copy from training for a random line different from previous
        var randLine = $scope.allLines[Math.floor(Math.random()*$scope.allLines.length)];
        var randWords = randLine.split(",");
        // Pick a random word
        var randIndex = Math.round(Math.random());
        if (randIndex == 0) { randOpp = 1; } else { randOpp = 0; }
        // Test for multiple possible words and answers
        $scope.allWords = randWords[randIndex].trim().split("|");
        $scope.allCorrections = randWords[randOpp].trim().split("|");
        // Pick 1 random word (different from previous word)
				var prevWord = $scope.history[$scope.history.length-1];
        //if ( $scope.nbAttacks > 1) { // More than 1 word in history
        if ( prevWord ) { // More than 1 word in history
          while ( $scope.word == $scope.history[$scope.history.length-1]) {
            $scope.word = chance.pick($scope.allWords);
          }
        } else {
            $scope.word = chance.pick($scope.allWords);
        }
        // Add word to history
        $scope.history.push($scope.word);
        $scope.nbAttacks += 1;
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
      $scope.wrong = true;
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

exerciseApp.controller('TrainingCtrl', function ($scope, $http, $timeout, $interval, $window) {
  $scope.waitForStart = true;
  $scope.result = 0;
  $scope.history = new Array();
  $scope.lineHistory = new Array();
	$scope.newCorrections = new Array();
  $scope.counter = 0; // #
  $scope.exType = '';
  $scope.exData = '';
  $scope.correct = false;
  $scope.utPoint = false;
  $scope.isFocused = false; // Automatic focus on input field
  $scope.runningInterval = false;

	$window.addEventListener("beforeunload", function (e) {
		if (!$scope.waitForStart) {
			var confirmationMessage = "Do you really want to quit the page?";

			(e || window.event).returnValue = confirmationMessage; //Gecko + IE
			return confirmationMessage;                            //Webkit, Safari, Chrome
		} else {
			return false;
		}
	});

	$scope.nationality = ['French', 'English', 'Scottish', 'Welsh', 'American', 'Australian', 'Canadian', 'Irish', 'German', 'Spanish', 'Italian', 'Swedish', 'Brazilian', 'Greek', 'Turkish', 'Russian', 'Chinese', 'Belgian'];

  $scope.init = function(exerciseId, redirectUrl, playerId, submitUrl) {
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
      // Manage special variables
			// %fname% : First name (female or male)
			// %fnamef% : First name female
			// %fnamem% : First name male
			// %name% : Full name (female or male)
			// %age% : Age
			// %nationality% : Nationality
			// (...) : Displayed text but optional in answers
      for (var i=0; i<$scope.allLines.length; i++) {
        var str = $scope.allLines[i];
				var pattern = /%name%/i;
        if (str.search(pattern) != -1 ) {
					var sub = chance.name();
          $scope.allLines[i] = str.replace(pattern, sub);
					str = $scope.allLines[i];
				}
				var pattern = /%fname%/i;
        if (str.search(pattern) != -1 ) {
					var sub = chance.first();
          $scope.allLines[i] = str.replace(pattern, sub);
					str = $scope.allLines[i];
				}
				var pattern = /%fnamef%/i;
        if (str.search(pattern) != -1 ) {
					var sub = chance.first({gender:"female"});
          $scope.allLines[i] = str.replace(pattern, sub);
					str = $scope.allLines[i];
				}
				var pattern = /%fnamem%/i;
        if (str.search(pattern) != -1 ) {
					var sub = chance.first({gender:"male"});
          $scope.allLines[i] = str.replace(pattern, sub);
					str = $scope.allLines[i];
				}
				var pattern = /%age%/i;
        if (str.search(pattern) != -1 ) {
					var sub = chance.age();
          $scope.allLines[i] = str.replace(pattern, sub);
					str = $scope.allLines[i];
				}
				var pattern = /%nationality%/i;
        if (str.search(pattern) != -1 ) {
					var sub = chance.pick($scope.nationality);
          $scope.allLines[i] = str.replace(pattern, sub);
					str = $scope.allLines[i];
				}
			}
      // Enable start Fight button
      $scope.waitForStart = false;
      // Pick first question
      $scope.pickQuestion($scope.exType);
    })
    $scope.exerciseId = exerciseId;
    $scope.redirectUrl = redirectUrl;
    $scope.playerId = playerId;
    $scope.submitUrl = submitUrl;
  }

  $scope.pickQuestion = function(exType) {
    // End animation
    $timeout(function() { $scope.correct = false; }, 1000);
		// Init new question
    $scope.wrong = false;
    $scope.showCorrection = '';
		// Pick a random line
		// Different from previous line
		var randNum = Math.floor(Math.random()*$scope.allLines.length);
		if ( $scope.lineHistory.length > 1) {
			while ( randNum == $scope.lineHistory[$scope.lineHistory.length-1] ) {
				var randNum = Math.floor(Math.random()*$scope.allLines.length);
			}
		}
		$scope.lineHistory.push(randNum);
		var randLine = $scope.allLines[randNum];
    switch(exType) {
      case 'translate' :
        var randWords = randLine.split(",");
        // Pick a random word : either left or right
        var randIndex = Math.round(Math.random());
        if (randIndex == 0) { randOpp = 1; } else { randOpp = 0; }
        // Test for multiple possible words and answers
        $scope.allWords = randWords[randIndex].split("|");
				// Trim eventual extra spaces
				for (i=0; i<$scope.allWords.length; i++) {
					$scope.allWords[i] = $scope.allWords[i].trim();
				}
				var allCorrections = randWords[randOpp].split("|");
				$scope.allCorrections = $scope.parseCorrections(allCorrections);
        break;
      case 'quiz' :
				// Question will be $scope.allWords[0]
        var quiz = randLine.split("::");
				$scope.allWords = quiz[0].split("|");
        // Test for multiple possible answers
        var allCorrections = quiz[1].split("|");
				$scope.allCorrections = $scope.parseCorrections(allCorrections);
        break;
      default:
        console.log('Unknown exType');
        break;
    }
		// Pick 1 random word from possible words
		if ($scope.allWords.length > 1) {
			$scope.word = chance.pick($scope.allWords);
		} else {
			$scope.word = $scope.allWords[0];
		}
		// Add word to history
		$scope.history.push($scope.word);
		// Help with 1st mixed answers
		$scope.mixedWord = $scope.shuffle($scope.allCorrections[0]);
		// Set focus on input field
		$timeout($scope.focusInput, 300);
  }

	$scope.parseCorrections = function(allCorrections) {
		var newCorrections = [];
		var tempCorrections = [];
		for (i=0; i<allCorrections.length; i++) {
			// Trim extra spaces
			allCorrections[i] = allCorrections[i].trim();
			// Add optional text functionality : (...)
			var pattern = /\((.*?)\)/i;
			var str = allCorrections[i];
			if (str.search(pattern) != -1 ) {
				tempCorrections.push(str.replace(pattern, ""));
				tempCorrections.push(str.replace(pattern, "$1"));
			}
			// Add to newCorrections
			for (var j=0; j<tempCorrections.length; j++) {
				newCorrections.push(tempCorrections[j].trim());
			}
			tempCorrections = [];
		}
		// Add to allCorrections
		for (var j=0; j<newCorrections.length; j++) {
			allCorrections.push(newCorrections[j]);
		}
		newCorrections = [];
		return allCorrections;
	}

  $scope.shuffle = function (str) {
      var a = str.split(""),
          n = a.length;

      for(var i = n - 1; i > 0; i--) {
          var j = Math.floor(Math.random() * (i + 1));
          var tmp = a[i];
          a[i] = a[j];
          a[j] = tmp;
      }
      return a.join("");
  }

  $scope.attack = function() {
    if ($scope.playerAnswer) {
      $scope.checkAnswer($scope.playerAnswer);
    }
  }

  $scope.checkAnswer = function(submitted) {
    if ($scope.allCorrections.indexOf(submitted) != -1 ) { // Correct answer
			$scope.playerAnswer = '';
			$scope.isFocused = false;
			if (!$scope.wrong) { // Count word only if no need for answer
				// Trigger animation
				$scope.correct = true;
				$scope.counter++;
			}
      // Get number of words and calculate result
      if ($scope.counter >= 10) {
        if (Math.floor($scope.counter/10) > $scope.result) {
          $scope.result++;
          $scope.utPoint = true;
          $timeout(function() { $scope.utPoint = false; }, 1000);
          $scope.stopSession(); // Alert +1 U.T. : Stop or continue?
        }
      }
      // Pick another question (timeout workaround so animation starts from 0)
      $scope.pickQuestion($scope.exType);
    } else { // Wrong answer
      $scope.wrong = true;
    }
  }

  $scope.stopSession = function() {
    if ($scope.result >= 1) {
      swal({
        html: true,
        title: "Stop training?",
        text: "Good job! You've set a number of <span class='label label-success'>"+$scope.counter+" words</span> in your brain. This will credit you of <span class='label label-success'>+"+$scope.result+" U.T.</span>",
        type: "success",
        showCancelButton : true,
        cancelButtonText: "Keep the helmet on (Continue training)",
        confirmButtonText: "Take the helmet off (Stop training & Save results)"
      }, function(isConfirm) {
        if (isConfirm) { // Save and redirect
          $scope.saveData(true);
					$scope.waitForStart = true;
        } else { // Save and continue
					// Do not save (prevent a bug?), just continue
          //$scope.saveData(false);
        }
      });
    } else {
      swal({
        title: "Stop training?",
        text: "You didn't use the memory helmet enough to record words in your brain. Are you sure you want to stop?",
        type: "warning",
        showCancelButton : true,
        cancelButtonText: "Keep the helmet on (Continue training)",
        confirmButtonText: "Take the helmet off (Stop training)"
      }, function() {
        // DO not save, but redirect
        $window.location.href = $scope.redirectUrl;
      });;
    }
  }

  $scope.saveData = function (redirect) {
    // Save result
    $http({
      url: $scope.submitUrl,
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      data: $.param({
        exerciseId : $scope.exerciseId,
        training: true,
        result : $scope.result
      })
    }).then(function(data, status, headers, config){ //make a get request to mock json file.
      $scope.saved = 'Result saved!';
			if (data["data"] == '1') {
				swal({
					html: true,
					title: "Congratulations !",
					text: "You've just set a new training record !<br /><br /><small>This message should disappear in 2 seconds. If not, click 'OK' below :)</small>",
					type: "success",
					timer: 2000
				}, function() {
					if (redirect === true) {
						$window.location.href = $scope.redirectUrl;
					}
				});
			} else {
				if (redirect === true) {
					$window.location.href = $scope.redirectUrl;
				}
			}
    }, function(data, status, headers, config) {
			swal("Sorry, but an error occurred.", "Please, contact the admin.", "error");
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
