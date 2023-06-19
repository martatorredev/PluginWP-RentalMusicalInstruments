<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div id="alquiler_product_data" class="panel woocommerce_options_panel">
    <div class="options_group show_if_alquilable">
	        <?php 
			  woocommerce_wp_text_input( array(
                'id'                => 'precio_alquiler',
                'class'             => 'precio_alquiler',
                'name'              => '_precio_alquiler',
                'label'             => 'Precio Alquiler ('.get_woocommerce_currency_symbol().')',
                'desc_tip'          => 'true',
                'description'       => 'Coloque el Costo o Precio MENSUAL del Alquiler de este Instrumento',
                'value'             => ! empty( $product->get_meta( '_precio_alquiler' ) ) || $product->get_meta( '_precio_alquiler' ) === '0' ? $product->get_meta( '_precio_alquiler' ) : '',
                'placeholder'       => 'Precio MENSUAL del Alquiler',
                'type'              => 'text',
				'data_type'         => 'price'
                 ) );
	/*
	//// INICIALMENTE ERAN TRES OPCIONES DE ALQUILER
             $periodo_options = array('12 Meses.','18 Meses.','24 Meses.');	
			 
			 woocommerce_wp_radio( array(
                'id'                => 'periodo_alquiler',
                'class'             => 'periodo_alquiler',
                'name'              => '_periodo_alquiler',
                'label'             => 'Periodo Alquiler',
                'desc_tip'          => 'true',
                'description'       => 'Seleccione la cantidad de MESES que puede ALQUILAR este Instrumento',
                'value'             => ! empty( $product->get_meta( '_periodo_alquiler' ) ) || $product->get_meta( '_periodo_alquiler' ) === '0' ? $product->get_meta( '_periodo_alquiler' ) : '',
                'options'           => $periodo_options // Options for radio inputs, array
                 ) ); 
	*/
	
	
	         woocommerce_wp_text_input( array(
                'id'                => 'periodo_alquiler',
                'class'             => 'periodo_alquiler',
                'name'              => '_periodo_alquiler',
                'label'             => 'Periodo Alquiler',
                'desc_tip'          => 'true',
                'description'       => 'Coloque la cantidad de MESES que puede ALQUILAR este Instrumento',
                'value'             => ! empty( $product->get_meta( '_periodo_alquiler' ) ) || $product->get_meta( '_periodo_alquiler' ) === '0' ? $product->get_meta( '_periodo_alquiler' ) : '',
                'type'              => 'number',
				'data_type'         => 'number',
				'custom_attributes' => array('step' 	=> 'any',
									         'min'	=> '0',
									         'max'	=> '36'
								       ) 
                 ) );
	
            ?>       

    </div>

    <?php do_action( 'wcai_after_alquiler_options', $product ); ?>
    <?php do_action( 'wcai_after_' . $product_type . '_alquiler_options', $product ); ?>

</div>