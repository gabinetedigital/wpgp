jQuery(function() {
  var $ = jQuery;

  $("#add-theme").click(function(ev) {
    ev.preventDefault();
    slow_operation(function(done) {
      $.ajax({
        url: 'admin-ajax.php',
        type: 'post',
        data: {action:'govp_create_theme',
               data:{session_id:get_query('session_id'),
                     name:$("#new-theme").val()}},
        success: function(data) {
          done();
          document.location.reload();
        }
      });
    });
  });

  $("#save").click(function() {
    slow_operation(function(done) {
      $.ajax({
        url: 'admin-ajax.php',
        type: 'post',
        data: {action:'govp_save_config',
               data:{name: $("#name").val(),
                     purl: $("#pairwise-url").val(),
                     phost: $("#pairwise-host").val(),
                     pname: $("#pairwise-name").val(),
                     puser: $("#pairwise-user").val(),
                     ppass: $("#pairwise-pass").val(),
                     session_id:get_query('session_id')}},
        success: function(data) {
          done();
          document.location.reload();
        }
      });
    });
  });
});