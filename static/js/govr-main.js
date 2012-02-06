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

  $(".theme-scores").click(function(ev) {
    ev.preventDefault();
    window.location.href = window.location.href
      + "&subpage=scores&theme_id="+$(this).attr("name");
  });

  $(".theme-stats").click(function(ev) {
    ev.preventDefault();
    window.location.href = window.location.href
      + "&subpage=stats&theme_id="+$(this).attr("name");
  });

  /* Datepics */
  $('.date').datepicker({ dateForrmat: 'dd/mm/yy' });
});