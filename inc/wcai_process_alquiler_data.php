<?php
/**
* Procesa los datos del Instrumento o Producto a alquilar para mostrarlo 
* en el carrito de compras o el checkout (pagina de pago).
* File: wcai_process_alquiler_data.php
*/

  defined( 'ABSPATH' ) || exit;
 
  $product = wc_get_product( $id_product );
  // print_r($product); 
  
  if (!wcai_is_alquilable( $id_product )) return;

  global $precio_alquiler, $meses_alquiler, $wcai_date_from, $wcai_date_to, $cart_item_data, $nombre_producto_alquiler ;
  $precio_alquiler = $product->get_meta( '_precio_alquiler', true );
  $periodo_alquiler = $product->get_meta( '_periodo_alquiler', true );
/*
  if ($periodo_alquiler == 0) $meses_alquiler = 12;
  if ($periodo_alquiler == 1) $meses_alquiler = 18;
  if ($periodo_alquiler == 2) $meses_alquiler = 24;
*/

  $meses_alquiler = $periodo_alquiler;

  $vencimiento = new DateTime(+$meses_alquiler.' month');
  $wcai_date_to = $vencimiento->format('d/m/Y');
  
  //Le cambia el nombre al producto
  $nombre_producto_alquiler = $product->get_name().' Alquilado Por '.$meses_alquiler.' Meses. ';
  $nombre_producto_alquiler .= ' CUOTA INICIAL - ';
  $nombre_producto_alquiler .= ' <br/>Comienza: '.$wcai_date_from;
  $nombre_producto_alquiler .= ' <br/>Finaliza: '.$wcai_date_to;
  //echo "<br/><br/><br/>NUEVO NOMBRE: ".$nombre_producto_alquiler.'<br/><br/><br/>';//die;

  //1.- VACIA EL CARRITO DE COMPRAS PARA ASEGURAR INICIO DESDE CERO
        WC()->cart->empty_cart();
        
  //2.- CAMBIA EL PRECIO DE VENTA DEL PRODUCTO POR EL PRECIO DE ALQUILER
  //    CAMBIA EL NOMBRE DEL PRODUCTO 
        add_filter( 'woocommerce_add_cart_item', 'add_cart_item', 99, 1 );    
		 
  //3.- AGREGA EL PRODUCTO O INSTRUMENTO A ALQUILAR
	    WC()->cart->add_to_cart( $product->get_id(), 1, 0, 0, $cart_item_data );

/// FOR  DEBUGGING PURPOSE 
//		 foreach ( WC()->cart->get_cart() as  $cart_item_key => $cart_item ) {
		     
		  /*
			echo '<br/>KEY : '. $cart_item ['key'];
		    echo '<br/>CANTIDAD : '. $cart_item ['quantity'] ;
		    echo '<br/>ID PRODUCTO: '.$cart_item ['product_id'] ;
		    echo '<br/>SUBTOTAL: '.$cart_item ['line_subtotal'];
		    echo '<br/>SUBTOTAL TAX: '.$cart_item ['line_subtotal_tax'];
		    echo '<br/>TOTAL: '.$cart_item ['line_total'] ;
            echo '<br/>TOTAL TAX: '.$cart_item ['line_tax'] ;
            echo '<br/>TOTAL PRODUCTOS: '.WC()->cart->get_totals()['cart_contents_total'];  /// este funciono
            echo '<br/>TOTAL IMPUESTOS: '.WC()->cart->get_totals()['cart_contents_tax'];  /// este funciono
            echo '<br/>TOTAL DEL CARRITO: '.WC()->cart->get_totals()['total'].'<br/>';
            
            echo '<br/>**********************************************************<br/>';
            
            	 // print_r($cart_item['data']);
            
            echo '<br/>TOTAL DEL CARRITO <br/>';
            print_r(WC()->cart->get_totals());
            */
//			}
		
//	 	 echo '<br/><br/>DATOS modificados de prueba DEL CARRITO DE COMPRAS:<br/><br/>'; 
//		 print_r(WC()->cart->get_cart());  
	

