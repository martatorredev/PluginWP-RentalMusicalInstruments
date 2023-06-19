<?php
/**
 * Created by OnDesarrollo
 * Plantilla Vista del PAGO POR LA LIQUIDACION DEL alquiler del producto, despues del checkout o pagina de FACTURAR.
 * si el pago es EXITOSO O APROBADO, ACTUALIZA el plan de pagos futuros del alquiler y marca la orden como "PROCESANDO". 
 * File: html-wcai-order-liquidar-pay-page.php
 */

 defined( 'ABSPATH' ) || exit;

 global $woocommerce, $meses_alquiler, $wcai_date_from, $wcai_date_to, $wcai_id_product, $wcai_nro_orden ; 
 global  $wcai_pago_liquidacion, $wcai_nombre_liquidacion,  $wcai_nro_orden_liquidar;
/* 
 echo '<BR><BR>ESTOY EN HTML-wcai-order-liquidar-pay-page.php CON wcai_id_product = '.$wcai_id_product.' CON wcai_nro_orden = '.$wcai_nro_orden.'<br>';
 echo ' CON wcai_pago_liquidacion = '.$wcai_pago_liquidacion.' CON wcai_nombre_liquidacion = '.$wcai_nombre_liquidacion.'<br><br>';
*/ 
?>
 <div class="woocommerce"><div class="woocommerce-notices-wrapper"></div>

<!---- /////////// INICIO DEL CODIGO MODIFICADO TOMADO DE LA PAGINA woocommerce/checkout/thankyou.php /////////// --->

<div class="woocommerce-order">

	<?php
	if ( $order ) :

	    if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

				<li class="woocommerce-order-overview__order order">
					<?php esc_html_e( 'Order number:', 'woocommerce' ); ?>
					<strong><?php echo sanitize_text_field($order->get_order_number()); ?></strong>
				</li>

				<li class="woocommerce-order-overview__date date">
					<?php esc_html_e( 'Date:', 'woocommerce' ); ?>
					<strong><?php echo sanitize_text_field(wc_format_datetime( $order->get_date_created())); ?></strong>
				</li>

				<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
					<li class="woocommerce-order-overview__email email">
						<?php esc_html_e( 'Email:', 'woocommerce' ); ?>
						<strong><?php echo sanitize_text_field($order->get_billing_email()); ?></strong>
					</li>
				<?php endif; ?>

				<li class="woocommerce-order-overview__total total">
					<?php esc_html_e( 'Total:', 'woocommerce' ); ?>
					<strong><?php echo sanitize_text_field($order->get_formatted_order_total()); ?></strong>
				</li>

				<?php if ( $order->get_payment_method_title() ) : ?>
					<li class="woocommerce-order-overview__payment-method method">
						<?php esc_html_e( 'Payment method:', 'woocommerce' ); ?>
						<strong><?php echo sanitize_text_field(wp_kses_post( $order->get_payment_method_title()) ); ?></strong>
					</li>
				<?php endif; ?>

			</ul>

		<?php endif; ?>

	<?php else : ?>

	<?php endif; ?>

</div>
<!---- /////////// FINAL DEL CODIGO MODIFICADO TOMADO DE LA PAGINA woocommerce/checkout/thankyou.php /////////// --->

<?php 
// Crea la instancia para el pago por Realex
$realex_payment = new WC_Gateway_Realex_Redirect();

///echo '<BR>ESTOY EN HTML-wcai-order-pay_page.php, usa este boton para probar traer los datos de la orden '.$wcai_nro_orden.'<BR><BR>';

///do_action( 'woocommerce_payment_complete', $wcai_nro_orden );


// Si es hosted-pay-form (en la misma página)
 if ($realex_payment->get_form_type() == 'iframe') {
     
     // Desplega en pantalla el formulario para el pago por Realex
     $realex_payment->generate_pay_form( $order );

     
     
 }

