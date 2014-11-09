$(document).ready(function() {
  $(".ajax").click(function() {
    $("#report").html("<p>Loading...</p>"); 
    
    $.get($(this).attr('href'), function(data) { 
        $("#reportDiv").html(data); 
    }); 

    return false; 
  }); 

  $('#report_button').click( function() {
    var url = $('#players_list').val();

    $.get(url, function(data) { 
        $("#reportDiv").html(data); 
    }); 

    return false; 
  });


  $('#participation').click( function() {
    var limitCheckbox = $('#limit10');
    if ($(this).is(':checked')) {
      limitCheckbox.removeAttr('disabled'); // Enable limit
      $('a.reportButton').each( function() {
        var href = $(this).attr('href');
        $(this).attr('href', href+'/participation');
      });
    } else {
      $('a.reportButton').each( function() {
        var href = $(this).attr('href');
        if (href.indexOf('/participation') != -1) {
          var mySplitResult = href.split("\/");
          if (limitCheckbox.is(':checked')) { // Uncheck limit
            href = href.substring(0, href.length-3);
          }
          href = href.substring(0, href.length-14); // remove '/participation' from url
          $(this).attr('href', href); // remove '/participation' from url
        }
      });
      limitCheckbox.attr('checked', false);
      limitCheckbox.attr('disabled', true); // Disabled limit
    }
  });

  $('#limit10').click( function() {
    if ($(this).is(':checked')) {
      $('a.reportButton').each( function() {
        var href = $(this).attr('href');
        $(this).attr('href', href+'/10');
      });
    } else {
      $('a.reportButton').each( function() {
        var href = $(this).attr('href');
        $(this).attr('href', href.substring(0, href.length-3));
      });
    }
  });
}); 
