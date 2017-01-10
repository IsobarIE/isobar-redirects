(function($) {

  $('.js-add-redirect').click(function(e) {
    addRedirect( $(this) );

    e.preventDefault();
  });

  $('.js-delete-redirect').click(function(e) {
    deleteRedirect( $(this) );

    e.preventDefault();
  });

  $('.js-isobar-upload-redirects').click(function(e) {
    // Show Modal Dialog
    $modal = $('.modal');
    $modal.show();
  });

  $('.js-isobar-export-redirects').click(function() {
    exportExistingRedirects();
  });

  $('.close').click(function() {
    $('.modal').hide();
  });

  $('.js-jump-bottom').click(function() {
    scrollToBottom();
  });

  // Delete all redirects
  $('.js-delete-redirects').click(function() {
    if( confirm('This will delete ALL current redirects. Are you sure you want to do this?') ) {
      deleteAllRedirects();
    }
  });

  function addRedirect( element ) {
    var $tr = $('.js-redirect:last');
    var $clone = $tr.clone();
    $clone.find('input').val('');

    $tr.after($clone);
  }

  function deleteRedirect( element ) {
    $tr = element.parent().parent();
    $tr.remove();
  }

  function deleteAllRedirects() {
    var form_body = $('.js-redirect-body').empty();
    var data = $('<input>').attr('type', 'hidden').attr('name', 'submit_301_hidden').val('');

    form_body.append( $(data) );
    $('.js-isobar-redirect-form').submit();
  }

  function scrollToBottom() {
    $('html, body').scrollTop($(document).height());
  }

  function exportExistingRedirects() {
    $('.js-submit-export').submit();
  }

})(jQuery);