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