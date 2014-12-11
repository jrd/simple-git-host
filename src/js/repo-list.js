$(function() {
  $('#tabs>li>a').each(function(index) {
    if (!$(this).data('div')) {
      return;
    }
    $(this).click(function() {
      $(this).parent().parent().children().removeClass('active');
      $(this).parent().parent().find('a').each(function(index2) {
        $($(this).data('div')).hide();
      });
      $(this).parent().addClass('active');
      $($(this).data('div')).show();
      return false;
    });
    if (!$(this).parent().hasClass('active')) {
      $($(this).data('div')).hide();
    }
  });
});

