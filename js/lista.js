$(document).ready(function () {
    if (accion === 'lista') {
        let iconos_menu_lista = $('.icono_menu_lista');
        let registro_id = -1;
        let status_row = 'activo';
        $('.registro_lista').click(function () {
            let registro_anterior = registro_id;
            registro_id = $(this).data('registro_id');
            status_row = $(this).data('status_row');
            iconos_menu_lista.each(function (index) {
                let url_anterior = $(this).attr('href');
                let url_nueva = '';
                if (registro_anterior === -1) {
                    url_nueva = url_anterior.replace("{registro_id}", registro_id);
                } else {
                    url_nueva = url_anterior.replace("registro_id=" + registro_anterior, "registro_id=" + registro_id);
                }
                $(this).attr('href', url_nueva);
            });
            let activa_ct = $('.activa_bd');
            let desactiva_ct = $('.desactiva_bd');
            activa_ct.hide();
            desactiva_ct.hide();

            if(status_row ==='activo'){
                desactiva_ct.show();
            }
            if(status_row ==='inactivo'){
                activa_ct.show();
            }

        });
        iconos_menu_lista.click(function () {
            if (registro_id === -1) {
                alert('Debes seleccionar un registro');
                return false;
            }
        });
        if ($(".btn_modal").length) {
            $('.btn_modal').click(function () {
                let name_accion = $(this).data("name_accion");
                $('#accionLabel').html(name_accion);
                let url = 'index.php?seccion=' + seccion + '&accion=' + name_accion + '&view=1&session_id=' + session_id + '&registro_id=' + registro_id;
                $('#accion_modal').load(url);
            });
        }
    }
})
