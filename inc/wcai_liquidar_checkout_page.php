<?php
/**
* Muestra los datos del comprador y del Instrumento o Producto a alquilar en el checkout o pagina de pago. 
* File: wcai_liquidar_checkout_page.php
*/

/// FOR  DEBUGGING PURPOSE
 
/* echo 'LLEGA a wcai_liquidar_checkout_page.php UNA PETICION CON <b>' .json_encode($_POST).'</b></b></b>';
   var_dump($_POST);
   die();
/**/

defined( 'ABSPATH' ) || exit;

global $site_url, $site_dir, $cart_item_data, $cart_item, $pago_liquidacion, $nombre_liquidacion, $alquiler_shipping_class_id;

$site_url = '';
$site_dir = '';
$id_product = '' ;
$nro_orden = '';
$usuario_actual = '';
$pago_liquidacion = '';
$nombre_liquidacion = '';
$alquiler_shipping_class_id =-1;

if(isset($_POST['site_url'])) $site_url = sanitize_text_field($_POST['site_url']);
if(isset($_POST['site_dir'])) $site_dir = sanitize_text_field($_POST['site_dir']);
if(isset($_POST['id_product']))   $id_product = sanitize_text_field($_POST['id_product']); 
if(isset($_POST['nro_orden']))   $nro_orden = sanitize_text_field($_POST['nro_orden']); 
if(isset($_POST['usuario_actual']))   $usuario_actual = sanitize_text_field($_POST['usuario_actual']); 
if(isset($_POST['pago_liquidacion']))   $pago_liquidacion = sanitize_text_field($_POST['pago_liquidacion']); 
if(isset($_POST['nombre_liquidacion']))   $nombre_liquidacion = sanitize_text_field($_POST['nombre_liquidacion']);

// Si no tiene id_product, muestra el short-code y regresa
if ($id_product =='') {
    echo  '<!-- wp:shortcode -->[wcai_facturar_liquidar_alquiler]<!-- /wp:shortcode -->';
    return;
}

$product = wc_get_product( $id_product );

  /// Transforma (procesa) los datos de la liquidación del alquiler.
  //1.- VACIA EL CARRITO DE COMPRAS PARA ASEGURAR INICIO DESDE CERO
        WC()->cart->empty_cart();
        
  //2.- AGREGA LOS DATOS DE LA LIQUIDACION DEL ALQUILER AL CARRITO DE COMPRAS 
        add_filter( 'woocommerce_add_cart_item', 'liquidar_add_cart_item', 99, 1 );
        
  //3.- Para eliminar gastos de envio
        add_filter( 'woocommerce_package_rates', 'zeroes_cost_tax_rates' );
 
 // 4.- Agrega la liquidacion al carrito de compra
        WC()->cart->add_to_cart( $product->get_id(), 1, 0, 0, $cart_item_data );
        
  // Check cart items are valid.
   do_action( 'woocommerce_check_cart_items' );

    /// desplega la vista en la pantalla del cliente.
    include( WCAI_PLUGIN_PATH .'inc/views/html-wcai-liquidar-checkout-page.php' );
    
  