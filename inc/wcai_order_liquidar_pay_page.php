<?php
/**
* Procesa los datos del pago DE LA LIQUIDACION DE UN ALQUILER  desde el checkout o pagina de pago 
* (aqui se hace el pago y se crea la orden o el pedido DE LIQUIDACION) 
* File: wcai_order_liquidar_pay_page.php
*/

/// FOR  DEBUGGING PURPOSE
/* echo '<pre>LLEGA a wcai_order_liquidar_pay_page.php UNA PETICION CON <b>' .json_encode($_POST).'</b></b></b></pre>';
   var_dump($_POST);
   die();
/**/

defined( 'ABSPATH' ) || exit;

global $wpdb, $site_url, $site_dir, $woocommerce, $wcai_nro_orden_liquidar, $wcai_id_product ;
global $wcai_nro_orden, $wcai_pago_liquidacion, $wcai_nombre_liquidacion; 

$site_url = '';
$site_dir = '';
$id_product = '' ;
$nro_orden_liquidar = '';
$usuario_actual = '';
$pago_liquidacion = '';
$nombre_liquidacion = '';

if(isset($_POST['site_url'])) $site_url =  strip_tags(sanitize_text_field($_POST['site_url']));
if(isset($_POST['site_dir'])) $site_dir =  strip_tags(sanitize_text_field($_POST['site_dir']));
if(isset($_POST['id_product']))   $id_product = strip_tags(sanitize_text_field($_POST['id_product'])); 
if(isset($_POST['nro_orden_liquidar']))   $nro_orden_liquidar = strip_tags(sanitize_text_field($_POST['nro_orden_liquidar'])); 
if(isset($_POST['usuario_actual']))   $usuario_actual = strip_tags(sanitize_text_field($_POST['usuario_actual'])); 
if(isset($_POST['pago_liquidacion']))   $pago_liquidacion = strip_tags(sanitize_text_field($_POST['pago_liquidacion'])); 
if(isset($_POST['nombre_liquidacion']))   $nombre_liquidacion = strip_tags(sanitize_text_field($_POST['nombre_liquidacion']));


// Si no tiene id_product, muestra el short-code y regresa
if ($id_product =='') {
    echo  '<!-- wp:shortcode -->[wcai_gracias_por_alquilar]<!-- /wp:shortcode -->';
    return;
}

$total_tax =  sanitize_text_field( $_POST['total_tax']); 
$sub_total_alquiler =  sanitize_text_field( $_POST['subtotal']); 
$total_alquiler =  sanitize_text_field($_POST['total']);
$nombre_producto_alquiler = sanitize_text_field($_POST['nombre_liquidacion']);

$form_shipping_method =  sanitize_text_field($_POST['shipping_method'][0]);
$dos_puntos = stripos($form_shipping_method,':');
$form_shipping_method_2 = substr($form_shipping_method,0,$dos_puntos);
$instance_id = substr($form_shipping_method,$dos_puntos+1);

//Ajusta los datos del envio
$alquiler_shipping_method = implode( " | ", wc_get_chosen_shipping_method_ids());
WC()->session->set( 'chosen_shipping_methods', wc_get_chosen_shipping_method_ids() );
$packages = WC()->cart->get_shipping_packages();
$shipping_total = WC()->cart->get_shipping_total();

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
        $alquiler_shipping_country =   strip_tags(sanitize_text_field($_POST['shipping_country']));
    $resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_code = '$alquiler_shipping_country'", OBJECT );
    
    // si no consigue el pais, le pone el global
    if (($wpdb->num_rows =0) || ($resultado = 'Array ( )')) 
        $zone_id = 0; // Le asigna la zona global
     else
        $zone_id = $resultado[0]->zone_id;
    
    $resultado = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE zone_id = '$zone_id' AND method_id = '$alquiler_shipping_method'", OBJECT );
    $instance_id = $resultado[0]->instance_id;
    $alquiler_shipping_data = array(
						            'method_title' =>  strip_tags(sanitize_text_field($_POST['shipping_lines'][0]['method_title'])),
						            'method_id'    =>  strip_tags(sanitize_text_field($_POST['shipping_lines'][0]['method_id'])),
						            'instance_id'  => $instance_id,
						            'total'        => wc_format_decimal(  strip_tags(sanitize_text_field($_POST['shipping_lines'][0]['total']))),
						            'cost'         => wc_format_decimal(  strip_tags(sanitize_text_field($_POST['shipping_lines'][0]['total']))),
						            'taxes'        => array('total' => WC()->cart->get_shipping_tax() ),
						            'total_tax'    => WC()->cart->get_shipping_tax(),
						            'Artículos'    =>  strip_tags(sanitize_text_field($_POST['nombre_liquidacion'])), 
						      );
}

//Crea el objeto checkout con los datos del POST    
$checkout = new WC_Checkout($_POST); 

$customer_id = get_current_user_id();

// Crea el Nro. de la orden o pedido
$id_pedido = $checkout->create_order($_POST);
//echo '<br>Nro. Pedido: '.$id_pedido.'<br><br>';

$order = new WC_Order($id_pedido);
$nro_orden = $order->get_order_number();

//Cambia el nombre del producto
$table = $wpdb->prefix.'woocommerce_order_items';
//echo '<br>LA TABLA es: '.$table.'<br><br>';

$data_cambiar = array('order_item_name' => strip_tags( sanitize_text_field($_POST['nombre_liquidacion'])));
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

//Agrega un metadato nuevo ("_order_liquidar_id")
// es el Nro. del pedido a liquidar
$data_agregar = array ( 'order_item_id' => $order_item_id,
                        'meta_key'      => '_order_liquidar_id',
                        'meta_value'    => $nro_orden_liquidar, 
                            );

$resultado = $wpdb->insert( $table, $data_agregar, array( "%s" ) );

//Agrega un metadato nuevo ("_alquiler_type")
// indica si es un alquiler "normal" o una LIQUIDACION
$data_agregar = array ( 'order_item_id' => $order_item_id,
                        'meta_key'      => '_alquiler_type',
                        'meta_value'    => '__liquidar__', 
                            );

$resultado = $wpdb->insert( $table, $data_agregar, array( "%s" ) );

/*
$order->set_meta_data(array ( 'order_item_id' => $order_item_id,
                              'meta_key'      => '_order_liquidar_id',
                              'meta_value'    => $nro_orden_liquidar, 
                            )
                     );
*/

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

$data_cambiar = array('order_item_id' => $order_item_id , 'meta_key' => 'Artículos', 'meta_value' => $nombre_liquidacion);
$resultado = $wpdb->insert( $table, $data_cambiar );

$data_cambiar = array('meta_value' =>  strip_tags(sanitize_text_field($_POST['shipping_lines'][0]['method_id'])), 'order_item_id' => $order_item_id , 'meta_key' => 'method_id');
$resultado = $wpdb->insert( $table, $data_cambiar );

$data_cambiar = array('meta_value' => $instance_id, 'order_item_id' => $order_item_id , 'meta_key' => 'instance_id');
$resultado = $wpdb->insert( $table, $data_cambiar );

$data_cambiar = array('meta_value' => wc_format_decimal(  strip_tags(sanitize_text_field($_POST['shipping_lines'][0]['total']))), 'order_item_id' => $order_item_id , 'meta_key' => 'cost');
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

// Agrega el metadato de liquidar alquiler en la tabla postmeta (_liquidar_alquiler_item = "yes")
// INDICA  que es una orden de una LIQUIDACION de un producto alquilado 
    add_post_meta( $nro_orden, '_liquidar_alquiler_item', 'yes', true );

// datos GLOBALES para ACTUALIZAR el plan de pagos futuros
    $wcai_nombre_liquidacion = $nombre_liquidacion;
    $wcai_pago_liquidacion = $pago_liquidacion;
    $wcai_id_product = $id_product;
    $wcai_nro_orden = $nro_orden ;
    $wcai_nro_orden_liquidar = $nro_orden_liquidar;
    
/// desplega la vista en la pantalla del cliente.
    ///include( WCAI_PLUGIN_PATH .'inc/views/html-wcai-process-checkout.php' ); 
    include( WCAI_PLUGIN_PATH .'inc/views/html-wcai-order-liquidar-pay-page.php');

     //echo '<br>Para revisar su cuenta, sus pedidos y otros datos use este boton ===> <a class="button " href="'.$site_url.'/mi-cuenta/" >Ver Mi Cuenta</a><br><br>';

// VACIA EL CARRITO DE COMPRAS PARA ASEGURAR INICIO DESDE CERO   ************************************* OJO ACTIVARLO EN PRODUCCION
    WC()->cart->empty_cart();

