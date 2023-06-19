<?php
/**
 * Created by OnDesarrollo
 * Client Products Page for Woocommerce Alquiler De Instrumentos (FRONTEND)
 * File: wcai_product.php
 */

defined( 'ABSPATH' ) || exit;

// Hook para agregar las datos de alquiler del producto a la pagina del producto.
// LO AGREGA DEBAJO DEL BOTON DE COMPRAR (hay que ajustar algo en la vista)
//add_filter( 'woocommerce_share', 'alquilable_product_data_html', 10 );

// LO AGREGA ARRIBA DEL BOTON DE COMPRAR
add_action( 'woocommerce_product_price_class', 'alquilable_product_data_html', 10 );

/**
* Desplega los datos de alquiler del producto SI ES ALQUILABLE
* @param WC_Product | $product
*/
    function alquilable_product_data_html( $product ) {
	    global $product;
		//Si el Producto es alquilable
        if ( wcai_is_alquilable( $product ) ) { 
            include_once( WCAI_PLUGIN_PATH .'inc/views/html-wcai-product-data.php' );                      
        }       
     } 

