<?php
/**
* Muestra los datos del comprador y del Instrumento o Producto a alquilar en el checkout o pagina de pago. 
* File: wcai_checkout_page.php
*/

/// FOR  DEBUGGING PURPOSE
/* 
 echo '<pre>LLEGA a wcai_checkout_page.php UNA PETICION CON <b>' .json_encode($_POST).'</b></b></b>';
   var_dump($_POST);
   //die();
/**/

 defined( 'ABSPATH' ) || exit;
 
global $site_url, $site_dir, $cart_item_data, $wcai_date_from, $wcai_date_to;
$site_url = '';
$site_dir = '';
$id_product = '' ;
$wcai_date_from = date ('d/m/Y', time());

if(isset($_POST['site_url'])) $site_url = sanitize_text_field($_POST['site_url']);
if(isset($_POST['site_dir'])) $site_dir = sanitize_text_field($_POST['site_dir']);
if(isset($_POST['id_product']))   $id_product = sanitize_text_field($_POST['id_product']); 

// Si no tiene id_product, muestra el short-code y regresa
if ($id_product =='') {
    echo  '<!-- wp:shortcode -->[wcai_facturar_alquiler]<!-- /wp:shortcode -->';
    return;
}


  /// Obtiene y transforma (procesa) los datos del instrumento o producto a alquilar.
    include( WCAI_PLUGIN_PATH .'inc/wcai_process_alquiler_data.php' );
    
  // Check cart items are valid.
   do_action( 'woocommerce_check_cart_items' );

  /// desplega la vista en la pantalla del cliente.
    include( WCAI_PLUGIN_PATH .'inc/views/html-wcai-checkout-page.php' );
    
  
