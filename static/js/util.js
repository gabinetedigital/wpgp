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


var $ = jQuery;
function reduce(arr,fn) {
    var ret = $([]);
    for(var i = 0; i < arr.length; i++) {
        var x = $(arr[i]);
        if (fn(x))  {
            ret = ret.add(x);
        }
    }
    return ret;
}

//"loading..." stuff
function slow_operation(fn) {
    $(".wpgp-status-bar").slideDown();
    fn(function() { $(".wpgp-status-bar").slideUp(); });
}

//useful functions...
function get_row_id(tr) {
    return parseInt(tr.attr("id").split("-")[1]);
}

function is_child(id) {
    return $("#row-"+id).hasClass("is-child");
}

function is_parent(id) {
    return !is_child(id);
}

function is_approved(id) {
    return $("#row-"+id).hasClass('wpgp-approved');
}

function move_parent_row(id) {
    var trs = $("#contrib-rows tr");
    var tr = null;
    for (var i = 0; i < trs.length; i++) {
        var trid = get_row_id($(trs[i]));
        if (trid == id) continue;
        if (trid > id) {
            tr = $(trs[i]);
            break;
        }
    }
    if (tr) {
        //there is a tr bigger than id, insert ourself before it
        tr.before($("#row-"+id).detach());
    } else {
        //we are the bigger id. insert after the last
        //note: the last could also be ourself
        var last_tr = $(trs[trs.length-1]);
        if (get_row_id(last_tr) != id) {
            $(trs[trs.length-1]).after($("#row-"+id).detach());
        } //else, stay were we are, already the last
    }
}


function get_query(name) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS = "[\\?&]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec(window.location.href);
    if(results == null)
        return "";
    else
        return decodeURIComponent(results[1].replace(/\+/g, " "));
}

