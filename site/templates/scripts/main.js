$(document).ready(function() {
	$('#scrollDown').on('click', function(e) {
		e.preventDefault();
		window.scrollBy(0,350);
	});
	$('#scrollUp').on('click', function(e) {
		e.preventDefault();
		window.scrollBy(0,-350);
	});

  $('.close').on('click', function () {
    var id = $(this).attr('data-id');
    $(id).hide();
  });

	$('a.frenchVersion').on('click', function() {
		$('div.frenchVersion').toggle();
		return false;
	});

  
	$('.publishElement').on('click', function() {
		var href = $(this).attr('href');
		var $this = $(this);
    $this.html(lang.saving); 
    $.get(href, function(data) { 
			$this.prev().removeClass('strikeText');
			$this.remove();
    }); 
    return false; 
	});

	$('.selectElement').on('click', function() {
    $("#ajaxViewport").html("<p>"+lang.saving+"</p>"); 
		var href = $(this).attr('href');
		$el = $(this).parent('li');
		var $this = $(this);
		$parentId = $el.parent().prop('id');
    $.get(href, function(data) { 
			$("#ajaxViewport").html(''); 
      var $publish = $el.children('.togglePublish');
			if ($parentId == 'teacherElements') {
				$('#notTeacherElements').append($el);
        if ($publish.length > 0) {
          $publish.addClass('hidden');
        }
			} else {
				$('#teacherElements').append($el);
        if ($publish.length > 0) {
          $publish.removeClass('hidden');
        }
			}
    }); 
    return false; 
	});

	$('.deleteFromId').on('click', function(e) {
    e.preventDefault();
		$this = $(this);
		swal({
			title: lang.sure,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			confirmButtonText: lang.yes,
			cancelButtonText: lang.no
		}).then( result => {
			if (result.value) {
				var href = $this.attr('data-href');
				$('<span>Saving...</span>').insertAfter($this);
				$.get(href, function(data) { 
					$this.parent('li').remove();
				}); 
			} else { 
				return false;
			}
		});
	});

	$('.togglePublish').on('click', function(e) {
    e.preventDefault();
		$this = $(this);
		var href = $this.attr('data-href');
		$('.tooltip').hide();
		$('<span>'+lang.saving+'</span>').insertAfter($this);
		$.get(href, function(data) { 
			if ($this.text() == '✓') {
				$this.text('✗');
				$this.attr("class", "label label-danger");
				$this.attr("title", "Publish");
        $this.parent().find('span.toStrike').addClass('strikeText');
			} else {
				$this.text('✓');
				$this.attr("class", "label label-success");
				$this.attr("title", "Unpublish");
        $this.parent().find('span.toStrike').removeClass('strikeText');
			}
			$this.next('span').remove();
		});
	});

	$('.announcement').on('close.bs.alert', function(e) {
		e.preventDefault();
		var $this = $(this);
		var $href = $this.attr('data-href')
		swal({
			title: lang.sure,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			confirmButtonText: lang.yes,
			cancelButtonText: lang.no,
		}).then( result => {
			if (result.value) {
				$.get($href, function(data) { 
					$this.remove();
				}); 
			} else {
				return false;
			}
		});
	});

	$('.teamOption').on('click', function() {
    $("#ajaxViewport").html("<p>"+lang.loading+"</p>"); 
		var href = $(this).attr('href');
    $.get(href, function(data) { 
			$("#ajaxViewport").html(data); 
    }); 
    return false; 
  }); 

  $(".adminAction").on('click', function() {
    $("#ajaxViewport").html("<p>"+lang.loading+"</p>"); 
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
			var taskId = $('#taskId').val();
			if (taskId) {
				href = href + '&taskId='+taskId;
			}
		} else {
			var href = $(this).attr('data-href') + action + '/' + playerId + '?startDate='+ startDate +'&endDate=' + endDate + type;
		}
		if (action == 'save-options') {
			var href = $(this).attr('data-href') + action + '?&periodId=' + $('#periodId').val();
		}
    $.get(href, function(data) { 
        $("#ajaxViewport").html(data); 
    }); 
    return false; 
  }); 

	$('.limitButton').on('click', function() {
		$('.limitButton').each( function() {
			$(this).prop('class', 'limitButton btn btn-primary');
		});
		$(this).toggleClass('btn-primary btn-success');
		$(".monsterDiv").show();
		switch($(this).prop('id')) {
			case 'limitTrainable' : $(".monsterDiv:not('.trainable')").toggle();
				break;
			case 'limitFightable' : $(".monsterDiv:not('.fightable')").toggle();
				break;
			case 'limitNever' : $(".monsterDiv:not('.neverTrained')").toggle();
				break;
			default : $(".monsterDiv").show();
				break;
		}
	});

	$('.monsterInfo').on('click', function() {
		$this = $(this);
		var $url = $this.attr('data-href');
		swal({
			title: lang.loading,
			onOpen: function() {
				swal.showLoading();
				$.get($url, function(data) { 
					var $myContent = data;
					swal({
						html: $myContent,
						cancelButtonText : lang.ok,
						showConfirmButton: false,
						showCancelButton: true,
						allowOutsideClick: true,
						width: 800
					}).then( result => {
							return;
					});
				});
			}
		});
	});

	$(document).on('click', '.simpleConfirm', (function(e) {
    e.preventDefault();
    $this = $(this);
    if ($this.attr("data-href")) {
      var $href = $this.attr("data-href");
    } else {
      var $href = '';
    }
    if ($this.attr("data-msg")) {
      var $msg = $this.attr("data-msg");
    } else {
      var $msg = '';
    }
		swal({
			title: lang.sure,
      text: $msg,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			cancelButtonText: lang.no,
			confirmButtonText: lang.yes
		}).then( result => {
			if (result.value) {
        if ($href != '') { // Ajax-call before following href
					$.get($href, function(data) { 
            window.location.href = $this.attr("href");
					}); 
        } else {
          window.location.href = $this.attr("href");
        }
      } else {
        return false;
      }
    })
  }));

	$('.basicConfirm').on('click', function(e) {
    e.preventDefault();
		var $this = $(this);
		var $href = $this.attr('data-href');
    if ($this.attr("data-reload")) {
      var $reload = $this.attr("data-reload");
    } else {
      var $reload = 'false';
    }
    if ($this.attr("data-msg")) {
      var $msg = $this.attr("data-msg");
    } else {
      var $msg = '';
    }
    if ($this.attr("data-toDelete")) {
      var $toDelete = $this.parents($this.attr("data-toDelete"));
    } else {
      var $toDelete = '';
    }
		swal({
			title: lang.sure,
      text: $msg,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			confirmButtonText: lang.yes,
			cancelButtonText: lang.no,
		}).then( result => {
			if (result.value) {
				$this.next('.proceedFeedback').html(lang.saving);
				$('.notification').remove();
				if ($reload == 'false') {
					$.get($href, function(data) { 
						$this.next('.proceedFeedback').html(lang.saved);
						// $('#wrap').prepend(data);
						setTimeout( function() { $this.next('.proceedFeedback').html(''); }, 3000);
            if ($toDelete != '') { $toDelete.remove(); }
            swal({
              title: lang.reload,
              showCancelButton : false,
              showConfirmButton: false,
              timer: 500,
            });
					}); 
				} else {
					window.location.href = $href;
				}
			} else {
				return false;
			}
		});
	});

	$(document).on('click', '.simpleAjax', function(e) {
		$this = $(this);
    $hide = $this.attr('data-hide-feedback');
    $disable = $this.attr("data-disable");
    var href = $this.attr('data-href');
    if ($this.next().hasClass("ajaxFeedback")) {
      $this.next().remove();
    }
    $that = $('<span class="ajaxFeedback"> '+lang.loading+'</span>').insertAfter($this);
    $.get(href, function(data) { 
      if (data) {
        $that.html(data);
      } else {
        $that.html(' '+lang.saved);
      }
      if ($hide == 'true') {
        setTimeout( function() { $that.remove(); }, 1000);
      }
      if ($disable == 'true') { $this.prop('disabled', true); }
    }); 
    return false;
	});

	$('.confirmSubmit').on('click', function(e) {
    $this = $(this);
    e.preventDefault();
		swal({
			title: lang.sure,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			confirmButtonText: lang.yes,
			cancelButtonText: lang.no,
		}).then(result => {
			if (result.value) {
        $this.parents('form').submit();
			} else {
				return false;
			}
		});
  });

  $('.toggleCheckboxes').on('click', function(e) {
    var checked = $(this).prop('checked');
    var index = $(this).parent().parent().index();
    $('tr').each(function(i, val){
      $(val).children().eq(index).children().children('input[type=checkbox]').prop("checked", checked);
    });
  });

	$('.confirm').on('click', function() {
		$this = $(this);
		swal({
			title: lang.sure,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			confirmButtonText: lang.yes,
			cancelButtonText: lang.no,
		}).then(result => {
			if (result.value) {
				var href = $this.attr('data-href');
				$('<span> '+lang.saving+'</span>').insertAfter($this);
				$.get(href, function(data) { 
					$this.next('span').html(' '+lang.saved);
					setTimeout( function() { $this.next('span').remove(); }, 1000);
				}); 
			} else {
				return false;
			}
		});
	});

	$('.proceed').on('click', function() {
		$this = $(this);
		swal({
			title: lang.sure,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			confirmButtonText: lang.yes,
			cancelButtonText: lang.no,
		}).then(result => {
			if (result.value) {
				var href = $this.attr('data-href') + "/save-options/" + $('#periodId').val() + "/1";
				$this.next('.proceedFeedback').html(lang.saving);
				$('.notification').remove();
				$.get(href, function(data) { 
					$this.next('.proceedFeedback').html(lang.saved);
					$('#wrap').prepend(data);
					setTimeout( function() { $this.next('.proceedFeedback').html(''); }, 3000);
				}); 
			} else {
				return false;
			}
		});
	});

	$('#playerId').on('change', function() {
		var pageId = $(this).val();
		var url = $('#backendEditable').attr('data-href');
		$('#backendEditable').attr('href', url+pageId); 
	});
	$('#backendEditable').on('click', function() {
		if ($(this).attr('href') != '') {
			var url = $(this).attr('href');
			window.location.href = url;
			return false;
		} else {
			window.alert(lang.pleaseSelect);
		}
	})

  $(".toggleEnabled").on('click', function(e) {
		e.preventDefault();
		$checkbox = $(this).parents('td').find("input");
		$checkbox.prop('disabled', !$checkbox.prop('disabled'));
	});

  $(".removeAbs").on('click', function(e) {
		e.preventDefault();
    $this = $(this);
		swal({
			title: lang.sure,
			type: "question",
			showCancelButton : true,
			allowOutsideClick : true,
			cancelButtonText: lang.no,
			confirmButtonText: lang.yes,
		}).then( result => {
			if (result.value) {
				$.get($this.attr('data-url'), function(data) { 
					$tr = $this.parents("tr");
					$tr.removeClass('negative');
					$tr.find("input[type=checkbox]").each( function() {
							$(this).prop('disabled', 0);
					});
					$tr.find("a.toggleEnabled").each( function() {
						$(this).remove();
					});
					$this.remove();
				}); 
			} else {
				return false;
			}
		}); 
    return false; 
  }); 

	$(document).on('click', '#copied', (function() {
		var $this = $(this);
		var $url = $this.attr('data-url');
		var $playerId = $this.attr('data-playerId');
		var $lessonId = $this.attr('data-lessonId');
		var $taskId = $this.attr('data-taskId');
		swal({
			title: lang.sure,
			html: lang.teacherAlert,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			cancelButtonText: lang.no,
			confirmButtonText: lang.yes
		}).then( result => {
			if (result.value) {
				$url = $url + '&playerId=' + $playerId + '&lessonId='+$lessonId+'&taskId='+$taskId;
				$this.remove();
				$.get($url, function(data) { 
					swal({
						title: lang.saved,
						text: lang.thanks,
						timer: 1000,
						showConfirmButton: false
					}).catch(swal.noop);
				});
			} else {
				return false;
			}
		});
		return false;
	}));

	$(document).on('click', '.buyPdf', (function() {
		var $this = $(this);
		var $url = $this.attr('data-url');
		var $playerId = $this.attr('data-playerId');
		var $lessonId = $this.attr('data-lessonId');
		swal({
			title: lang.sure,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			cancelButtonText: lang.no,
			confirmButtonText: lang.yes
		}).then( result => {
			if (result.value) {
				$url = $url + '&playerId=' + $playerId + '&lessonId='+$lessonId;
				$.get($url, function(data) { 
					// Display PDF Link
					$this.next(".feedback").html('<a href="'+$this.attr("href")+'" class="btn btn-lg btn-primary">'+lang.getPdf+'</a>');
					// Remove Buy button
					$this.remove();
				});
			} else {
				return false;
			}
		});
		return false;
	}));

  $(document).on('click', '.del', (function() {
    var $this = $(this);
		swal({
			title: lang.sure,
			text: lang.noReturn,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			cancelButtonText: lang.no,
			confirmButtonText: lang.yes
		}).then( result => {
			if (result.value) {
				$this.parent('li').remove();
				$.get($this.attr('href'), function(data) { 
					swal({
						title: "Saved !",
						text: "",
						timer: 1000,
						showConfirmButton: false
					}).catch(swal.noop);
				}); 
			} else {
				return false;
			}
		});
		return false;
  })); 

  $(document).on('click', '.ajaxUnpublish', (function() {
		$delLink = $(this).parent().next('a.del');
		if ($delLink) { $delLink.toggle(); }
    $(this).parents('li').toggleClass('strikeText');
    var $this = $(this);
    $.get($(this).val(), function(data) { 
        //$(this).parent().next(".feedback").html(''); 
    }); 
  })); 

  $(document).on('click', '.validatePotion', (function() {
		var $this = $(this);
		var $result = $this.attr('data-result');
		var $playerId = $this.attr('data-playerId');
		var $url = $this.attr('data-url');
		switch($result) {
			case 'good': var $text= '<i class="glyphicon glyphicon-thumbs-up"></i> '+lang.good; break;
			case 'bad': var $text= '<i class="glyphicon glyphicon-thumbs-down"></i> '+lang.bad; break;
			default: var $text='';
		}
		swal({
			title: lang.sure,
			type: "question",
			html: $text,
			showCancelButton : true,
			allowOutsideClick : true,
			cancelButtonText: lang.no,
			confirmButtonText: lang.yes,
		}).then( result => {
			if (result.value) {
				// Send form (via Ajax)
				var $data = 'result='+$result+'&playerId='+$playerId;
				$.post($url, $data, function(data) {
					data = data;
					swal({
						title: lang.saved,
						text: lang.thanks,
						timer: 1000,
						showConfirmButton: false
					}).then( result => {
						if (result.dismiss === swal.DismissReason.timer) {
							$this.remove();
						}
					});
				});
			} else {
				return false;
			}
		});
    return false; 
  })); 

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

  $('button.popup').click( function(e) {
		e.preventDefault();
		$(this).closest('form').attr('target', '_blank').submit(); // Open form in new tab/window
	});

  $('#periodId').focus( function() {
		$('#selectedPeriod').click();
	});
  $('#periodId').on('change', function() {
		$confirmButton = $(this).next('button');
		$href = $(this).next('button').attr('data-href');
		$confirmButton.attr('data-href', $href+'/'+$(this).val());
		$confirmButton.prop('disabled', false);
	});
  $('#startDate, #endDate').focus( function() {
		$('#customDates').click();
	});
  $('.reportCat').click( function() {
		var $reportId = $(this).attr("data-reportId");
		$('#allOptions').show("blind");
		$(".specificOption").hide("blind");
		if ($reportId) {
			$('#'+$reportId).show("blind");
		}
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

  $('#buyFormSubmit').on( "click", function(e) {
		e.preventDefault();
		var $this = $(this).parents("form");
		swal({
			title: lang.sure,
			type: "question",
			showCancelButton : true,
			allowOutsideClick : true,
			cancelButtonText: lang.no,
			confirmButtonText: lang.yes,
		}).then( result => {
			if (result.value) {
				// Send form (via Ajax)
				var $data = $this.serialize()+'&buyFormSubmit=save';
				var $formUrl = $this.attr('action');
				$.post($formUrl, $data, function(data) {
					data = JSON.parse(data);
					$redirectUrl = data.url;
					window.location.href = $redirectUrl;
				});
			} else {
				return false;
			}
		});
	});

  $('#donateFormSubmit').on( "click", function(e) {
		e.preventDefault();
		var $this = $(this).parents("form");
    if ($('#donator').val() == 0 ) {
      swal('Donator error?');
      return false;
    }
    if ($('#receiver').val() == 0 ) {
      swal(lang.pleaseSelect);
      return false;
    }
    if ($('#amount').val() == 0 || $('#amount').val() == '') {
			swal(lang.invalidAmount);
      return false;
    } 
		swal({
			title: lang.sure,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			cancelButtonText: lang.no,
			confirmButtonText: lang.yes,
		}).then( result => { // Send form
			if (result.value) {
				$('#donateFormSubmit').prop('disabled', true);
				// Send form (via Ajax)
				var $data = $this.serialize()+'&donateFormSubmit=save';
				var $formUrl = $this.attr('action');
				// var $redirectUrl = $('#redirectUrl').val();
				$.post($formUrl, $data, function(data) {
					data = JSON.parse(data);
					$redirectUrl = data.url;
					window.location.href = $redirectUrl;
				});
			} else { // Don't send form
				return false;
			}
		});
	});
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
	$('#donator').on('change', function() {
		if ($(this).val() != 0) {
			$(this).next('.form-control-feedback').hide();
			if (checkAmount($('#amount').val())) {
				$('#donateFormSubmit').prop('disabled', false);
			}
		} else {
			$(this).next('.form-control-feedback').show();
			$('#donateFormSubmit').prop('disabled', true);
		}
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

	$('[data-toggle=tooltip]').hover(function(){
			// on mouseenter
			$(this).tooltip({container : 'body'});
			$(this).tooltip('show');
	}, function(){
			// on mouseleave
			$(this).tooltip('hide');
	});

	// Monster invasions
  $('#toggle').on('click', function() {
    $('#quizMenu').toggleClass('shown');
    $('#quizMenu').toggleClass('hidden');

    return false;
  });
  $('#tickAll').on('click', function() {
    $('.list-group-item input[type=checkbox]').each( function() {
			$(this).prop('checked', true);
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
    $('.list-group-item input[type=checkbox]').each( function(index) {
      if ( $(this).prop('checked') === true) {
				// At least 1 player is checked
        noChecked = false;
			}
		});
		if (noChecked == true && $('#quizMenu').is(':visible')) { // No checked players
			alert(lang.pleaseSelect);
			return false;
		}
  });

  $('.showInfo').on('click', function() {
		var $this = $(this);
		var $itemId = $this.attr("data-id");
		if ($this.hasClass("buy")) {
			var $playerGC = parseInt($('#playerGC').text());
			var $todayItemsCount = parseInt($('#todayItemsCount').text());
			var $url = $('#showInfo').attr('data-href') + '?id=buy&pageId=' + $itemId;
			var $submit = $this.attr('data-href');
			var $formData = "buyFormSubmit=on&playerId="+$this.attr('data-playerId')+"&item="+$this.attr('data-id');
			swal({
				title: lang.loading,
				onOpen: function() {
					swal.showLoading();
					$.get($url, function(data) { 
						if ($todayItemsCount < 3) {
							var $myContent = data;
						} else {
							var $myContent = lang.errorLimit;
						}
						swal({
							title: lang.buy,
							html: $myContent,
							showConfirmButton: true,
							confirmButtonText : lang.yes,
							cancelButtonText : lang.no,
							showCancelButton: true,
							allowOutsideClick: true,
							width: 800
						}).then( result => { // Buy item
							if (result.value) {
								swal.showLoading();
								$.post($submit, $formData, function(data) { 
									swal({
										title: lang.saved,
										text: lang.thanks,
										timer: 1000,
										showConfirmButton: false
									}).then(result => {
										if (result.dismiss === swal.DismissReason.timer) {
											// JS update to avoid reloading
											// window.location.reload();
											// Set new player GC
											$itemGC = parseInt($this.parent('li').attr('data-gc'));
											$playerNewGC = $playerGC-$itemGC;
											$("#playerGC").text($playerNewGC);
										  // Remove newly bought item
											$this.parent('li').remove();
											// Increase limit of today's items ad update lists
											$todayItemsCount++;
											$('#todayItemsCount').text($todayItemsCount);
											if ($todayItemsCount < 3) {
												$('li.possibleItems').each(function() {
													$itemGC = parseInt($(this).attr('data-gc'));
													if ($itemGC > $playerNewGC) {
														$(this).remove();
													}
												});
											} else {
												$('p.label-primary').remove();
												$('.possibleItems').remove();
											}
										  // Display update message (for miniProfile)
											$('.reloadRequired').removeClass('hidden');
										}
									});
								});
							} else {
								return;
							}
						});
					});
			}});
		} else {
			var $url = $('#showInfo').attr('data-href') + '?id=showInfo&pageId=' + $itemId;
			swal({
				title: 'Loading info...',
				onOpen: function() {
					swal.showLoading()
					$.get($url, function(data) { 
						var $myContent = data;
						swal({
							title: '',
							html: $myContent,
							showConfirmButton: false,
							cancelButtonText : lang.close,
							showCancelButton: true,
							allowOutsideClick: true,
							width: 800
						}).catch(swal.noop);
					});
			}}).catch(swal.noop);
		}
		return false;
	});

  $('.pickFromList').on('click', function() {
		var $this = $(this);
		var list = $this.attr("data-list");
		var items = list.split(',');
		var $pageId = chance.pick(items);
		var $team = $this.attr("data-team");
		if ($team) { $team = '&teamId='+$team; }
		var $news = $('#newsList li').length;
		var $url = $('#ajaxDecision').attr('data-href') + '?id=' + $('#ajaxDecision').attr('data-id')+'&pageId='+$pageId+'&news='+$news+$team;
		swal({
			title: lang.decision,
			onOpen: function () {
				swal.showLoading()
				$.get($url, function(data) { 
					var $myContent = data;
					swal({
						title: lang.whatDo,
						html: $myContent,
						width: 800,
						showConfirmButton: false,
						cancelButtonText : lang.nothing,
						showCancelButton: true,
						allowOutsideClick: true,
					});
				});
			}
		}).catch(swal.noop);
	});

	$(document).on('click', '.toggleStrike', function() {
		$(this).toggleClass('label-danger label-success');
		$(this).next('span').toggleClass('strikeText');
	});

	$(document).on('click', '.ajaxBtn', function() {
		var $this = $(this);
		var $type = $this.attr("data-type");
		if ($type == 'memory') {
			var $result = $this.attr('data-result');
			switch($result) {
				case 'good': var $text= '<i class="glyphicon glyphicon-thumbs-up"></i> '+lang.good; break;
				case 'bad': var $text= '<i class="glyphicon glyphicon-thumbs-down"></i> '+lang.bad; break;
				default: var $text='';
			}
			swal({
				title: lang.sure,
				type: "question",
				html: $text,
				showCancelButton : true,
				allowOutsideClick : true,
				confirmButtonText: lang.yes,
				cancelButtonText: lang.no,
			}).then( result => {
				if (result.value) {
					var $url = $this.attr('data-url');
					$.get($url, function(data) { 
						$this.parents("li").remove();
						swal({
							title: lang.saved,
							text: lang.thanks,
							timer: 1000,
							showConfirmButton: false
						}).catch(swal.noop);
					});
				} else {
					return false;
				}
			});
		}
		if ($type == 'fightRequest') {
			var $result = $this.attr('data-result');
			switch($result) {
        case 'v' : var $text= '<i class="glyphicon glyphicon-thumbs-up"></i> '+lang.v; break;
				case 'vv': var $text= '<i class="glyphicon glyphicon-thumbs-up"></i> '+lang.vv; break;
        case 'r' : var $text= '<i class="glyphicon glyphicon-thumbs-down"></i> '+lang.r; break;
				case 'rr': var $text= '<i class="glyphicon glyphicon-thumbs-down"></i> '+lang.rr; break;
				default: var $text='';
			}
			swal({
				title: lang.sure,
				type: "question",
				html: $text,
				showCancelButton : true,
				allowOutsideClick : true,
				confirmButtonText: lang.yes,
				cancelButtonText: lang.no,
			}).then( result => {
				if (result.value) {
					var $url = $this.attr('data-url');
					$.get($url, function(data) { 
						$this.parents("li").remove();
						swal({
							title: lang.saved,
							text: lang.thanks,
							timer: 1000,
							showConfirmButton: false
						}).catch(swal.noop);
					});
				} else {
					return false;
				}
			});
		}
		if ($type == 'initiative') {
			swal({
				title: lang.tell,
				type: "info",
				html: lang.talkIndications,
				showCancelButton : true,
				allowOutsideClick : false,
				cancelButtonText: lang.enough,
				confirmButtonText: lang.goodJob,
			}).then( result => {
				if (result.value) {
					var $url = $this.attr('data-url');
					$.get($url, function(data) { 
						swal({
							title: lang.saved,
							text: lang.thanks,
							timer: 1000,
							showConfirmButton: false
						}).catch(swal.noop);
					});
				} else {
					return false;
				}
			});
		}
		if ($type == 'teamNews') {
			var $teamNews = $('#newsList').html();
			swal({
				title: lang.teamNews,
				text: lang.teamNewsIndications,
				timer: 2000,
				showConfirmButton : false,
			}).catch(swal.noop);
			window.scrollBy(0,1000);
		}
		if ($type == 'discount') {
			var $discount = $('#discount').html();
			swal({
				title: lang.discountSearch,
				text: lang.wait,
				timer: 2000,
				showConfirmButton : false,
				onClose : () => {
					swal({
						title: lang.discount,
						html: $discount,
						showConfirmButton : false,
						showCancelButton : true,
						allowOutsideClick : false,
						cancelButtonText: lang.nevermind
					});
				}
			}).catch(swal.noop);
		}
		if ($type == 'showInfo') {
			var $this = $(this);
			var $itemId = $this.attr("data-id");
			var $url = $('#showInfo').attr('data-href') + '?id=showInfo&pageId=' + $itemId;
			swal({
				title: lang.loading,
				onOpen: function() {
					swal.showLoading()
					$.get($url, function(data) { 
						var $myContent = data;
						swal({
							title: '',
							html: $myContent,
							showConfirmButton: false,
							cancelButtonText : 'Close',
							showCancelButton: true,
							allowOutsideClick: true,
							width: 800
						}).catch(swal.noop);
					});
			}}).catch(swal.noop);
		}
		return false;
	});

	$(document).on('click', '.buyBtn', function() {
		var $this = $(this);
		swal({
			title: lang.sure,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			confirmButtonText: lang.yes,
			cancelButtonText: lang.no,
		}).then( result => {
			if (result.value) {
				var $url = $this.attr('data-url');
				var $type = $this.attr('data-type');
				if ($type == 'heal')  {
					$this.parents("div.thumbnail").remove();
				}
				$.get($url, function(data) { 
					swal({
						title: lang.saved,
						text: lang.thnks,
						timer: 1000,
						showConfirmButton: false
					}).catch(swal.noop);
				});
			} else {
				return false;
			}
		});
		return false;
	});

  $('#startFight').on('click', function() {
    // TODO : Move function into exercise.js?
		var $this = $(this);
		$('#exHeader').hide('slow', function() {
			$this.parents('.alert').hide();
			$('#energyDiv').show('slow');
			$('#fightForm').show();
		});
    // Start exercise
    // TODO : Record session start...
  });

	$('#adminTableForm :submit').on('click', function(e){
		var $this = $(this).parents("form");
		var $redirectUrl = '';
		e.preventDefault();
		swal({
			title: lang.sure,
			type: "warning",
			showCancelButton : true,
			allowOutsideClick : true,
			cancelButtonText: lang.noCheck,
			confirmButtonText: lang.yesSave,
		}).then( result => {
			if (result.value) {
				// Send adminTableForm (via Ajax)
				$("#adminTableForm :submit").prop('disabled', true);
				var $checked = $this.find(' :checkbox:checked').not('.selectAll, .commonComment, #adminTableRedirection');
        var $customDate = $('#customDate').val();
        if ($('#adminTableRedirection').prop('checked')) {
          var $toSave = 'adminTableSubmit=Save&adminTableRedirection=1&customDate='+$customDate+'&';
        } else {
          var $toSave = 'adminTableSubmit=Save&customDate='+$customDate+'&';
        }
				var $formUrl = $this.attr('action');
				for (var i=0; i<$checked.length; i++) {
					var $customId = $checked.eq(i).attr('data-customId');
					var $comment = $("input:text[name*="+$customId+"]");
					$toSave += $checked.eq(i).attr('name')+'=on&'+$comment.attr('name')+'='+$comment.val()+'&';
					if ($checked.length-i > 5) {
						if (i>0 && i % 5 == 0) {
							$.post($formUrl, $toSave, function(data) {
								data = JSON.parse(data);
								$alreadySaved = parseInt($('#progress').text());
								$('#progress').text($alreadySaved + data.saved+' saved.');
							}).fail( function() {
								$('#progress').text(lang.error);
							});
							$toSave = 'adminTableSubmit=Save&';
						}
					} else { // In the 5 last
						if (i == $checked.length-1) {
							$.post($formUrl, $toSave, function(data) {
								data = JSON.parse(data);
								$alreadySaved = parseInt($('#progress').text());
								$('#progress').text($alreadySaved + data.saved +' saved.');
								$redirectUrl = data.url;
							}).fail( function() {
								$('#progress').text(lang.error);
							});
						}
					}
				}
				$(document).ajaxStop(function() {
          window.location.href = $redirectUrl;
					setTimeout( function(){ $('#progress').text(lang.redirecting); }, 1000);
				})
				swal({
					title: '<span id="progress">0 saved.</span>',
					html: lang.saveForm+"<p>("+$checked.length+" "+lang.itemsTosave+")</p>",
					showConfirmButton: false
				});
			} else { // Don't send adminForm
				return false;
			}
		});
	});

	if ($('div.ajaxContent')) {
		var timerFast = 0;
		var timerSlow = 1000;
		$('div.ajaxContent').each( function() {
			var el = $(this);
			var url = $(this).attr('data-href');
			if (el.attr('data-priority') == '1') {
				setTimeout( function() { getContentFromAjax(url, el); }, timerFast);
			} else {
				setTimeout( function() { getContentFromAjax(url, el); }, timerSlow);
			}
			timerFast += 200; 
			timerSlow += 500;
		});
	}
	function getContentFromAjax(url, el) {
		var id = el.attr('data-id');
    $.get(url+'?id='+id+'&randSeed='+Math.random(), function(data) { 
			el.html(data); 
			el.children('[data-toggle="tooltip"]').tooltip();
			initTables();
    }); 
    return false; 
	}

	// Gallery function
	if ($('.grid').length > 0) { $('.grid').masonry(); }
	
	// Init tables if needed
	if ($('table').length > 0) { initTables(); }

	if ($('#helpAlert').length > 0) {
		swal({
			position: 'top',
			backdrop: false,
			title: $('#helpTitle').html(),
			html: $('#helpMessage').html(),
			showCloseButton: true,
			showConfirmButton: false,
			allowOutsideClick : false,
			timer: 8000
		});
	}
}); 

var FEEL = {
	onBeforeReload: function (o) {
			if($('#usersTable').length) {
				return false;
			}
	}
};
// Tables init
var initTables = function() {
  var usersTable = $('#usersTable').DataTable({
    lengthMenu: [ [30, 50, -1], [30, 50, "All"] ]
  });
  $('#mapTable').DataTable({
    dom: 'ft',
    paging: false,
    order: [[ 0, "asc"]]
  });
  var trainingTable = $('#trainingTable').DataTable({
    lengthMenu: [ [25, 50, -1], [25, 50, lang.all] ],
    order: [[ 0, "asc"], [1, "asc"]]
  });
  var historyTable = $('#historyTable').DataTable({
		retrieve: true,
    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, lang.all] ],
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
  $('#fightersTable').DataTable({
    paging: false,
    order: [[ 0, "asc" ]],
  });
  $('#teamTable').DataTable({
    paging: false,
    searching: false,
    columnDefs: [{ "orderable": false, "targets": 1 },
      { "orderable": false, "targets": 4}],
    order: [[ 3, "desc" ]],
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
    } ],
  });
  var monstersTable = $('#monstersTable').DataTable({
    lengthMenu: [ [25, 50, -1], [25, 50, lang.all] ],
    order: [[ 2, "asc"], [0, "asc"]],
    orderCellsTop: true
  });
  var loggedTable = $('#loggedTable').DataTable({
    lengthMenu: [ [25, 50, -1], [25, 50, 75, 100, lang.all] ],
    order: [[ 1, "desc"]],
    orderCellsTop: true
  });
  var lessonsTable = $('#lessonsTable').DataTable({
    paging: false,
    lengthMenu: [ [25, 50, -1], [25, 50, 75, 100, lang.all] ],
    order: [[ 0, "asc"], [1, "asc"]],
    orderCellsTop: true
  });

  $('.categoryFilter').click(function(){
    mainShop.draw();
    taskTable.draw();
    historyTable.draw();
    monstersTable.draw();
    trainingTable.draw();
  });		    
    
  $('.toggle-vis').on( 'click', function (e) {
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
}

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
	$('.ct_'+taskId).not(':disabled').prop('checked', $('#csat_'+taskId).prop('checked'));
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
	var nbChecked = parseInt($('#nbChecked').text());
  if ( $(obj).prop('checked') === true) {
    var newGC = remainingGC - itemGC;
    $('#remainingGC').text(newGC);
		nbChecked++;
		$('#nbChecked').text(nbChecked);
		if (nbChecked >= 3) {
			// Disable all items left
			$('ul.itemList li input[type=checkbox]').not($(obj)).each(function() {
				if ($(this).prop('checked') === false) {
					$(this).prop('disabled', true);
					$(this).parent('label').css('text-decoration', 'line-through');
				}
			});
		} else {
			// Disable impossible items left
			$('ul.itemList li input[type=checkbox]').not($(obj)).each(function() {
				if ($(this).attr('data-gc') > newGC && $(this).prop('checked') === false) {
					$(this).prop('disabled', true);
					$(this).parent('label').css('text-decoration', 'line-through');
				}
			});
			// Enable save buttons
			$("#marketPlaceForm :submit").prop('disabled', false);
		}
  } else {
    var newGC = parseInt(remainingGC) + parseInt(itemGC);
    $('#remainingGC').text(newGC);
		nbChecked--;
		$('#nbChecked').text(nbChecked);
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
			if (nbChecked > 0 && nbChecked <= 3) {
				$("#marketPlaceForm :submit").prop('disabled', false);
			}
    } else {
      $("#marketPlaceForm :submit").prop('disabled', true);
    }
  }
}

$('#marketPlaceForm :submit').on('click', function(e){
	var $this = $(this).parents("form");
	e.preventDefault();
	swal({
		title: lang.sure,
		type: "warning",
		showCancelButton : true,
		allowOutsideClick : true,
		cancelButtonText: lang.noCheck,
		confirmButtonText: lang.yesSave,
	}).then( result => { // Send form
		if (result.value) { // Send form (via Ajax)
			$("#marketPlaceForm :submit").prop('disabled', true);
			var $data = $this.serialize()+'&marketPlaceSubmit=save';
			var $formUrl = $this.attr('action');
			$.post($formUrl, $data, function(data) {
				console.log(data);
				data = JSON.parse(data);
				$redirectUrl = data.url;
				window.location.href = $redirectUrl;
			});
		} else { // Don't send form
			return false;
		}
	});
});

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
