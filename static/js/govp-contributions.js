
jQuery(function() {
  var $ = jQuery;

  //event binder
  function inliner(dbfield, accessor, editable) {
    var original_text;
    var td;
    var p;
    function show_field() {
      p = $(this).find('p');
      td = p.parent();
      p.unbind("dblclick", arguments.callee);
      var id = /\[([0-9]+)\]/.exec(td.attr("id"))[1];
      original_text = p.html();

      var ok = $("<input type='submit' value='OK'>").click(function() {
        var data = {id:id,field:dbfield};
        data.value = accessor.call(editable);

        slow_operation(function(done) {
          $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            data: {action:'govp_update_contrib',data:data},
            success: function(data) {
              done();
              original_text = accessor.call(editable);
              revert_editable();
            }
          });
        });
      });

      var cancel = $("<input type='submit' value='Cancel'>")
        .click(revert_editable);

      accessor.call(editable, p.html());
      p.html('');
      p.append(editable).append(ok).append(cancel);
    }
    function revert_editable() {
      p.bind('dblclick',show_field);
      p.html(original_text);
    }
    return show_field;
  }

  $(".add-new-h2").click(function(ev) {
    ev.preventDefault();
    $(".wpgp-new-contrib").show();
  });


  //form to insert contribution
  $(".wpgp-new-contrib input[name=Cancel]").click(
    function() { $(".wpgp-new-contrib").hide()});

  $(".wpgp-new-contrib input[name=OK]").click(function() {
    var theme = $(".wpgp-new-contrib select").val();
    var title = $(".wpgp-new-contrib input[name=title]").attr("value");
    var content = $(".wpgp-new-contrib textarea").val();
    var part = $(".wpgp-new-contrib input[name=part]").attr("value");
    slow_operation(function(done) {
      $.ajax({
        url: 'admin-ajax.php',
        type: 'post',
        data: {action:'govp_create_contrib',
               data:{theme_id:theme,title:title,content:content, part:part}},
        success: function(data) {
          done();
          window.location.reload();
        }
      });
    });
  });

  //hide delete button from approved
  reduce($(".delete-contrib"), function(x) {
    return is_approved(x.attr("name"));
  }).hide();

  reduce($(".delete-contrib"), function(x) {
    return !is_approved(x.attr("name"));
  }).click(function(ev) {
    ev.preventDefault();
    var id = $(this).attr("name");
    if (confirm("Are you sure you want to delete?")) {
      if ($(".child-of-"+id).length > 0) {
        alert("Unassociate children before removing this");
        return;
      }
      slow_operation(function(done) {
        $.ajax({
          url: 'admin-ajax.php',
          type: 'post',
          data: {action:'govp_delete_contrib',
                 data:{id:id}},
          success: function(data) {
            done();
            window.location.reload();
          }
        });
      });
    }
  });


  //hide change status for approved
  reduce($(".wpgp-status"), function(x) {
    return is_approved(x.attr("name"));
  }).replaceWith("approved");

  //changing status
  $(".wpgp-status").change(function() {
    var self = $(this);
    var id = self.attr("name");
    var current = $("#contrib-status-val-"+id).val();
    var newvalue = self.val();
    if(confirm("Change the status?")) {
      var data = {id:id,field:'status', 'value': newvalue};
      slow_operation(function(done) {
        $.ajax({
          url: 'admin-ajax.php',
          type: 'post',
          data: {action:'govp_update_contrib',
                 data:data},
          success: function(data) {
            done();
            window.location.reload();
          }
        });
      });
    } else {
      self.val(current);
    }
  });


  //changing 'duplicate'
  $(".contrib-duplicates").change(function() {
    var self = $(this);
    var id = /\[([0-9]+)\]/.exec(self.attr("id"))[1];
    var parent = /contrib-duplicate\[([0-9]+)\]/.exec(self.attr("class"))[1];
    var new_parent = self.val();

    if (is_child(new_parent)) {
      alert("Can't be a duplicated of a duplicated")
      self.val(parent);
      return;
    }
    if (id == new_parent) {
      alert("Can't be duplicated of itself");
      self.val(parent);
      return;
    }

    if (parent == self.val()) return;

    if (!confirm("Confirm change?")) {
      self.val(parent);
      return;
    }

    var data = {id:id,field:'parent', value:new_parent};
    slow_operation(function(done) {
      $.ajax({
        url: 'admin-ajax.php',
        type: 'post',
        data: {action:'govp_update_contrib',data:data},
        success: function(res) {
          done();
          self.val(parent);
          if (res == 'not-found') {
            alert("Contribution " + new_parent + " not found");
          } else {
            window.location.reload();
          }
        }
      });
    });
  });

  //changing theme
  $(".wpgp-theme").change(function() {
    var self = $(this);
    var id = self.attr("name");
    var current = $("#contrib-theme-val-"+id).val();
    var newvalue = self.find(":selected").attr("name");

    if(confirm("Change the theme?")) {
      var data = {id:id,field:'theme_id', 'value': newvalue};
      slow_operation(function(done) {
        $.ajax({
          url: 'admin-ajax.php',
          type: 'post',
          data: {action:'govp_update_contrib',
                 data:data},
          success: function(data) {
            done();
            window.location.reload();
          }
        });
      });
    } else {
      self.val(current);
    }
  });

  //hiding status combo for approved
  //careful: the form has a .wpgp-theme too!
  reduce($(".wp-list-table .wpgp-theme"), function(x) {
    var id =x.attr("name");
    return is_approved(id);
  }).each(function() {
    $(this).replaceWith("<span>"+$(this).val()+"</span>")
  });


  //binding events: title and content inline editing
  $(".contribution").bind(
    'dblclick',inliner('content',$().val, $("<textarea/>")));

  $(".contribution-title").bind(
    'dblclick',inliner(
      'title',function(val) {
        if(val)
          return this.attr('value',val);
        else
          return this.attr('value');
      },
      $("<input type='text'/>")));


  //hiding duplicate field of approved contribs
  reduce($(".contrib-duplicates"), function(x) {
    var id = /\[([0-9]+)\]/.exec(x.attr("id"))[1];
    return is_approved(id);
  }).each(function() {
    $(this).replaceWith("<span></span>")
  });

  //changing parts
  $(".contrib-parts").change(function() {
    var self = $(this);
    var id = /\[([0-9]+)\]/.exec(self.attr("id"))[1];
    var default_val = self.attr('default');
    var new_parent = self.val();

    // var parent = /contrib-part\[([0-9]+)\]/.exec(self.attr("class"))[1];
    // if (is_child(new_parent)) {
    //   alert("Can't be a part of a part");
    //   self.val(parent);
    //   return;
    // }

    // if (new_parent != 0 && $("#row-"+new_parent).length == 0) {
    //   alert("Can't find contrib with ID = " + new_parent);
    //   self.val(parent);
    //   return;
    // }

    // if (id == new_parent) {
    //   alert("Can't be part of itself");
    //   self.val(parent);
    //   return;
    // }

    // if (parent == new_parent) return;

    if (!confirm("Confirm change?")) {
      self.val(default_val);
      return;
    }

    var data = {id:id,field:'part', value:new_parent};
    slow_operation(function(done) {
      $.ajax({
        url: 'admin-ajax.php',
        type: 'post',
        data: {action:'govp_update_contrib',data:data},
        success: function(res) {
          done();
          if (res == 'not-found') {
            alert("Contribution " + id + " not found");
            self.val(default_val);
          } else {
            window.location.reload();
          }
        }
      });
    });
  });

  $(".wp-list-table").show();
  $(".wp-list-table-loading").hide();
});
