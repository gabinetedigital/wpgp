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


function slow_operation(fn) { fn(new Function) }

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