jQuery(function () {
  $(".theme-name").click(function(ev) {
    ev.preventDefault();
    window.location.href = window.location.href
      + "&subpage=theme_contrib_score&theme_id="+$(this).attr("name");
  });
});