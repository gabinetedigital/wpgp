/* Copyright (C) 2011  Governo do Estado do Rio Grande do Sul
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

(function ($) {
    function getdata($el) {
        return $.parseJSON($el.attr('data'));
    }

    $(function() {
        var data;
        var xaxis_date_options = {
          mode: "time",
          timeformat: "%d/%m/%y"
        };

        var $chart_byday = $("#chart_byday");
        data = $.map(getdata($chart_byday), function (item) {
            var date = new Date(item.year,item.month-1,item.day);
            return [[date, item.count]];
        });
        $.plot($chart_byday, [data], {xaxis: xaxis_date_options});
    });
})(jQuery);
