jQuery(function () {
  var $ = jQuery;

  $(".wpgp-new-theme").find("input[name='Cancel']").click(function() {
    $(".wpgp-new-theme").hide();
  });

  $(".add-new-h2").click(function(ev) {
    ev.preventDefault();
    $(".wpgp-new-theme").show();
  });

  $(".wpgp-new-theme").find("input[name='OK']").click(function() {
    var theme_name = $(".wpgp-new-theme")
      .find('input[name=title]')
      .attr('value');
    slow_operation(function(done) {
      $.ajax({
        url: 'admin-ajax.php',
        type: 'post',
        data: {action:'govr_create_theme',
               data:{theme_name:theme_name}},
        success: function(data) {
          done();
          window.location.reload();
        }
      });
    });
  });

  $(".delete-theme").click(function(ev) {
    ev.preventDefault();
    var id = $(this).attr("id");
    slow_operation(function(done) {
      $.ajax({
        url: 'admin-ajax.php',
        type: 'post',
        data: {action:'govr_delete_theme',
               data:{theme_id:id}},
        success: function(data) {
          done();
          if (data == "non-empty") {
            alert("Only empty themes can be deleted");
          } else {
            window.location.reload();
          }
        }
      });
    });
  });

  $(".theme-name").click(function(ev) {
    ev.preventDefault();
    window.location.href = window.location.href
      + "&subpage=contributions&theme_id="+$(this).attr("name");
  });
});