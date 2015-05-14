/**
 * Created by user on 15/1/13.
 */
'use strict';
var toast_confg = {
    appendTo:"body",
    offset:
    {
        from: "top",
        amount: 10
    },
    align: "center",
    delay: 4000,
    minWidth: 250,
    maxWidth: 320
};
var success_toast = function(msg){
    $.simplyToast(msg, 'success', toast_confg);
}

var warn_toast = function(msg){
    $.simplyToast(msg, 'warning', toast_confg);
}
var danger_toast = function(msg){
    $.simplyToast(msg, 'danger', toast_confg);
}
var storage = {
    set: function(key, value) {
        if (!key || !value) {return;}
        if (typeof value === "object") {
            value = JSON.stringify(value);
        }
        localStorage.setItem(key, value);
    },
    get: function(key) {
        var value = localStorage.getItem(key);
        if (!value) {return;}
        if (value[0] === "{") {
            value = JSON.parse(value);
        }
        return value;
    },
    remove:function(key){
        localStorage.removeItem(key);
    },
    pop: function(key,value){
        var list = this.get(key);
        if (Array.isArray(list)){
            var idx = list.indexOf(value);
            if (idx == -1){
                list.unshift(value);
            }else{
                list.splice(idx,1).unshift(value);
            }
        }
    }
}
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function redirectTo (url) {
    var ua        = navigator.userAgent.toLowerCase(),
        isIE      = ua.indexOf('msie') !== -1,
        version   = parseInt(ua.substr(4, 2), 10);

    // Internet Explorer 8 and lower
    if (isIE && version < 9) {
        var link = document.createElement('a');
        link.href = url;
        document.body.appendChild(link);
        link.click();
    }

    // All other browsers
    else { window.location.href = url; }
}