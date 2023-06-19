<?php
/**
 * Admin Products Page for Woocommerce Alquiler De Instrumentos
 * File: wcai_admin_product.php
 */

 defined( 'ABSPATH' ) || exit;
 
// Hooks para agregar la opcion de alquilar a los tipos de productos
    add_action( 'product_type_options', 'agrega_opcion_alquilar', 10 );
    add_filter( 'woocommerce_product_data_tabs', 'agrega_alquiler_tab', 10 );
	add_action( 'woocommerce_product_data_panels', 'alquiler_data_panel', 10 );
	
	$allowed_types = allowed_types();
	if ( $allowed_types ) foreach ( $allowed_types as $type ) {
            add_action( 'woocommerce_process_product_meta_' . $type, 'save_alquiler_product_options', 10 );
        }
	
 

 /**
    * Agrega un checkbox  a la pagina de producto del Admin para indicar si el producto es alquilable.
    * @param array $product_type_options
    * @return array $product_type_options
    **/
    function agrega_opcion_alquilar( $product_type_options ) {
        global $product_object;
        $es_alquilable = is_a( $product_object, 'WC_Product' ) ? $product_object->get_meta( '_alquilable', true ) : '';	
        $show = array();
		$allowed_types = allowed_types();
        if ( $allowed_types ) foreach ( $allowed_types as $type ) {
            $show[] = 'show_if_' . $type;
        }

        $product_type_options['wcai_alquilable'] = array(
            'id'            => '_alquilable',
            'wrapper_class' => implode( ' ', $show ),
            'label'         => 'Alquilable',
            'description'   => 'Instrumentos Alquilables pueden ser alquilados por un tiempo determinado',
            'default'       => $es_alquilable === 'yes' ? 'yes' : 'no'
        );
        return $product_type_options;
    }
	
	
 /**
    * Agrega la pestana del alquiler a la pagina de producto del Admin para las opciones del alquiler.
    * @param array $product_data_tabs
    * @return array $product_data_tabs
   **/
    function agrega_alquiler_tab( $product_data_tabs ) {
        $product_data_tabs['alquiler'] = array(
                'label'    => 'Alquiler',
                'target'   => 'alquiler_product_data',
                'class'    => array( 'alquiler_options','show_if_alquilable','hide_if_external' ),
                'priority' => 15
        );
        return $product_data_tabs;

    }

/**
 * Agrega las opciones a la pestana del alquiler.
 **/
    function alquiler_data_panel() {
        global $post;
        $product = wc_get_product( $post->ID );
        $product_type = $product->get_type();
        include( WCAI_PLUGIN_PATH .'inc/views/html-wcai-product-options.php' );
    }

/**
 * Almacena el valor del checkbox value y las opciones del alquiler del producto.
 * @param int $post_id
**/
    function save_alquiler_product_options( $post_id ) {

        $alquiler_data = array(
            'alquilable'          => isset( $_POST['_alquilable'] ) ? 'yes' : 'no',
            'precio_alquiler'     => isset( $_POST['_precio_alquiler'] )  ? $_POST['_precio_alquiler'] : '',
            'periodo_alquiler'    => isset( $_POST['_periodo_alquiler'] ) ? $_POST['_periodo_alquiler'] : ''
         
        );

        ///wcai_save_alquiler_product_options( $post_id, $alquiler_data );
		$is_alquilable  = sanitize_checkbox( $alquiler_data['alquilable'] );
		
		foreach ( $alquiler_data as $name => $value ) {
        
          switch ( $value ) {
            case '' :
                ${$name} = '';
            break;

            case 0 :
                ${$name} = '0';
            break;

            default :
                ${$name} = sanitize_text_field( $value );
            break;
          }
        
        }
		
		if ( $alquiler_data['precio_alquiler'] < 0 ) {
             \WC_Admin_Meta_Boxes::add_error( 'El Precio Del Alquiler NO PUEDE SER NEGATIVO.' );
         } else {
              update_post_meta( $post_id, '_precio_alquiler', $alquiler_data['precio_alquiler'] );
              update_post_meta( $post_id, '_periodo_alquiler', $alquiler_data['periodo_alquiler']);
			  update_post_meta( $post_id, '_alquilable', $is_alquilable );
        }

    }

