<?php
/*
* Busca los datos o detalles de una orden de alquiler
* File: wcai_order_alquiler_data.php
*/
/// FOR  DEBUGGING PURPOSE
/*  echo 'LLEGA a wcai_order_alquiler_data.php UNA PETICION CON <b>' .json_encode($_POST).'</b><br/><br/>';
   var_dump($_POST);
   die();
/**/
$site_url = '';
$site_dir = '';

if(isset($_POST['site_url'])) $site_url = $_POST['site_url'];
if(isset($_POST['site_dir'])) $site_dir = $_POST['site_dir'];  

/// PARA USAR LAS FUNCIONES Y VENTAJAS DEL WORDPRESS
 define('WP_USE_THEMES', false);

 /**
 * Loads the WordPress environment and template.
 *
 * @package WordPress
 */
//echo '<br/>site_dir ===>'.$site_dir.'<br/>';
echo ' ';
 if ( !isset($wp_did_header) ) {

  	$wp_did_header = true;

	// Load the WordPress library.
  	require_once( $site_dir . '/wp-load.php' );
 
	// Set up the WordPress query
   	wp();
   	

	// Load the theme template.
   	require_once( ABSPATH . WPINC . '/template-loader.php' );
 
  }

if(!defined('ABSPATH')) exit;

global $wpdb;

$nro_orden = '';
$usuario_actual = '';
$wcai_cookie_cancelar_alquiler = '';

if(isset($_POST['nro_orden'])) $nro_orden = $_POST['nro_orden'];
if(isset($_POST['usuario_actual'])) $usuario_actual = $_POST['usuario_actual'];
if(isset($_POST['wcai_cookie_cancelar_alquiler'])) $wcai_cookie_cancelar_alquiler = $_POST['wcai_cookie_cancelar_alquiler'];

// Controla si abre en otra ventana o en la misma
  $wcai_target = '_self';
  $wcai_open_new_window  = get_option('wcai_open_new_window') ;
  if ($wcai_open_new_window =='yes' ) $wcai_target = '_blank';

//Busca los pagos de esa orden y con ese usuario
    $table_name = $wpdb->prefix.'wcai_pagos_mensuales';   
	$consulta = "SELECT * FROM $table_name WHERE pago_user_id = '$usuario_actual' AND pago_order_id = '$nro_orden' ORDER BY pago_id ASC";  
	
	$resultado_alquiler_data = $wpdb->get_results( $consulta );
	
	// Imprime la imagen, el total pagado y la deuda pendiente, además de la oferta de liquidación.
	$nro_pago=1;
	$cuotas_pagadas=0;
	$cuotas_pendientes=0;
	$cantidad_pagada=0;
	$cantidad_por_pagar=0;
	
	foreach($resultado_alquiler_data as $alquiler_data) {
	    if ( $alquiler_data->pago_status =='wc_completed') {
	        $cuotas_pagadas  = $cuotas_pagadas + 1;
	        $cantidad_pagada = $cantidad_pagada + $alquiler_data->pago_amount;
	    } else {
	        $cuotas_pendientes  = $cuotas_pendientes + 1;
	        $cantidad_por_pagar = $cantidad_por_pagar + $alquiler_data->pago_amount;
	    }
	    
	   $nro_pago= $nro_pago + 1; 
	}

$nro_pago= $nro_pago - 1;
$id_producto = $alquiler_data->pago_product_id;
$el_producto = wc_get_product(trim($id_producto));
$nombre_del_producto = $el_producto->get_name();
$imagen_del_producto = $el_producto->get_image();
$precio_del_producto = $el_producto->get_price();
$simbolo_monetario = get_woocommerce_currency_symbol();

$fecha_1er_pago = new DateTime(str_ireplace('/','-',$resultado_alquiler_data[0]->pago_fecha));
$fecha_actual = new DateTime(date('Y-m-d'));
$diferencia_fecha = $fecha_1er_pago->diff($fecha_actual);
$meses = ( $diferencia_fecha->y * 12 ) + $diferencia_fecha->m + 1; //Le suma el primer mes tambien
$nro_cuota_actual = $meses; 

if ($nro_cuota_actual <=12) $porcentaje_descuento = esc_attr(get_option('wcai_descuento_02')); 
if ($nro_cuota_actual <=6)  $porcentaje_descuento = esc_attr(get_option('wcai_descuento_01'));
if ($nro_cuota_actual >12)  $porcentaje_descuento = 0; 
     
$pago_liquidacion = ($precio_del_producto*(100 - $porcentaje_descuento)/100 - $cantidad_pagada);

// Genera la tabla con los datos de la orden de alquiler
$html = '  
<table class="alquiler_table_details my_account_orders" border="0" style="color: '.esc_attr(get_option('wcai_font_color_alquiler')).';" >
<!--<tr>
<td style="width: 30%; ">
<div id="wcai_imagen_producto" >
  '. $nombre_del_producto.'<br/>'. $imagen_del_producto.'<br/>
</div>
<div id="wcai_datos_del_pago" style="display: inherit;text-align: center; width: 25%;">
 Codigo del Producto = '.$id_producto.'<br/>
 Cuotas Pagadas = '.$cuotas_pagadas.'<br/>
 Cuotas Pendientes = '.	$cuotas_pendientes.'<br/>
 Cantidad Pagada = '.	$cantidad_pagada.'<br/>
 Cantidad Por Pagar = '.	$cantidad_por_pagar.'<br/>
 TOTAL DE CUOTAS = '.$nro_pago.'<br/> 
</div>
 </td>
 <td>
<!---*********************************************************--->';
$datosTabla = array(
        array( "Cuotas Pagadas", $cuotas_pagadas, "#BDDA4C"),
        array( "Cuotas Pendientes", $cuotas_pendientes, "#FF9A68")
        );
$maximo = 0;
foreach ( $datosTabla as $ElemArray ) { $maximo += $ElemArray[1]; }

$html .= '
<!--<table cellspacing="0" cellpadding="2">';

foreach( $datosTabla as $ElemArray ) {
$porcentaje = round((( $ElemArray[1] / $maximo ) * 100),2);

$html .= '
<tr >
    <td width="20%"><strong>'. $ElemArray[0] .'</strong></td>
    <td width="10%">'. $porcentaje.' %</td>
    <td>';
if ($porcentaje >0) {
    $html .= '
        <table style="width:'.$porcentaje.'%;" bgcolor="'.$ElemArray[2].'">
           <tr style="height: 30px;">
              <td style="width:'.$porcentaje.'%;" bgcolor="'.$ElemArray[2].'">
              </td>
           </tr>
        </table>';
}

$html .= '
    </td>
    </tr>';
    
    } 

$html .= '<tr><td colspan="3"><button  style="float:right; " onclick="alert(\'Esta funcionalidad aun no esta disponible\');" >LIQUIDAR</button></td></tr>
</table></td></tr>-->';

//<!---************** VISTA DEL MODELO 2     ****---> 

$html .= '<!--<tr><td><br/><br/>  <font color="blue" >DEBAJO ESTA LA VISTA DEL MODELO 2 </font></td><td><font color="blue" >DEBAJO ESTA LA VISTA DEL MODELO 2</font></td></tr>-->
<tr>
<td style="width: 30%; ">
<div id="wcai_imagen_producto" >
  '. $nombre_del_producto.'<br/>'. $imagen_del_producto.'<br/>
</div>
<div id="wcai_datos_del_pago" >
 <br/>
 Codigo del Producto = '.$id_producto.'<br/>
 Cuotas Pagadas = '.$cuotas_pagadas.'<br/>
 Cuotas Pendientes = '.	$cuotas_pendientes.'<br/>
 Cantidad Pagada = '.	$cantidad_pagada.'<br/>
 Cantidad Por Pagar = '.	$cantidad_por_pagar.'<br/>
 TOTAL DE CUOTAS = '.$nro_pago.'<br/> <!--FECHA 1ER PAGO = '.date_format($fecha_1er_pago,"Y/m/d").'<BR> FECHA ACTUAL = '.date_format($fecha_actual,"Y/m/d").'<BR> MESES = '.$meses.'<BR> -->
</div>
 </td>
 <td>
<!---*********************************************************--->';

$maximo = $cuotas_pagadas + $cuotas_pendientes;

$html .= '
<table cellspacing="0" cellpadding="2">';

$porcentaje_pagado = round((( $cuotas_pagadas / $maximo ) * 100),2);
$porcentaje_pendiente = round((( $cuotas_pendientes / $maximo ) * 100),2);
$color_pagado = '#BDDA4C';
$color_pendiente = '#FF9A68';

$html .= '
    <table class="sin_borde" >
       <tr style="height: 30px; ">';
if (($cuotas_pendientes >0) && ($cantidad_por_pagar >0)) {
         $html .= '  
         <td colspan="2" class="sin_borde centrado" >Si Liquidas ahora '.$nombre_del_producto.' te sale por: '.$pago_liquidacion.' '.$simbolo_monetario.'</td>
       </tr>
       <tr>
         <td colspan="2" class="sin_borde centrado " > ( '.$precio_del_producto.' - '.$porcentaje_descuento.' % ) - '.$cantidad_pagada.' '.$simbolo_monetario.' = '.$pago_liquidacion.' '.$simbolo_monetario.'</td>
       </tr>
       <tr>';
 }

/// Muestra la escala de pagos    
if ($porcentaje_pagado >0) {
    if (($cuotas_pendientes >0) && ($cantidad_por_pagar >0)) {
       $html .= '
           <td class="bordes_laterales " style="padding-left: 0px; " >0 '.$simbolo_monetario.'</td>
           <td class="borde_derecho" style="padding-left: 0px; padding-right: 0px; "><span class="izquierda" >'.$cantidad_pagada.' '.$simbolo_monetario.'</span><span class="derecha" >'.$precio_del_producto.' '.$simbolo_monetario.'</span> </td>';
     } else {
       $html .= '
         <td colspan="2" class="bordes_laterales" style="padding-left: 0px; padding-right: 0px; width: 95%;"><span class="izquierda" >0 '.$simbolo_monetario.'</span><span class="derecha" >'.$precio_del_producto.' '.$simbolo_monetario.'</span> </td>';
     }
} else {
    $html .= '
         <td colspan="2" class="bordes_laterales" style="padding-left: 0px; padding-right: 0px; width: 95%;"><span class="izquierda" >0 '.$simbolo_monetario.'</span><span class="derecha" >'.$precio_del_producto.' '.$simbolo_monetario.'</span> </td>';

}
$html .= '         
       </tr>
       <tr style="height: 20px;">';
       
/// Muestra la BARRA de pagos
if ($porcentaje_pagado >0) {
    if (($cuotas_pendientes >0) && ($cantidad_por_pagar >0)) {
       $html .= '
          <td class="sin_borde" style="width:'.$porcentaje_pagado.'%;" bgcolor="'.$color_pagado.'"></td>
          <td class="sin_borde" style="width:'.$porcentaje_pendiente.'%;" bgcolor="'.$color_pendiente.'"></td>';
     } else {
            $html .= '
             <td colspan="2" class="bordes_laterales" style="width:'.$porcentaje_pagado.'%;" bgcolor="'.$color_pagado.'"></td>';
     }
} else {
     $html .= '<td class="sin_borde" style="width:'.$porcentaje_pendiente.'%;" bgcolor="'.$color_pendiente.'"></td>'; 
}

/// Muestra EL RESTO
$html .= '
        
       </tr>
       <tr class="sin_borde" style="height: 10px;" >
       <td class="sin_borde" colspan="2"><br/>
          Cuotas Pagadas ==> <span style="background-color:'.$color_pagado.';">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cuotas Pendientes ==> <span style="background-color:'.$color_pendiente.';">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
          
          <form id="liquidar_alquiler_cart_form" class="woocommerce-cart-form" name="liquidar_alquiler_cart_form" action="'. SITE_URL . '/carrito-liquidar-alquiler/" method="post" accept-charset="utf-8"  target="'. $wcai_target.'">
            <input type="hidden" name="_wcai_nonce" class="wcai_nonce" value="'.wp_create_nonce( "carrito-liquidar-alquiler" ).'" />
	        <input type="hidden" name="site_url" value="'. site_url().'" />
            <input type="hidden" name="site_dir" value="'. SITE_HOME_DIR.'" />
	        <input type="hidden" name="id_product" id="id_product" value="'. $id_producto.'" />
            <input type="hidden" name="nro_orden" id="nro_orden" value="'.$nro_orden.'" />
			<input type="hidden" name="simbolo_monetario" id="simbolo_monetario" value="'.$simbolo_monetario.'" />
			<input type="hidden" name="cuota_alquiler" id="cuota_alquiler" value="'.$alquiler_data->pago_amount.'" />
            <input type="hidden" name="usuario_actual" id="usuario_actual" value="'.$usuario_actual.'" />
			 <input type="hidden" name="pendiente_pagar" id="pendiente_pagar" value="'.$cantidad_por_pagar.'" />
            <input type="hidden" name="pago_liquidacion" id="pago_liquidacion" value="'.$pago_liquidacion.'" /> 
            <input type="hidden" name="nombre_liquidacion" id="nombre_liquidacion" value="Liquidacion de alquiler del '.$nombre_del_producto.' del Pedido Nro.'.$nro_orden.'" />'; 
            
            if (($cuotas_pendientes >0) && ($cantidad_por_pagar >0) ) {
          		if ($wcai_cookie_cancelar_alquiler !='enviado')
                	$html .= '<button type="button" class="wcai-btn-outline-secondary derecha"  id="wcai_button_57cancelar_alquiler" onclick="wcai_cancelar_alquiler(\''.$cantidad_por_pagar.$simbolo_monetario.'\',\''.$nro_orden.'\');" >Cancelar Alquiler</button>';
                $html .= '<button type="submit" class="btn btn-primary derecha" onclick="" >LIQUIDAR</button>';
            }
            
            $html .= '
          </form>
          
       </td>
    </tr>
    </table>
    
</table>



<!--- ************************************************-->
</td></tr>
<tr>
<td colspan = 2>

<table class="alquiler_table_details my_account_orders" style="width: 95%; color: '.esc_attr(get_option('wcai_font_color_alquiler')).';" >
	<thead>
		<tr>
			<th class="order-number"><span class="nobr">&#8470;</span></th>
			<th class="order-date"><span class="nobr">Vencimiento</span></th>
			<th class="order-total"><span class="nobr">Pagada en:</span></th>
			<th class="order-total"><span class="nobr">Precio ('.$simbolo_monetario.')</span></th>
			<th class="order-descripcion"><span class="nobr">Detalle</span></th>
			<th class="order-status"><span class="nobr">Estado</span></th>
		<!-- <th class="order-pagos"><span class="nobr">Recibo #</span></th> -->
		</tr>
	</thead>
	<tbody>';
	
	$nro_pago=1;
	foreach($resultado_alquiler_data AS $alquiler_data) {
	    $alquiler_pago_status = '';
	    if ( $alquiler_data->pago_status =='wc_pending') $alquiler_pago_status = 'Pendiente';
	    if ( $alquiler_data->pago_status =='wc_completed') $alquiler_pago_status = 'Pagado';
	    if ( $alquiler_data->pago_status =='wc_failed') $alquiler_pago_status = 'Fallido';
	    
	    $html .= '
	    <tr class="order">
		<td class="order-number">'.trim(strval($nro_pago)).'
		</td>
		<td class="order-date">'.$alquiler_data->pago_fecha.'
		</td>
		<td class="order-date">'.$alquiler_data->pago_fecha_completo.'
		</td>
		<td class="order-total">'.$alquiler_data->pago_amount.'
		</td>
		<td class="order-name">'.substr($alquiler_data->pago_descripcion,0,strpos($alquiler_data->pago_descripcion, "- Comienza:")).'
		</td>
		<td class="order-status" style="text-align:left; white-space:nowrap;">'.$alquiler_pago_status.'
		</td>
	<!-- <td class="order-recibo">
			
		</td> -->
	</tr>';  
	 $nro_pago= $nro_pago + 1;   
	}

 $html .= '</td></tr></table></table>';
 $mensaje = 'SE EJECUTO EL AJAX EN wcai_order_alquiler_data.php';
 $estatus = 'exito';

// Crea el objeto con la respuesta
 $respuesta = ['estatus' =>$estatus,
               'nro_orden' => $nro_orden,
               'mensaje' => $mensaje,
               'html' => $html, 
               ];

// Codifica el objeto para trasmisi??n
$respuesta = json_encode($respuesta, JSON_FORCE_OBJECT);

echo $respuesta; 

return true;


?>
