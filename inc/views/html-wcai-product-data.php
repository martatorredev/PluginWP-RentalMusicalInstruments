<?php
/**
 * Created by OnDesarrollo
 * Plantilla Vista de los datos de alquiler del producto en la pagina del producto
 * File: html-wcai-product-data.php
 */

  defined( 'ABSPATH' ) || exit;
  
  // Controla si abre en otra ventana o en la misma
  $wcai_target = '_self';
  $wcai_open_new_window  = get_option('wcai_open_new_window') ;
  if ($wcai_open_new_window =='yes' ) $wcai_target = '_blank';
  
  // Controla si Oculta o Muestra el botón de comprar
  $wcai_show_buy_button  = get_option('wcai_show_buy_button') ;
  if ($wcai_show_buy_button =='no' ) {
      // Oculta el el botón de comprar por JavaScript
      echo '<script>
               if (document.querySelectorAll(".cart")[0])
                  document.querySelectorAll(".cart")[0].style.display="none";
            </script>';
  }
  
  do_action( 'alquiler_before_details', $product ); 
?>
"></p><div class="alquiler_product_data_wrap">
  <form id="alquiler_form" name="alquiler_form" action="<?php echo SITE_URL . '/carrito-alquiler/';?>" method="post" accept-charset="utf-8"  target="<?php echo $wcai_target; ?>">
    <!--<p class="form-row form-row-wide wcai_puntitos">......................................................</p>-->
	<h1 class="product_title entry-title wcai_titulo">Precio Alquiler</h1>
	<p class="wcai_price">
	  <span class="woocommerce-Price-amount amount precio_alquiler">
	    <?php 
	         global $precio_alquiler;
		      $precio_alquiler = $product->get_meta( '_precio_alquiler', true );
			  $periodo_alquiler = $product->get_meta( '_periodo_alquiler', true );
			  /*
			  if ($periodo_alquiler == 0) $meses_alquiler = 12;
			  if ($periodo_alquiler == 1) $meses_alquiler = 18;
			  if ($periodo_alquiler == 2) $meses_alquiler = 24;
			  */
			  $meses_alquiler = $periodo_alquiler;
			  
		      echo esc_attr( $precio_alquiler ). '<span class="woocommerce-Price-currencySymbol"> &euro;</span>';    
		?></span></p>
	<p class="woocommerce-product-details__short-description wcai_cuota">Cuota Mensual con IVA incluido</p>
	<p class="form-row form-row-wide periodo_alquiler">Meses de Alquiler <?php echo esc_attr( $meses_alquiler ); ?></p>
	
    <input type="hidden" name="_wcai_nonce" class="wcai_nonce" value="<?php echo wp_create_nonce( 'alquiler_data' ); ?>">
	<input type="hidden" name="site_url" value="<?php echo site_url(); ?>">
    <input type="hidden" name="site_dir" value="<?php echo SITE_HOME_DIR ; ?>">
	<input type="hidden" name="id_product" id="id_product" value="<?php echo $product->get_id() ; ?>">
	
<button type="submit" name="_action" id="_action" value="alquilar_ahora" class="button">Alquílame</button></form>

	<p class="form-row form-row-wide wcai_puntitos">............................................................</p>
	<h1 class="product_title entry-title wcai_titulo">Precio de Venta</h1>
	<p class="price<?php do_action( 'alquiler_after_details', $product ); 

