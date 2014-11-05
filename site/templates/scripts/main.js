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
}); 
