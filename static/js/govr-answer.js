/* Copyright (C) 2012  Governo do Estado do Rio Grande do Sul
 *
 *   Author: Lincoln de Sousa <lincoln@gg.rs.gov.br>
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

function save() {
    var $ = jQuery;
    var val = function(n) {
        return $('form [name=' + n + ']').val();
    };

    slow_operation(function(done) {
        $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            data: {
                action:'govr_contrib_answer',
                data: {
                    id: val('id'),
                    answer: val('answer'),
                    category: val('category'),
                    date: val('answered_at'),
                    data: val('data')
                }
            },
            success: function(data) {
                done();
                var url = 'admin.php?' +
                        'page=gov-responde&' +
                        'subpage=contributions&' +
                        'theme_id=' + val('theme_id');
                window.location.href = url;
            }
        });
    });
}

jQuery(function () {
  var $ = jQuery;

  /* Datepics */
  $('.date').datepicker({ dateForrmat: 'dd/mm/yy' });
});
