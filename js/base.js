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

function activa_chk(chk){
    chk.val('activo');
    chk.prop('checked',true);
    chk.siblings(".data_hidden").remove();

}

function asigna_chk_data(value, chk){
    if(value === 'activo'){
        activa_chk(chk);
    }
    if(value === 'inactivo'){
        desactiva_chk(chk);
    }
}

function desactiva_chk(chk){
    chk.val('inactivo');
    let name = (chk.attr("name"));
    let hidden = '<input type="hidden" name="'+name+'" value="inactivo" class="data_hidden"/>';
    chk.parent().append(hidden);
    chk.prop('checked',false);
}

function chkboxes(){
    let checboxes = $(".checkboxes");

    checboxes.each(function(){
        asigna_chk_data($(this).val(),$(this));
    });

    checboxes.click(function () {
        if( $(this).prop('checked') ) {
            activa_chk($(this));
        }
        else{
            desactiva_chk($(this));
        }
    });
}