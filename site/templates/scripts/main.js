$(document).ready(function() {

  $('.close').on('click', function () {
    var id = $(this).attr('data-id');
    $(id).hide();
  });

  $(".adminAction").on('click', function() {
    $("#ajaxViewport").html("<p>Loading...</p>"); 
    
		var playerId = $('#playerId').val();
		if (playerId == '-1' || playerId == null) {
			var teamId = $('#teamId').val(); 
			if (teamId != '-1' || teamId != null) {
				playerId = $('#teamId').val();
				var type = '&type=team';
			} else {
				playerId = '-1';
			}
		}
		var action = $(this).attr('data-action');
		var startDate = $('#startDate').val();
		var endDate = $('#endDate').val();
		if (type) {
			var href = $(this).attr('data-href') + action + '/' + playerId + '?startDate='+ startDate +'&endDate=' + endDate + type;
		} else {
			var href = $(this).attr('data-href') + action + '/' + playerId + '?startDate='+ startDate +'&endDate=' + endDate + type;
		}
    $.get(href, function(data) { 
        $("#ajaxViewport").html(data); 
    }); 
    return false; 
  }); 

  $(".ajax").click(function() {
    $("#reportDiv").html("<p>Loading...</p>"); 
    
    $.get($(this).attr('href'), function(data) { 
        $("#reportDiv").html(data); 
    }); 

    return false; 
  }); 

  $(".ajaxUnpublish").click(function() {
    //$("#feedback").html("<p>Loading...</p>"); 
    $(this).parents('li').toggleClass('strikeText');
    var $this = $(this);
    $.get($(this).val(), function(data) { 
        //$("#feedback").html(''); 
    }); 
  }); 

  $('#shopSelect').change( function() {
    var url = $('#shopSelect').val();

    $.get(url, function(data) { 
        $("#possibleItems").html(data); 
    }); 

    return false; 
  });

  $('#switchGallery').click( function() {
    $('#galleryPlacesList').toggle();
    $('#detailedPlacesList').toggle();
  });

  $('#report_button').click( function() {
    var url = $('#players_list').val();

    $.get(url, function(data) { 
        $("#reportDiv").html(data); 
    }); 

    return false; 
  });

  $('#reportPlayer').change( function() {
    if ($(this)[0].selectedIndex > 0) {
      $('#allCat').prop('checked', true);
      $('#participation').attr('disabled', true);
      $('#planetAlert').attr('disabled', true);
    } else {
      $('#participation').attr('disabled', false);
      $('#planetAlert').attr('disabled', false);
    }
  });
  $('#reportUrl_button').click( function() {
    var reportUrl = $(this).attr('data-reportUrl');
    // Add report category
    reportUrl += $('.reportCat:checked').val()+'/';
    // Add report team or player
    if ($('#reportPlayer').val() == '') { // No single player selected
      reportUrl += $('.reportTeam:checked').val()+'/';
    } else {
      reportUrl += $('#reportPlayer').val()+'/';
    }
    // Add period Id
    reportUrl += $('#periodId').val()+'/';
    // Add sorting GET parameter
    reportUrl += '?sort='+$('.reportSort:checked').val();

    $(this).attr('href', reportUrl);
    return true;
    // Go to report_generator
    /*
    $("#reportDiv").html("<p>Loading...</p>"); 
    $.get(reportUrl, function(data) { 
        $("#reportDiv").html(data); 
    }); 
    */
  });

  $('#participation').click( function() {
    var limitCheckbox = $('#limit10');
    if ($(this).is(':checked')) {
      limitCheckbox.removeAttr('disabled'); // Enable limit
      $('a.reportButton').each( function() {
        var href = $(this).attr('href');
        if ($('#lastName').is(':checked')) { // Remove from the URL
          href = href.replace(/\?sort=lastName/g, "");
        }
        href = href+'/participation'; // Add urlSegment
        if ($('#lastName').is(':checked')) { // Append to the URL
          href = href+'?sort=lastName';
        }
        $(this).attr('href', href);
      });
    } else {
      $('a.reportButton').each( function() {
        var href = $(this).attr('href');
        href = href.replace(/\/participation(\/10)?/g, "");
        $(this).attr('href', href);
      });
      limitCheckbox.attr('checked', false);
      limitCheckbox.attr('disabled', true); // Disabled limit
    }
  });

  $('#limit10').click( function() {
    if ($(this).is(':checked')) {
      $('a.reportButton').each( function() {
        var href = $(this).attr('href');
        if ($('#lastName').is(':checked')) {
          href = href.replace(/\?sort=lastName/g, "");
        }
        href = href+'/10';
        if ($('#lastName').is(':checked')) {
          href = href+'?sort=lastName';
        }
        $(this).attr('href', href);
      });
    } else {
      $('a.reportButton').each( function() {
        var href = $(this).attr('href');
        href = href.replace(/\/10/g, "");
        $(this).attr('href', href);
      });
    }
  });

  $('#period_list').change( function() {
    $('a.reportButton').each( function() {
      var href = $(this).attr('href');
    });
  });

  $('#lastName').click( function() {
    $('a.reportButton').each( function() {
      var href = $(this).attr('href');
      href = href+'?sort=lastName';
      $(this).attr('href', href);
    });
  });
  $('#firstName').click( function() {
    $('a.reportButton').each( function() {
      var href = $(this).attr('href');
      // Remove sort GET variable from URL
      href = href.replace(/\?sort=lastName/g, "");
      $(this).attr('href', href);
    });
  });

  $('#quizForm').submit( function(event) {
    //event.preventDefault();
  });
  $('#showAnswer').click( function() {
    $('#answer').toggle();
    return false;
  });

  $('#playerQuizButton').click( function() {
    var url = $('#players_list').val();
    window.location.href = url;
  });

  if ($('#worldMap').length > 0) {
    setTimeout( function() { del(); }, 1000);
  }
  function del() {
    svgPanZoom('#worldMap', {
      controlIconsEnabled: true
    });
  }

  $('#donation').click( function() {
    $('#donationDiv').toggle();
    $('#marketPlaceForm').toggle();
    if ($(this).html() === 'Make a donation') {
      $(this).html('Go back to the Marketplace');
    } else {
      $(this).html('Make a donation');
    }
  });
  var submitDonation = function() {
    if ($('#receiver').val() == 0 ) {
      alert('You must select a player!');
      return false;
    }
    if ($('#amount').val() == 0 || $('#amount').val() == '') {
      alert('Invalid amount !');
      return false;
    } 
    return true; // Submit form
  };
  $('#donateFormSubmit').on( "click", submitDonation);
	var checkAmount = function(amount) {
    if (!$.isNumeric(amount)) { // A number is needed ! 
      $('#amount').val('');
			$('#amount').next('.form-control-feedback').show();
			$('#donateFormSubmit').prop('disabled', true);
			return false;
    } else { // Invalid amount ?
      if (amount > parseInt($('#amount').attr('data-max'))) {
        $('#amount').val('');
				$('#amount').next('.form-control-feedback').show();
				$('#donateFormSubmit').prop('disabled', true);
				return false;
      }
    }
		if ($('#receiver').val() != 0) { // No receiver selected
			$('#donateFormSubmit').prop('disabled', false);
		}
		$('#amount').next('.form-control-feedback').hide();
		return true;
	}
  $('#amount').on('keyup', function() {
		checkAmount($(this).val());
  });
	$('#receiver').on('change', function() {
		if ($(this).val() != 0) {
			$(this).next('.form-control-feedback').hide();
			if (checkAmount($('#amount').val())) {
				$('#donateFormSubmit').prop('disabled', false);
			}
		} else {
			$(this).next('.form-control-feedback').show();
			$('#donateFormSubmit').prop('disabled', true);
		}
	})
	$('#donateFormSubmit').on('click', function() {
		$(this).hide();
	})

  $('[data-toggle="tooltip"]').tooltip({ container: 'body'});

  $('#mapTable').DataTable({
    dom: 'ft',
    paging: false,
    order: [[ 0, "asc"]]
  });
  var trainingTable = $('#trainingTable').DataTable({
    lengthMenu: [ [25, 50, -1], [25, 50, "All"] ],
    order: [[ 2, "asc"], [1, "asc"]]
  });
  var historyTable = $('#historyTable').DataTable({
    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
    order: [[ 0, "desc"]]
  });
  var taskTable = $('#taskTable').DataTable({
    dom: 'ft',
    paging: false,
    order: [[ 0, "asc"]]
  });
  var mainShop = $('#mainShop').DataTable({
    dom: 'ft',
    paging: false,
    order: [[ 0, "asc"]]
  });
  var shopAdminTable = $('#shopAdminTable').DataTable({
    paging: false,
    order: [[ 0, "asc"]]
  });
  $('#freeWorld').DataTable({
    dom: 'ft',
    paging: false,
    order: [[ 6, "desc" ]]
  });
  $('#teamTable').DataTable({
    paging: false,
    columnDefs: [{ "orderable": false, "targets": 1 },
      { "orderable": false, "targets": 4}],
    order: [[ 3, "desc" ]]
  });
  var adminTable = $('#adminTable').DataTable({
    dom: 't',
    paging: false,
    order: [[ 1, "asc" ]],
    orderCellsTop: true,
    searching: false,
    "columnDefs": [ {
      "targets": "task",
      "orderable": false
    } ]
  });
  var monstersTable = $('#monstersTable').DataTable({
    lengthMenu: [ [25, 50, -1], [25, 50, "All"] ],
    order: [[ 2, "asc"], [0, "asc"]],
    orderCellsTop: true
  });
  var loggedTable = $('#loggedTable').DataTable({
    lengthMenu: [ [25, 50, -1], [25, 50, 75, 100, "All"] ],
    order: [[ 1, "desc"]],
    orderCellsTop: true
  });

  $('.categoryFilter').click(function(){
    mainShop.draw();
    taskTable.draw();
    historyTable.draw();
    monstersTable.draw();
  });		    
    
  $('a.toggle-vis').on( 'click', function (e) {
    e.preventDefault();
    // Show all columns
    adminTable.columns( ).visible( true, false );
    // Get the column API object
    var category = $(this).attr('data-category');
    if (category !== '') {
      // Get columns index of the category
      var allColumns = $("#adminTable th[data-category='"+ category +"']");
      var indexHidden = new Array();
      $('#adminTable th.task').each( function(index) {
        if ( $(this).attr('data-category') !== category && $(this).attr('data-keepVisible') != 'true' ) { // Nothing is checked in this column : hide it
          indexHidden.push(index+2);
        }
      });
      adminTable.columns( indexHidden ).visible( false, false );
      //adminTable.columns.adjust().draw( false ); // adjust column sizing and redraw
    }
  });

  $('#lastQuestion').on('click', function() {
    $('#toggle').click();
  });
  $('#toggle').on('click', function() {
    $('.list-group').toggleClass('shown');
    $('.list-group').toggleClass('hidden');

    return false;
  });

  $('.tickNbPlaces').on('click', function() {
    var nbPlaces = $(this).val();
    var sender = $(this);
    $('.list-group-item input[type=checkbox]').each( function() {
      if ($(this).attr('data-nbPlaces') === nbPlaces) {
        if (sender.prop('checked')) {
          $(this).prop('checked', true);
        } else {
          $(this).prop('checked', false);
        }
      }
    });
  });
  $('.tickNbInvasions').on('click', function() {
    var nbInvasions = $(this).val();
    var sender = $(this);
    $('.list-group-item input[type=checkbox]').each( function() {
      if ($(this).attr('data-nbInvasions') === nbInvasions) {
        if (sender.prop('checked')) {
          $(this).prop('checked', true);
        } else {
          $(this).prop('checked', false);
        }
      }
    });
  });
  $('.tickRatio').on('click', function() {
    var ratio = $(this).val();
    var sender = $(this);
    $('.list-group-item input[type=checkbox]').each( function() {
      if ($(this).attr('data-ratio') === ratio) {
        if (sender.prop('checked')) {
          $(this).prop('checked', true);
        } else {
          $(this).prop('checked', false);
        }
      }
    });
  });
  $('#tickAll').on('click', function() {
    $('.list-group-item input[type=checkbox]').each( function() {
      if (!$(this).prop('disabled')) {
        $(this).prop('checked', true);
      }
    });
    return false;
  });
  $('#untickAll').on('click', function() {
    $('.list-group-item input[type=checkbox]').each( function() {
      $(this).prop('checked', false);
    });
    return false;
  });
  $('button.generateQuiz').on('click', function() {
    var noChecked = true;
    var urls = [];
    var ids = [];
    var playersIndex = [];
    $('.list-group-item input[type=checkbox]').not('.tickRatio, .tickNbInvasions, .tickNbPlaces').each( function(index) {
      if ( $(this).prop('checked') === true) {
        noChecked = false;
        urls.push($(this).val());
        ids.push($(this).val());
        playersIndex.push(index);
      }
    });
    if (noChecked == true) {
      // No checked
      // Check if last question is checked
      if ($('#lastQuestion').prop('checked') === true) {
        return true;
      } else {
        alert('Please, select at least 1 player!');
        return false;
      }
    } else {
      // Pick a random player and go to quiz
      if (ids.length > 0) {
        var randomIndex = Math.floor(Math.random() * ids.length);
        var randomId = ids[randomIndex];
        // Get rid of selected player
        ids.splice(randomIndex, 1);
      } else {
        var randomId = '-1';
      }
      // Modify form parameters accordingly & send form
      $('#selectedIds').val(ids);
      $('#selectedPlayer').val(randomId);
    }
  });

  $('#startFight').on('click', function() {
    // TODO : Move function into exercise.js?
    $(this).parents('.alert').hide();
    $('#fightForm').show();
    $('#exTitle').hide();
    $('#energyDiv').show();
    // Start exercise
    // TODO : Record session start...
  });

	$('#adminTableForm :submit').on('click', function(e){
		var $this = $(this).parents("form");
		e.preventDefault();
		swal({
			html: true,
			title: "Are you sure?",
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			cancelButtonText: "No, let me check again...",
			confirmButtonText: "Yes, save it!"
		}, function(isConfirm) {
			if (isConfirm) { // Send adminTableForm
				$this.submit();
				$("#adminTableForm :submit").prop('disabled', true);
				// return true;
			} else { // Don't send adminTableForm
				return false;
			}
		});
		// return confirm("Click OK to continue?");
	})

}); 

// Hide rows functions
$.fn.dataTable.ext.search.push(
  function( settings, data, dataIndex ) {
    var categoryFilter,categoryCol,categoryArray,found;
    var fColIndex;
    //creates selected checkbox array
    categoryFilter = $('.categoryFilter:checked').map(function () {
      return this.value;
    }).get();
    if(categoryFilter.length){
      fColIndex = $('#Filters').attr('data-fcolindex');
      if (fColIndex) {
        categoryCol = data[fColIndex]; //filter column
      } else {
        categoryCol = data[0]; //filter column
      }
      categoryArray =  $.map( categoryCol.split(','), $.trim); // splits comma separated string into array
      // finding array intersection
      found = $(categoryArray).not($(categoryArray).not(categoryFilter)).length;
      if(found == 0){
        return false;
      } else {
        return true;
      }      
    }
    // default no filter
    return true;
});


// adminTable functions
var setCommonComment = function(taskId, obj) {
	var commonComment = obj.val();
	$('.cc_'+taskId).each( function() {
		if ($(this).prev(':checkbox').prop('checked')) {
			$(this).val(commonComment);
		}
	});
}
var showComment = function(taskId) {
	$('#commonComment_'+taskId).toggle();
	$('.cc_'+taskId).toggle();
}
var isAnyChecked = function() {
	var anyChecked = false;
	$('#adminTable .ctPlayer').each(function() {
		if ( $(this).prop('checked') === true) {
			anyChecked = true;
			return false;
		}
	});
	if (anyChecked === true) {
		$("#adminTableForm :submit").prop('disabled', false);
	} else {
		$("#adminTableForm :submit").prop('disabled', true);
	}
}
var isAnyCheckedCol = function(taskId) {
	var anyChecked = false;
	$('.ct_'+taskId).each( function() {
		if ( $(this).prop('checked') === true) {
			anyChecked = true;
			return false;
		}
	});
	if (anyChecked === true) {
		$('#th_'+taskId).attr('data-keepVisible', 'true');
	} else {
		$('#th_'+taskId).attr('data-keepVisible', '');
	}
}
var selectAll = function(taskId) {
	$('.ct_'+taskId).prop('checked', $('#csat_'+taskId).prop('checked'));
	isAnyChecked();
	isAnyCheckedCol(taskId);
}
var onCheck = function(taskId) {
	// Disable 'Select all' checkbox
	$('#csat_'+taskId).prop('checked', false)
	// Enable submit buttons if needed
	isAnyChecked();
	// Set column visible state
	isAnyCheckedCol(taskId);
}

// shopAdminTable functions
var shopCheck = function(obj, remainingGC, itemGC) {
  //alert(remainingGC+'-'+itemGC);
  if ( $(obj).prop('checked') === true) {
    var newGC = remainingGC - itemGC;
    $('#remainingGC').text(newGC);
    // Disable impossible items left
    $('ul.itemList li input[type=checkbox]').not($(obj)).each(function() {
      if ($(this).attr('data-gc') > newGC && $(this).prop('checked') === false) {
        $(this).prop('disabled', true);
        $(this).parent('label').css('text-decoration', 'line-through');
      }
    });
    // Enable save buttons
    $("#marketPlaceForm :submit").prop('disabled', false);
  } else {
    var newGC = parseInt(remainingGC) + parseInt(itemGC);
    $('#remainingGC').text(newGC);
    $('ul.itemList li input[type=checkbox]').each(function() {
      if ($(this).attr('data-gc') > newGC) {
        $(this).prop('disabled', true);
        $(this).parent('label').css('text-decoration', 'line-through');
      } else {
        $(this).prop('disabled', false);
        $(this).parent('label').css('text-decoration', '');
      }
    });
    var anyChecked = false;
    $('ul.itemList li input[type=checkbox]').each(function() {
      if ( $(this).prop('checked') === true) {
        anyChecked = true;
        return false;
      }
    });
    if (anyChecked === true) {
      $("#marketPlaceForm :submit").prop('disabled', false);
    } else {
      $("#marketPlaceForm :submit").prop('disabled', true);
    }
  }
}

var marketPlaceSelect = function(obj, playerId) {
  var type = obj.className;
  //alert(type+'-'+ playerId);
  //alert($(obj).val());
  var objId = $(obj).val();
  if (objId != 0 & $('#'+playerId+objId).length == 0 ) {
    var newObj = '<p id="'+playerId+objId+'">'+$(obj).find('option:selected').text()+' <button type="button" class="close" aria-label="Close" onclick="alert(\'TODO\')"><span aria-hidden="true">&times;</span></button></p>';
    var newInput = '<input type="hidden" name="inputToSave_'+playerId+'_'+objId+'" />';
    $('#toSave_'+playerId).append(newObj);
    $('#toSave_'+playerId).append(newInput);
  }
}

/**
 * Debounce function - Thanks to Kevin Subileau for sharing (http://www.kevinsubileau.fr/informatique/boite-a-code/php-html-css/javascript-debounce-throttle-reduire-appels-fonction.html)
 * Retourne une fonction qui, tant qu'elle continue à être invoquée,
 * ne sera pas exécutée. La fonction ne sera exécutée que lorsque
 * l'on cessera de l'appeler pendant plus de N millisecondes.
 * Si le paramètre `immediate` vaut vrai, alors la fonction 
 * sera exécutée au premier appel au lieu du dernier.
 * Paramètres :
 *  - func : la fonction à `debouncer`
 *  - wait : le nombre de millisecondes (N) à attendre avant 
 *           d'appeler func()
 *  - immediate (optionnel) : Appeler func() à la première invocation
 *                            au lieu de la dernière (Faux par défaut)
 *  - context (optionnel) : le contexte dans lequel appeler func()
 *                          (this par défaut)
 */
function debounce(func, wait, immediate, context) {
    var result;
    var timeout = null;
    return function() {
        var ctx = context || this, args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) result = func.apply(ctx, args);
        };
        var callNow = immediate && !timeout;
        // Tant que la fonction est appelée, on reset le timeout.
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) result = func.apply(ctx, args);
        return result;
    };
}
