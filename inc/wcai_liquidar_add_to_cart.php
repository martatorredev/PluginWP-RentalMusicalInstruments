<?php
/**
* Liquida el alquiler del Instrumento o Producto alquilado, lo pone en el carrito de alquiler listo para el checkout 
* File: wcai_liquidar_add_to_cart.php  
*/

/// FOR  DEBUGGING PURPOSE
 
/* echo '<pre>LLEGA a wcai_liquidar_add_to_cart.php  UNA PETICION CON <b>' .json_encode($_POST).'</b><br/><br/></pre>';
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

if(isset($_POST['site_url'])) $site_url = $_POST['site_url'];
if(isset($_POST['site_dir'])) $site_dir = $_POST['site_dir'];
if(isset($_POST['id_product']))   $id_product = $_POST['id_product']; 
if(isset($_POST['nro_orden']))   $nro_orden = $_POST['nro_orden']; 
if(isset($_POST['usuario_actual']))   $usuario_actual = $_POST['usuario_actual']; 
if(isset($_POST['pago_liquidacion']))   $pago_liquidacion = $_POST['pago_liquidacion']; 
if(isset($_POST['nombre_liquidacion']))   $nombre_liquidacion = $_POST['nombre_liquidacion'];

// Si no tiene id_product, muestra el short-code y regresa
if ($id_product =='') {
    echo  '<!-- wp:shortcode -->[wcai_carrito_liquidar_alquiler]<!-- /wp:shortcode -->';
    return;
}

$product = wc_get_product( $id_product );

  /// Transforma (procesa) los datos de la liquidación del alquiler.
  //1.- VACIA EL CARRITO DE COMPRAS PARA ASEGURAR INICIO DESDE CERO
        WC()->cart->empty_cart();
        
  //2.- AGREGA LOS DATOS DE LA LIQUIDACION DEL ALQUILER AL CARRITO DE COMPRAS 
        add_filter( 'woocommerce_add_cart_item', 'liquidar_add_cart_item', 99, 1 );
        
        // Para eliminar gastos de envio
        add_filter( 'woocommerce_package_rates', 'zeroes_cost_tax_rates' );
 
        WC()->cart->add_to_cart( $product->get_id(), 1, 0, 0, $cart_item_data );
        
        
  //3.- Ajusta costos del envio  (No hay envios)  
  /*    //esta opcion tambien funciona
         $costo_total_carrito = WC()->cart->get_totals()['total'];
         $impuesto_total_carrito = WC()->cart->get_total_tax( );
         $costo_envio = WC()->cart->get_shipping_total();
         $impuesto_envio = WC()->cart->get_shipping_tax();
         
         $nuevo_costo_total_carrito = $costo_total_carrito - $costo_envio - $impuesto_envio;
         $nuevo_impuesto_total_carrito = $impuesto_total_carrito - $impuesto_envio ;
         WC()->cart->set_shipping_total(0);
         WC()->cart->set_shipping_tax(0);
         WC()->cart->set_shipping_taxes(array(0));
         
         WC()->cart->set_total($nuevo_costo_total_carrito);
         WC()->cart->set_total_tax ($nuevo_impuesto_total_carrito);
         WC()->cart->check_cart_items();
     */   
        /// FOR  DEBUGGING PURPOSE
      /*  
		 foreach ( WC()->cart->get_cart() as  $cart_item_key => $cart_item ) {
		     
			echo '<br/>KEY : '. $cart_item ['key'];
		    echo '<br/>CANTIDAD : '. $cart_item ['quantity'] ;
		    echo '<br/>ID PRODUCTO: '.$cart_item ['product_id'] ;
		    echo '<br/>SUBTOTAL: '.$cart_item ['line_subtotal'];
		    echo '<br/>SUBTOTAL TAX: '.$cart_item ['line_subtotal_tax'];
		    echo '<br/>TOTAL: '.$cart_item ['line_total'] ;
            echo '<br/>TOTAL TAX: '.$cart_item ['line_tax'] ;
            echo '<br/>COSTO ENVIO: '.WC()->cart->get_shipping_total();
            echo '<br/>ENVIO TAX: '.WC()->cart->get_shipping_tax();
            echo '<br/>TOTAL PRODUCTOS: '.WC()->cart->get_totals()['cart_contents_total'];  
            echo '<br/>TOTAL IMPUESTOS PRODUCTOS: '.WC()->cart->get_totals()['cart_contents_tax'];  
            echo '<br/>TOTAL DEL CARRITO: '.WC()->cart->get_totals()['total'].'<br/>';
            echo '<br/>TOTAL IMPUESTOS DEL CARRITO: '.WC()->cart->get_totals()['total_tax'];  
            
            echo '<br/>**********************************************************<br/>';
            
            
           // print_r($cart_item['data']);
            
            echo '<br/>TOTAL DEL CARRITO <br/>';
            print_r(WC()->cart->get_totals());
            //die;
           /* */
		//	}
		
	 	   // echo '<br/><br/>DATOS modificados de prueba DEL CARRITO DE COMPRAS:<br/><br/>'; 
		   //print_r(WC()->cart->get_cart());  
	       // die;

  // Check cart items are valid.
   do_action( 'woocommerce_check_cart_items' );

  /// desplega la vista en la pantalla del cliente.
    include( WCAI_PLUGIN_PATH .'inc/views/html-wcai-liquidar-add-to-cart.php' );

