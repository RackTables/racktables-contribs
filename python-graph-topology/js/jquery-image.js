$(document).ready(function(){
  //$('.topo').resizable({
  //  containment: ".result"
  //});

  var helpPaneWidth = $('#help-pane').width();
  $('#help-pane').css('margin-right', '-' + helpPaneWidth + 'px');

  $('#help-button').hover(function(){
      $(this).css('background-color', 'rgba(60, 120, 181, 0.9)')
    },
    function(){
      $(this).css('background-color', 'rgba(60, 120, 181, 0.5)');
    }
  );

  $('#help-button').click(function(){
    if($('#help-pane').css("margin-right") == 0 + "px" && !$(this).is(':animated'))
    {
        $('#help-pane').animate({"margin-right": '-=' + helpPaneWidth});
    }
    else
    {
        if(!$(this).is(':animated'))//perevent double click to double margin
        {
            $('#help-pane').animate({"margin-right": '+=' + helpPaneWidth});
        }
    }

  });
});
