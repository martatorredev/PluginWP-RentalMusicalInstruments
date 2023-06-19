<?php
/*
* Plantilla Vista de los instrumentos alquilados
* File: html-wcai-alquileres.php 
*/

if(!defined('ABSPATH')) exit;

global $wpdb;

$usuario_actual = get_current_user_id();

// Busca Ordenes del usuario
$user_orders=get_posts(apply_filters( 'woocommerce_my_account_my_orders_query', array(
	'numberposts' => -1,
	'meta_key'    => '_customer_user',
	'meta_value'  => $usuario_actual,
	'post_type'   => wc_get_order_types( 'view-orders' ),
	'post_status' => array_keys(wc_get_order_statuses()),
)));

// Busca Ordenes del usuario que son de alquiler
$meta_type = 'post';
$meta_key  = '_alquiler_item';

foreach ( $user_orders as $user_order ) {
    $meta_value ='';
    $meta = get_metadata( $meta_type, $user_order->ID, $meta_key, false );
    if (is_array ( $meta )) {
        if (empty($meta) )
            $meta_value ='';
         else
           $meta_value = $meta[0];
      } else 
            $meta_value = $meta;
    
   // var_dump($meta );
   // echo '<br/><br/>'.$meta_type.' = '. $user_order->ID .' VALOR= '.  $meta_value .'<br/><br/>';
    if ($meta_value == 'yes') $alquiler_orders[] = $user_order; 
     
}

?>
<?php if(empty($alquiler_orders)) { ?>
<h2 class="secondary"><?php echo 'No Tiene Alquileres'; ?></h2>
<?php } else { ?>

<table class="alquiler_table my_account_orders">
	<thead>
		<tr>
			<th class="order-number"><span class="nobr">&#8470;</span></th>
			<th class="order-date"><span class="nobr"><?php echo 'Fecha'; ?></span></th>
			<th class="order-descripcion"><span class="nobr"><?php echo 'Descripción'; ?></span></th>
			<th class="order-status"><span class="nobr"><?php echo 'Estado'; ?></span></th>
			<th class="order-total"><span class="nobr"><?php echo 'Total'; ?></span></th>
			<th class="order-pagos"><span class="nobr"><?php echo 'Pagos'; ?></span></th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $alquiler_orders as $alquiler_order ) {
     $order = wc_get_order($alquiler_order);
	//var_dump($order);
	$item_count = $order->get_item_count();
	$order_date = $order->get_date_created($context = 'view'  ); 
	$nro_orden = $order->get_order_number(); 
	
	/// PARA EXTRAER EL NOMBRE DEL PRODUCTO ALQUILADO
	$line_items          = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
	foreach ( $line_items as $item_id => $item ) {
			$order_name = 	$item->get_name();
	        ///echo '<br/><br/><br/>NOMBRE DEL ITEM ===>'.$order_name.' <br/><br/><br/>';
     }
	
	?>
	<tr class="order">
		<td class="order-number">
			<a href="<?php echo $order->get_view_order_url(); ?>">
				<?php echo $nro_orden;?>
			</a>
		</td>
		<td class="order-date">
			<time datetime="<?php echo date('Y-m-d', strtotime($order_date)); ?>" title="<?php echo esc_attr(strtotime($order_date)); ?>"><?php echo date_i18n(get_option( 'date_format' ), strtotime($order_date)); ?></time>
		</td>
		<td class="order-name">
			<?php echo $order_name; ?>
		</td>
		<td class="order-status" style="text-align:left; white-space:nowrap;">
			<?php echo wc_get_order_status_name($order->get_status()); ?>
		</td>
		<td class="order-total">
			<?php echo $order->get_formatted_order_total(); ?>
		</td>
		<td class="order-pagos">
			<?php echo '<button  class="btn btn-primary" target="_blank" onclick="ver_detalles_alquiler('.$nro_orden.','.$usuario_actual.');"> Ver</button>'; ?>
		</td>
	</tr>
	<?php } ?>
	</tbody>
</table>

<!-- Modal PARA VER los detalles del alquiler por AJAX -->

<div class="" id="Modal_alquiler_data" tabindex="-1" role="dialog" aria-labelledby="Modal_alquiler_data" aria-hidden="true">
  <div class="wcai_modal-dialog" role="document">
    <div class="wcai_modal-content" id="contenido_modal_alquiler_data" style="background-color: <?php echo esc_attr(get_option('wcai_bgcolor_alquiler')) ;?> !important; ">
      <div class="wcai_modal-header">
          <button type="button" class="close btn btn-primary" data-dismiss="" aria-label="Cerrar" onclick="document.getElementById('Modal_alquiler_data').style.display='none';document.getElementById('Modal_cancelar_alquiler').style.display='none';">
          <span aria-hidden="false">&times;</span>
        </button>
        <p align="center" class="" id="ModalLabel_alquiler_data">Detalles del Alquiler Nro.<?php echo $nro_orden;?></p>
      </div>
      <div class="modal-body">

<p align="center" class="" id="ModalLabel1"><b>Plan de pago del Alquiler:</b></p> 
<p align="center" class="" id="ModalLabel2"><b><span id="resultado_ajax"></span></b></p>

<div align="center" class="" id="ModalLabel3"><b>Oprima el Boton Para Cerrar esta Ventana y Continuar</b><br>
<hr>       
<center><button type="submit" class="btn btn-primary" onclick="document.getElementById('Modal_alquiler_data').style.display='none';document.getElementById('Modal_cancelar_alquiler').style.display='none';">Cerrar y Continuar</button></center>
</div>
      </div>       
      
    </div>
  </div>
  <!-- Modal PARA CANCELAR (suspender, eliminar o detener) EL ALQUILER de un producto por AJAX -->

<div class="modal fade" id="Modal_cancelar_alquiler" tabindex="-1" role="dialog" aria-labelledby="Modal_cancelar_alquiler" aria-hidden="true">
  <div class="wcai_modal-dialog" role="document">
    <div class="wcai_modal-content" id="contenido_modal_cancelar_alquiler" style="">
      <div class="wcai_modal-header">
          <button type="button" class="close btn btn-primary" data-dismiss="" aria-label="Cerrar" onclick="wcai_restar_modal_cancelar_alquiler();">
          <span aria-hidden="false">&times;</span>
        </button>
        <p align="center" class="wcai_p" id="ModalLabel_cancelar_alquiler"><b>Cancelar Alquiler Nro.<?php echo $nro_orden;?></b> </p>
      </div>
    
    <div class="modal-body">
        <p align="center" class="wcai_p" id="ModalLabel_cancelar_alquiler1"><span id="detalles_cancelar_alquiler"></span></p> 
        <div align="center" class="wcai_p" id="ModalLabel_cancelar_alquiler2">
            <center>
                <button type="button" class="btn btn-primary" onclick="wcai_restar_modal_cancelar_alquiler();" >SEGUIR CON EL ALQUILER</button>
                <span id="wcai_button_send_emails_cancelar"></span>
            </center>
        </div>
    </div>       
    </div>
  </div>
</div><!-- FINAL DE Modal PARA CANCELAR (suspender, eliminar o detener) EL ALQUILER de un alquiler por AJAX -->  
</div><!-- FINAL DE Modal PARA VER los detalles del alquiler por AJAX -->



<script>

    var  site_url = "<?php echo SITE_URL;?>"; 
    var  site_dir = "<?php echo SITE_HOME_DIR;?>";

    function wcai_send_emails_cancelar(nro_orden) {
        var  destino = "<?php echo WCAI_PLUGIN_URL.'inc/wcai_send_emails_cancelar.php';?>";
        var  datos_ajax = { "nro_orden" : nro_orden, 
                            "site_url" : site_url,
                            "site_dir" : site_dir 
                            };
        jQuery.ajax({
             url: destino, //archivo php que EJECUTA EL AJAX
             cache:       false,
             type:        "post",
             beforeSend: function () {
                            document.getElementById('detalles_cancelar_alquiler').innerHTML = "";
                            document.getElementById('ModalLabel_cancelar_alquiler2').innerHTML = '<i><b><font face="Comic Sans MS" size="5" color="#0000FF">Enviando Correo sobre Cancelar este Alquiler Nro. ' + nro_orden +', Espere por favor...</font></b></i>';
                          
                           },
             data:        datos_ajax,
             success:     function (response) {
                                 
                                 /// FOR DEBUGGING PURPOSE
                                    hmtl_respuesta = JSON.stringify(response);
                                    //alert ("la respuesta es: " + JSON.stringify(response));
                                   
                                   _cancelar_alquiler_data_response = JSON.parse(response);
                                   estatus = _cancelar_alquiler_data_response.estatus;
                                   nro_orden_respuesta = _cancelar_alquiler_data_response.nro_orden;
                                   mensaje_respuesta = _cancelar_alquiler_data_response.mensaje;
                                   
                                   if (estatus.search("error") > 0) {
                                       document.getElementById('ModalLabel_cancelar_alquiler2').innerHTML = "<p class='wcai_p'><b>Hubo problemas al enviar los correos sobre esta solicitud de CANCELAR el alquiler:</b>";
                                     } else { document.getElementById('ModalLabel_cancelar_alquiler2').innerHTML = "<p class='wcai_p'>Su Solicitud de CANCELAR el alquiler est&aacute; siendo procesada:</b>";
											wcai_setCookie("wcai_cookie_cancelar_alquiler", "enviado", 1);
											 document.getElementById('wcai_button_57cancelar_alquiler').style.display = "none";
											 
   
                                    }
                                    
                                   wcai_guion = mensaje_respuesta.search("-");
                                   mensaje_respuesta_cliente = mensaje_respuesta.substr(0,wcai_guion);
                                   mensaje_respuesta_admin   = mensaje_respuesta.substr(wcai_guion+1);
                                   
                                    document.getElementById('ModalLabel_cancelar_alquiler2').innerHTML +=mensaje_respuesta_admin + "<br/>" +mensaje_respuesta_cliente + "</p>" + '<hr class="wcai_p" ><div align="center" class="wcai_p" id="ModalLabel_cancelar_alquiler3"><b>Oprima el Boton Para Cerrar esta Ventana y Continuar</b><br>';
                                    document.getElementById('ModalLabel_cancelar_alquiler2').innerHTML +='<center><button type="button" class="btn btn-primary" onclick="wcai_restar_modal_cancelar_alquiler();">Cerrar y Continuar</button></center></div>';
                                   
  
                            }
           
           
           
             });
    
     }
     
     
    function wcai_cancelar_alquiler(cantidad_por_pagar, nro_orden ) { 
		var simbolo_monetario = document.getElementById("simbolo_monetario").value;
        document.getElementById("Modal_cancelar_alquiler").style.display="block";
		document.getElementById("detalles_cancelar_alquiler").innerHTML = "Importe Cuota Alquiler: <b>" + document.getElementById("cuota_alquiler").value + simbolo_monetario + "</b><br/>Importe Pendiente de Pagar: <b>" + document.getElementById("pendiente_pagar").value + simbolo_monetario + "</b><br/>Importe Liquidación: <b>" +document.getElementById("pago_liquidacion").value + simbolo_monetario + "</b><br/><br/>";
        document.getElementById("detalles_cancelar_alquiler").innerHTML += "Te quedan " + cantidad_por_pagar + " de alquiler. \u00BFEst\u00E1s seguro de cancelar el alquiler? <br/><b>Por solo " + cantidad_por_pagar + " el instrumento ser\u00E1 tuyo.</b>"; 
        document.getElementById("ModalLabel_cancelar_alquiler").innerHTML = "<b>Cancelar Alquiler Nro." + nro_orden +"</b>"; 
        document.getElementById("wcai_button_send_emails_cancelar").innerHTML = '<button type="button" class="wcai-btn-outline-secondary" onclick="wcai_send_emails_cancelar(' + nro_orden + ');" >Cancelar Alquiler</button>';
     }

    function ver_detalles_alquiler(nro_orden, usuario_actual) {
        var  destino = "<?php echo WCAI_PLUGIN_URL.'inc/wcai_order_alquiler_data.php';?>";
		var wcai_cookie_cancelar_alquiler = wcai_getCookie("wcai_cookie_cancelar_alquiler"); 
  
        var  datos_ajax = { "nro_orden" : nro_orden, 
                            "usuario_actual" : usuario_actual,
                            "site_url" : site_url,
                            "site_dir" : site_dir,
						   	"wcai_cookie_cancelar_alquiler" : wcai_cookie_cancelar_alquiler
                            };
        //alert ("DATOS DEL AJAX = " + JSON.stringify(datos_ajax));
             jQuery.ajax({
             url: destino, //archivo php que EJECUTA EL AJAX
             cache:       false,
             type:        "post",
             beforeSend: function () {
                          // alert('EJECUTADO AJAX con la orden= '+nro_orden+', Espere por favor...');
                          document.getElementById('Modal_alquiler_data').style.display = "block";
                          document.getElementById("ModalLabel_alquiler_data").innerHTML = "Detalles del Alquiler Nro." + nro_orden ;
                          document.getElementById('ModalLabel_alquiler_data').style.display = "none";
                          document.getElementById('ModalLabel1').style.display = "none";
                          document.getElementById('ModalLabel3').style.display = "none";
                          document.getElementById('resultado_ajax').innerHTML = '<i><b><font face="Comic Sans MS" size="4" color="#0000FF">Buscando Datos del Pedido Nro. ' + nro_orden +', Espere por favor...</font></b></i>';
                          
                           },
             data:        datos_ajax,
             success:     function (response) {
                                 
                                 /// FOR DEBUGGING PURPOSE
                                hmtl_respuesta = JSON.stringify(response);
                                //   alert ("la respuesta es: " + JSON.stringify(hmtl_respuesta));
                                   
                                   
                                   _alquiler_data_response = JSON.parse(response);
                                   estatus = _alquiler_data_response.estatus;
                                   nro_orden_respuesta = _alquiler_data_response.nro_orden;
                                   hmtl_respuesta = _alquiler_data_response.html;
                                   document.getElementById('Modal_alquiler_data').style.display = "block";
                                   document.getElementById('ModalLabel_alquiler_data').style.display = "block";
                                   document.getElementById('ModalLabel1').style.display = "block";
                                   document.getElementById('ModalLabel3').style.display = "block";
                                   
                                   /**/
                                   document.getElementById('resultado_ajax').innerHTML = hmtl_respuesta; 
                                  
                                   
  
                            }
           
           
           
             });
           
    }
 
function wcai_setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + "; " + expires;
}
 
function wcai_getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);  
  var ca = decodedCookie.split(';'); 
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
  
}
	
function wcai_checkCookie(cname) {
  var resul = false;
  var username = wcai_getCookie(cname);
  if (username != "") {
    resul = true;
  }  
  return resul;
}
	
function wcai_restar_modal_cancelar_alquiler() {
	document.getElementById("ModalLabel_cancelar_alquiler1").innerHTML ='<span id="detalles_cancelar_alquiler"></span>';
	document.getElementById("ModalLabel_cancelar_alquiler2").innerHTML ='<center><button type="button" class="btn btn-primary" onclick="wcai_restar_modal_cancelar_alquiler();" >SEGUIR CON EL ALQUILER</button><span id="wcai_button_send_emails_cancelar"></span></center>';
	document.getElementById('Modal_cancelar_alquiler').style.display='none';
	
}
	
</script>

<?php } 



