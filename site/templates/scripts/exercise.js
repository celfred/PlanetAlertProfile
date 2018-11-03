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
			for (var i=0; i<allLines.length; i++) {
				// Manage priorities
				var pattern = /{(\d+)}/i; // {n} at the beginning of a line
				var str = allLines[i];
				if (str.match(pattern) != null ) {
					// Get n and copy the line accordingly
					var n = str.match(pattern);
					// Clean original line
					allLines[i] = str.replace(pattern, '');
					for (var j=0; j<n[1]; j++) {
						newLines.push(allLines[i]);
					}
				}
			}
			// Add new lines
			for (var i=0; i<newLines.length; i++) {
				allLines.push(newLines[i]);
			}
			// Manage special variables
			// %fname% : First name (female or male)
			// %fnamef% : First name female
			// %fnamem% : First name male
			// %name% : Full name (female or male)
			// %age% : Age
			// %nationality% : Nationality
			// (...) : Displayed text but optional in answers (see parseCorrections())
			// $...$ : Information displayed as feedback
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

			// console.log(allLines);
			// return allLines;
		},

		pickQuestion : function (type) {
			var question = [];
			// Pick a random line
			// Different from previous line
			if (allLines.length > 1) {
				var randNum = Math.floor(Math.random()*allLines.length);
				if (lineHistory.length >= 1) {
					while (randNum == lineHistory[lineHistory.length-1]) {
						randNum = Math.floor(Math.random()*allLines.length);
					}
				}
				lineHistory.push(randNum);
			} else {
				randNum = 0;
			}
			// console.log(type);
			var randLine = allLines[randNum];
			// Test if training or fight
			if (type == 'fight') {
				// Pick target language (right)
				var randNum = 1;
				var randOpp = 0;
			} else { // Training
				// Pick random target or source target language
				var randNum = Math.round(Math.random());
				if (randNum == 1) {
					var randOpp = 0;
				} else {
					var randOpp = 1;
				}
			}
			// console.log(randNum);
			switch(exerciseData['exType']) {
				case 'translate' :
					var randWords = randLine.split(",");
					// Test for multiple possible words and answers
					var allWords = randWords[randNum].split("|");
					// Trim eventual extra spaces
					for (i=0; i<allWords.length; i++) {
						allWords[i] = allWords[i].trim();
					}
					var allCorrections = randWords[randOpp].split("|");
					question['allCorrections'] = this.parseCorrections(allCorrections);
					break;
				case 'categorize' :
					// Question will be allWords[0]
					var quiz = randLine.split("::");
					var allWords = quiz[0].split("|");
					// Get categories (correct answer is first)
					var allCategories = quiz[1].split(",");
					// Trim eventual extra spaces
					for (i=0; i<allCategories.length; i++) {
						allCategories[i] = allCategories[i].trim();
					}
					question['allCorrections'] = allCategories[0];
					this.shuffleArray(allCategories);
					question['allCategories'] = allCategories;
					break;
				case 'quiz' :
					// Question will be allWords[0]
					var quiz = randLine.split("::");
					var allWords = quiz[0].split("|");
					// Test for multiple possible answers
					var allCorrections = quiz[1].split("|");
					question['allCorrections'] = this.parseCorrections(allCorrections);
					break;
				case 'image-map' : // Works like Quiz
					// Question will be allWords[0]
					var quiz = randLine.split("::");
					var allWords = quiz[0].split("|");
					// Test for multiple possible answers
					var allCorrections = quiz[1].split("|");
					question['allCorrections'] = this.parseCorrections(allCorrections);
					break;
				case 'jumble' :
					// Split chunks
					var allWords = '';
					var help = '';
					// Check for help (translation between $...$
					var pattern = /\$.*?\$/i;
					if (randLine.search(pattern) != -1 ) {
						help = randLine.match(pattern)[0];
						help = help.replace(/\$/g, '');
						randLine = randLine.replace(pattern, "");
					}
					var chunks = randLine.split("|");
					var correction = new Array();
					correction[0] = '';
					for(var i=0; i<chunks.length; i++) {
						if (i>0) {
							chunks[i] = ' '+$.trim(chunks[i]);
						} else {
							chunks[i] = $.trim(chunks[i]);
						}
						correction[0] += chunks[i];
					}
					question['allCorrections'] = this.parseCorrections(correction);
					break;
				default: 
					console.log('Unknown exType');
			}
			// Pick 1 random word from possible words
			if (allWords.length > 1) {
				question['word'] = chance.pick(allWords);
				if (exerciseData['exType'] == 'image-map') {
					question['word'] = lang.whatNumber+question['word']+" ?";
				}
			} else {
				question['word'] = allWords[0];
				if (exerciseData['exType'] == 'image-map') {
					question['word'] = lang.whatNumber+question['word']+" ?";
				}
				if (exerciseData['exType'] == 'jumble') {
					this.shuffleArray(chunks);
					question['word'] = chunks;
				}
			}
			// Add word to history
			if (exerciseData['exType'] == 'jumble') {
				history.push(question['allCorrections']);
			} else {
				history.push(question['word']);
			}
			// Help with 1st mixed answers
			if (exerciseData['exType'] == 'jumble') {
				question['mixedWord'] = help;
			} else {
				question['mixedWord'] = this.shuffle(question['allCorrections'][0]);
			}

			return question;
		},

		cleanTags : function(str) {
			if (str) {
				str = str.replace(/[\*\\_]/g, '');
			}
			return str;
		},

		parseCorrections : function(allCorrections) {
			var newCorrections = [];
			var tempCorrections = [];
			var feedback = '';
			for (i=0; i<allCorrections.length; i++) {
				var str = allCorrections[i];
				// Get rid of feedback
				var pattern = /\$(.*?)\$/i;
				if (str.search(pattern) != -1 ) {
					allCorrections[i] = str.replace(pattern, "");
					feedback = str.match(pattern, "$1")[1];
				} else {
					feedback = '';
				}
				// Add optional text functionality : (...) only 1/line for the moment
				pattern = /\((.*?)\)/i;
				str = $.trim(allCorrections[i]);
				if (str.search(pattern) != -1 ) {
					allCorrections[i] = str.replace(pattern, "");
					allCorrections[i] = $.trim(allCorrections[i]);
					tempCorrections.push(str);
					tempCorrections.push(str.replace(pattern, "$1"));
				} else {
					// Trim extra spaces
					allCorrections[i] = $.trim(allCorrections[i]);
				}
				// Add to newCorrections
				for (var j=0; j<tempCorrections.length; j++) {
					tempCorrections[j] = $.trim(tempCorrections[j]);
					newCorrections.push(tempCorrections[j]);
				}
				tempCorrections = [];
			}
			// Add to allCorrections
			for (var j=0; j<newCorrections.length; j++) {
				allCorrections.push(newCorrections[j]);
			}
			newCorrections = [];
			allCorrections['feedback'] = feedback;
			// Clean paTags
			for (var j=0; j<allCorrections.length; j++) {
				allCorrections[j] = this.cleanTags(allCorrections[j]);
			}
			return allCorrections;
		},

		shuffle : function(str) {
			// Remove paTags
			str = this.cleanTags(str);
      var a = str.split(""),
          n = a.length;
      for(var i = n - 1; i > 0; i--) {
          var j = Math.floor(Math.random() * (i + 1));
          var tmp = a[i];
          a[i] = a[j];
          a[j] = tmp;
      }
      return a.join("");
		},

		shuffleArray : function(a) {
			var j, x, i;
			for (i = a.length; i; i--) {
					j = Math.floor(Math.random() * i);
					x = a[i - 1];
					a[i - 1] = a[j];
					a[j] = x;
			}
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
	$scope.playerAnswer = '';
  $scope.correct = false;
  $scope.wrong = false;
	$scope.shownWords = 0;
  $scope.wonFight = false;
  $scope.isFocused = false; // Automatic focus on input field
  $scope.runningInterval = false;
  
	// Disable selection
	if (typeof document.body.onselectstart !== "undefined") { //IE 
		document.body.onselectstart = function(){ return false; };
	} else if (typeof document.body.style.MozUserSelect !== "undefined") { //Firefox
		document.body.style.MozUserSelect = "none";
	} else { //All other ie: Opera
		document.body.onmousedown = function(){ return false; };
	}

	$window.addEventListener("beforeunload", function (e) {
		if (!$scope.waitForStart) {
			var confirmationMessage = lang.quit;

			(e || window.event).returnValue = confirmationMessage; //Gecko + IE
			return confirmationMessage;                            //Webkit, Safari, Chrome
		} else {
			return false;
		}
	});

  $scope.init = function(service, exerciseId, redirectUrl, playerId, weaponRatio, protectionRatio, submitUrl) {
    $scope.playerPower += parseInt(weaponRatio);
    $scope.monsterPower -= parseInt(protectionRatio);
		// var url = '/service-pages/?template=exercise&id='+exerciseId;
		var url = service+'?template=exercise&id='+exerciseId;
		myData.getData(url).then( function(exerciseData) {
			$scope.exType = exerciseData['exType'];
			myData.parseData();
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
		$scope.question = myData.pickQuestion('fight');
		$scope.initQuestion();
  }

	$scope.initQuestion = function() {
		$scope.word = $scope.question['word'];
		$scope.mixedWord = $scope.question['mixedWord'];
		$scope.allCorrections = $scope.question['allCorrections'];
		$scope.categories = $scope.question['allCategories'];
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
		// No selected words (if jumble)
		$scope.selectedItems = [];
	}

  $scope.throwQuestion = function() {
		if ($scope.exType != 'jumble') {
			// Set focus on input field
			$timeout($scope.focusInput, 300);
			// Player loses 1HP every second
			if ($scope.runningInterval == false ) {
				$scope.promise = $interval($scope.loseHP, 1000);
			}
		} else {
			// Player loses 1HP every 2 seconds
			if ($scope.runningInterval == false ) {
				$scope.promise = $interval($scope.loseHP, 2000);
			}
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
		$scope.isFocused = false;
		// Show correction
    $scope.showCorrection = $scope.allCorrections.join(', ');
    // Player admits "I don't know" : reduced loss
		$scope.playerDamage = Math.round($scope.monsterPower/2);
		$scope.playerHP = $scope.playerHP - $scope.playerDamage;
		$scope.hidePlayerDamage = false;
		$timeout(function() { $scope.hidePlayerDamage = true; }, 1500);
    $scope.counter = 10;
		$scope.shownWords += 1;
		// Set focus on input field
		$timeout($scope.focusInput, 300);
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
        $timeout(function() { $scope.question = myData.pickQuestion('fight'); $scope.initQuestion(); }, 550);
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

	$scope.pickWord = function(w, i){
		w = myData.cleanTags(w);
		if(!$scope.selectedItems){
       $scope.selectedItems = [];
    }

    var index = $scope.selectedItems.indexOf(i);

    if(index === -1) {   // not selected already and add
       $scope.selectedItems.push(i);
       $scope.playerAnswer += w;
    } else {   // selected already and remove
       $scope.selectedItems.splice(index, 1);
       $scope.playerAnswer = $scope.playerAnswer.replace(w, "");
    }
	}

	$scope.pickCategory = function(c, i){
		c = myData.cleanTags(c);
	  $scope.playerAnswer = c;
		$scope.attack();
	}

	$scope.clear = function(){
		$scope.playerAnswer = '';
		$scope.selectedItems = [];
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
			var feedback = lang.success;
			$scope.result = 'VV';
		} else {
			var feedback = lang.won;
			$scope.result = 'V';
		}
    $scope.saveData();
    swal({
      title: lang.congratulations,
      html: lang.ranAway1 + $scope.nbAttacks + lang.ranAway2 + $scope.quality + lang.ranAway3 + feedback + lang.ranAway4,
      type: "success",
      confirmButtonText: lang.seeProfile
    }).then( function() {
			$timeout($scope.redirect($scope.redirectUrl), 200);
    });
  }

  $scope.loseFight = function () {
    // Stop animation
    $interval.cancel($scope.promise);
    $scope.runningInterval = false;
		$scope.waitForStart = true;
		$scope.quality = Math.round(100-(($scope.shownWords/$scope.nbAttacks)*100));
		if ($scope.monsterHP < 50) {
			var feedback = lang.lost;
			$scope.result = 'R';
		} else {
			var feedback = lang.disastrous;
			$scope.result = 'RR';
		}
    $scope.saveData();
    swal({
      title: lang.sorry,
      html: lang.revise1 + feedback + lang.revise2,
      type: "error",
      confirmButtonText: lang.better
    }).then( function() {
			$timeout($scope.redirect($scope.redirectUrl), 200);
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
			swal(lang.error2, lang.contactAdmin, "error");
      $scope.saved = lang.error2+lang.contactAdmin;
    })
  }

	$scope.redirect = function(url) {
		$window.location.href = url;
	}

  $scope.focusInput = function() {
    $scope.isFocused = !$scope.isFocused;
  }
});

exerciseApp.controller('TrainingCtrl', function ($scope, $http, $timeout, $interval, $window, myData) {
  $scope.waitForStart = true;
  $scope.result = 0;
	$scope.question = [];
	$scope.playerAnswer = '';
	$scope.newCorrections = new Array();
  $scope.counter = 0; // #
  $scope.exType = '';
  $scope.exData = '';
  $scope.correct = false;
  $scope.utPoint = false;
  $scope.isFocused = false; // Automatic focus on input field
  $scope.runningInterval = false;

	// Disable selection
	if (typeof document.body.onselectstart !== "undefined") { //IE 
		document.body.onselectstart = function(){ return false; };
	} else if (typeof document.body.style.MozUserSelect !== "undefined") { //Firefox
		document.body.style.MozUserSelect = "none";
	} else { //All other ie: Opera
		document.body.onmousedown = function(){ return false; };
	}

	$window.addEventListener("beforeunload", function (e) {
		if (!$scope.waitForStart) {
			var confirmationMessage = lang.quit;

			(e || window.event).returnValue = confirmationMessage; //Gecko + IE
			return confirmationMessage;                            //Webkit, Safari, Chrome
		} else {
			return false;
		}
	});

  $scope.init = function(service, exerciseId, redirectUrl, playerId, submitUrl) {
		var url = service+'?template=exercise&id='+exerciseId;
		myData.getData(url).then( function(exerciseData) {
			$scope.exType = exerciseData['exType'];
			myData.parseData();
			// Enable start Fight button
			$scope.waitForStart = false;
			// Pick another question
			$scope.question = myData.pickQuestion('training');
			$scope.initQuestion();
    })
    $scope.exerciseId = exerciseId;
    $scope.redirectUrl = redirectUrl;
    $scope.playerId = playerId;
    $scope.submitUrl = submitUrl;
  }

	$scope.pickWord = function(w, i){
		w = myData.cleanTags(w);
		if(!$scope.selectedItems){
       $scope.selectedItems = [];
    }
    var index = $scope.selectedItems.indexOf(i);
    if(index === -1) {   // not selected already and add
       $scope.selectedItems.push(i);
       $scope.playerAnswer += w;
    } else {   // selected already and remove
       $scope.selectedItems.splice(index, 1);
       $scope.playerAnswer = $scope.playerAnswer.replace(w, "");
    }
	}

	$scope.pickCategory = function(c, i){
		c = myData.cleanTags(c);
	  $scope.playerAnswer = c;
		$scope.attack();
	}

	$scope.clear = function(){
		$scope.playerAnswer = '';
		$scope.selectedItems = [];
	}

  $scope.attack = function() {
    if ($scope.playerAnswer) {
      $scope.checkAnswer($scope.playerAnswer);
    }
  }
  $scope.dodge = function() { // Show correction
		$scope.isFocused = false;
    $scope.showCorrection = $scope.allCorrections.join(', ');
		$scope.wrong = true;
		// Set focus on input field
		$timeout($scope.focusInput, 300);
  }

	$scope.initQuestion = function() {
		$scope.word = $scope.question['word'];
		$scope.mixedWord = $scope.question['mixedWord'];
		$scope.categories = $scope.question['allCategories'];
		$scope.allCorrections = $scope.question['allCorrections'];
		if ($scope.allCorrections['feedback'] != '') {
			$scope.feedback = '['+$scope.allCorrections['feedback']+']';
		}
		// Init new question
    $scope.wrong = false;
		if (($scope.mixedWord).length < 4) { // Don't help on very short words
			$scope.showClue = false;
		} else {
			$scope.showClue = true;
		}
    $scope.showCorrection = '';
		if ($scope.exType != 'jumble') {
			// Set focus on input field
			$timeout($scope.focusInput, 300);
		} else {
			// Deselect all words
			$scope.selectedItems = [];
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
				// Get number of words
				if (Math.floor($scope.counter/10) < $scope.result+1) {
					swal({
						title: lang.correct,
						html: lang.plus1word,
						type: "success",
						showConfirmButton: false,
						timer: 1000
					}).catch(swal.noop);
				} else { // Calculate result
					$scope.result++;
					$scope.utPoint = true;
					$timeout(function() { $scope.utPoint = false; }, 1000);
					$scope.stopSession(); // Alert +1 U.T. : Stop or continue?
				}
			}
      // Pick another question
			$scope.question = myData.pickQuestion('training');
			$scope.initQuestion();
    } else { // Wrong answer
			swal({
				title: lang.wrong,
				html: lang.copyCorrection,
				type: "error",
				showConfirmButton: false,
				timer: 1000
			}).catch(swal.noop);
			// Show correction
			$scope.showCorrection = $scope.allCorrections.join(', ');
      $scope.wrong = true;
    }
  }

	$scope.correctMessage = function() {
	}

  $scope.stopSession = function() {
    if ($scope.result >= 1) {
      swal({
        title: lang.stop,
        html: lang.stop1 + $scope.result + lang.stop2 + $scope.counter + lang.stop3,
        type: "success",
        showCancelButton : true,
        cancelButtonText: lang.continue,
        confirmButtonText: lang.stopSave
      }).then( function() {
        // Save and redirect
				$scope.saveData(true);
				$scope.waitForStart = true;
			},function(dismiss) {
				if (dismiss === 'cancel' || dismiss == 'overlay') { return false; }
			});
    } else {
      swal({
        title: lang.stop,
        text: lang.stop4,
        type: "warning",
        showCancelButton : true,
        cancelButtonText: lang.continue,
        confirmButtonText: lang.stopOrder
      }).then( function() { // DO not save, but redirect
				$timeout($scope.redirect($scope.redirectUrl), 200);
      },function(dismiss) {
				if (dismiss === 'cancel' || dismiss == 'overlay') { return false; }
			});
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
      $scope.saved = lang.saved;
			if (data["data"] == '1') {
				swal({
					title: lang.congratulations,
					html: lang.training,
					type: "success",
					timer: 2000
				}).then( function() {
					if (redirect === true) {
						$timeout($scope.redirect($scope.redirectUrl), 200);
					}
				}), function(dismiss) {
					if (dismiss === 'cancel' || dismiss == 'overlay') { return false; }
				};
			} else {
				if (redirect === true) {
					$timeout($scope.redirect($scope.redirectUrl), 200);
				}
			}
    }, function(data, status, headers, config) {
			swal(lang.error2, lang.contactAdmin, "error");
      $scope.saved = lang.error2+lang.contactAdmin;
    })
  }

	$scope.redirect = function(url) {
		$window.location.href = url;
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

exerciseApp.filter('paTags', function () {
	return function(input) {
		if (input) {
			var pattern = /\*+(.*?)\*+/ig;
			if (input.search(pattern) != -1 ) {
				var sub = '<span class="paTagFramed">$1</span>';
				input = input.replace(pattern, sub);
			}
			var pattern = /_+(.*?)_+/ig;
			if (input.search(pattern) != -1 ) {
				var sub = '<span class="paTagUnderlined">$1</span>';
				input = input.replace(pattern, sub);
			}
			var pattern = /\\+(.*?)\\+/ig;
			if (input.search(pattern) != -1 ) {
				var sub = '<span class="paTagItalicised">$1</span>';
				input = input.replace(pattern, sub);
			}
			return input;
		}
	}
});

exerciseApp.filter('underline', function () {
	return function(input) {
		if (input) {
			var allAnswers = input.split(',');
			allAnswers[0] = '<u>'+allAnswers[0]+'</u>';
			return allAnswers.join(', ');
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
