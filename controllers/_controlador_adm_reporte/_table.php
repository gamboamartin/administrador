<?php
namespace gamboamartin\controllers\_controlador_adm_reporte;

use gamboamartin\errores\errores;
use stdClass;

class _table
{
    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    final public function genera_ths_html(string $adm_reporte_descripcion): array|string
    {
        $ths = $this->ths_array(adm_reporte_descripcion: $adm_reporte_descripcion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener ths',data:  $ths);
        }

        $ths_html = $this->ths_html(ths: $ths);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener ths_html',data:  $ths_html);
        }
        return $ths_html;
    }

    final public function genera_trs_html(string $adm_reporte_descripcion,  stdClass $result): array|string
    {
        $ths = $this->ths_array(adm_reporte_descripcion: $adm_reporte_descripcion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener ths',data:  $ths);
        }

        $trs_html = '';
        foreach ($result->registros as $registro){
            $trs_html = $this->integra_trs_html(bold: false, registro: $registro, ths: $ths, trs_html: $trs_html);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener trs_html',data:  $trs_html);
            }
        }

        $registro_totales = $this->registro_totales(result: $result,ths:  $ths);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro_totales',data:  $registro_totales);
        }
        $trs_html = $this->integra_trs_html(bold: true, registro: $registro_totales, ths: $ths, trs_html: $trs_html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener trs_html',data:  $trs_html);
        }

        return $trs_html;
    }

    private function integra_td(bool $bold, array $data_ths, array $registro, string $tds_html): array|string
    {
        $key_registro = $data_ths['campo'];
        $td = $this->td(bold: $bold, key_registro: $key_registro,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener td',data:  $td);
        }

        $tds_html.="$td";
        return $tds_html;
    }

    private function integra_trs_html(bool $bold, array $registro, array $ths, string $trs_html): array|string
    {
        $tds_html = $this->tds_html(bold: $bold, registro: $registro, ths: $ths);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener tds_html',data:  $tds_html);
        }
        $trs_html.="<tr>$tds_html</tr>";
        return $trs_html;
    }

    private function registro_totales(stdClass $result, array $ths): array
    {
        $registro_totales = (array)$result->totales;

        foreach ($ths as $th){
            $columna = $th['campo'];
            if(!isset($registro_totales[$columna])){
                $registro_totales[$columna] = '';
            }
        }
        return $registro_totales;

    }

    private function td(bool $bold,string $key_registro, array $registro): string|array
    {
        $key_registro = trim($key_registro);
        if($key_registro === ''){
            return $this->error->error(mensaje: 'Error key_registro esta vacio',data:  $key_registro);
        }
        if(!isset($registro[$key_registro])){
            return $this->error->error(mensaje: '$registro['.$key_registro.'] no existe',data:  $registro);
        }
        $contenido = $registro[$key_registro];
        if($bold){
            $contenido = "<b>$contenido</b>";
        }
        return "<td>$contenido</td>";
    }

    private function tds_html(bool $bold,array $registro, array $ths): array|string
    {
        $tds_html = '';
        foreach ($ths as $data_ths){
            $tds_html = $this->integra_td(bold: $bold, data_ths: $data_ths, registro: $registro, tds_html: $tds_html);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener td',data:  $tds_html);
            }
        }
        return $tds_html;
    }


    private function th(string $etiqueta): string
    {
        return "<th>$etiqueta</th>";
    }

    private function ths_array(string $adm_reporte_descripcion): array
    {
        $ths = array();

        if($adm_reporte_descripcion === 'Facturas'){
            $ths[] = array('etiqueta'=>'Folio', 'campo'=>'fc_factura_folio');
            $ths[] = array('etiqueta'=>'UUID', 'campo'=>'fc_factura_uuid');
            $ths[] = array('etiqueta'=>'Cliente', 'campo'=>'com_cliente_razon_social');
            $ths[] = array('etiqueta'=>'Sub Total', 'campo'=>'fc_factura_sub_total_base');
            $ths[] = array('etiqueta'=>'Descuento', 'campo'=>'fc_factura_total_descuento');
            $ths[] = array('etiqueta'=>'Traslados', 'campo'=>'fc_factura_total_traslados');
            $ths[] = array('etiqueta'=>'Retenciones', 'campo'=>'fc_factura_total_retenciones');
            $ths[] = array('etiqueta'=>'Total', 'campo'=>'fc_factura_total');
            $ths[] = array('etiqueta'=>'Fecha', 'campo'=>'fc_factura_fecha');
            $ths[] = array('etiqueta'=>'Forma de Pago', 'campo'=>'cat_sat_forma_pago_descripcion');
            $ths[] = array('etiqueta'=>'Metodo de Pago', 'campo'=>'cat_sat_metodo_pago_descripcion');
            $ths[] = array('etiqueta'=>'Moneda', 'campo'=>'cat_sat_moneda_codigo');
            $ths[] = array('etiqueta'=>'Tipo Cambio', 'campo'=>'com_tipo_cambio_monto');
            $ths[] = array('etiqueta'=>'Uso CFDI', 'campo'=>'cat_sat_uso_cfdi_descripcion');
            $ths[] = array('etiqueta'=>'Exportacion', 'campo'=>'fc_factura_exportacion');
        }
        if($adm_reporte_descripcion === 'Pagos'){
            $ths[] = array('etiqueta'=>'Folio', 'campo'=>'fc_complemento_pago_folio');
            $ths[] = array('etiqueta'=>'UUID', 'campo'=>'fc_complemento_pago_uuid');
            $ths[] = array('etiqueta'=>'Cliente', 'campo'=>'com_cliente_razon_social');
            $ths[] = array('etiqueta'=>'Sub Total', 'campo'=>'fc_complemento_pago_sub_total_base');
            $ths[] = array('etiqueta'=>'Descuento', 'campo'=>'fc_complemento_pago_total_descuento');
            $ths[] = array('etiqueta'=>'Traslados', 'campo'=>'fc_complemento_pago_total_traslados');
            $ths[] = array('etiqueta'=>'Retenciones', 'campo'=>'fc_complemento_pago_total_retenciones');
            $ths[] = array('etiqueta'=>'Total', 'campo'=>'fc_complemento_pago_total');
            $ths[] = array('etiqueta'=>'Fecha', 'campo'=>'fc_complemento_pago_fecha');
            $ths[] = array('etiqueta'=>'Forma de Pago', 'campo'=>'cat_sat_forma_pago_descripcion');
            $ths[] = array('etiqueta'=>'Metodo de Pago', 'campo'=>'cat_sat_metodo_pago_descripcion');
            $ths[] = array('etiqueta'=>'Moneda', 'campo'=>'cat_sat_moneda_codigo');
            $ths[] = array('etiqueta'=>'Tipo Cambio', 'campo'=>'com_tipo_cambio_monto');
            $ths[] = array('etiqueta'=>'Uso CFDI', 'campo'=>'cat_sat_uso_cfdi_descripcion');
            $ths[] = array('etiqueta'=>'Exportacion', 'campo'=>'fc_complemento_pago_exportacion');
        }
        if($adm_reporte_descripcion === 'Egresos'){
            $ths[] = array('etiqueta'=>'Folio', 'campo'=>'fc_nota_credito_folio');
            $ths[] = array('etiqueta'=>'UUID', 'campo'=>'fc_nota_credito_uuid');
            $ths[] = array('etiqueta'=>'Cliente', 'campo'=>'com_cliente_razon_social');
            $ths[] = array('etiqueta'=>'Sub Total', 'campo'=>'fc_nota_credito_sub_total_base');
            $ths[] = array('etiqueta'=>'Descuento', 'campo'=>'fc_nota_credito_total_descuento');
            $ths[] = array('etiqueta'=>'Traslados', 'campo'=>'fc_nota_credito_total_traslados');
            $ths[] = array('etiqueta'=>'Retenciones', 'campo'=>'fc_nota_credito_total_retenciones');
            $ths[] = array('etiqueta'=>'Total', 'campo'=>'fc_nota_credito_total');
            $ths[] = array('etiqueta'=>'Fecha', 'campo'=>'fc_nota_credito_fecha');
            $ths[] = array('etiqueta'=>'Forma de Pago', 'campo'=>'cat_sat_forma_pago_descripcion');
            $ths[] = array('etiqueta'=>'Metodo de Pago', 'campo'=>'cat_sat_metodo_pago_descripcion');
            $ths[] = array('etiqueta'=>'Moneda', 'campo'=>'cat_sat_moneda_codigo');
            $ths[] = array('etiqueta'=>'Tipo Cambio', 'campo'=>'com_tipo_cambio_monto');
            $ths[] = array('etiqueta'=>'Uso CFDI', 'campo'=>'cat_sat_uso_cfdi_descripcion');
            $ths[] = array('etiqueta'=>'Exportacion', 'campo'=>'fc_nota_credito_exportacion');
        }
        return $ths;
    }
    private function ths_html(array $ths): array|string
    {
        $ths_html = '';
        foreach ($ths as $th_data){
            $etiqueta = $th_data['etiqueta'];

            $th = $this->th(etiqueta: $etiqueta);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener th',data:  $th);
            }
            $ths_html.=$th;
        }
        return $ths_html;
    }





}