<?php
/**
* Agrega el Instrumento o Producto a alquilar al carrito de compras y redirige al checkout 
* File: wcai_add_to_cart.php  
*/

/// FOR  DEBUGGING PURPOSE
/* 
 echo '<pre>LLEGA a wcai_add_to_cart.php UNA PETICION CON <b>' .json_encode($_POST).'</b></b></b></pre>';
  // var_dump($_POST);
  // die();
/**/

global $site_url, $site_dir, $wcai_date_from, $wcai_date_to, $cart_item_data;
$site_url = '';
$site_dir = '';
$id_product = '' ;
$wcai_date_from = date ('d/m/Y', time());

if(isset($_POST['site_url'])) $site_url = $_POST['site_url'];
if(isset($_POST['site_dir'])) $site_dir = $_POST['site_dir'];
if(isset($_POST['id_product']))   $id_product = $_POST['id_product']; 

  defined( 'ABSPATH' ) || exit;
  
// Si no tiene id_product, muestra el short-code y regresa
if ($id_product =='') {
    echo  '<!-- wp:shortcode -->[wcai_carrito_alquiler]<!-- /wp:shortcode -->';
    return;
}
 
  /// Obtiene y transforma (procesa) los datos del instrumento o producto a alquilar.
    include( WCAI_PLUGIN_PATH .'inc/wcai_process_alquiler_data.php' );
    
  // Check cart items are valid.
   do_action( 'woocommerce_check_cart_items' );

  /// desplega la vista en la pantalla del cliente.
    include( WCAI_PLUGIN_PATH .'inc/views/html-wcai-add-to-cart.php' );

