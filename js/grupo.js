$( document ).ready(function() {

    if(seccion === 'grupo' && (accion === 'asigna_accion')) {
        let btn_agrega_accion_bd = $(".agrega_accion_bd");
        let btn_elimina_accion_bd = $(".elimina_accion_bd");
        let url = './index.php?seccion=accion_grupo&accion=alta_bd&session_id='+session_id+'&ws=1';
        let url_elimina = './index.php?seccion=accion_grupo&accion=elimina_permiso_bd&session_id='+session_id+'&ws=1';
        btn_agrega_accion_bd.click(function(){
            let accion_id = $( this ).data( 'accion_id' );
            let grupo_id = $( this ).data( 'grupo_id' );

            $.post(url, {accion_id: accion_id, grupo_id:grupo_id,status:'activo'},
                function(data, status){
                    alert(data.registro_id);
                });
        });

        btn_elimina_accion_bd.click(function(){
            let accion_id = $( this ).data( 'accion_id' );
            let grupo_id = $( this ).data( 'grupo_id' );

            $.post(url_elimina, {accion_id: accion_id, grupo_id:grupo_id,status:'activo'},
                function(data, status){
                    alert(data);
                });
        });
    }

});