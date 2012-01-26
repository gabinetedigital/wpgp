/* Copyright (C) 2012  Governo do Estado do Rio Grande do Sul
 *
 *    Author: Lincoln de Sousa <lincoln@gg.rs.gov.br>
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
                action:'gove_audience_new',
                data: {
                    title: val('title'),
                    date: val('date'),
                    visible: val('visible'),
                    subject: val('subject'),
                    description: val('description'),
                    data: val('data')
                }
            },
            success: function(data) {
                done();
                window.location.href = 'admin.php?page=gov-escuta';
            }
        });
    });
    return false;
}
