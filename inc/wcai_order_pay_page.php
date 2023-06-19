<?php
/**
* Procesa los datos del pago desde el checkout o pagina de pago (FACTURACION)
* (aqui se crea la orden o el pedido y se hace el pago  ) 
* File: wcai_order_pay_page.php
*/

defined( 'ABSPATH' ) || exit;

global $wpdb, $site_url, $site_dir, $woocommerce, $meses_alquiler, $wcai_date_from, $wcai_date_to ;
global $wcai_cuenta_nueva;
global $wcai_id_product, $wcai_nro_orden, $wcai_date_from, $wcai_date_to, $wcai_total_alquiler, $wcai_nombre_producto_alquiler;  

$site_url = '';
$site_dir = '';
$id_product = '' ;
$wcai_date_from = '' ;
$wcai_date_to = '' ;
$wcai_cuenta_nueva = 'no';

if(isset($_POST['site_url'])) $site_url =  sanitize_text_field($_POST['site_url']);
if(isset($_POST['site_dir'])) $site_dir =  sanitize_text_field($_POST['site_dir']);
if(isset($_POST['id_product']))   $id_product = sanitize_text_field($_POST['id_product']); 
if(isset($_POST['wcai_date_from']))   $wcai_date_from = sanitize_text_field($_POST['wcai_date_from']);
if(isset($_POST['wcai_date_to']))   $wcai_date_to = sanitize_text_field($_POST['wcai_date_to']); 

// Si no tiene id_product, muestra el short-code y regresa
if ($id_product =='') {
    echo  '<!-- wp:shortcode -->[wcai_finalizar_alquiler]<!-- /wp:shortcode -->';
    return;
}

/// Obtiene y transforma (procesa) los datos del instrumento o producto a alquilar.
    include( WCAI_PLUGIN_PATH .'inc/wcai_process_alquiler_data.php' );

$total_tax =  sanitize_text_field( $_POST['total_tax']); 
$sub_total_alquiler =  sanitize_text_field( $_POST['subtotal']); 
$total_alquiler =  sanitize_text_field($_POST['total']);
$nombre_producto_alquiler = sanitize_text_field($_POST['nombre_producto_alquiler']);
$total_cart = sanitize_text_field($_POST['total_cart']);
$cart_contents_total = sanitize_text_field($_POST['cart_contents_total']);
$cart_contents_tax = sanitize_text_field($_POST['cart_contents_tax']);
$total_cart_tax = sanitize_text_field($_POST['total_cart_tax']);

$form_shipping_method =  sanitize_text_field($_POST['shipping_method'][0]);
$dos_puntos = stripos($form_shipping_method,':');
$form_shipping_method_2 = substr($form_shipping_method,0,$dos_puntos);
$instance_id = substr($form_shipping_method,$dos_puntos+1);

//Ajusta los datos del envio
$alquiler_shipping_method = implode( " | ", wc_get_chosen_shipping_method_ids());
WC()->session->set( 'chosen_shipping_methods', wc_get_chosen_shipping_method_ids() );
$packages = WC()->cart->get_shipping_packages();
$shipping_total = WC()->cart->get_shipping_total(); 
$shipping_total_tax = WC()->cart->get_shipping_tax();

if ($shipping_total ==0)  {
     $shipping_total = sanitize_text_field($_POST['shipping_total']);
     $shipping_total_tax = sanitize_text_field($_POST['shipping_total_tax']);
}

if ($alquiler_shipping_method =='') $alquiler_shipping_method = $form_shipping_method_2; 

if ($alquiler_shipping_method =='flat_rate') {
    $_POST['shipping_lines'] = array (array(
            'method_id' => 'flat_rate',
            'method_title' => 'Flat Rate',
            'total' => $shipping_total
        ));
    
    if ($packages[0]['destination']['country']) 
        $alquiler_shipping_country = $packages[0]['destination']['country'];
     else
        $alquiler_shipping_country =   trim(sanitize_text_field($_POST['shipping_country'])); 
        
    $resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_code ='$alquiler_shipping_country';", OBJECT );
    //var_dump($resultado); echo "<BR>NUMERO DE FILAS ENCONTRADAS ===>".$wpdb->num_rows;
    // si no consigue el pais, le pone el global
    if ($wpdb->num_rows ==0) 
        $zone_id = 0; // Le asigna la zona global 
     else
        $zone_id = $resultado[0]->zone_id; ///echo "<BR>YA LO TENEMOS ------->>> zone_id = ".$zone_id."  Location Code = ".$alquiler_shipping_country;die;
    
    $resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE zone_id = '$zone_id' AND method_id = '$alquiler_shipping_method'", OBJECT );
    $instance_id = $resultado[0]->instance_id;
    
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
}

// Reconstruye el carrito de alquiler (compra) for backwards compatibility.

 WC()->cart->set_subtotal_tax($total_tax); 
 WC()->cart->set_total($total_cart); 
 WC()->cart->set_subtotal($sub_total_alquiler);
 WC()->cart->set_shipping_total($shipping_total);
 WC()->cart->set_shipping_tax($shipping_total_tax);
 WC()->cart->set_cart_contents_total($cart_contents_total);
 WC()->cart->set_cart_contents_tax($cart_contents_tax);
 WC()->cart->set_total_tax($total_cart_tax);
 WC()->cart->set_shipping_taxes( array(1 => floatval($shipping_total_tax)));
 WC()->cart->set_cart_contents_taxes( array(1 => floatval($cart_contents_tax)));


$form_shipping_method =  sanitize_text_field($_POST['shipping_method'][0]);

//Crea el objeto checkout con los datos del POST    
$checkout = new WC_Checkout($_POST); 

// almacena el contenido del carrito de alquiler para reconstruir el carrito despues de crear la cuenta
        $alquiler_cart_contents = WC()->cart->get_cart_contents(); 
/*
echo "<br><br><br>CONTENIDO DEL CHECKOUT<BR>";
        var_dump($checkout); 
    echo "<br><br><br>CONTENIDO DEL CARRO<BR>";
    var_dump($alquiler_cart_contents); 
    //die;
*/

// Chequea si es cliente nuevo y crea la cuenta si es necesario
// TOMADO DE LA protected function process_customer DE LA CLASE $checkout 

if ( ! is_user_logged_in() && ( $checkout->is_registration_required() || ! empty(sanitize_text_field($_POST['createaccount']) ) ) ) {
        //echo '<br><br>CREANDO CUENTA NUEVA<BR><BR>'; 
        

        $data = $checkout->get_posted_data(); 
   
            $username    = ! empty( $data['billing_first_name'] ) ? $data['billing_first_name'] : '';
            $username    .= '.'.( ! empty( $data['billing_last_name'] ) ? $data['billing_last_name'] : '');
			$password    = ! empty( $data['account_password'] ) ? $data['account_password'] : '';
			$customer_id = wc_create_new_customer( $data['billing_email'], $username, $password );
			

			if ( is_wp_error( $customer_id ) ) {
				throw new Exception( $customer_id->get_error_message() );
			}

            // Se creo la cuenta, se loguea con la cuenta creada
            $wcai_cuenta_nueva = 'yes';
			wp_set_current_user( $customer_id );
			wc_set_customer_auth_cookie( $customer_id );
			

			// As we are now logged in, checkout will need to refresh to show logged in data.
			WC()->session->set( 'reload_checkout', true ); ///var_dump(WC()->session);
			
			//Reconstruye el carrito
		//	echo '<br><br><br><br>CREANDO CUENTA CARRITO<br><br><br><BR><BR>'; 
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
	
} else {
     $customer_id = apply_filters( 'woocommerce_checkout_customer_id', get_current_user_id() ); //get_current_user_id();
     
}

// Crea el Nro. de la orden o pedido
$id_pedido = $checkout->create_order($_POST);

$order = new WC_Order($id_pedido);
$nro_orden = $order->get_order_number();

//Cambia el nombre del producto
$table = $wpdb->prefix.'woocommerce_order_items';

$data_cambiar = array('order_item_name' => strip_tags( sanitize_text_field($_POST['nombre_producto_alquiler'])));
$data_filtro = array ('order_id' => $nro_orden, 'order_item_type' => 'line_item');
$resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );

// Busca el order_item_id
$resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = $nro_orden AND order_item_type ='line_item'", OBJECT );

//Cambia los datos de la linea en la orden (totales de costo e impuestos)
$order_item_id = $resultado[0]->order_item_id;
$table = $wpdb->prefix.'woocommerce_order_itemmeta';

$data_cambiar = array('meta_value' => $total_tax);
$data_filtro = array ('order_item_id' => $order_item_id , 'meta_key' => '_line_tax');
$resultado = $wpdb->update( $table, $data_cambiar, $data_filtro, array( "%s" ) );

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
wc_add_order_item_meta( $order_item_id, '_alquiler_type', '__alquiler__', $unique = false );

//Cambia los datos del ENVIO en la orden (solo el nombre del producto enviado)
$table = $wpdb->prefix.'woocommerce_order_items';

// Busca el order_item_id del ENVIO
$resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = '$nro_orden' AND order_item_type ='shipping'", OBJECT );

// si no lo consigue, lo agrega 
if (($wpdb->num_rows =0) || ($resultado = 'Array ( )')) {
     $resultado = $wpdb->get_results( "INSERT INTO {$wpdb->prefix}woocommerce_order_items (order_item_id, order_item_name, order_item_type, order_id) VALUES (NULL, 'Precio Fijo', 'shipping', '$nro_orden')", OBJECT );
     $resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = '$nro_orden' AND order_item_type ='shipping'", OBJECT );

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

}

// Agrega el metadato de alquiler en la tabla postmeta (_alquiler_item = "yes")
// INDICA  que es una orden de un producto alquilado
    add_post_meta( $nro_orden, '_alquiler_item', 'yes', true );

// datos GLOBALES para el plan de pagos futuros
    $wcai_nombre_producto_alquiler = $nombre_producto_alquiler;
    $wcai_total_alquiler = $total_alquiler;
    $wcai_id_product = $id_product;
    $wcai_nro_orden = $nro_orden ;
       
/// desplega la vista en la pantalla del cliente PARA PROCEDER AL PAGO.
    include( WCAI_PLUGIN_PATH .'inc/views/html-wcai-order-pay-page.php' );
    
