/* Copyright (C) 2011  Governo do Estado do Rio Grande do Sul
 *
 *   Author: Thiago Silva <thiago@metareload.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function slow_operation(fn) { fn(new Function) }
jQuery(function () {
  var $ = jQuery;

  $(".wpgp-new-session").find("input[name='Cancel']").click(function() {
    $(".wpgp-new-session").hide();
  });

  $(".add-new-h2").click(function(ev) {
    ev.preventDefault();
    $(".wpgp-new-session").show();
  });

  $(".wpgp-new-session").find("input[name='OK']").click(function() {
    var session_name = $(".wpgp-new-session")
      .find('input[name=title]')
      .attr('value');
    slow_operation(function(done) {
      $.ajax({
        url: 'admin-ajax.php',
        type: 'post',
        data: {action:'govp_create_session',
               data:{session_name:session_name}},
        success: function(data) {
          done();
          window.location.reload();
        }
      });
    });
  });

  $(".delete-session").click(function(ev) {
    ev.preventDefault();
    var id = $(this).attr("id");
    slow_operation(function(done) {
      $.ajax({
        url: 'admin-ajax.php',
        type: 'post',
        data: {action:'govp_delete_session',
               data:{session_id:id}},
        success: function(data) {
          done();
          if (data == "non-empty") {
            alert("Only empty sessions can be deleted");
          } else {
            window.location.reload();
          }
        }
      });
    });
  });

  $(".session-name").click(function(ev) {
    ev.preventDefault();
    window.location.href = window.location.href
      + "&subpage=contributions&session_id="+$(this).attr("name");
  });

  $(".session-config").click(function(ev) {
    ev.preventDefault();
    window.location.href = window.location.href
      + "&subpage=session_config&session_id="+$(this).attr("name");
  });

  $(".session-scores").click(function(ev) {
    ev.preventDefault();
    window.location.href = window.location.href
      + "&subpage=total_scores&session_id="+$(this).attr("name");
  });

  $(".session-theme-scores").click(function(ev) {
    ev.preventDefault();
    window.location.href = window.location.href
      + "&subpage=theme_list_score&session_id="+$(this).attr("name");
  });

  $(".session-stats").click(function(ev) {
    ev.preventDefault();
    window.location.href = window.location.href
      + "&subpage=stats&session_id="+$(this).attr("name");
  });


  $(".wp-list-table").show();
  $(".wp-list-table-loading").hide();
});