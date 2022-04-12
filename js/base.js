(function($) {
    $.get = function(key)   {
        key = key.replace(/[\[]/, '\\[');
        key = key.replace(/[\]]/, '\\]');
        let pattern = "[\\?&]" + key + "=([^&#]*)";
        let regex = new RegExp(pattern);
        let url = unescape(window.location.href);
        let results = regex.exec(url);
        if (results === null) {
            return null;
        } else {
            return results[1];
        }
    }

})(jQuery);

const seccion = $.get("seccion");
const accion = $.get("accion");
const session_id = $.get("session_id");