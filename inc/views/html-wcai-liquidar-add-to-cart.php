<?php
/**
 * Created by OnDesarrollo
 * Plantilla Vista de la liquidacion del alquiler del producto en el carrito de compras.
 * File: html-wcai-liquidar-add-to-cart.php
 */

 defined( 'ABSPATH' ) || exit;
 
 // Controla si abre en otra ventana o en la misma
  $wcai_target = '_self';
  $wcai_open_new_window  = get_option('wcai_open_new_window') ;
  if ($wcai_open_new_window =='yes' ) $wcai_target = '_blank';
 
 global $nombre_producto_alquiler;
 wc_print_notices();
 echo '<div class="woocommerce">';
 do_action('woocommerce_before_cart'); 
 
?>
<div id="content">
	<div class="eightcol column">
		<form id="liquidar_alquiler_cart_form" class="woocommerce-cart-form" name="liquidar_alquiler_cart_form" action="<?php echo SITE_URL . '/facturar-liquidar-alquiler';?>" method="post" accept-charset="utf-8"  target="<?php echo $wcai_target; ?>">
            <input type="hidden" name="_wcai_nonce" class="wcai_nonce" value="<?php echo wp_create_nonce( 'proceder_alquiler' ); ?>">
	        <input type="hidden" name="site_url" value="<?php echo site_url(); ?>">
            <input type="hidden" name="site_dir" value="<?php echo SITE_HOME_DIR ; ?>">
	        <input type="hidden" name="id_product" id="id_product" value="<?php echo $product->get_id() ; ?>">

			<?php do_action('woocommerce_before_cart_table'); ?>
			<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0" border="1">
				<thead>
					<tr>
						
						<th class="product-thumbnail">&nbsp;</th>
						<th class="product-name"><?php _e('Product', 'woocommerce'); ?></th>
						<th class="product-price"><?php _e('Price', 'woocommerce'); ?></th>
						<th class="product-quantity"><?php _e('Quantity', 'woocommerce'); ?></th>
						<th class="product-subtotal"><?php _e('Total', 'woocommerce'); ?></th>
						 <!--- <th class="product-remove">&nbsp;</th> -->
					</tr>
				</thead>
				<tbody>
					<?php do_action('woocommerce_before_cart_contents'); ?>
					<?php
					foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item){
						$_product=apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key); //print_r($_product);//die;
						$product_id=apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

						if($_product && $_product->exists()&& $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)){
						?>
						<tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
							<td class="product-thumbnail">
								<?php
								$thumbnail=apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

								if(! $_product->is_visible())
									echo $thumbnail;
								else
									printf('<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail);
								?>
							</td>
							<td class="product-name" data-title="Producto">
								<?php  	
								if(! $_product->is_visible())
									echo apply_filters('woocommerce_cart_item_name', $nombre_liquidacion, $cart_item, $cart_item_key);
								 else 
									echo apply_filters('woocommerce_cart_item_name', $nombre_liquidacion, $cart_item, $cart_item_key);

								if($_product->backorders_require_notification()&& $_product->is_on_backorder($cart_item['quantity']))
									echo '<p class="backorder_notification">'.__('Available on backorder', 'woocommerce').'</p>';
								?>
							</td>
							<td class="product-price" data-title="Precio">
								<?php
								echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
								?>
							</td>
							<td class="product-quantity" data-title="Cantidad">
								<?php
								
									$product_quantity=sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
								

								echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key);
								?>
							</td>
							<td class="product-subtotal" data-title="Subtotal">
								<?php
								echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
								?>
							</td>
						</tr>
						<?php
						}
					}

					do_action('woocommerce_cart_contents');
					?>
					<tr>
						<td colspan="6" class="actions">
							<?php if(WC()->cart->coupons_enabled()){ ?>
								<div class="coupon">
									<label for="coupon_code"><?php _e('Coupon', 'woocommerce'); ?>:</label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php _e('Coupon Code', 'woocommerce'); ?>" /> 
									<input type="submit" class="button secondary" name="apply_coupon" value="<?php _e('Apply', 'woocommerce'); ?>" />
									<?php do_action('woocommerce_cart_coupon'); ?>
								</div>
							<?php }  ?>
							
							<input type="hidden" name="nro_orden" id="nro_orden" value="<?php echo $nro_orden;?>" />
                            <input type="hidden" name="usuario_actual" id="usuario_actual" value="<?php echo $usuario_actual;?>" />
                            <input type="hidden" name="pago_liquidacion" id="pago_liquidacion" value="<?php echo $pago_liquidacion;?>" />
                            <input type="hidden" name="nombre_liquidacion" id="nombre_liquidacion" value="<?php echo $nombre_liquidacion;?>" />
							
						    <!--- CHECKOUT PAGE (PAGINA DE PAGO) EXCLUSIVA PARA LA LIQUIDACION DEL ALQUILER DE LOS INSTRUMENTOS O PRODUCTOS  -->
						    <button type="submit" name="_action" id="_action" value="proceder_alquiler" class="button">Liquidar AHORA</button>
							<?php wp_nonce_field('woocommerce-cart'); ?>
						</td>
					</tr>
					<?php do_action('woocommerce_after_cart_contents'); ?>
				</tbody>
			</table>
			<?php do_action('woocommerce_after_cart_table'); ?>
		</form>
	</div>
	<div class="fourcol column last">
		<?php do_action('woocommerce_cart_collaterals'); ?>		
	</div>
</div>
<?php 

 do_action('woocommerce_after_cart'); 
 
 
?>

<style>
/* ALGUNOS AJUSTES DE ESTILO  */
/*.footer-wrap { position: relative ; bottom: 0;}*/
.attachment-woocommerce_thumbnail {
    width: 32px;
    box-shadow: none;
}
</style>

<?php 
echo '<script>
    
    //Dispara el proceso de Ocultar el boton del checkout de la compra
    // porque algunos temas lo muestran
   setTimeout(EncondePagarCompra(),3000);
   
   function EncondePagarCompra() {
        //Oculta el boton del checkout de la compra, por si acaso el tema lo muestra
        var boton_de_compra = document.querySelectorAll(".wc-proceed-to-checkout")[0];
        boton_de_compra.style.display="none"; /// oculta el boton
   }
   
   linea_envio = document.querySelectorAll(".woocommerce-shipping-totals")[0];
   linea_envio.style.display ="none";
    
</script>';

do_action('woocommerce_after_main_content');
get_footer('shop');

