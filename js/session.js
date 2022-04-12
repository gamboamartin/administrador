let registro_id = $.get("registro_id");

$( document ).ready(function() {

    if(seccion === 'session' && (accion === 'inicio')) {
        let btn_selecciona = $("button[name=btn_selecciona]");
        let check = $(".checkboxes");

        btn_selecciona.click(function () {
            check.prop('checked',true);
        });
    }
});