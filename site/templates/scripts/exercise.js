var exerciseApp = angular.module('exerciseApp', ['ngAnimate', 'ngSanitize']);

exerciseApp.service('myData', function($http) {
	var exerciseData = [];
	var allLines = [];
	var lineHistory = [];
	var history = [];

	return {
		getData : function(url) {
			return $http.get(url).then(function(response){
				exerciseData['exType'] = response.data.matches[0].type.name;
				exerciseData['rawData'] = response.data.matches[0].exData;

				return exerciseData;
			})
		},

		parseData : function() {
			var newLines = new Array();
			// Replace some escaped characters
			// var tmp = rawData;
			var tmp = exerciseData['rawData'];
			rawData = tmp.replace(/&#039;/g, "'");
			// Build data array
			allLines = rawData.split("\n");
			// Manage priorities
			var pattern = /^{(\d+)}/i; // {n} at the beginning of a line
			for (var i=0; i<allLines.length; i++) {
				var str = allLines[i];
				if (str.search(pattern) != -1 ) {
					// Get n and copy the line accordingly
					var n = str.match(pattern);
					// Clean original line
					allLines[i] = str.replace(pattern, '');
					for (var j=0; j<n[1]; j++) {
						newLines.push(allLines[i]);
					}
				}
				// Manage special variables
				// %fname% : First name (female or male)
				// %fnamef% : First name female
				// %fnamem% : First name male
				// %name% : Full name (female or male)
				// %age% : Age
				// %nationality% : Nationality
				// (...) : Displayed text but optional in answers
				var nationality = ['French', 'English', 'Scottish', 'Welsh', 'American', 'Australian', 'Canadian', 'Irish', 'German', 'Spanish', 'Italian', 'Swedish', 'Brazilian', 'Greek', 'Turkish', 'Russian', 'Chinese', 'Belgian'];
				for (var i=0; i<allLines.length; i++) {
					var str = allLines[i];
					var pattern = /%name%/i;
					if (str.search(pattern) != -1 ) {
						var sub = chance.name();
						allLines[i] = str.replace(pattern, sub);
						str = allLines[i];
					}
					var pattern = /%fname%/i;
					if (str.search(pattern) != -1 ) {
						var sub = chance.first();
						allLines[i] = str.replace(pattern, sub);
						str = allLines[i];
					}
					var pattern = /%fnamef%/i;
					if (str.search(pattern) != -1 ) {
						var sub = chance.first({gender:"female"});
						allLines[i] = str.replace(pattern, sub);
						str = allLines[i];
					}
					var pattern = /%fnamem%/i;
					if (str.search(pattern) != -1 ) {
						var sub = chance.first({gender:"male"});
						allLines[i] = str.replace(pattern, sub);
						str = allLines[i];
					}
					var pattern = /%age%/i;
					if (str.search(pattern) != -1 ) {
						var sub = chance.age();
						allLines[i] = str.replace(pattern, sub);
						str = allLines[i];
					}
					var pattern = /%nationality%/i;
					if (str.search(pattern) != -1 ) {
						var sub = chance.pick(nationality);
						allLines[i] = str.replace(pattern, sub);
						str = allLines[i];
					}
				}
			}
			// Add new lines
			for (var i=0; i<newLines.length; i++) {
				allLines.push(newLines[i]);
			}

			return allLines;
		},

		pickQuestion : function () {
			var question = [];
			// Pick a random line
			// Different from previous line
			var randNum = Math.floor(Math.random()*allLines.length);
			if ( lineHistory.length >= 1) {
				while ( randNum == lineHistory[lineHistory.length-1] ) {
					var randNum = Math.floor(Math.random()*allLines.length);
				}
			}
			lineHistory.push(randNum);
			var randLine = allLines[randNum];
			switch(exerciseData['exType']) {
				case 'translate' :
					var randWords = randLine.split(",");
					// Pick target language (right)
					// TODO : parameter to test if memory or fight
					// Test for multiple possible words and answers
					var allWords = randWords[1].split("|");
					// Trim eventual extra spaces
					for (i=0; i<allWords.length; i++) {
						allWords[i] = allWords[i].trim();
					}
					var allCorrections = randWords[0].split("|");
					question['allCorrections'] = this.parseCorrections(allCorrections);
					break;
				case 'quiz' :
					// Question will be allWords[0]
					var quiz = randLine.split("::");
					var allWords = quiz[0].split("|");
					// Test for multiple possible answers
					var allCorrections = quiz[1].split("|");
					question['allCorrections'] = this.parseCorrections(allCorrections);
					break;
				default: 
					console.log('Unknown exType');
			}
			// Pick 1 random word from possible words
			if (allWords.length > 1) {
				question['word'] = chance.pick(allWords);
			} else {
				question['word'] = allWords[0];
			}
			// Add word to history
			history.push(question['word']);
			// Help with 1st mixed answers
			question['mixedWord'] = this.shuffle(question['allCorrections'][0]);

			return question;
		},

		parseCorrections : function(allCorrections) {
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
		},

		shuffle : function(str) {
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
	}
});

exerciseApp.controller('FightCtrl', function ($scope, $http, $timeout, $interval, $window, myData) {
  $scope.waitForStart = true;
  $scope.monsterHP = 100;
  $scope.playerHP = 100;
  $scope.monsterPower = 8; // Depending on player's equipment
  $scope.playerPower = 3; // Depending on player's equipment
  $scope.nbAttacks = 0; // # of words
  $scope.counter = 0; // # of tries to copy correction
	$scope.playerDamage = 0;
	$scope.hidePlayerDamage = true;
	$scope.monsterDamage = 0;
	$scope.hideMonsterDamage = true;
	$scope.question = [];
  $scope.correct = false;
  $scope.wrong = false;
	$scope.shownWords = 0;
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
		var url = 'service-pages/?template=exercise&id='+exerciseId;
		myData.getData(url).then( function(exerciseData) {
			$scope.exType = exerciseData['exType'];
			$scope.allLines = myData.parseData();
			// Enable start Fight button
			$scope.waitForStart = false;
    })
    $scope.exerciseId = exerciseId;
    $scope.redirectUrl = redirectUrl;
		$scope.submitUrl = submitUrl;
    $scope.playerId = playerId;
  }

  $scope.startFight = function() {
		$scope.started = true;
		// Pick a new question
		$scope.question = myData.pickQuestion();
		$scope.initQuestion();
  }

	$scope.initQuestion = function() {
		$scope.word = $scope.question['word'];
		$scope.mixedWord = $scope.question['mixedWord'];
		$scope.allCorrections = $scope.question['allCorrections'];
    // End animation
    $timeout(function() { $scope.correct = false; }, 1000);
		// Init new question
    $scope.wrong = false;
    $scope.showCorrection = '';
		// Throw question
		$scope.throwQuestion();
		// Count new question
		$scope.nbAttacks += 1;
		// Reset counter
		$scope.counter = 0;
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
		// Show correction
    $scope.showCorrection = $scope.allCorrections.join(', ');
    // Player admits "I don't know" : reduced loss
		$scope.playerDamage = Math.round($scope.monsterPower/2);
		$scope.playerHP = $scope.playerHP - $scope.playerDamage;
		$scope.hidePlayerDamage = false;
		$timeout(function() { $scope.hidePlayerDamage = true; }, 1500);
    $scope.counter = 10;
		$scope.shownWords += 1;
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
				// Player answered under 5 seconds
        if ($scope.counter <= 5) {
					$scope.monsterDamage = Math.round($scope.playerPower/2);
        }
				// Player answered between 5 and 10 seconds
        if ($scope.counter > 5 && $scope.counter <= 10) {
          $scope.monsterDamage = Math.round($scope.playerPower/3);
        }
				// Player answered after 10 seconds
        if ($scope.counter > 10) {
          $scope.monsterDamage = 1;
        }
        $scope.counter = 0;
      } else { // First time hit
        if ($scope.counter <= 10) { // Player answered under 10 seconds, full loss for monster
					$scope.monsterDamage = $scope.playerPower;
				} else { // Half-powered loss
          $scope.monsterDamage = Math.round($scope.playerPower/2);
				}
				$scope.playerHP += 1;
				if ($scope.playerHP > 100) { $scope.playerHP = 100;}
			}
			$scope.monsterHP = $scope.monsterHP - $scope.monsterDamage;
			$scope.hideMonsterDamage = false;
			$timeout(function() { $scope.hideMonsterDamage = true; }, 1500);
      if ($scope.monsterHP <= 0) { // Player wins, monster loses
        $scope.winFight();
      } else {
        $scope.playerAnswer = '';
        $scope.isFocused = false;
        // Pick another question (timeout workaround so animation starts from 0)
        $timeout(function() { $scope.question = myData.pickQuestion(); $scope.initQuestion(); }, 550);
      }
    } else { // Wrong answer
			$scope.shownWords += 1;
      $scope.wrong = true;
      // Full HP loss
			$scope.playerDamage = $scope.monsterPower;
      $scope.playerHP = $scope.playerHP - $scope.playerDamage;
			$scope.hidePlayerDamage = false;
			$timeout(function() { $scope.hidePlayerDamage = true; }, 1500);

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
		$scope.waitForStart = true;
		$scope.quality = Math.round(100-(($scope.shownWords/$scope.nbAttacks)*100));
		if ($scope.quality > 90) {
			var feedback = 'sweeping victory (VV)';
			$scope.result = 'VV';
		} else {
			var feedback = 'won (V)';
			$scope.result = 'V';
		}
    $scope.saveData();
    swal({
			html: true,
      title: "Congratulations !",
      text: "The monster ran away ! You have repelled "+ $scope.nbAttacks +" words with a quality of "+ $scope.quality +"%.<br />This is a <span class='label label-primary'>"+ feedback +"</span> fight !",
      type: "success",
      confirmButtonText: "Cool ! Let's see my updated profile !"
    }, function() {
      $window.location.href = $scope.redirectUrl;
    });;
  }

  $scope.loseFight = function () {
    // Stop animation
    $interval.cancel($scope.promise);
    $scope.runningInterval = false;
		$scope.waitForStart = true;
		$scope.quality = Math.round(100-(($scope.shownWords/$scope.nbAttacks)*100));
		if ($scope.monsterHP < 50) {
			var feedback = 'lost (R)';
			$scope.result = 'R';
		} else {
			var feedback = 'heavy defeat (RR)';
			$scope.result = 'RR';
		}
    $scope.saveData();
    swal({
			html: true,
      title: "Sorry !",
      text: "You need to revise more and fight back against this monster ! Don't give up !<br />This is a <span class='label label-primary'>"+feedback+"</span> fight.",
      type: "error",
      confirmButtonText: "Ok, I'll do better next time..."
    }, function() {
      $window.location.href = $scope.redirectUrl;
    });
  }

  $scope.saveData = function () { // Save result
    $http({
      url: $scope.submitUrl,
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      data: $.param({
        exerciseId : $scope.exerciseId,
        quality : $scope.quality,
        result : $scope.result
      })
		}).then(function(data, status, headers, config){ //make a get request to mock json file.
      $scope.saved = 'Result saved!';
    }, function(data, status, headers, config) {
			swal("Sorry, but an error occurred.", "Please, contact the admin.", "error");
      $scope.saved = 'Error! Please contact the administrator.';
    })
  }

  $scope.focusInput = function() {
    $scope.isFocused = !$scope.isFocused;
  }
});

exerciseApp.controller('TrainingCtrl', function ($scope, $http, $timeout, $interval, $window, myData) {
  $scope.waitForStart = true;
  $scope.result = 0;
	$scope.question = [];
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

  $scope.init = function(exerciseId, redirectUrl, playerId, submitUrl) {
		var url = 'service-pages/?template=exercise&id='+exerciseId;
		myData.getData(url).then( function(exerciseData) {
			$scope.exType = exerciseData['exType'];
			$scope.allLines = myData.parseData();
			// Enable start Fight button
			$scope.waitForStart = false;
			// Pick another question
			$scope.question = myData.pickQuestion();
			$scope.initQuestion();
    })
    $scope.exerciseId = exerciseId;
    $scope.redirectUrl = redirectUrl;
    $scope.playerId = playerId;
    $scope.submitUrl = submitUrl;
  }

  $scope.attack = function() {
    if ($scope.playerAnswer) {
      $scope.checkAnswer($scope.playerAnswer);
    }
  }

	$scope.initQuestion = function() {
		$scope.word = $scope.question['word'];
		$scope.mixedWord = $scope.question['mixedWord'];
		$scope.allCorrections = $scope.question['allCorrections'];
		// Init new question
    $scope.wrong = false;
    $scope.showCorrection = '';
    // Set focus on input field
    $timeout($scope.focusInput, 300);
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
      // Pick another question
			$scope.question = myData.pickQuestion();
			$scope.initQuestion();
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
			}
		});
	};
});
