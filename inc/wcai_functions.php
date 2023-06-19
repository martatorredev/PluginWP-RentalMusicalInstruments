<?php
/**
 
 * Main Functions for Woocommerce Alquiler De Instrumentos
 * File: wcai_functions.php
 */

 defined( 'ABSPATH' ) || exit;

/**
* Filtra los Tipos de productos permitidos para el alquiler.
**/	
	function allowed_types () {
	//Tipos de productos permitidos para alquilar
     return apply_filters(
            'alquiler_allowed_product_types',
            array(
                'simple',
                'variable',
                'grouped',
                'bundle'
            ));
	}

/**
* Sana la opcion del checkbox para alquilable.
* @param str | $value
* @return str | 'yes' or 'no'
*/
	function sanitize_checkbox( $value ) {
		return ( ! empty( $value ) && $value !== 'no' ) ? 'yes' : 'no';
	}

	
/**
* Retorna si el producto es alquilable o no.
* @param mixed int | WC_Product 
* @return bool
**/
   function wcai_is_alquilable( $_product ) {

    // If product ID was passed, get the product
    if ( is_numeric( $_product ) ) {
        $_product = wc_get_product( $_product );
    }

    if ( ! $_product ) {
        return false;
    }

    $product_type = $_product->get_type();
    // Check the product type
    $allowed_product_types   = allowed_types();
    $is_alquilable = $_product->get_meta( '_alquilable', true );

    // Si esta vacio NO ES ALQUILABLE
    if ( empty( $is_alquilable ) ) {
        $is_alquilable = false;
    }

    $is_alquilable = ( $is_alquilable === 'yes' && in_array( $product_type, $allowed_product_types ) ) ? true : false;
    return $is_alquilable;

}

/**
* Ajusta el precio y otros datos del producto de acuerdo al alquiler mensual.
* @param mixed $cart_item
* @return array cart item
* Tomada y modificada del Woocommerce - Marzo - 2020
*/
	function add_cart_item( $cart_item ) { 
		global $precio_alquiler, $nombre_producto_alquiler, $alquiler_shipping_class_id; $alquiler_shipping_class_id =0;
		if (($precio_alquiler !='') || ($precio_alquiler != 0 )) $cart_item['data']->set_price( $precio_alquiler );
		if ($nombre_producto_alquiler !='') $cart_item['data']->set_name($nombre_producto_alquiler);
		if ($alquiler_shipping_class_id !='') $cart_item['data']->set_shipping_class_id($alquiler_shipping_class_id) ; 
		return $cart_item;
	}


/**
* Ajusta el precio y otros datos de la liquidacion del alquiler.
* @param mixed $cart_item
* @return array cart item
* Tomada y modificada del Woocommerce - Abril - 2020
*/
	function liquidar_add_cart_item( $cart_item ) { 
		global $pago_liquidacion, $nombre_liquidacion, $alquiler_shipping_class_id; 
		if (($pago_liquidacion !='') || ($pago_liquidacion != 0 )) $cart_item['data']->set_price( $pago_liquidacion );
        if ($nombre_liquidacion !='') $cart_item['data']->set_name($nombre_liquidacion);
        if ($alquiler_shipping_class_id !='') $cart_item['data']->set_shipping_class_id($alquiler_shipping_class_id) ;  
		return $cart_item;
	}

/**
* Coloca en CERO el costo y el impuesto del envio en la liquidacion del alquiler.
* @param mixed $cart_item
* @return array cart item
**/
    function zeroes_cost_tax_rates( $rates ) {
        foreach( $rates as $rate_key => $rate ){
            // Check if the shipping method ID is UPS
            if( ($rate->method_id == 'flat_rate') ) { 
                // Set cost to zero
                $rates[$rate_key]->cost = 0;
                // Set TAX to zero
                $rates[$rate_key]->taxes = array(0);
             } 
         }

        return $rates;        
    }


