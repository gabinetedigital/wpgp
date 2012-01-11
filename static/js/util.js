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

