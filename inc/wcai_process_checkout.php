<?php
/**
* Procesa los datos del pago desde el checkout o pagina de pago 
* (aqui se hace el pago y se crea la orden o el pedido) 
* File: wcai_process_checkout.php
*/

/// FOR  DEBUGGING PURPOSE
/* echo '<pre>LLEGA a wcai_process_checkout.php UNA PETICION CON <b>' .json_encode($_POST).'</b></b></b></pre>';
   var_dump($_POST);
   die();
/**/

defined( 'ABSPATH' ) || exit;

global $wpdb, $site_url, $site_dir, $woocommerce, $meses_alquiler, $wcai_date_from, $wcai_date_to ;
$site_url = '';
$site_dir = '';
$id_product = '' ;
$wcai_date_from = '' ;
$wcai_date_to = '' ;
$cuenta_nueva = 'no';

if(isset($_POST['site_url'])) $site_url =  sanitize_text_field($_POST['site_url']);
if(isset($_POST['site_dir'])) $site_dir =  sanitize_text_field($_POST['site_dir']);
if(isset($_POST['id_product']))   $id_product = sanitize_text_field($_POST['id_product']); 
if(isset($_POST['wcai_date_from']))   $wcai_date_from = sanitize_text_field($_POST['wcai_date_from']);
if(isset($_POST['wcai_date_to']))   $wcai_date_to = sanitize_text_field($_POST['wcai_date_to']); 

// Si no tiene id_product, muestra el short-code y regresa
if ($id_product =='') {
    echo  '<!-- wp:shortcode -->[wcai_gracias_por_alquilar]<!-- /wp:shortcode -->';
    return;
}

$total_tax =  sanitize_text_field( $_POST['total_tax']); 
$sub_total_alquiler =  sanitize_text_field( $_POST['subtotal']); 
$total_alquiler =  sanitize_text_field($_POST['total']);
$nombre_producto_alquiler = sanitize_text_field($_POST['nombre_producto_alquiler']);

$form_shipping_method =  sanitize_text_field($_POST['shipping_method'][0]);
//echo '<br><br>form_shipping_method: '.$form_shipping_method.'<br>';
$dos_puntos = stripos($form_shipping_method,':');
$form_shipping_method_2 = substr($form_shipping_method,0,$dos_puntos);
//echo 'NUEVO form_shipping_method: '.$form_shipping_method_2.'<br>';
$instance_id = substr($form_shipping_method,$dos_puntos+1);
//echo 'NUEVO instance_id: '.$instance_id.'<br><br>';

//Ajusta los datos del envio
$alquiler_shipping_method = implode( " | ", wc_get_chosen_shipping_method_ids());
WC()->session->set( 'chosen_shipping_methods', wc_get_chosen_shipping_method_ids() );
$packages = WC()->cart->get_shipping_packages();
$shipping_total = WC()->cart->get_shipping_total();

//echo '<br><br>alquiler_shipping_method: '.$alquiler_shipping_method.'<br><br>';
//echo '<br><br>alquiler_shipping_COUNTRY: '.$_POST['shipping_country'].'<br><br>';
//print_r($packages);//die;

if ($alquiler_shipping_method =='') $alquiler_shipping_method = $form_shipping_method_2;

//echo '<br><br>NUEVO alquiler_shipping_method: '.$alquiler_shipping_method.'<br><br>';
//echo '<br><br>NUEVO alquiler_shipping_COUNTRY: '.$_POST['shipping_country'].'<br><br>';
//print_r($packages);//die;


if ($alquiler_shipping_method =='flat_rate') {
    $_POST['shipping_lines'] = array (array(
            'method_id' => 'flat_rate',
            'method_title' => 'Flat Rate',
            'total' => $shipping_total
        ));
   // echo json_encode($_POST);
    
    if ($packages[0]['destination']['country']) 
        $alquiler_shipping_country = $packages[0]['destination']['country'];
     else
        $alquiler_shipping_country =   sanitize_text_field($_POST['shipping_country']);
    $resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_code = '$alquiler_shipping_country'", OBJECT );
    //echo  '<br>NUEVO resultado : '.var_dump($resultado);//die;
    
    // si no consigue el pais, le pone el global
    if (($wpdb->num_rows =0) || ($resultado = 'Array ( )')) 
        $zone_id = 0; // Le asigna la zona global
     else
        $zone_id = $resultado[0]->zone_id;
    //echo '<br>NUEVO zone_id : '.$zone_id;//die;
    
    $resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE zone_id = '$zone_id' AND method_id = '$alquiler_shipping_method'", OBJECT );
    $instance_id = $resultado[0]->instance_id;
    //echo $instance_id;
    
    $alquiler_shipping_data = array(
						            'method_title' =>  sanitize_text_field($_POST['shipping_lines'][0]['method_title']),
						            'method_id'    =>  sanitize_text_field($_POST['shipping_lines'][0]['method_id']),
						            'instance_id'  => $instance_id,
						            'total'        => wc_format_decimal(  sanitize_text_field($_POST['shipping_lines'][0]['total'])),
						            'cost'         => wc_format_decimal(  sanitize_text_field($_POST['shipping_lines'][0]['total'])),
						            'taxes'        => array('total' => WC()->cart->get_shipping_tax() ),
						            'total_tax'    => WC()->cart->get_shipping_tax(),
						            'Artículos'    =>  sanitize_text_field($_POST['nombre_producto_alquiler']), 
						      );
	//echo '<br><br>DATOS DEL ENVIO<br>' ;					      
	//var_dump($alquiler_shipping_data );
    //die;
}

//Crea el objeto checkout con los datos del POST    
$checkout = new WC_Checkout($_POST); 

// Chequea si es cliente nuevo y crea la cuenta si es necesario
// TOMADO DE LA protected function process_customer DE LA CLASE $checkout 

if ( ! is_user_logged_in() && ( $checkout->is_registration_required() || ! empty(sanitize_text_field($_POST['createaccount']) ) ) ) {
        echo '<br><br>CREANDO CUENTA NUEVA<BR><BR>'; 
        // almacena el contenido del carrito de alquiler para reconstruir el carrito despues de crear la cuenta
        $alquiler_cart_contents = WC()->cart->get_cart_contents(); 
    
        var_dump($alquiler_cart_contents); 

        $data = $checkout->get_posted_data(); 
   
            $username    = ! empty( $data['billing_first_name'] ) ? $data['billing_first_name'] : '';
            $username    .= '.'.( ! empty( $data['billing_last_name'] ) ? $data['billing_last_name'] : '');
			$password    = ! empty( $data['account_password'] ) ? $data['account_password'] : '';
			$customer_id = wc_create_new_customer( $data['billing_email'], $username, $password );
			

			if ( is_wp_error( $customer_id ) ) {
				throw new Exception( $customer_id->get_error_message() );
			}

            // Se creo la cuenta, se loguea con la cuenta creada
            $cuenta_nueva = 'yes';
			wp_set_current_user( $customer_id );
			wc_set_customer_auth_cookie( $customer_id );
			

			// As we are now logged in, checkout will need to refresh to show logged in data.
			WC()->session->set( 'reload_checkout', true );
			
			//Reconstruye el carrito
			echo '<br><br><br><br>CREANDO CUENTA CARRITO<br><br><br><BR><BR>'; 
			WC()->cart->set_cart_contents ($alquiler_cart_contents);

			// Also, recalculate cart totals to reveal any role-based discounts that were unavailable before registering.
			WC()->cart->calculate_totals();
			
			
		// On multisite, ensure user exists on current site, if not add them before allowing login.
		if ( $customer_id && is_multisite() && is_user_logged_in() && ! is_user_member_of_blog() ) {
			add_user_to_blog( get_current_blog_id(), $customer_id, 'customer' );
		}

		// Add customer info from other fields.
		if ( $customer_id && apply_filters( 'woocommerce_checkout_update_customer_data', true, $checkout ) ) {
			$customer = new WC_Customer( $customer_id );

			if ( ! empty( $data['billing_first_name'] ) ) {
				$customer->set_first_name( $data['billing_first_name'] );
			}

			if ( ! empty( $data['billing_last_name'] ) ) {
				$customer->set_last_name( $data['billing_last_name'] );
			}

			// If the display name is an email, update to the user's full name.
			if ( is_email( $customer->get_display_name() ) ) {
				$customer->set_display_name( $data['billing_first_name'] . ' ' . $data['billing_last_name'] );
			}

			foreach ( $data as $key => $value ) {
				// Use setters where available.
				if ( is_callable( array( $customer, "set_{$key}" ) ) ) {
					$customer->{"set_{$key}"}( $value );

					// Store custom fields prefixed with wither shipping_ or billing_.
				} elseif ( 0 === stripos( $key, 'billing_' ) || 0 === stripos( $key, 'shipping_' ) ) {
					$customer->update_meta_data( $key, $value );
				}
			}

			/**
			 * Action hook to adjust customer before save.
			 *
			 * @since 3.0.0
			 */
			do_action( 'woocommerce_checkout_update_customer', $customer, $data );

			$customer->save();
		}

		do_action( 'woocommerce_checkout_update_user_meta', $customer_id, $data );
	
	//var_dump($_POST);
  
    
    
    
} else 
 $customer_id = get_current_user_id();
/**/
//die;

//echo '<pre>DATOS DE LA ORDEN</pre>';
// Crea el Nro. de la orden o pedido
$id_pedido = $checkout->create_order($_POST);
//echo '<br>Nro. Pedido: '.$id_pedido.'<br><br>';

$order = new WC_Order($id_pedido);
$nro_orden = $order->get_order_number();

//Cambia el nombre del producto
$table = $wpdb->prefix.'woocommerce_order_items';
//echo '<br>LA TABLA es: '.$table.'<br><br>';

$data_cambiar = array('order_item_name' => strip_tags( sanitize_text_field($_POST['nombre_producto_alquiler'])));
$data_filtro = array ('order_id' => $nro_orden, 'order_item_type' => 'line_item');
$resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );
//echo '<br>el resultado es: '.$resultado.'<br><br>';

// Busca el order_item_id
$resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = $nro_orden AND order_item_type ='line_item'", OBJECT );

// si no lo consigue, lo agrega
/*
if (($wpdb->num_rows =0) || ($resultado = 'Array ( )')) {
     $nombre_producto_alquiler2 = strip_tags($_POST['nombre_producto_alquiler']) ;
     $resultado = $wpdb->get_results( "INSERT INTO {$wpdb->prefix}woocommerce_order_items (order_item_id, order_item_name, order_item_type, order_id) VALUES (NULL, '$nombre_producto_alquiler2', 'line_item', '$nro_orden')", OBJECT );
     $resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = '$nro_orden' AND order_item_type ='line_item'", OBJECT );
//die;
print_r($resultado);    
    
}
*/

/*
echo '<br>AHORA CAMBIA LOS DATOS DE LA ORDEN EN LA BASE DE DATOS<br><br>';
*/

//Cambia los datos de la linea en la orden (totales de costo e impuestos)
$order_item_id = $resultado[0]->order_item_id;
$table = $wpdb->prefix.'woocommerce_order_itemmeta';
//echo '<br>LA TABLA es: '.$table.'<br><br>';
                      
$data_cambiar = array('meta_value' => $total_tax);
$data_filtro = array ('order_item_id' => $order_item_id , 'meta_key' => '_line_tax');
$resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );
//echo '<br>el resultado es: '.$resultado.'<br><br>';

//Array ( [total] => Array ( [1] => 535.364807 ) [subtotal] => Array ( [1] => 535.364807 ) ) 

$total_tax_serializado = maybe_serialize(array ( 'total'=> array ( 1 => $total_tax ), 'subtotal' => array ( 1 => $total_tax ) )); 
$data_cambiar = array('meta_value' => $total_tax_serializado);
$data_filtro = array ('order_item_id' => $order_item_id , 'meta_key' => '_line_tax_data');
$resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );


$data_cambiar = array('meta_value' => $sub_total_alquiler);
$data_filtro = array ('order_item_id' => $order_item_id , 'meta_key' => '_line_total');
$resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );

$data_cambiar = array('meta_value' => $total_tax);
$data_filtro = array ('order_item_id' => $order_item_id , 'meta_key' => '_line_subtotal_tax');
$resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );

$data_cambiar = array('meta_value' => $sub_total_alquiler);
$data_filtro = array ('order_item_id' => $order_item_id , 'meta_key' => '_line_subtotal');
$resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );

// Agrega el dato del order item meta data para saber que tipo de orden de alquiler es
wc_add_order_item_meta( $order_item_id, '_alquiler_type', '__alquiler__', $unique = true );


//Cambia los datos del ENVIO en la orden (solo el nombre del producto enviado)
$table = $wpdb->prefix.'woocommerce_order_items';
//echo '<br>LA TABLA es: '.$table.'<br><br>';

// Busca el order_item_id del ENVIO
$resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = '$nro_orden' AND order_item_type ='shipping'", OBJECT );

// si no lo consigue, lo agrega 
if (($wpdb->num_rows =0) || ($resultado = 'Array ( )')) {
     $resultado = $wpdb->get_results( "INSERT INTO {$wpdb->prefix}woocommerce_order_items (order_item_id, order_item_name, order_item_type, order_id) VALUES (NULL, 'Precio Fijo', 'shipping', '$nro_orden')", OBJECT );
     $resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = '$nro_orden' AND order_item_type ='shipping'", OBJECT );
//die;
//print_r($resultado);    
    
}

//Cambia los datos del ENVIO en la orden
$order_item_id = $resultado[0]->order_item_id;
$table = $wpdb->prefix.'woocommerce_order_itemmeta';

$data_cambiar = array('order_item_id' => $order_item_id , 'meta_key' => 'Artículos', 'meta_value' => $nombre_producto_alquiler);
$resultado = $wpdb->insert( $table, $data_cambiar );

$data_cambiar = array('meta_value' =>  sanitize_text_field($_POST['shipping_lines'][0]['method_id']), 'order_item_id' => $order_item_id , 'meta_key' => 'method_id');
$resultado = $wpdb->insert( $table, $data_cambiar );

$data_cambiar = array('meta_value' => $instance_id, 'order_item_id' => $order_item_id , 'meta_key' => 'instance_id');
$resultado = $wpdb->insert( $table, $data_cambiar );

$data_cambiar = array('meta_value' => wc_format_decimal(  sanitize_text_field($_POST['shipping_lines'][0]['total'])), 'order_item_id' => $order_item_id , 'meta_key' => 'cost');
$resultado = $wpdb->insert( $table, $data_cambiar );

$data_cambiar = array('meta_value' => WC()->cart->get_shipping_tax(), 'order_item_id' => $order_item_id , 'meta_key' => 'total_tax');
$resultado = $wpdb->insert( $table, $data_cambiar );
$taxes_serializado = serialize(array ( 'total'=> array ( 1 => wc_format_decimal(WC()->cart->get_shipping_tax())) )); 
$data_cambiar = array('meta_value' => $taxes_serializado , 'order_item_id' => $order_item_id , 'meta_key' => 'taxes');
$resultado = $wpdb->insert( $table, $data_cambiar );

//Cambia los datos del IMPUESTO en la orden (solo el tax_amount)
$table = $wpdb->prefix.'woocommerce_order_items';

// Busca el order_item_id del IMPUESTO
$resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = $nro_orden AND order_item_type ='tax'", OBJECT );

if (($wpdb->num_rows !=0) || ($resultado !=null)) {
    //Cambia los datos del IMPUESTO en la orden
     $order_item_id = $resultado[0]->order_item_id;
     $table = $wpdb->prefix.'woocommerce_order_itemmeta';
     $data_cambiar = array('meta_value' => $total_tax);
     $data_filtro = array ('order_item_id' => $order_item_id , 'meta_key' => 'tax_amount');
     $resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );
//echo '<br>el resultado es: '.$resultado.'<br><br>';

}

// Agrega el metadato de alquiler en la tabla postmeta (_alquiler_item = "yes")
// INDICA  que es una orden de un producto alquilado
add_post_meta( $nro_orden, '_alquiler_item', 'yes', true );


// Cambia los datos de la orden en la tabla postmeta (solo el total de la orden y los impuestos)
//SELECT * FROM `edu_postmeta` WHERE `post_id` = 2674 AND meta_key = '_order_total' 
/*
     $table = $wpdb->prefix.'postmeta';
     $data_cambiar = array('meta_value' => $total_alquiler);
     $data_filtro = array ('post_id' => $nro_orden , 'meta_key' => '_order_total');
     $resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );
     
     $data_cambiar = array('meta_value' => $total_tax);
     $data_filtro = array ('post_id' => $nro_orden , 'meta_key' => '_order_tax');
     $resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );
*/

/// Almacena los datos del plan de pagos para el alquiler del producto 

  $product_data = wc_get_product($id_product);
  $meses_alquiler = 0;
  $precio_alquiler  = $product_data->get_meta( '_precio_alquiler', true );
  $periodo_alquiler = $product_data->get_meta( '_periodo_alquiler', true );
/*
  if ($periodo_alquiler == 0) $meses_alquiler = 12;
  if ($periodo_alquiler == 1) $meses_alquiler = 18;
  if ($periodo_alquiler == 2) $meses_alquiler = 24;
*/
  $meses_alquiler = $periodo_alquiler;

// SUBSTITUYE LOS '/' POR GUIONES
   $wcai_date_from = str_ireplace('/','-',$wcai_date_from);
   $wcai_date_to = str_ireplace('/','-',$wcai_date_to);
   $startdate=strtotime($wcai_date_from);
   $enddate=strtotime($wcai_date_to);
   $i = 1 ;

   while ($i < $meses_alquiler ) {
        $i ++;
        $startdate = strtotime("+1 month", $startdate);
        $fecha_vencimiento = date("d/m/Y", $startdate);
        ///echo $i.' ==> '.$fecha_vencimiento. "<br>";
        $pago_actual = 'Cuota del mes Nro:'.$i;
        $descripcion_del_pago  = str_replace('CUOTA INICIAL', $pago_actual, $nombre_producto_alquiler);
    
        $resultado = $wpdb->get_results( "INSERT INTO {$wpdb->prefix}wcai_pagos_mensuales (pago_id, pago_user_id, pago_product_id, pago_order_id, pago_amount, pago_descripcion, pago_fecha, pago_fecha_completo, pago_status, pago_notas) 
                 VALUES (NULL, $customer_id, $id_product, $nro_orden, $total_alquiler,'$descripcion_del_pago', '$fecha_vencimiento', 'por pagar', 'wc_pending', ' ' )", OBJECT );

     }

///var_dump($product_data); die;


 
/// desplega la vista en la pantalla del cliente.
    include( WCAI_PLUGIN_PATH .'inc/views/html-wcai-process-checkout.php' );
    
    if ($cuenta_nueva == 'yes') {
         echo '<br>Se ha creado un Cuenta Nueva, su nombre de usuario es: "'.$username .'" y los datos de acceso fueron enviados a su correo: '.$data['billing_email'].'<br><br>';
     }
     
     echo '<br><b>También se ha creado un plan de pagos mensuales por los próximos '.$meses_alquiler. ' meses por '.$precio_alquiler. ' '.get_woocommerce_currency_symbol().' cada uno.</b>';

     echo '<br>Para revisar su cuenta, sus pedidos y otros datos use este boton ===> <a class="button " href="'.$site_url.'/mi-cuenta/" >Ver Mi Cuenta</a><br><br>';

// VACIA EL CARRITO DE COMPRAS PARA ASEGURAR INICIO DESDE CERO   ************************************* OJO ACTIVARLO EN PRODUCCION
   WC()->cart->empty_cart();

// VACIA EL BUFFER DE SALIDA SI LO HAY
//    if (ob_flush()) ob_flush();
    
