<?php
/**
 * Created by OnDesarrollo
 * Plantilla Vista del alquiler del producto en la pagina del checkout o pagina de pago.
 * File: html-wcai-checkout-page.php
 */

 defined( 'ABSPATH' ) || exit;
 
 // Controla si abre en otra ventana o en la misma
  $wcai_target = '_self';
  $wcai_open_new_window  = get_option('wcai_open_new_window') ;
  if ($wcai_open_new_window =='yes' ) $wcai_target = '_blank';

 global $woocommerce, $meses_alquiler, $wcai_date_from, $wcai_date_to, $nombre_producto_alquiler ; 
 $checkout = WC()->checkout();

 /**
 * Simple checkout field addition for billing data.
 * 
 * @param  array $fields List of existing billing fields.
 * @return array         List of modified billing fields.
 */
function wcai_add_checkout_fields($fields) { 
    //Define campo para direccion Linea 1
    $fields['billing_address_3'] = array(
        'label'        => 'Dirección de facturación, Línea 1',
        'type'         => 'text',
        'class'        => array( 'form-row-wide' ),
        'priority'     => 35,
        'required'     => true,
    );
    
    //Define campo para direccion Linea 2
    $fields['billing_address_4'] = array(
        'label'        => 'Dirección de facturación, Línea 2',
        'type'         => 'text',
        'class'        => array( 'form-row-wide' ),
        'priority'     => 36,
        'required'     => true,
    );
    
    //Define campo para la ciudad
    $fields['billing_city_2'] = array(
        'label'        => 'Ciudad de facturación',
        'type'         => 'text',
        'class'        => array( 'form-row-wide' ),
        'priority'     => 37,
        'required'     => true,
    );

    return $fields;
}

/**
 * Simple checkout field addition for shipping data.
 * 
 * @param  array $fields List of existing billing fields.
 * @return array         List of modified billing fields.
 */
function wcai_add_checkout_shipping_fields($fields) { 
    
    //Define campo para direccion de envio Linea 1
    $fields['shipping_address_3'] = array(
        'label'        => 'Dirección de envio, Línea 1',
        'type'         => 'text',
        'class'        => array( 'form-row-wide' ),
        'priority'     => 35,
        'required'     => true,
    );
    
    //Define campo para direccion de envio Linea 2
    $fields['shipping_address_4'] = array(
        'label'        => 'Dirección de envio, Línea 2',
        'type'         => 'text',
        'class'        => array( 'form-row-wide' ),
        'priority'     => 36,
        'required'     => true,
    );
    
    //Define campo para la ciudad  de envio 
    $fields['shipping_city_2'] = array(
        'label'        => 'Ciudad de envio',
        'type'         => 'text',
        'class'        => array( 'form-row-wide' ),
        'priority'     => 37,
        'required'     => true,
    );

    return $fields;
}

//Agrega Los Campos de direccion de envio ALGUNOS TEMAS NECESITAN ESTO
//add_filter( 'woocommerce_shipping_fields', 'wcai_add_checkout_shipping_fields');


?>
 <div class="woocommerce"><div class="woocommerce-notices-wrapper"></div>
 

<?php
 wc_print_notices();
 do_action('woocommerce_before_checkout_form', $checkout);
 // If checkout registration is disabled and not logged in, the user cannot checkout.
 if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>
<form name="checkout" id="checkout" method="post" class="checkout woocommerce-checkout" action="" 
      enctype="multipart/form-data" onSubmit="return (validar_checout57(checkout));" target="<?php echo $wcai_target; ?>">

	<?php if(sizeof($checkout->checkout_fields ) > 0) : ?>
	<div class="column fourcol">
		<div class="billing-details">
			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
			<?php do_action( 'woocommerce_checkout_billing' ); ?>
			<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
			<?php do_action('woocommerce_before_order_notes', $checkout); ?>
			<?php do_action('woocommerce_after_order_notes', $checkout); ?>
			<?php //if (woocommerce_get_page_id('terms')>0) : ?>
		<!--	<p class="form-row terms">
				<input type="checkbox" class="input-checkbox" name="terms" <?php //if (isset($_POST['terms'])) echo 'checked="checked"'; ?> id="terms" />
				<label for="terms" class="checkbox"><?php _e('I accept the', 'academy'); ?> <a href="<?php //echo esc_url( get_permalink(woocommerce_get_page_id('terms')) ); ?>" target="_blank"><?php _e('terms &amp; conditions', 'academy'); ?></a></label>
			</p> -->
			<?php //endif; ?>
			<input id="shiptobilling-checkbox" type="hidden" name="shiptobilling" value="1" />
		</div>
	</div>
	<?php endif; ?>
	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
	<div id="order_review" class="woocommerce-checkout-review-order fivecol column last">
		<?php //do_action('woocommerce_checkout_order_review'); ?>
	<!--- AQUI COMIENZA EL CUADRO DEL PEDIDO O PRODUCTO A ALQUILAR EXCLUSIVO PARA ESTE PLUGIN  -->
		<table class="shop_table woocommerce-checkout-review-order-table">
	      <thead>
		   <tr>
			<th class="product-name" style="width:50%"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-total"><?php _e( 'Total', 'woocommerce' ); ?></th>
		   </tr>
	     </thead>
	     <tfoot>
		   <tr class="cart-subtotal">
			<th><?php _e( 'Cart Subtotal', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		  </tr>
		<?php foreach ( WC()->cart->get_coupons( 'cart' ) as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( $code ); ?>">
				<th><?php _e( 'Coupon:', 'woocommerce' ); ?> <?php echo esc_html( $code ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>
		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
			<?php wc_cart_totals_shipping_html(); ?>
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		<?php endif; ?>
		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>
		<?php if ( WC()->cart->tax_display_cart === 'excl' ) : ?>
			<?php if ( get_option( 'woocommerce_tax_total_display' ) === 'itemized' ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
						<th><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php echo wc_price( WC()->cart->get_taxes_total() ); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>
		<?php foreach ( WC()->cart->get_coupons( 'order' ) as $code => $coupon ) : ?>
			<tr class="order-discount coupon-<?php echo esc_attr( $code ); ?>">
				<th><?php _e( 'Coupon:', 'woocommerce' ); ?> <?php echo esc_html( $code ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>
		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
		<tr class="order-total">
			<th><?php _e( 'Order Total', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>
		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	   </tfoot>
	   <tbody>
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product=apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	//		print_r($_product);
			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
			?>
			<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<td class="product-name">
					<?php 
						echo apply_filters( 'woocommerce_cart_item_name',$nombre_producto_alquiler, $cart_item, $cart_item_key ); 
					         
					    echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . '</strong>', $cart_item, $cart_item_key ); ?>
					<?php //echo WC()->cart->get_item_data( $cart_item ); ?>
				</td>
				<td class="product-total">
					<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ).' /Mensual', $cart_item, $cart_item_key ); ?>
				</td>
			</tr>
			<?php
			}
		}
		do_action( 'woocommerce_review_order_after_cart_contents' ); ?>	

	   </tbody>
    </table>
<!--- AQUI TERMINA EL CUADRO DEL PEDIDO O PRODUCTO A ALQUILAR EXCLUSIVO PARA ESTE PLUGIN  -->		
	 <?php  woocommerce_checkout_payment(); 	
	 
	 // Obtiene los subtotales y totales del carrito de alquiler
	 $totales_alquiler = WC()->cart->get_totals();     ///var_dump($totales_alquiler);
	 $subtotal_alquiler = $totales_alquiler['subtotal'];
	 $subtotal_tax = $totales_alquiler['subtotal_tax'];
	 $total_alquiler = $subtotal_alquiler + $subtotal_tax;  
	 $shipping_total = $totales_alquiler['shipping_total'];
	 $shipping_total_tax = $totales_alquiler['shipping_taxes'][1];
	 $cart_contents_total = $totales_alquiler['cart_contents_total'];
	 $cart_contents_tax = $totales_alquiler['cart_contents_tax'];
	 $total_cart = $totales_alquiler['total'];
	 $total_cart_tax = $totales_alquiler['total_tax'];
	 
	 ?>	

<!--- unos campos de facturacion adicionales ALGUNOS TEMAS NECESITAN ESTO -->
<!---      <input id="billing_address_1" type="hidden" name="billing_address_1" value="" />
      <input id="billing_address_2" type="hidden" name="billing_address_2" value="" />
      <input id="billing_city" type="hidden" name="billing_city" value="" /> -->
      
<!--- unos campos de envio adicionales ALGUNOS TEMAS NECESITAN ESTO -->
<!---      <input id="shipping_address_1" type="hidden" name="shipping_address_1" value="" />
      <input id="shipping_address_2" type="hidden" name="shipping_address_2" value="" />
      <input id="shipping_city" type="hidden" name="shipping_city" value="" /> 
-->
<!--- Otros datos adicionales -->      
      <input type="hidden" name="site_url" value="<?php echo site_url(); ?>" />
      <input type="hidden" name="site_dir" value="<?php echo SITE_HOME_DIR; ?>" />
      <input type="hidden" name="id_product" value='<?php echo $id_product ; ?>' />
      
      <input type="hidden" name="subtotal" value='<?php echo $subtotal_alquiler ; ?>' />
      <input type="hidden" name="total_tax" value='<?php echo $subtotal_tax ; ?>' />
      <input type="hidden" name="total" value='<?php echo $total_alquiler ; ?>' />
      <input type="hidden" name="shipping_total" value='<?php echo  $shipping_total; ?>' />
      <input type="hidden" name="shipping_total_tax" value='<?php echo  $shipping_total_tax; ?>' />
      <input type="hidden" name="cart_contents_total" value='<?php echo $cart_contents_total; ?>' />
	  <input type="hidden" name="cart_contents_tax" value='<?php echo $cart_contents_tax; ?>' />
	  <input type="hidden" name="total_cart" value='<?php echo $total_cart; ?>' />
	  <input type="hidden" name="total_cart_tax" value='<?php echo $total_cart_tax; ?>' /> 
      
      <input type="hidden" name="nombre_producto_alquiler" value='<?php echo  $_product->get_title(); ?>' />
      <input type="hidden" name="meses_alquiler" value='<?php echo  $meses_alquiler; ?>' />
      <input type="hidden" name="wcai_date_from" value='<?php echo  $wcai_date_from ; ?>' />
      <input type="hidden" name="wcai_date_to" value='<?php echo  $wcai_date_to ; ?>' />
      <input type="hidden" name="wcai_cart_subtotal" value='<?php echo   WC()->cart->get_displayed_subtotal() ; ?>' />
        
      
      <?php //print_r( $totales_alquiler ); ?>'

      
	</div>
	<div class="clear"></div>
</form>
<?php  
do_action('woocommerce_after_checkout_form', $checkout);

?>
<style> 
.attachment-woocommerce_thumbnail {
    width: 32px;
    box-shadow: none;
}

.footer-wrap {
    position: relative;
}

.woocommerce form.checkout_coupon, .woocommerce form.login, .woocommerce form.register {
    border: 1px solid #d3ced2;
    padding: 20px;
    margin: 2em 0;
    text-align: left;
    border-radius: 5px;
}
.datos_invalidos {
    border-color: red !important;
    border-width: 2px !important;
    background-color: #ffffee !important;
    border: solid;
}
.datos_validos {
    border-color: #6dc22e !important;
    border-width: 1px !important;
    background-color: #fff !important;
    border: solid;
}
.con_borde {
    border: solid ;
}
.sin_borde {
    border: none ;
}

</style>

<link rel="stylesheet" id="select2-css"  href="<?php echo SITE_URL;?>/wp-content/plugins/woocommerce/assets/css/select2.css?ver=4.0.1" type="text/css" media="all" />
<script type="text/javascript" src="<?php echo SITE_URL;?>/wp-content/plugins/woocommerce/assets/js/selectWoo/selectWoo.full.min.js?ver=1.0.6"></script>
<script type="text/javascript">
/* <![CDATA[ */
var wc_country_select_params = {"countries":"{\"AF\":[],\"AO\":{\"BGO\":\"Bengo\",\"BLU\":\"Benguela\",\"BIE\":\"Bi\\u00e9\",\"CAB\":\"Cabinda\",\"CNN\":\"Cunene\",\"HUA\":\"Huambo\",\"HUI\":\"Hu\\u00edla\",\"CCU\":\"Kuando Kubango\",\"CNO\":\"Kwanza-Norte\",\"CUS\":\"Kwanza-Sul\",\"LUA\":\"Luanda\",\"LNO\":\"Lunda-Norte\",\"LSU\":\"Lunda-Sul\",\"MAL\":\"Malanje\",\"MOX\":\"Moxico\",\"NAM\":\"Namibe\",\"UIG\":\"U\\u00edge\",\"ZAI\":\"Zaire\"},\"AR\":{\"C\":\"Ciudad Aut\\u00f3noma de Buenos Aires\",\"B\":\"Buenos Aires\",\"K\":\"Catamarca\",\"H\":\"Chaco\",\"U\":\"Chubut\",\"X\":\"C\\u00f3rdoba\",\"W\":\"Corrientes\",\"E\":\"Entre R\\u00edos\",\"P\":\"Formosa\",\"Y\":\"Jujuy\",\"L\":\"La Pampa\",\"F\":\"La Rioja\",\"M\":\"Mendoza\",\"N\":\"Misiones\",\"Q\":\"Neuqu\\u00e9n\",\"R\":\"R\\u00edo Negro\",\"A\":\"Salta\",\"J\":\"San Juan\",\"D\":\"San Luis\",\"Z\":\"Santa Cruz\",\"S\":\"Santa Fe\",\"G\":\"Santiago del Estero\",\"V\":\"Tierra del Fuego\",\"T\":\"Tucum\u00e1n\"},\"AT\":[],\"AU\":{\"ACT\":\"Australia Central\",\"NSW\":\"Nueva Gales del Sur\",\"NT\":\"Northern Territory\",\"QLD\":\"Queensland\",\"SA\":\"South Australia\",\"TAS\":\"Tasmania\",\"VIC\":\"Victoria\",\"WA\":\"Western Australia\"},\"AX\":[],\"BD\":{\"BD-05\":\"Bagerhat\",\"BD-01\":\"Bandarban\",\"BD-02\":\"Barguna\",\"BD-06\":\"Barishal\",\"BD-07\":\"Bhola\",\"BD-03\":\"Bogura\",\"BD-04\":\"Brahmanbaria\",\"BD-09\":\"Chandpur\",\"BD-10\":\"Chattogram\",\"BD-12\":\"Chuadanga\",\"BD-11\":\"Cox's Bazar\",\"BD-08\":\"Cumilla\",\"BD-13\":\"Dhaka\",\"BD-14\":\"Dinajpur\",\"BD-15\":\"Faridpur \",\"BD-16\":\"Feni\",\"BD-19\":\"Gaibandha\",\"BD-18\":\"Gazipur\",\"BD-17\":\"Gopalganj\",\"BD-20\":\"Habiganj\",\"BD-21\":\"Jamalpur\",\"BD-22\":\"Jashore\",\"BD-25\":\"Jhalokati\",\"BD-23\":\"Jhenaidah\",\"BD-24\":\"Joypurhat\",\"BD-29\":\"Khagrachhari\",\"BD-27\":\"Khulna\",\"BD-26\":\"Kishoreganj\",\"BD-28\":\"Kurigram\",\"BD-30\":\"Kushtia\",\"BD-31\":\"Lakshmipur\",\"BD-32\":\"Lalmonirhat\",\"BD-36\":\"Madaripur\",\"BD-37\":\"Magura\",\"BD-33\":\"Manikganj \",\"BD-39\":\"Meherpur\",\"BD-38\":\"Moulvibazar\",\"BD-35\":\"Munshiganj\",\"BD-34\":\"Mymensingh\",\"BD-48\":\"Naogaon\",\"BD-43\":\"Narail\",\"BD-40\":\"Narayanganj\",\"BD-42\":\"Narsingdi\",\"BD-44\":\"Natore\",\"BD-45\":\"Nawabganj\",\"BD-41\":\"Netrakona\",\"BD-46\":\"Nilphamari\",\"BD-47\":\"Noakhali\",\"BD-49\":\"Pabna\",\"BD-52\":\"Panchagarh\",\"BD-51\":\"Patuakhali\",\"BD-50\":\"Pirojpur\",\"BD-53\":\"Rajbari\",\"BD-54\":\"Rajshahi\",\"BD-56\":\"Rangamati\",\"BD-55\":\"Rangpur\",\"BD-58\":\"Satkhira\",\"BD-62\":\"Shariatpur\",\"BD-57\":\"Sherpur\",\"BD-59\":\"Sirajganj\",\"BD-61\":\"Sunamganj\",\"BD-60\":\"Sylhet\",\"BD-63\":\"Tangail\",\"BD-64\":\"Thakurgaon\"},\"BE\":[],\"BG\":{\"BG-01\":\"Blagoevgrad\",\"BG-02\":\"Burgas\",\"BG-08\":\"Dobrich\",\"BG-07\":\"Gabrovo\",\"BG-26\":\"Haskovo\",\"BG-09\":\"Kardzhali\",\"BG-10\":\"Kyustendil\",\"BG-11\":\"Lovech\",\"BG-12\":\"Montana\",\"BG-13\":\"Pazardzhik\",\"BG-14\":\"Pernik\",\"BG-15\":\"Pleven\",\"BG-16\":\"Plovdiv\",\"BG-17\":\"Razgrad\",\"BG-18\":\"Ruse\",\"BG-27\":\"Shumen\",\"BG-19\":\"Silistra\",\"BG-20\":\"Sliven\",\"BG-21\":\"Smolyan\",\"BG-23\":\"Sofia\",\"BG-22\":\"Sofia-Grad\",\"BG-24\":\"Stara Zagora\",\"BG-25\":\"Targovishte\",\"BG-03\":\"Varna\",\"BG-04\":\"Veliko Tarnovo\",\"BG-05\":\"Vidin\",\"BG-06\":\"Vratsa\",\"BG-28\":\"Yambol\"},\"BH\":[],\"BI\":[],\"BO\":{\"B\":\"Chuquisaca\",\"H\":\"Beni\",\"C\":\"Cochabamba\",\"L\":\"La Paz\",\"O\":\"Oruro\",\"N\":\"Pando\",\"P\":\"Potos\\u00ed\",\"S\":\"Santa Cruz\",\"T\":\"Tarija\"},\"BR\":{\"AC\":\"Acre\",\"AL\":\"Alagoas\",\"AP\":\"Amap\u00e1\",\"AM\":\"Amazonas\",\"BA\":\"Bahia\",\"CE\":\"Cear\u00e1\",\"DF\":\"Distrito Federal\",\"ES\":\"Esp\u00edrito Santo\",\"GO\":\"Goi\u00e1s\",\"MA\":\"Maranh\u00e3o\",\"MT\":\"Mato Grosso\",\"MS\":\"Mato Grosso del Sur\",\"MG\":\"Minas Gerais\",\"PA\":\"Par\u00e1\",\"PB\":\"Para\u00edba\",\"PR\":\"Paran\u00e1\",\"PE\":\"Pernambuco\",\"PI\":\"Piau\u00ed\",\"RJ\":\"Rio de Janeiro\",\"RN\":\"R\\u00edo Grande del Norte\",\"RS\":\"R\\u00edo Grande del Sur\",\"RO\":\"Rond\u00f4nia\",\"RR\":\"Roraima\",\"SC\":\"Santa Catalina\",\"SP\":\"S\u00e3o Paulo\",\"SE\":\"Sergipe\",\"TO\":\"Tocantins\"},\"CA\":{\"AB\":\"Alberta\",\"BC\":\"Columbia Brit\\u00e1nica\",\"MB\":\"Manitoba\",\"NB\":\"New Brunswick\",\"NL\":\"Newfoundland y Labrador\",\"NT\":\"Northwest Territories\",\"NS\":\"Nova Scotia\",\"NU\":\"Nunavut\",\"ON\":\"Ontario\",\"PE\":\"Isla del Pr\\u00edncipe Eduardo\",\"QC\":\"Quebec\",\"SK\":\"Saskatchewan\",\"YT\":\"Yukon Territory\"},\"CH\":{\"AG\":\"Aargau\",\"AR\":\"Appenzell Ausserrhoden\",\"AI\":\"Appenzell Innerrhoden\",\"BL\":\"Basel-Landschaft\",\"BS\":\"Basel-Stadt\",\"BE\":\"Bern\",\"FR\":\"Fribourg\",\"GE\":\"Geneva\",\"GL\":\"Glarus\",\"GR\":\"Graub\u00fcnden\",\"JU\":\"Jura\",\"LU\":\"Luzern\",\"NE\":\"Neuch\u00e2tel\",\"NW\":\"Nidwalden\",\"OW\":\"Obwalden\",\"SH\":\"Schaffhausen\",\"SZ\":\"Schwyz\",\"SO\":\"Solothurn\",\"SG\":\"St. Gallen\",\"TG\":\"Thurgau\",\"TI\":\"Ticino\",\"UR\":\"Uri\",\"VS\":\"Valais\",\"VD\":\"Vaud\",\"ZG\":\"Zug\",\"ZH\":\"Z\u00fcrich\"},\"CN\":{\"CN1\":\"Yunnan \\\/ \u4e91\u5357\",\"CN2\":\"Beijing \\\/ \u5317\u4eac\",\"CN3\":\"Tianjin \\\/ \u5929\u6d25\",\"CN4\":\"Hebei \\\/ \u6cb3\u5317\",\"CN5\":\"Shanxi \\\/ \u5c71\u897f\",\"CN6\":\"Inner Mongolia \\\/ \u5167\u8499\u53e4\",\"CN7\":\"Liaoning \\\/ \u8fbd\u5b81\",\"CN8\":\"Jilin \\\/ \u5409\u6797\",\"CN9\":\"Heilongjiang \\\/ \u9ed1\u9f99\u6c5f\",\"CN10\":\"Shanghai \\\/ \u4e0a\u6d77\",\"CN11\":\"Jiangsu \\\/ \u6c5f\u82cf\",\"CN12\":\"Zhejiang \\\/ \u6d59\u6c5f\",\"CN13\":\"Anhui \\\/ \u5b89\u5fbd\",\"CN14\":\"Fujian \\\/ \u798f\u5efa\",\"CN15\":\"Jiangxi \\\/ \u6c5f\u897f\",\"CN16\":\"Shandong \\\/ \u5c71\u4e1c\",\"CN17\":\"Henan \\\/ \u6cb3\u5357\",\"CN18\":\"Hubei \\\/ \u6e56\u5317\",\"CN19\":\"Hunan \\\/ \u6e56\u5357\",\"CN20\":\"Guangdong \\\/ \u5e7f\u4e1c\",\"CN21\":\"Guangxi Zhuang \\\/ \u5e7f\u897f\u58ee\u65cf\",\"CN22\":\"Hainan \\\/ \u6d77\u5357\",\"CN23\":\"Chongqing \\\/ \u91cd\u5e86\",\"CN24\":\"Sichuan \\\/ \u56db\u5ddd\",\"CN25\":\"Guizhou \\\/ \u8d35\u5dde\",\"CN26\":\"Shaanxi \\\/ \u9655\u897f\",\"CN27\":\"Gansu \\\/ \u7518\u8083\",\"CN28\":\"Qinghai \\\/ \u9752\u6d77\",\"CN29\":\"Ningxia Hui \\\/ \u5b81\u590f\",\"CN30\":\"Macao \\\/ \u6fb3\u95e8\",\"CN31\":\"Tibet \\\/ \u897f\u85cf\",\"CN32\":\"Xinjiang \\\/ \u65b0\u7586\"},\"CZ\":[],\"DE\":[],\"DK\":[],\"EE\":[],\"ES\":{\"C\":\"A Coru\u00f1a\",\"VI\":\"Araba\\\/\u00c1lava\",\"AB\":\"Albacete\",\"A\":\"Alicante\",\"AL\":\"Almer\u00eda\",\"O\":\"Asturias\",\"AV\":\"\u00c1vila\",\"BA\":\"Badajoz\",\"PM\":\"Baleares\",\"B\":\"Barcelona\",\"BU\":\"Burgos\",\"CC\":\"C\u00e1ceres\",\"CA\":\"C\u00e1diz\",\"S\":\"Cantabria\",\"CS\":\"Castell\u00f3n\",\"CE\":\"Ceuta\",\"CR\":\"Ciudad Real\",\"CO\":\"C\\u00f3rdoba\",\"CU\":\"Cuenca\",\"GI\":\"Girona\",\"GR\":\"Granada\",\"GU\":\"Guadalajara\",\"SS\":\"Gipuzkoa\",\"H\":\"Huelva\",\"HU\":\"Huesca\",\"J\":\"Ja\u00e9n\",\"LO\":\"La Rioja\",\"GC\":\"Las Palmas\",\"LE\":\"Le\u00f3n\",\"L\":\"Lleida\",\"LU\":\"Lugo\",\"M\":\"Madrid\",\"MA\":\"M\u00e1laga\",\"ML\":\"Melilla\",\"MU\":\"Murcia\",\"NA\":\"Navarra\",\"OR\":\"Ourense\",\"P\":\"Palencia\",\"PO\":\"Pontevedra\",\"SA\":\"Salamanca\",\"TF\":\"Santa Cruz de Tenerife\",\"SG\":\"Segovia\",\"SE\":\"Sevilla\",\"SO\":\"Soria\",\"T\":\"Tarragona\",\"TE\":\"Teruel\",\"TO\":\"Toledo\",\"V\":\"Valencia\",\"VA\":\"Valladolid\",\"BI\":\"Bizkaia\",\"ZA\":\"Zamora\",\"Z\":\"Zaragoza\"},\"FI\":[],\"FR\":[],\"GP\":[],\"GR\":{\"I\":\"\\u00c1tica\",\"A\":\"Macedonia Oriental y Tracia\",\"B\":\"Macedonia Central\",\"C\":\"Macedonia Occidental\",\"D\":\"\\u00c9piro\",\"E\":\"Tesalia\",\"F\":\"Islas J\\u00f3nicas\",\"G\":\"Grecia Occidental\",\"H\":\"Grecia Central\",\"J\":\"Peloponeso\",\"K\":\"Egeo Septentrional\",\"L\":\"Egeo Meridional\",\"M\":\"Creta\"},\"GF\":[],\"HK\":{\"HONG KONG\":\"Isla de Hong Kong\",\"KOWLOON\":\"Kowloon\",\"NEW TERRITORIES\":\"Nuevos territorios\"},\"HU\":{\"BK\":\"B\\u00e1cs-Kiskun\",\"BE\":\"B\\u00e9k\\u00e9s\",\"BA\":\"Baranya\",\"BZ\":\"Borsod-Aba\\u00faj-Zempl\\u00e9n\",\"BU\":\"Budapest\",\"CS\":\"Csongr\\u00e1d\",\"FE\":\"Fej\\u00e9r\",\"GS\":\"Gy\\u0151r-Moson-Sopron\",\"HB\":\"Hajd\\u00fa-Bihar\",\"HE\":\"Heves\",\"JN\":\"J\\u00e1sz-Nagykun-Szolnok\",\"KE\":\"Kom\\u00e1rom-Esztergom\",\"NO\":\"N\\u00f3gr\\u00e1d\",\"PE\":\"Pest\",\"SO\":\"Somogy\",\"SZ\":\"Szabolcs-Szatm\\u00e1r-Bereg\",\"TO\":\"Tolna\",\"VA\":\"Vas\",\"VE\":\"Veszpr\\u00e9m\",\"ZA\":\"Zala\"},\"ID\":{\"AC\":\"Daerah Istimewa Aceh\",\"SU\":\"Sumatra Septentrional\",\"SB\":\"Sumatra Occidental\",\"RI\":\"Riau\",\"KR\":\"Kepulauan Riau\",\"JA\":\"Jambi\",\"SS\":\"Sumatra Meridional\",\"BB\":\"Bangka Belitung\",\"BE\":\"Bengkulu\",\"LA\":\"Lampung\",\"JK\":\"DKI Jakarta\",\"JB\":\"Jawa Barat\",\"BT\":\"Banten\",\"JT\":\"Jawa Tengah\",\"JI\":\"Jawa Timur\",\"YO\":\"Yogyakarta\",\"BA\":\"Bali\",\"NB\":\"Nusatenggara Occidental\",\"NT\":\"Nusatenggara Oriental\",\"KB\":\"Borneo Occidental\",\"KT\":\"Borneo Central\",\"KI\":\"Kalimantan Oriental\",\"KS\":\"Borneo Meridional\",\"KU\":\"Borneo del Norte\",\"SA\":\"C\\u00e9lebes Septentrional\",\"ST\":\"C\\u00e9lebes Central\",\"SG\":\"C\\u00e9lebes Suroriental\",\"SR\":\"C\\u00e9lebes Occidental\",\"SN\":\"C\\u00e9lebes Meridional\",\"GO\":\"Gorontalo\",\"MA\":\"Las islas Molucas \",\"MU\":\"Molucas septentrionales\",\"PA\":\"Pap\\u00faa\",\"PB\":\"Provincia de Pap\\u00faa Occidental\"},\"IE\":{\"CW\":\"Carlow\",\"CN\":\"Cavan\",\"CE\":\"Clare\",\"CO\":\"Cork\",\"DL\":\"Donegal\",\"D\":\"Dubl\\u00edn\",\"G\":\"Galway\",\"KY\":\"Kerry\",\"KE\":\"Kildare\",\"KK\":\"Kilkenny\",\"LS\":\"Laois\",\"LM\":\"Leitrim\",\"LK\":\"Limerick\",\"LD\":\"Longford\",\"LH\":\"Louth\",\"MO\":\"Mayo\",\"MH\":\"Meath\",\"MN\":\"Monaghan\",\"OY\":\"Offaly\",\"RN\":\"Roscommon\",\"SO\":\"Sligo\",\"TA\":\"Tipperary\",\"WD\":\"Waterford\",\"WH\":\"Westmeath\",\"WX\":\"Wexford\",\"WW\":\"Wicklow\"},\"IN\":{\"AP\":\"Andra Pradesh\",\"AR\":\"Arunachal Pradesh\",\"AS\":\"Assam\",\"BR\":\"Bihar\",\"CT\":\"Chhattisgarh\",\"GA\":\"Goa\",\"GJ\":\"Gujarat\",\"HR\":\"Haryana\",\"HP\":\"Himachal Pradesh\",\"JK\":\"Jammu and Kashmir\",\"JH\":\"Jharkhand\",\"KA\":\"Karnataka\",\"KL\":\"Kerala\",\"MP\":\"Madhya Pradesh\",\"MH\":\"Maharashtra\",\"MN\":\"Manipur\",\"ML\":\"Meghalaya\",\"MZ\":\"Mizoram\",\"NL\":\"Nagaland\",\"OR\":\"Orissa\",\"PB\":\"Punjab\",\"RJ\":\"Rajasthan\",\"SK\":\"Sikkim\",\"TN\":\"Tamil Nadu\",\"TS\":\"Telangana\",\"TR\":\"Tripura\",\"UK\":\"Uttarakhand\",\"UP\":\"Uttar Pradesh\",\"WB\":\"West Bengal\",\"AN\":\"Islas Andaman y Nicobar\",\"CH\":\"Chandigarh\",\"DN\":\"Dadra y Nagar Haveli\",\"DD\":\"Daman and Diu\",\"DL\":\"Delhi\",\"LD\":\"Lakshadeep\",\"PY\":\"Pondicherry (Puducherry)\"},\"IR\":{\"KHZ\":\"Juzest\\u00e1n (\\u062e\\u0648\\u0632\\u0633\\u062a\\u0627\\u0646)\",\"THR\":\"Teher\\u00e1n  (\\u062a\\u0647\\u0631\\u0627\\u0646)\",\"ILM\":\"Ilaam (\\u0627\\u06cc\\u0644\\u0627\\u0645)\",\"BHR\":\"Bujara (\\u0628\\u0648\\u0634\\u0647\\u0631)\",\"ADL\":\"Ardebil (\\u0627\\u0631\\u062f\\u0628\\u06cc\\u0644)\",\"ESF\":\"Isfah\\u00e1n (\\u0627\\u0635\\u0641\\u0647\\u0627\\u0646)\",\"YZD\":\"Yazd (\\u06cc\\u0632\\u062f)\",\"KRH\":\"Kermanshah (\\u06a9\\u0631\\u0645\\u0627\\u0646\\u0634\\u0627\\u0647)\",\"KRN\":\"Kerm\\u00e1n (\\u06a9\\u0631\\u0645\\u0627\\u0646)\",\"HDN\":\"Hamad\\u00e1n (\\u0647\\u0645\\u062f\\u0627\\u0646)\",\"GZN\":\"Qazv\\u00edn (\\u0642\\u0632\\u0648\\u06cc\\u0646)\",\"ZJN\":\"Zany\\u00e1n (\\u0632\\u0646\\u062c\\u0627\\u0646)\",\"LRS\":\"Lorist\\u00e1n (\\u0644\\u0631\\u0633\\u062a\\u0627\\u0646)\",\"ABZ\":\"Elburz (\\u0627\\u0644\\u0628\\u0631\\u0632)\",\"EAZ\":\"Azerbaiy\\u00e1n Oriental (\\u0622\\u0630\\u0631\\u0628\\u0627\\u06cc\\u062c\\u0627\\u0646 \\u0634\\u0631\\u0642\\u06cc)\",\"WAZ\":\"Azerbaiy\\u00e1n Occidental (\\u0622\\u0630\\u0631\\u0628\\u0627\\u06cc\\u062c\\u0627\\u0646 \\u063a\\u0631\\u0628\\u06cc)\",\"CHB\":\"Chahar y Bajtiari (\\u0686\\u0647\\u0627\\u0631\\u0645\\u062d\\u0627\\u0644 \\u0648 \\u0628\\u062e\\u062a\\u06cc\\u0627\\u0631\\u06cc)\",\"SKH\":\"Joras\\u00e1n del Sur (\\u062e\\u0631\\u0627\\u0633\\u0627\\u0646 \\u062c\\u0646\\u0648\\u0628\\u06cc)\",\"RKH\":\"Joras\\u00e1n Razav\\u00ed (\\u062e\\u0631\\u0627\\u0633\\u0627\\u0646 \\u0631\\u0636\\u0648\\u06cc)\",\"NKH\":\"Joras\\u00e1n del Norte (\\u062e\\u0631\\u0627\\u0633\\u0627\\u0646 \\u062c\\u0646\\u0648\\u0628\\u06cc)\",\"SMN\":\"Semn\\u00e1n (\\u0633\\u0645\\u0646\\u0627\\u0646)\",\"FRS\":\"Fars (\\u0641\\u0627\\u0631\\u0633)\",\"QHM\":\"Qom (\\u0642\\u0645)\",\"KRD\":\"Kurdist\\u00e1n \\\/ \\u06a9\\u0631\\u062f\\u0633\\u062a\\u0627\\u0646)\",\"KBD\":\"Kohkiluyeh y Buyer Ahmad (\\u06a9\\u0647\\u06af\\u06cc\\u0644\\u0648\\u06cc\\u06cc\\u0647 \\u0648 \\u0628\\u0648\\u06cc\\u0631\\u0627\\u062d\\u0645\\u062f)\",\"GLS\":\"Golest\\u00e1n (\\u06af\\u0644\\u0633\\u062a\\u0627\\u0646)\",\"GIL\":\"Guil\\u00e1n (\\u06af\\u06cc\\u0644\\u0627\\u0646)\",\"MZN\":\"Mazandar\\u00e1n (\\u0645\\u0627\\u0632\\u0646\\u062f\\u0631\\u0627\\u0646)\",\"MKZ\":\"Markaz\\u00ed (\\u0645\\u0631\\u06a9\\u0632\\u06cc)\",\"HRZ\":\"Hormozg\\u00e1n (\\u0647\\u0631\\u0645\\u0632\\u06af\\u0627\\u0646)\",\"SBN\":\"Sist\\u00e1n and Baluchist\\u00e1n (\\u0633\\u06cc\\u0633\\u062a\\u0627\\u0646 \\u0648 \\u0628\\u0644\\u0648\\u0686\\u0633\\u062a\\u0627\\u0646)\"},\"IS\":[],\"IT\":{\"AG\":\"Agrigento\",\"AL\":\"Alessandria\",\"AN\":\"Ancona\",\"AO\":\"Aosta\",\"AR\":\"Arezzo\",\"AP\":\"Ascoli Piceno\",\"AT\":\"Asti\",\"AV\":\"Avellino\",\"BA\":\"Bari\",\"BT\":\"Barletta-Andria-Trani\",\"BL\":\"Belluno\",\"BN\":\"Benevento\",\"BG\":\"Bergamo\",\"BI\":\"Biella\",\"BO\":\"Bologna\",\"BZ\":\"Bolzano\",\"BS\":\"Brescia\",\"BR\":\"Brindisi\",\"CA\":\"Cagliari\",\"CL\":\"Caltanissetta\",\"CB\":\"Campobasso\",\"CE\":\"Caserta\",\"CT\":\"Catania\",\"CZ\":\"Catanzaro\",\"CH\":\"Chieti\",\"CO\":\"Como\",\"CS\":\"Cosenza\",\"CR\":\"Cremona\",\"KR\":\"Crotone\",\"CN\":\"Cuneo\",\"EN\":\"Enna\",\"FM\":\"Fermo\",\"FE\":\"Ferrara\",\"FI\":\"Firenze\",\"FG\":\"Foggia\",\"FC\":\"Forl\\u00ec-Cesena\",\"FR\":\"Frosinone\",\"GE\":\"Genova\",\"GO\":\"Gorizia\",\"GR\":\"Grosseto\",\"IM\":\"Imperia\",\"IS\":\"Isernia\",\"SP\":\"La Spezia\",\"AQ\":\"L'Aquila\",\"LT\":\"Latina\",\"LE\":\"Lecce\",\"LC\":\"Lecco\",\"LI\":\"Livorno\",\"LO\":\"Lodi\",\"LU\":\"Lucca\",\"MC\":\"Macerata\",\"MN\":\"Mantova\",\"MS\":\"Massa-Carrara\",\"MT\":\"Matera\",\"ME\":\"Messina\",\"MI\":\"Milano\",\"MO\":\"Modena\",\"MB\":\"Monza e della Brianza\",\"NA\":\"Napoli\",\"NO\":\"Novara\",\"NU\":\"Nuoro\",\"OR\":\"Oristano\",\"PD\":\"Padova\",\"PA\":\"Palermo\",\"PR\":\"Parma\",\"PV\":\"Pavia\",\"PG\":\"Perugia\",\"PU\":\"Pesaro e Urbino\",\"PE\":\"Pescara\",\"PC\":\"Piacenza\",\"PI\":\"Pisa\",\"PT\":\"Pistoia\",\"PN\":\"Pordenone\",\"PZ\":\"Potenza\",\"PO\":\"Prato\",\"RG\":\"Ragusa\",\"RA\":\"Ravenna\",\"RC\":\"Reggio Calabria\",\"RE\":\"Reggio Emilia\",\"RI\":\"Rieti\",\"RN\":\"Rimini\",\"RM\":\"Roma\",\"RO\":\"Rovigo\",\"SA\":\"Salerno\",\"SS\":\"Sassari\",\"SV\":\"Savona\",\"SI\":\"Siena\",\"SR\":\"Siracusa\",\"SO\":\"Sondrio\",\"SU\":\"Sur de Cerde\\u00f1a\",\"TA\":\"Taranto\",\"TE\":\"Teramo\",\"TR\":\"Terni\",\"TO\":\"Torino\",\"TP\":\"Trapani\",\"TN\":\"Trento\",\"TV\":\"Treviso\",\"TS\":\"Trieste\",\"UD\":\"Udine\",\"VA\":\"Varese\",\"VE\":\"Venezia\",\"VB\":\"Verbano-Cusio-Ossola\",\"VC\":\"Vercelli\",\"VR\":\"Verona\",\"VV\":\"Vibo Valentia\",\"VI\":\"Vicenza\",\"VT\":\"Viterbo\"},\"IL\":[],\"IM\":[],\"JP\":{\"JP01\":\"Hokkaid\\u014d\",\"JP02\":\"Aomori\",\"JP03\":\"Iwate\",\"JP04\":\"Miyagi\",\"JP05\":\"Akita\",\"JP06\":\"Yamagata\",\"JP07\":\"Fukushima\",\"JP08\":\"Ibaraki\",\"JP09\":\"Tochigi\",\"JP10\":\"Gunma\",\"JP11\":\"Saitama\",\"JP12\":\"Chiba\",\"JP13\":\"Tokyo\",\"JP14\":\"Kanagawa\",\"JP15\":\"Niigata\",\"JP16\":\"Toyama\",\"JP17\":\"Ishikawa\",\"JP18\":\"Fukui\",\"JP19\":\"Yamanashi\",\"JP20\":\"Nagano\",\"JP21\":\"Gifu\",\"JP22\":\"Shizuoka\",\"JP23\":\"Aichi\",\"JP24\":\"Mie\",\"JP25\":\"Shiga\",\"JP26\":\"Kyoto\",\"JP27\":\"Osaka\",\"JP28\":\"Hyogo\",\"JP29\":\"Nara\",\"JP30\":\"Wakayama\",\"JP31\":\"Tottori\",\"JP32\":\"Shimane\",\"JP33\":\"Okayama\",\"JP34\":\"Hiroshima\",\"JP35\":\"Yamaguchi\",\"JP36\":\"Tokushima\",\"JP37\":\"Kagawa\",\"JP38\":\"Ehime\",\"JP39\":\"Coch\\u00edn\",\"JP40\":\"Fukuoka\",\"JP41\":\"Saga\",\"JP42\":\"Nagasaki\",\"JP43\":\"Kumamoto\",\"JP44\":\"\\u014cita\",\"JP45\":\"Miyazaki\",\"JP46\":\"Kagoshima\",\"JP47\":\"Okinawa\"},\"KR\":[],\"KW\":[],\"LA\":{\"AT\":\"Attapeu\",\"BK\":\"Bokeo\",\"BL\":\"Bolikhamsai\",\"CH\":\"Champasak\",\"HO\":\"Houaphanh\",\"KH\":\"Khammouane\",\"LM\":\"Luang Namtha\",\"LP\":\"Luang Prabang\",\"OU\":\"Oudomxay\",\"PH\":\"Phongsaly\",\"SL\":\"Salavan\",\"SV\":\"Savannakhet\",\"VI\":\"Provincia de Vientiane\",\"VT\":\"Vientiane\",\"XA\":\"Sainyabuli\",\"XE\":\"Sekong\",\"XI\":\"Xiangkhouang\",\"XS\":\"Xaisomboun\"},\"LB\":[],\"LR\":{\"BM\":\"Bomi\",\"BN\":\"Bong\",\"GA\":\"Gbarpolu\",\"GB\":\"Grand Bassa\",\"GC\":\"Grand Cape Mount\",\"GG\":\"Grand Gedeh\",\"GK\":\"Grand Kru\",\"LO\":\"Lofa\",\"MA\":\"Margibi\",\"MY\":\"Maryland\",\"MO\":\"Montserrado\",\"NM\":\"Nimba\",\"RV\":\"Rivercess\",\"RG\":\"River Gee\",\"SN\":\"Sinoe\"},\"LU\":[],\"MD\":{\"C\":\"Chi\u0219in\u0103u\",\"BL\":\"B\u0103l\u021bi\",\"AN\":\"Anenii Noi\",\"BS\":\"Basarabeasca\",\"BR\":\"Briceni\",\"CH\":\"Cahul\",\"CT\":\"Cantemir\",\"CL\":\"C\u0103l\u0103ra\u0219i\",\"CS\":\"C\u0103u\u0219eni\",\"CM\":\"Cimi\u0219lia\",\"CR\":\"Criuleni\",\"DN\":\"Dondu\u0219eni\",\"DR\":\"Drochia\",\"DB\":\"Dub\u0103sari\",\"ED\":\"Edine\u021b\",\"FL\":\"F\u0103le\u0219ti\",\"FR\":\"Flore\u0219ti\",\"GE\":\"UTA G\u0103g\u0103uzia\",\"GL\":\"Glodeni\",\"HN\":\"H\u00eence\u0219ti\",\"IL\":\"Ialoveni\",\"LV\":\"Leova\",\"NS\":\"Nisporeni\",\"OC\":\"Ocni\u021ba\",\"OR\":\"Orhei\",\"RZ\":\"Rezina\",\"RS\":\"R\u00ee\u0219cani\",\"SG\":\"S\u00eengerei\",\"SR\":\"Soroca\",\"ST\":\"Str\u0103\u0219eni\",\"SD\":\"\u0218old\u0103ne\u0219ti\",\"SV\":\"\u0218tefan Vod\u0103\",\"TR\":\"Taraclia\",\"TL\":\"Telene\u0219ti\",\"UN\":\"Ungheni\"},\"MQ\":[],\"MT\":[],\"MX\":{\"DF\":\"Ciudad de M\u00e9xico\",\"JA\":\"Jalisco\",\"NL\":\"Nuevo Le\u00f3n\",\"AG\":\"Aguascalientes\",\"BC\":\"Baja California\",\"BS\":\"Baja California Sur\",\"CM\":\"Campeche\",\"CS\":\"Chiapas\",\"CH\":\"Chihuahua\",\"CO\":\"Coahuila\",\"CL\":\"Colima\",\"DG\":\"Durango\",\"GT\":\"Guanajuato\",\"GR\":\"Guerrero\",\"HG\":\"Hidalgo\",\"MX\":\"Estado de M\u00e9xico\",\"MI\":\"Michoac\u00e1n\",\"MO\":\"Morelos\",\"NA\":\"Nayarit\",\"OA\":\"Oaxaca\",\"PU\":\"Puebla\",\"QT\":\"Quer\u00e9taro\",\"QR\":\"Quintana Roo\",\"SL\":\"San Luis Potos\u00ed\",\"SI\":\"Sinaloa\",\"SO\":\"Sonora\",\"TB\":\"Tabasco\",\"TM\":\"Tamaulipas\",\"TL\":\"Tlaxcala\",\"VE\":\"Veracruz\",\"YU\":\"Yucat\u00e1n\",\"ZA\":\"Zacatecas\"},\"MY\":{\"JHR\":\"Johor\",\"KDH\":\"Kedah\",\"KTN\":\"Kelantan\",\"LBN\":\"Labuan\",\"MLK\":\"Malaca (Melaca)\",\"NSN\":\"Negeri Sembilan\",\"PHG\":\"Pahang\",\"PNG\":\"Penang (Pulau Pinang)\",\"PRK\":\"Perak\",\"PLS\":\"Perlis\",\"SBH\":\"Sabah\",\"SWK\":\"Sarawak\",\"SGR\":\"Selangor\",\"TRG\":\"Terengganu\",\"PJY\":\"Putrajaya\",\"KUL\":\"Kuala Lumpur\"},\"NG\":{\"AB\":\"Abia\",\"FC\":\"Abuja\",\"AD\":\"Adamawa\",\"AK\":\"Akwa Ibom\",\"AN\":\"Anambra\",\"BA\":\"Bauchi\",\"BY\":\"Bayelsa\",\"BE\":\"Benue\",\"BO\":\"Borno\",\"CR\":\"Cross River\",\"DE\":\"Delta\",\"EB\":\"Ebonyi\",\"ED\":\"Edo\",\"EK\":\"Ekiti\",\"EN\":\"Enugu\",\"GO\":\"Gombe\",\"IM\":\"Imo\",\"JI\":\"Jigawa\",\"KD\":\"Kaduna\",\"KN\":\"Kano\",\"KT\":\"Katsina\",\"KE\":\"Kebbi\",\"KO\":\"Kogi\",\"KW\":\"Kwara\",\"LA\":\"Lagos\",\"NA\":\"Nasarawa\",\"NI\":\"N\\u00edger\",\"OG\":\"Ogun\",\"ON\":\"Ondo\",\"OS\":\"Osun\",\"OY\":\"Oyo\",\"PL\":\"Plateau\",\"RI\":\"Rivers\",\"SO\":\"Sokoto\",\"TA\":\"Taraba\",\"YO\":\"Yobe\",\"ZA\":\"Zamfara\"},\"NL\":[],\"NO\":[],\"NP\":{\"BAG\":\"Bagmati\",\"BHE\":\"Bheri\",\"DHA\":\"Dhaulagiri\",\"GAN\":\"Gandaki\",\"JAN\":\"Janakpur\",\"KAR\":\"Karnali\",\"KOS\":\"Koshi\",\"LUM\":\"Lumbini\",\"MAH\":\"Mahakali\",\"MEC\":\"Mechi\",\"NAR\":\"Narayani\",\"RAP\":\"Rapti\",\"SAG\":\"Sagarmatha\",\"SET\":\"Seti\"},\"NZ\":{\"NL\":\"Northland\",\"AK\":\"Auckland\",\"WA\":\"Waikato\",\"BP\":\"Bay of Plenty\",\"TK\":\"Taranaki\",\"GI\":\"Gisborne\",\"HB\":\"Bah\\u00eda de Hawke\",\"MW\":\"Manawatu-Wanganui\",\"WE\":\"Wellington\",\"NS\":\"Nelson\",\"MB\":\"Marlborough\",\"TM\":\"Tasman\",\"WC\":\"Costa Oeste\",\"CT\":\"Canterbury\",\"OT\":\"Otago\",\"SL\":\"Southland\"},\"PE\":{\"CAL\":\"El Callao\",\"LMA\":\"Municipalidad Metropolitana de Lima\",\"AMA\":\"Amazonas\",\"ANC\":\"Ancash\",\"APU\":\"Apur\u00edmac\",\"ARE\":\"Arequipa\",\"AYA\":\"Ayacucho\",\"CAJ\":\"Cajamarca\",\"CUS\":\"Cusco\",\"HUV\":\"Huancavelica\",\"HUC\":\"Hu\u00e1nuco\",\"ICA\":\"Ica\",\"JUN\":\"Jun\u00edn\",\"LAL\":\"La Libertad\",\"LAM\":\"Lambayeque\",\"LIM\":\"Lima\",\"LOR\":\"Loreto\",\"MDD\":\"Madre de Dios\",\"MOQ\":\"Moquegua\",\"PAS\":\"Pasco\",\"PIU\":\"Piura\",\"PUN\":\"Puno\",\"SAM\":\"San Mart\u00edn\",\"TAC\":\"Tacna\",\"TUM\":\"Tumbes\",\"UCA\":\"Ucayali\"},\"PH\":{\"ABR\":\"Abra\",\"AGN\":\"Agusan del Norte\",\"AGS\":\"Agusan del Sur\",\"AKL\":\"Aklan\",\"ALB\":\"Albay\",\"ANT\":\"Antique\",\"APA\":\"Apayao\",\"AUR\":\"Aurora\",\"BAS\":\"Basilan\",\"BAN\":\"Bataan\",\"BTN\":\"Batanes\",\"BTG\":\"Batangas\",\"BEN\":\"Benguet\",\"BIL\":\"Biliran\",\"BOH\":\"Bohol\",\"BUK\":\"Bukidnon\",\"BUL\":\"Bulacan\",\"CAG\":\"Cagayan\",\"CAN\":\"Camarines Norte\",\"CAS\":\"Camarines Sur\",\"CAM\":\"Camiguin\",\"CAP\":\"Capiz\",\"CAT\":\"Catanduanes\",\"CAV\":\"Cavite\",\"CEB\":\"Cebu\",\"COM\":\"Valle Compostela\",\"NCO\":\"Cotabato\",\"DAV\":\"Davao del Norte\",\"DAS\":\"Davao del Sur\",\"DAC\":\"Davao Occidental\",\"DAO\":\"Davao Oriental\",\"DIN\":\"Islas Dinagat\",\"EAS\":\"Samar Este\",\"GUI\":\"Guimaras\",\"IFU\":\"Ifugao\",\"ILN\":\"Ilocos Norte\",\"ILS\":\"Ilocos Sur\",\"ILI\":\"Iloilo\",\"ISA\":\"Isabela\",\"KAL\":\"Kalinga\",\"LUN\":\"La Union\",\"LAG\":\"Laguna\",\"LAN\":\"Lanao del Norte\",\"LAS\":\"Lanao del Sur\",\"LEY\":\"Leyte\",\"MAG\":\"Maguindanao\",\"MAD\":\"Marinduque\",\"MAS\":\"Masbate\",\"MSC\":\"Misamis Occidental\",\"MSR\":\"Misamis Oriental\",\"MOU\":\"Mountain Province\",\"NEC\":\"Negros Occidental\",\"NER\":\"Negros Oriental\",\"NSA\":\"Samar del Norte\",\"NUE\":\"Nueva Ecija\",\"NUV\":\"Nueva Vizcaya\",\"MDC\":\"Mindoro Occidental\",\"MDR\":\"Mindoro Oriental\",\"PLW\":\"Palawan\",\"PAM\":\"Pampanga\",\"PAN\":\"Pangasinan\",\"QUE\":\"Quezon\",\"QUI\":\"Quirino\",\"RIZ\":\"Rizal\",\"ROM\":\"Romblon\",\"WSA\":\"Samar\",\"SAR\":\"Sarangani\",\"SIQ\":\"Siquijor\",\"SOR\":\"Sorsogon\",\"SCO\":\"Cotabato Sur\",\"SLE\":\"Leyte del Sur\",\"SUK\":\"Sultan Kudarat\",\"SLU\":\"Sulu\",\"SUN\":\"Surigao del Norte\",\"SUR\":\"Surigao del Sur\",\"TAR\":\"Tarlac\",\"TAW\":\"Tawi-Tawi\",\"ZMB\":\"Zambales\",\"ZAN\":\"Zamboanga del Norte\",\"ZAS\":\"Zamboanga del Sur\",\"ZSI\":\"Zamboanga Sibugay\",\"00\":\"Metro Manila\"},\"PK\":{\"JK\":\"Azad Cachemira\",\"BA\":\"Baluchist\\u00e1n\",\"TA\":\"FATA\",\"GB\":\"Gilgit-Baltist\\u00e1n \",\"IS\":\"Territorio de la capital Islamabad\",\"KP\":\"Khyber Pakhtunkhwa\",\"PB\":\"Punjab\",\"SD\":\"Sindh\"},\"PL\":[],\"PT\":[],\"PY\":{\"PY-ASU\":\"Asunci\u00f3n\",\"PY-1\":\"Concepci\u00f3n\",\"PY-2\":\"San Pedro\",\"PY-3\":\"Cordillera\",\"PY-4\":\"Guair\u00e1\",\"PY-5\":\"Caaguaz\u00fa\",\"PY-6\":\"Caazap\u00e1\",\"PY-7\":\"Itap\u00faa\",\"PY-8\":\"Misiones\",\"PY-9\":\"Paraguar\u00ed\",\"PY-10\":\"Alto Paran\u00e1\",\"PY-11\":\"Central\",\"PY-12\":\"\u00d1eembuc\u00fa\",\"PY-13\":\"Amambay\",\"PY-14\":\"Canindey\u00fa\",\"PY-15\":\"Presidente Hayes\",\"PY-16\":\"Alto Paraguay\",\"PY-17\":\"Boquer\u00f3n\"},\"RE\":[],\"RO\":{\"AB\":\"Alba\",\"AR\":\"Arad\",\"AG\":\"Arge\u0219\",\"BC\":\"Bac\u0103u\",\"BH\":\"Bihor\",\"BN\":\"Bistri\u021ba-N\u0103s\u0103ud\",\"BT\":\"Boto\u0219ani\",\"BR\":\"Br\u0103ila\",\"BV\":\"Bra\u0219ov\",\"B\":\"Bucure\u0219ti\",\"BZ\":\"Buz\u0103u\",\"CL\":\"C\u0103l\u0103ra\u0219i\",\"CS\":\"Cara\u0219-Severin\",\"CJ\":\"Cluj\",\"CT\":\"Constan\u021ba\",\"CV\":\"Covasna\",\"DB\":\"D\u00e2mbovi\u021ba\",\"DJ\":\"Dolj\",\"GL\":\"Gala\u021bi\",\"GR\":\"Giurgiu\",\"GJ\":\"Gorj\",\"HR\":\"Harghita\",\"HD\":\"Hunedoara\",\"IL\":\"Ialomi\u021ba\",\"IS\":\"Ia\u0219i\",\"IF\":\"Ilfov\",\"MM\":\"Maramure\u0219\",\"MH\":\"Mehedin\u021bi\",\"MS\":\"Mure\u0219\",\"NT\":\"Neam\u021b\",\"OT\":\"Olt\",\"PH\":\"Prahova\",\"SJ\":\"S\u0103laj\",\"SM\":\"Satu Mare\",\"SB\":\"Sibiu\",\"SV\":\"Suceava\",\"TR\":\"Teleorman\",\"TM\":\"Timi\u0219\",\"TL\":\"Tulcea\",\"VL\":\"V\u00e2lcea\",\"VS\":\"Vaslui\",\"VN\":\"Vrancea\"},\"RS\":[],\"SG\":[],\"SK\":[],\"SI\":[],\"TH\":{\"TH-37\":\"Amnat Charoen\",\"TH-15\":\"Ang Thong\",\"TH-14\":\"Ayutthaya\",\"TH-10\":\"Bangkok\",\"TH-38\":\"Bueng Kan\",\"TH-31\":\"Buri Ram\",\"TH-24\":\"Chachoengsao\",\"TH-18\":\"Chai Nat\",\"TH-36\":\"Chaiyaphum\",\"TH-22\":\"Chanthaburi\",\"TH-50\":\"Chiang Mai\",\"TH-57\":\"Chiang Rai\",\"TH-20\":\"Chonburi\",\"TH-86\":\"Chumphon\",\"TH-46\":\"Kalasin\",\"TH-62\":\"Kamphaeng Phet\",\"TH-71\":\"Kanchanaburi\",\"TH-40\":\"Khon Kaen\",\"TH-81\":\"Krabi\",\"TH-52\":\"Lampang\",\"TH-51\":\"Lamphun\",\"TH-42\":\"Loei\",\"TH-16\":\"Lopburi\",\"TH-58\":\"Mae Hong Son\",\"TH-44\":\"Maha Sarakham\",\"TH-49\":\"Mukdahan\",\"TH-26\":\"Nakhon Nayok\",\"TH-73\":\"Nakhon Pathom\",\"TH-48\":\"Nakhon Phanom\",\"TH-30\":\"Nakhon Ratchasima\",\"TH-60\":\"Nakhon Sawan\",\"TH-80\":\"Nakhon Si Thammarat\",\"TH-55\":\"Nan\",\"TH-96\":\"Narathiwat\",\"TH-39\":\"Nong Bua Lam Phu\",\"TH-43\":\"Nong Khai\",\"TH-12\":\"Nonthaburi\",\"TH-13\":\"Pathum Thani\",\"TH-94\":\"Pattani\",\"TH-82\":\"Phang Nga\",\"TH-93\":\"Phatthalung\",\"TH-56\":\"Phayao\",\"TH-67\":\"Phetchabun\",\"TH-76\":\"Phetchaburi\",\"TH-66\":\"Phichit\",\"TH-65\":\"Phitsanulok\",\"TH-54\":\"Phrae\",\"TH-83\":\"Phuket\",\"TH-25\":\"Prachin Buri\",\"TH-77\":\"Prachuap Khiri Khan\",\"TH-85\":\"Ranong\",\"TH-70\":\"Ratchaburi\",\"TH-21\":\"Rayong\",\"TH-45\":\"Roi Et\",\"TH-27\":\"Sa Kaeo\",\"TH-47\":\"Sakon Nakhon\",\"TH-11\":\"Samut Prakan\",\"TH-74\":\"Samut Sakhon\",\"TH-75\":\"Samut Songkhram\",\"TH-19\":\"Saraburi\",\"TH-91\":\"Satun\",\"TH-17\":\"Sing Buri\",\"TH-33\":\"Sisaket\",\"TH-90\":\"Songkhla\",\"TH-64\":\"Sukhothai\",\"TH-72\":\"Suphan Buri\",\"TH-84\":\"Surat Thani\",\"TH-32\":\"Surin\",\"TH-63\":\"Tak\",\"TH-92\":\"Trang\",\"TH-23\":\"Trat\",\"TH-34\":\"Ubon Ratchathani\",\"TH-41\":\"Udon Thani\",\"TH-61\":\"Uthai Thani\",\"TH-53\":\"Uttaradit\",\"TH-95\":\"Yala\",\"TH-35\":\"Yasothon\"},\"TR\":{\"TR01\":\"Adana\",\"TR02\":\"Ad\u0131yaman\",\"TR03\":\"Afyon\",\"TR04\":\"A\u011fr\u0131\",\"TR05\":\"Amasya\",\"TR06\":\"Ankara\",\"TR07\":\"Antalya\",\"TR08\":\"Artvin\",\"TR09\":\"Ayd\u0131n\",\"TR10\":\"Bal\u0131kesir\",\"TR11\":\"Bilecik\",\"TR12\":\"Bing\u00f6l\",\"TR13\":\"Bitlis\",\"TR14\":\"Bolu\",\"TR15\":\"Burdur\",\"TR16\":\"Bursa\",\"TR17\":\"\u00c7anakkale\",\"TR18\":\"\u00c7ank\u0131r\u0131\",\"TR19\":\"\u00c7orum\",\"TR20\":\"Denizli\",\"TR21\":\"Diyarbak\u0131r\",\"TR22\":\"Edirne\",\"TR23\":\"Elaz\u0131\u011f\",\"TR24\":\"Erzincan\",\"TR25\":\"Erzurum\",\"TR26\":\"Eski\u015fehir\",\"TR27\":\"Gaziantep\",\"TR28\":\"Giresun\",\"TR29\":\"G\u00fcm\u00fc\u015fhane\",\"TR30\":\"Hakkari\",\"TR31\":\"Hatay\",\"TR32\":\"Isparta\",\"TR33\":\"\u0130\u00e7el\",\"TR34\":\"\u0130stanbul\",\"TR35\":\"\u0130zmir\",\"TR36\":\"Kars\",\"TR37\":\"Kastamonu\",\"TR38\":\"Kayseri\",\"TR39\":\"K\u0131rklareli\",\"TR40\":\"K\u0131r\u015fehir\",\"TR41\":\"Kocaeli\",\"TR42\":\"Konya\",\"TR43\":\"K\u00fctahya\",\"TR44\":\"Malatya\",\"TR45\":\"Manisa\",\"TR46\":\"Kahramanmara\u015f\",\"TR47\":\"Mardin\",\"TR48\":\"Mu\u011fla\",\"TR49\":\"Mu\u015f\",\"TR50\":\"Nev\u015fehir\",\"TR51\":\"Ni\u011fde\",\"TR52\":\"Ordu\",\"TR53\":\"Rize\",\"TR54\":\"Sakarya\",\"TR55\":\"Samsun\",\"TR56\":\"Siirt\",\"TR57\":\"Sinop\",\"TR58\":\"Sivas\",\"TR59\":\"Tekirda\u011f\",\"TR60\":\"Tokat\",\"TR61\":\"Trabzon\",\"TR62\":\"Tunceli\",\"TR63\":\"\u015eanl\u0131urfa\",\"TR64\":\"U\u015fak\",\"TR65\":\"Van\",\"TR66\":\"Yozgat\",\"TR67\":\"Zonguldak\",\"TR68\":\"Aksaray\",\"TR69\":\"Bayburt\",\"TR70\":\"Karaman\",\"TR71\":\"K\u0131r\u0131kkale\",\"TR72\":\"Batman\",\"TR73\":\"\u015e\u0131rnak\",\"TR74\":\"Bart\u0131n\",\"TR75\":\"Ardahan\",\"TR76\":\"I\u011fd\u0131r\",\"TR77\":\"Yalova\",\"TR78\":\"Karab\u00fck\",\"TR79\":\"Kilis\",\"TR80\":\"Osmaniye\",\"TR81\":\"D\u00fczce\"},\"TZ\":{\"TZ01\":\"Arusha\",\"TZ02\":\"Dar es Salaam\",\"TZ03\":\"Dodoma\",\"TZ04\":\"Iringa\",\"TZ05\":\"Kagera\",\"TZ06\":\"Pemba Norte\",\"TZ07\":\"Zanzibar Norte\",\"TZ08\":\"Kigoma\",\"TZ09\":\"Kilimanjaro\",\"TZ10\":\"Pemba Sur\",\"TZ11\":\"Zanzibar Sur\",\"TZ12\":\"Lindi\",\"TZ13\":\"Mara\",\"TZ14\":\"Mbeya\",\"TZ15\":\"Zanzibar Oeste\",\"TZ16\":\"Morogoro\",\"TZ17\":\"Mtwara\",\"TZ18\":\"Mwanza\",\"TZ19\":\"Coast\",\"TZ20\":\"Rukwa\",\"TZ21\":\"Ruvuma\",\"TZ22\":\"Shinyanga\",\"TZ23\":\"Singida\",\"TZ24\":\"Tabora\",\"TZ25\":\"Tanga\",\"TZ26\":\"Manyara\",\"TZ27\":\"Geita\",\"TZ28\":\"Katavi\",\"TZ29\":\"Njombe\",\"TZ30\":\"Simiyu\"},\"LK\":[],\"SE\":[],\"UG\":{\"UG314\":\"Abim\",\"UG301\":\"Adjumani\",\"UG322\":\"Agago\",\"UG323\":\"Alebtong\",\"UG315\":\"Amolatar\",\"UG324\":\"Amudat\",\"UG216\":\"Amuria\",\"UG316\":\"Amuru\",\"UG302\":\"Apac\",\"UG303\":\"Arua\",\"UG217\":\"Budaka\",\"UG218\":\"Bududa\",\"UG201\":\"Bugiri\",\"UG235\":\"Bugweri\",\"UG420\":\"Buhweju\",\"UG117\":\"Buikwe\",\"UG219\":\"Bukedea\",\"UG118\":\"Bukomansimbi\",\"UG220\":\"Bukwa\",\"UG225\":\"Bulambuli\",\"UG416\":\"Buliisa\",\"UG401\":\"Bundibugyo\",\"UG430\":\"Bunyangabu\",\"UG402\":\"Bushenyi\",\"UG202\":\"Busia\",\"UG221\":\"Butaleja\",\"UG119\":\"Butambala\",\"UG233\":\"Butebo\",\"UG120\":\"Buvuma\",\"UG226\":\"Buyende\",\"UG317\":\"Dokolo\",\"UG121\":\"Gomba\",\"UG304\":\"Gulu\",\"UG403\":\"Hoima\",\"UG417\":\"Ibanda\",\"UG203\":\"Iganga\",\"UG418\":\"Isingiro\",\"UG204\":\"Jinja\",\"UG318\":\"Kaabong\",\"UG404\":\"Kabale\",\"UG405\":\"Kabarole\",\"UG213\":\"Kaberamaido\",\"UG427\":\"Kagadi\",\"UG428\":\"Kakumiro\",\"UG101\":\"Kalangala\",\"UG222\":\"Kaliro\",\"UG122\":\"Kalungu\",\"UG102\":\"Kampala\",\"UG205\":\"Kamuli\",\"UG413\":\"Kamwenge\",\"UG414\":\"Kanungu\",\"UG206\":\"Kapchorwa\",\"UG236\":\"Kapelebyong\",\"UG126\":\"Kasanda\",\"UG406\":\"Kasese\",\"UG207\":\"Katakwi\",\"UG112\":\"Kayunga\",\"UG407\":\"Kibaale\",\"UG103\":\"Kiboga\",\"UG227\":\"Kibuku\",\"UG432\":\"Kikuube\",\"UG419\":\"Kiruhura\",\"UG421\":\"Kiryandongo\",\"UG408\":\"Kisoro\",\"UG305\":\"Kitgum\",\"UG319\":\"Koboko\",\"UG325\":\"Kole\",\"UG306\":\"Kotido\",\"UG208\":\"Kumi\",\"UG333\":\"Kwania\",\"UG228\":\"Kween\",\"UG123\":\"Kyankwanzi\",\"UG422\":\"Kyegegwa\",\"UG415\":\"Kyenjojo\",\"UG125\":\"Kyotera\",\"UG326\":\"Lamwo\",\"UG307\":\"Lira\",\"UG229\":\"Luuka\",\"UG104\":\"Luwero\",\"UG124\":\"Lwengo\",\"UG114\":\"Lyantonde\",\"UG223\":\"Manafwa\",\"UG320\":\"Maracha\",\"UG105\":\"Masaka\",\"UG409\":\"Masindi\",\"UG214\":\"Mayuge\",\"UG209\":\"Mbale\",\"UG410\":\"Mbarara\",\"UG423\":\"Mitooma\",\"UG115\":\"Mityana\",\"UG308\":\"Moroto\",\"UG309\":\"Moyo\",\"UG106\":\"Mpigi\",\"UG107\":\"Mubende\",\"UG108\":\"Mukono\",\"UG334\":\"Nabilatuk\",\"UG311\":\"Nakapiripirit\",\"UG116\":\"Nakaseke\",\"UG109\":\"Nakasongola\",\"UG230\":\"Namayingo\",\"UG234\":\"Namisindwa\",\"UG224\":\"Namutumba\",\"UG327\":\"Napak\",\"UG310\":\"Nebbi\",\"UG231\":\"Ngora\",\"UG424\":\"Ntoroko\",\"UG411\":\"Ntungamo\",\"UG328\":\"Nwoya\",\"UG331\":\"Omoro\",\"UG329\":\"Otuke\",\"UG321\":\"Oyam\",\"UG312\":\"Pader\",\"UG332\":\"Pakwach\",\"UG210\":\"Pallisa\",\"UG110\":\"Rakai\",\"UG429\":\"Rubanda\",\"UG425\":\"Rubirizi\",\"UG431\":\"Rukiga\",\"UG412\":\"Rukungiri\",\"UG111\":\"Sembabule\",\"UG232\":\"Serere\",\"UG426\":\"Sheema\",\"UG215\":\"Sironko\",\"UG211\":\"Soroti\",\"UG212\":\"Tororo\",\"UG113\":\"Wakiso\",\"UG313\":\"Yumbe\",\"UG330\":\"Zombo\"},\"UM\":{\"81\":\"Isla Baker\",\"84\":\"Isla Howland\",\"86\":\"Isla Jarvis\",\"67\":\"Atol\\u00f3n Johnston\",\"89\":\"Arrecife Kingman\",\"71\":\"Atol\\u00f3n de Midway\",\"76\":\"Isla de Navaza\",\"95\":\"Atol\\u00f3n Palmyra\",\"79\":\"Isla Wake\"},\"US\":{\"AL\":\"Alabama\",\"AK\":\"Alaska\",\"AZ\":\"Arizona\",\"AR\":\"Arkansas\",\"CA\":\"California\",\"CO\":\"Colorado\",\"CT\":\"Connecticut\",\"DE\":\"Delaware\",\"DC\":\"District Of Columbia\",\"FL\":\"Florida\",\"GA\":\"Georgia\",\"HI\":\"Hawaii\",\"ID\":\"Idaho\",\"IL\":\"Illinois\",\"IN\":\"Indiana\",\"IA\":\"Iowa\",\"KS\":\"Kansas\",\"KY\":\"Kentucky\",\"LA\":\"Louisiana\",\"ME\":\"Maine\",\"MD\":\"Maryland\",\"MA\":\"Massachusetts\",\"MI\":\"Michigan\",\"MN\":\"Minnesota\",\"MS\":\"Mississippi\",\"MO\":\"Missouri\",\"MT\":\"Montana\",\"NE\":\"Nebraska\",\"NV\":\"Nevada\",\"NH\":\"New Hampshire\",\"NJ\":\"New Jersey\",\"NM\":\"New Mexico\",\"NY\":\"New York\",\"NC\":\"North Carolina\",\"ND\":\"North Dakota\",\"OH\":\"Ohio\",\"OK\":\"Oklahoma\",\"OR\":\"Oregon\",\"PA\":\"Pennsylvania\",\"RI\":\"Rhode Island\",\"SC\":\"South Carolina\",\"SD\":\"South Dakota\",\"TN\":\"Tennessee\",\"TX\":\"Texas\",\"UT\":\"Utah\",\"VT\":\"Vermont\",\"VA\":\"Virginia\",\"WA\":\"Washington\",\"WV\":\"West Virginia\",\"WI\":\"Wisconsin\",\"WY\":\"Wyoming\",\"AA\":\"Fuerzas Armadas (AA)\",\"AE\":\"Fuerzas Armadas  US\",\"AP\":\"Fuerzas Armadas  US\"},\"VN\":[],\"YT\":[],\"ZA\":{\"EC\":\"Cabo del Este\",\"FS\":\"Estado Libre\",\"GP\":\"Gauteng\",\"KZN\":\"KwaZulu-Natal\",\"LP\":\"Limpopo\",\"MP\":\"Mpumalanga\",\"NC\":\"Provincia Septentrional del Cabo\",\"NW\":\"Noroeste\",\"WC\":\"Provincia Occidental del Cabo\"},\"ZM\":{\"ZM-01\":\"Occidental\",\"ZM-02\":\"Central\",\"ZM-03\":\"Oriental\",\"ZM-04\":\"Luapula\",\"ZM-05\":\"Septentrional\",\"ZM-06\":\"Noroccidental\",\"ZM-07\":\"Meridional\",\"ZM-08\":\"Copperbelt\",\"ZM-09\":\"Lusaka\",\"ZM-10\":\"Muchinga\"}}","i18n_select_state_text":"Elige una opci\u00f3n\u2026","i18n_no_matches":"No se han encontrado coincidencias","i18n_ajax_error":"Error al cargar","i18n_input_too_short_1":"Por favor, introduce 1 o m\u00e1s caracteres","i18n_input_too_short_n":"Por favor, introduce %qty% o m\u00e1s caracteres","i18n_input_too_long_1":"Por favor, borra 1 car\u00e1cter.","i18n_input_too_long_n":"Por favor borra %qty% caracteres","i18n_selection_too_long_1":"Solo puedes seleccionar 1 art\u00edculo","i18n_selection_too_long_n":"Solo puedes seleccionar %qty% art\u00edculos","i18n_load_more":"Cargando m\u00e1s resultados\u2026","i18n_searching":"Buscando\u2026"};
/* ]]> */
</script>
<script type="text/javascript" src="<?php echo SITE_URL;?>/wp-content/plugins/woocommerce/assets/js/frontend/country-select.min.js?ver=4.0.1"></script>

<script type="text/javascript">
/* <![CDATA[ */
var wc_address_i18n_params = {"locale":"{\"AE\":{\"postcode\":{\"required\":false,\"hidden\":true},\"state\":{\"required\":false}},\"AF\":{\"state\":{\"required\":false}},\"AO\":{\"postcode\":{\"required\":false,\"hidden\":true},\"state\":{\"label\":\"Estado\"}},\"AT\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"AU\":{\"city\":{\"label\":\"Barrio residencial\"},\"postcode\":{\"label\":\"C\\u00f3digo postal\"},\"state\":{\"label\":\"Estado\"}},\"AX\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"BD\":{\"postcode\":{\"required\":false},\"state\":{\"label\":\"Barrio\"}},\"BE\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false,\"label\":\"Estado\"}},\"BH\":{\"postcode\":{\"required\":false},\"state\":{\"required\":false}},\"BI\":{\"state\":{\"required\":false}},\"BO\":{\"postcode\":{\"required\":false,\"hidden\":true}},\"BS\":{\"postcode\":{\"required\":false,\"hidden\":true}},\"CA\":{\"postcode\":{\"label\":\"C\\u00f3digo postal\"},\"state\":{\"label\":\"Estado\"}},\"CH\":{\"postcode\":{\"priority\":65},\"state\":{\"label\":\"Cant\\u00f3n\",\"required\":false}},\"CL\":{\"city\":{\"required\":true},\"postcode\":{\"required\":false},\"state\":{\"label\":\"Regi\\u00f3n\"}},\"CN\":{\"state\":{\"label\":\"Estado\"}},\"CO\":{\"postcode\":{\"required\":false}},\"CZ\":{\"state\":{\"required\":false}},\"DE\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"DK\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"EE\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"FI\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"FR\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"GP\":{\"state\":{\"required\":false}},\"GF\":{\"state\":{\"required\":false}},\"GR\":{\"state\":{\"required\":false}},\"HK\":{\"postcode\":{\"required\":false},\"city\":{\"label\":\"Localidad \\\/ Distrito\"},\"state\":{\"label\":\"Regi\\u00f3n\"}},\"HU\":{\"state\":{\"label\":\"Estado\"}},\"ID\":{\"state\":{\"label\":\"Estado\"}},\"IE\":{\"postcode\":{\"required\":false,\"label\":\"Eircode\"},\"state\":{\"label\":\"Estado\"}},\"IS\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"IL\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"IM\":{\"state\":{\"required\":false}},\"IT\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":true,\"label\":\"Estado\"}},\"JP\":{\"last_name\":{\"class\":[\"form-row-first\"],\"priority\":10},\"first_name\":{\"class\":[\"form-row-last\"],\"priority\":20},\"postcode\":{\"class\":[\"form-row-first\"],\"priority\":65},\"state\":{\"label\":\"Prefectura\",\"class\":[\"form-row-last\"],\"priority\":66},\"city\":{\"priority\":67},\"address_1\":{\"priority\":68},\"address_2\":{\"priority\":69}},\"KR\":{\"state\":{\"required\":false}},\"KW\":{\"state\":{\"required\":false}},\"LV\":{\"state\":{\"label\":\"Municipio\",\"required\":false}},\"LB\":{\"state\":{\"required\":false}},\"MQ\":{\"state\":{\"required\":false}},\"MT\":{\"state\":{\"required\":false}},\"MZ\":{\"postcode\":{\"required\":false,\"hidden\":true},\"state\":{\"label\":\"Estado\"}},\"NL\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false,\"label\":\"Estado\"}},\"NG\":{\"postcode\":{\"label\":\"C\\u00f3digo postal\",\"required\":false,\"hidden\":true},\"state\":{\"label\":\"Estado\"}},\"NZ\":{\"postcode\":{\"label\":\"C\\u00f3digo postal\"},\"state\":{\"required\":false,\"label\":\"Regi\\u00f3n\"}},\"NO\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"NP\":{\"state\":{\"label\":\"Estado \\\/ Zona\"},\"postcode\":{\"required\":false}},\"PL\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"PT\":{\"state\":{\"required\":false}},\"RE\":{\"state\":{\"required\":false}},\"RO\":{\"state\":{\"label\":\"Estado\",\"required\":true}},\"RS\":{\"state\":{\"required\":false,\"hidden\":true}},\"SG\":{\"state\":{\"required\":false},\"city\":{\"required\":false}},\"SK\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"SI\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"SR\":{\"postcode\":{\"required\":false,\"hidden\":true}},\"ES\":{\"postcode\":{\"priority\":65},\"state\":{\"label\":\"Estado\"}},\"LI\":{\"postcode\":{\"priority\":65},\"state\":{\"label\":\"Municipio\",\"required\":false}},\"LK\":{\"state\":{\"required\":false}},\"LU\":{\"state\":{\"required\":false}},\"MD\":{\"state\":{\"label\":\"Municipio \\\/ Barrio\"}},\"SE\":{\"postcode\":{\"priority\":65},\"state\":{\"required\":false}},\"TR\":{\"postcode\":{\"priority\":65},\"state\":{\"label\":\"Estado\"}},\"UG\":{\"postcode\":{\"required\":false,\"hidden\":true},\"city\":{\"label\":\"Ciudad \\\/ pueblo\",\"required\":true},\"state\":{\"label\":\"Barrio\",\"required\":true}},\"US\":{\"postcode\":{\"label\":\"C\\u00f3digo postal\"},\"state\":{\"label\":\"Estado\"}},\"GB\":{\"postcode\":{\"label\":\"C\\u00f3digo postal\"},\"state\":{\"label\":\"Estado\",\"required\":false}},\"ST\":{\"postcode\":{\"required\":false,\"hidden\":true},\"state\":{\"label\":\"Barrio\"}},\"VN\":{\"state\":{\"required\":false,\"hidden\":true},\"postcode\":{\"priority\":65,\"required\":false,\"hidden\":false},\"address_2\":{\"required\":false,\"hidden\":true}},\"WS\":{\"postcode\":{\"required\":false,\"hidden\":true}},\"YT\":{\"state\":{\"required\":false}},\"ZA\":{\"state\":{\"label\":\"Estado\"}},\"ZW\":{\"postcode\":{\"required\":false,\"hidden\":true}},\"default\":{\"first_name\":{\"label\":\"Nombre\",\"required\":true,\"class\":[\"form-row-first\"],\"autocomplete\":\"given-name\",\"priority\":10},\"last_name\":{\"label\":\"Apellidos\",\"required\":true,\"class\":[\"form-row-last\"],\"autocomplete\":\"family-name\",\"priority\":20},\"company\":{\"label\":\"Nombre de la empresa\",\"class\":[\"form-row-wide\"],\"autocomplete\":\"organization\",\"priority\":30,\"required\":false},\"country\":{\"type\":\"country\",\"label\":\"Pa\\u00eds\\\/Regi\\u00f3n\",\"required\":true,\"class\":[\"form-row-wide\",\"address-field\",\"update_totals_on_change\"],\"autocomplete\":\"country\",\"priority\":40},\"address_1\":{\"label\":\"Direcci\\u00f3n de la calle\",\"placeholder\":\"N\\u00famero de la casa y nombre de la calle\",\"required\":true,\"class\":[\"form-row-wide\",\"address-field\"],\"autocomplete\":\"address-line1\",\"priority\":50},\"address_2\":{\"placeholder\":\"Apartamento, habitaci\\u00f3n, etc. (opcional)\",\"class\":[\"form-row-wide\",\"address-field\"],\"autocomplete\":\"address-line2\",\"priority\":60,\"required\":false},\"city\":{\"label\":\"Localidad \\\/ Ciudad\",\"required\":true,\"class\":[\"form-row-wide\",\"address-field\"],\"autocomplete\":\"address-level2\",\"priority\":70},\"state\":{\"type\":\"state\",\"label\":\"Regi\\u00f3n \\\/ Estado\",\"required\":true,\"class\":[\"form-row-wide\",\"address-field\"],\"validate\":[\"state\"],\"autocomplete\":\"address-level1\",\"priority\":80},\"postcode\":{\"label\":\"C\\u00f3digo postal\",\"required\":true,\"class\":[\"form-row-wide\",\"address-field\"],\"validate\":[\"postcode\"],\"autocomplete\":\"postal-code\",\"priority\":90}}}","locale_fields":"{\"address_1\":\"#billing_address_1_field, #shipping_address_1_field\",\"address_2\":\"#billing_address_2_field, #shipping_address_2_field\",\"state\":\"#billing_state_field, #shipping_state_field, #calc_shipping_state_field\",\"postcode\":\"#billing_postcode_field, #shipping_postcode_field, #calc_shipping_postcode_field\",\"city\":\"#billing_city_field, #shipping_city_field, #calc_shipping_city_field\"}","i18n_required_text":"obligatorio","i18n_optional_text":"opcional"};
/* ]]> */
</script>
<script type="text/javascript" src="<?php echo SITE_URL;?>/wp-content/plugins/woocommerce/assets/js/frontend/address-i18n.min.js?ver=4.0.1"></script>
<script type="text/javascript">
/* <![CDATA[ */
var wc_checkout_params = '';//{"ajax_url":"\/edu\/wp-admin\/admin-ajax.php","wc_ajax_url":"\/edu\/?wc-ajax=%%endpoint%%","update_order_review_nonce":"ec13d47bd2","apply_coupon_nonce":"fac3f19d9b","remove_coupon_nonce":"ea142be18c","option_guest_checkout":"yes","checkout_url":"\/edu\/?wc-ajax=checkout","is_checkout":"1","debug_mode":"1","i18n_checkout_error":"Error procesando el pedido. Por favor, int\u00e9ntalo de nuevo"};
/* ]]> */
</script>
<script type='text/javascript' >

// Se ejecuta cuando cargue la pagina totalmente
var $ = jQuery;
$( document ).ready(function() {
    console.log( "ready PÀGE NOW ====>" );

    //Oculta/Muestra los campos de envios secundarios
   document.getElementById("shipping_last_name_field").style.display="none";
   document.getElementById("shipping_first_name_field").style.display="none";
   document.getElementById("shipping_country_field").style.display="none";
   document.getElementById("shipping_address_1_field").style.display="none";
   document.getElementById("shipping_address_2_field").style.display="none";
   document.getElementById("shipping_city_field").style.display="none";
   document.getElementById("shipping_state_field").style.display="none";
   document.getElementById("shipping_postcode_field").style.display="none";
   document.getElementById("ship-to-different-address-checkbox").onchange = function() {CheckEnabled_Shipping()};
   
   //Controla los campos de Cupones de descuentos
   if (document.querySelectorAll(".showcoupon")[0])
       document.querySelectorAll(".showcoupon")[0].onclick = function() {ShowCoupon()};
   
   // Prueba de cambiar atributos al boton de cupones
   //document.querySelectorAll(".checkout_coupon")[0].onclick = function()  {AlterCoupon()};
 
   //Dispara el Cambio DEL TIPO del boton de descuento
   setTimeout(AlterCoupon(),2000);
   
   //Dispara el Cambio DEL TIPO del boton de pagar
   setTimeout(AlterPagar(),2000);
   
   //Oculta los Payment Boxes
   Hide_payment_box();
   
   //Define el pago seleccionado
   var pay_box_selected = "";
   
   // Checkea los campos del Shipping
   setTimeout(CheckEnabled_Shipping(),1000);
   
   // Checkea los payment_box
   setTimeout(Hide_payment_box(),1000);
   
});

// FUNCIONES USADAS EN ESTA PAGINA  

   function Hide_payment_box() {
     pay_box_selected = "";
     
     if (document.querySelectorAll(".payment_box")) {
          pay_box = document.querySelectorAll(".payment_box");
          PBLen = pay_box.length;
          
          // Si hay una sola forma de pago
          if (PBLen==1){
              //Oculta el payment box
               pay_box[0].style.display="none";
               // Le asigna la accion al dar click (no es un vector)
               document.checkout.payment_method.onclick = function()  {Hide_payment_box()};
               // Si esta seleccionado, lo muestra 
               if (document.checkout.payment_method.checked) {
                    pay_box_selected = document.checkout.payment_method.value;
                    pay_box[0].style.display="block";
                    if (pay_box_selected == "realex_redirect") {
                        document.getElementById("checkout").action = "<?php echo SITE_URL . '/finalizar-alquiler';?>"
                        //alert("pay_box_selected = " + pay_box_selected  );
                        //alert("ACTION SELected = " + document.getElementById("checkout").action  );
                    }
               }
               return;
          }
        
          for (i=0; i < PBLen; i++ ) { alert("payment box = "+i);
               //Oculta el payment box
               pay_box[i].style.display="none";
               
               // Le asigna la accion al dar click 
               document.checkout.payment_method[i].onclick = function()  {Hide_payment_box()};
               // Si esta seleccionado, lo muestra 
               if (document.checkout.payment_method[i].checked) {
                    pay_box_selected = document.checkout.payment_method[i].value;
                    pay_box[i].style.display="block";
                    if (pay_box_selected == "realex_redirect") {
                        document.getElementById("checkout").action = "<?php echo SITE_URL . '/finalizar-alquiler';?>"
                        //alert("pay_box_selected = " + pay_box_selected  );
                        // alert("ACTION SELected = " + document.getElementById("checkout").action  );
                    }
                }
            
        
           }
     }   
       
   }


   function AlterCoupon() {
      if (document.querySelectorAll(".checkout_coupon")[0]) {
        //Cambia EL TIPO del boton de descuento
        var padre = document.querySelectorAll(".checkout_coupon")[0];
        var boton_del_cupon = padre.querySelectorAll(".button")[0];

       // Cambia el tipo de boton
        boton_del_cupon.type ="button"; 
        
       // Le asigna la accion al dar click 
        boton_del_cupon.onclick = function()  {alert("Usted Oprimio 'Aplicar Cupon', este boton aun no funciona");};
        
        //alert("se ejecuto todo con EXITO");
       }
     }
     
      function AlterPagar() {
        //Cambia EL TIPO del boton de pagar
        var boton_de_pagar = document.getElementById("place_order");

       // Cambia el tipo de boton
        boton_de_pagar.type ="button"; 
        boton_de_pagar.keypress =""; 
        
       // Le asigna la accion al dar click 
        boton_de_pagar.onclick = function()  {document.checkout.onsubmit();}; 

     }
   
  function CheckEnabled_Shipping() {
    // Chequea el estado del checkbox de envio a otra direccion  
	if (document.getElementById("ship-to-different-address-checkbox").checked) {
	     document.getElementById("shipping_last_name_field").style.display="block";
	     document.getElementById("shipping_first_name_field").style.display="block";
	     document.getElementById("shipping_country_field").style.display="block";
	     document.getElementById("shipping_address_1_field").style.display="block";
         document.getElementById("shipping_address_2_field").style.display="block";
         document.getElementById("shipping_city_field").style.display="block";
         document.getElementById("shipping_state_field").style.display="block";
         document.getElementById("shipping_postcode_field").style.display="block";
	
	 } else {
	 	document.getElementById("shipping_last_name_field").style.display="none";
	 	document.getElementById("shipping_first_name_field").style.display="none";
	 	document.getElementById("shipping_country_field").style.display="none";
	 	document.getElementById("shipping_address_1_field").style.display="none";
        document.getElementById("shipping_address_2_field").style.display="none";
        document.getElementById("shipping_city_field").style.display="none";
        document.getElementById("shipping_state_field").style.display="none";
        document.getElementById("shipping_postcode_field").style.display="none";
	}
  }
  
  function ShowCoupon() {
      //Oculta/Muestra los campos del cupon de descuento
      //alert ("CLICK EN EL CUPON");
      if (document.querySelectorAll(".checkout_coupon")[0].style.display=="block") {
          document.querySelectorAll(".checkout_coupon")[0].style.display="none";
       } else {
         document.querySelectorAll(".checkout_coupon")[0].style.display="block";
       }
  }
  
  
  function validar_checout57(thisform){
     //Valida los datos del checkout;
       var es_valido = false;

       // Valida el nombre
       if (thisform.billing_first_name.value==""){
            alert("Por Favor, Coloque Su Nombre ");
            document.getElementById("billing_first_name").classList.remove("datos_validos");
            document.getElementById("billing_first_name").classList.add("datos_invalidos");
            thisform.billing_first_name.focus();
            return es_valido;
        } else {
            document.getElementById("billing_first_name").classList.remove("datos_invalidos");
            document.getElementById("billing_first_name").classList.add("datos_validos");
        }
        
        // Valida el apellido
       if (thisform.billing_last_name.value==""){
            alert("Por Favor, Coloque Su Apellido ");
            document.getElementById("billing_last_name").classList.remove("datos_validos");
            document.getElementById("billing_last_name").classList.add("datos_invalidos");
            thisform.billing_last_name.focus();
            return es_valido;
        } else {
            document.getElementById("billing_last_name").classList.remove("datos_invalidos");
            document.getElementById("billing_last_name").classList.add("datos_validos");
        }
        
        
        // Valida la direccion de facturacion, linea 1 
       if (thisform.billing_address_1.value==""){
            alert("Por Favor, Coloque Su Dirección de Facturación, Línea 1 ");
            document.getElementById("billing_address_1").classList.remove("datos_validos");
            document.getElementById("billing_address_1").classList.add("datos_invalidos");
            thisform.billing_address_1.focus();
            return es_valido;
        } else {
            document.getElementById("billing_address_1").classList.remove("datos_invalidos");
            document.getElementById("billing_address_1").classList.add("datos_validos");
        }
        
        // Valida el codigo posta de facturacion 
       if (thisform.billing_postcode.value==""){
            alert("Por Favor, Coloque Su Código Postal ");
            document.getElementById("billing_postcode").classList.remove("datos_validos");
            document.getElementById("billing_postcode").classList.add("datos_invalidos");
            thisform.billing_postcode.focus();
            return es_valido;
        } else {
            document.getElementById("billing_postcode").classList.remove("datos_invalidos");
            document.getElementById("billing_postcode").classList.add("datos_validos");
        }
        
        // Valida la Ciudad de facturacion
       if (thisform.billing_city.value==""){
            alert("Por Favor, Coloque Su Ciudad de Facturación ");
            document.getElementById("billing_city").classList.remove("datos_validos");
            document.getElementById("billing_city").classList.add("datos_invalidos");
            thisform.billing_city.focus();
            return es_valido;
        } else {
            document.getElementById("billing_city").classList.remove("datos_invalidos");
            document.getElementById("billing_city").classList.add("datos_validos");
        }
        
        // Valida si hay Estado de facturacion y si esta seleccionado
        if (document.getElementById("billing_state").classList !="hidden") {
            
            var estado = 0;
            if (document.getElementsByClassName("select2-selection")) estado = document.getElementsByClassName("select2-selection");
            
            //alert ("Estado===> " + estado.length);
            
            if (document.getElementById("billing_state").value==""){
                alert("Por Favor, Coloque Su Estado ");
                document.getElementById("billing_state").classList.remove("datos_validos");
                document.getElementById("billing_state").classList.add("datos_invalidos");
                document.getElementById("billing_state").focus();
            
                if (estado.length != 0) {
                     i = estado.length - 1 ;
                      //alert ("elementos del estado: " + estado.length);
                     estado[i].classList.add("datos_invalidos");
                     
                }
             
                
                return es_valido;
             } else {
                    document.getElementById("billing_state").classList.remove("datos_invalidos");
                    document.getElementById("billing_state").classList.add("datos_validos");
                    if (estado.length != 0) {
                         i = estado.length - 1 ;
                         //alert ("elementos del estado: " + estado.length);
                         estado[i].classList.remove("datos_invalidos");
                         estado[i].classList.add("datos_validos");
                    }
             
            }
            
        } 
        
        // Valida el telefono
       if (thisform.billing_phone.value==""){
            alert("Por Favor, Coloque Su Teléfono ");
            document.getElementById("billing_phone").classList.remove("datos_validos");
            document.getElementById("billing_phone").classList.add("datos_invalidos");
            thisform.billing_phone.focus();
            return es_valido;
        } else {
            document.getElementById("billing_phone").classList.remove("datos_invalidos");
            document.getElementById("billing_phone").classList.add("datos_validos");
        }
        
        
        // Valida el correo
       if (thisform.billing_email.value==""){
            alert("Por Favor, Coloque Su Correo ");
            document.getElementById("billing_email").classList.remove("datos_validos");
            document.getElementById("billing_email").classList.add("datos_invalidos");
            thisform.billing_email.focus();
            return es_valido;
        } else {
            document.getElementById("billing_email").classList.remove("datos_invalidos");
            document.getElementById("billing_email").classList.add("datos_validos");
        }
        

        // Valida si se envia a otra direccion
        if (document.getElementById("ship-to-different-address-checkbox").checked) {
            
            // Valida el nombre en la direccion de envio
            if (thisform.shipping_first_name.value==""){
                alert("Por Favor, Coloque Su Nombre en la Dirección de Envio ");
                document.getElementById("shipping_first_name").classList.remove("datos_validos");
                document.getElementById("shipping_first_name").classList.add("datos_invalidos");
                thisform.shipping_first_name.focus();
                return es_valido;
             } else {
                 document.getElementById("shipping_first_name").classList.remove("datos_invalidos");
                 document.getElementById("shipping_first_name").classList.add("datos_validos");
            }
            
            // Valida el apellido en la direccion de envio
            if (thisform.shipping_last_name.value==""){
                alert("Por Favor, Coloque Su Apellido en la Dirección de Envio ");
                document.getElementById("shipping_last_name").classList.remove("datos_validos");
                document.getElementById("shipping_last_name").classList.add("datos_invalidos");
                thisform.shipping_last_name.focus();
                return es_valido;
             } else {
                 document.getElementById("shipping_last_name").classList.remove("datos_invalidos");
                 document.getElementById("shipping_last_name").classList.add("datos_validos");
            }
            
            // Valida la direccion de envio, linea 1 
            if (thisform.shipping_address_1.value==""){
                 alert("Por Favor, Coloque Su Dirección de Envio, Línea 1 ");
                 document.getElementById("shipping_address_1").classList.remove("datos_validos");
                 document.getElementById("shipping_address_1").classList.add("datos_invalidos");
                 thisform.shipping_address_1.focus();
                 return es_valido;
             } else {
                 document.getElementById("shipping_address_1").classList.remove("datos_invalidos");
                 document.getElementById("shipping_address_1").classList.add("datos_validos");
            }
        
            // Valida la direccion de envio, linea 2 
            if (thisform.shipping_address_2.value==""){
                 alert("Por Favor, Coloque Su Dirección de Envio, Línea 2 ");
                 document.getElementById("shipping_address_2").classList.remove("datos_validos");
                 document.getElementById("shipping_address_2").classList.add("datos_invalidos");
                 thisform.shipping_address_2.focus();
                 return es_valido;
             } else {
                 document.getElementById("shipping_address_2").classList.remove("datos_invalidos");
                 document.getElementById("shipping_address_2").classList.add("datos_validos");
            }
        
            // Valida la Ciudad de envio
            if (thisform.shipping_city.value==""){
                 alert("Por Favor, Coloque Su Ciudad de Envio ");
                 document.getElementById("shipping_city").classList.remove("datos_validos");
                 document.getElementById("shipping_city").classList.add("datos_invalidos");
                 thisform.shipping_city.focus();
                 return es_valido;
             } else {
                 document.getElementById("shipping_city").classList.remove("datos_invalidos");
                 document.getElementById("shipping_city").classList.add("datos_validos");
            }
        
            
        }
        
        
        // Valida aceptar los terminos y condiciones  border: 1px solid #ccc;
	    if (!document.getElementById("terms").checked) {
	        alert("Por Favor, Acepte los términos y condiciones ");
	        
            if (document.querySelectorAll(".form-row .validate-required")) {
                var terminos = document.querySelectorAll(".form-row .validate-required");  
                 ///alert ("elementos del termino: " + terminos.length);
                terminos[0].classList.remove("datos_validos");
                terminos[0].classList.remove("sin_borde");
                terminos[0].classList.add("datos_invalidos");
                terminos[0].classList.add("con_borde");
            }
            
                document.getElementById("terms").focus();
               return es_valido;
	    } else {
	        
            if (document.querySelectorAll(".form-row .validate-required")) {
                var terminos = document.querySelectorAll(".form-row .validate-required");  
                terminos[0].classList.remove("datos_invalidos");
                terminos[0].classList.remove("con_borde");
                terminos[0].classList.add("datos_validos");
                terminos[0].classList.add("sin_borde");
            }
        }
        
        
    // Pasa los datos de facturacion adicionales ALGUNOS TEMAS NECESITAN ESTO
/*        document.getElementById("billing_address_1").value = document.getElementById("billing_address_3").value ;
        document.getElementById("billing_address_2").value = document.getElementById("billing_address_4").value ;
        document.getElementById("billing_city").value = document.getElementById("billing_city_2").value ;
        
        // Pasa los datos de envio adicionales  ALGUNOS TEMAS NECESITAN ESTO
        document.getElementById("shipping_address_1").value = document.getElementById("shipping_address_3").value ;
        document.getElementById("shipping_address_2").value = document.getElementById("shipping_address_4").value ;
        document.getElementById("shipping_city").value = document.getElementById("shipping_city_2").value ;
*/      
       // indica que los datos pasan la validacion y hace el submit
       es_valido = true;
       //return es_valido;
       
       // Ajusta el shipping country de acuerdo al billing country si es necesario
       if (! document.getElementById("ship-to-different-address-checkbox").checked) {
          document.getElementById("shipping_country").value = document.getElementById("billing_country").value ; 
       }
       
       
       if (es_valido) document.checkout.submit();
}
  
  
</script> 

<?php 

do_action('woocommerce_after_main_content');
get_footer('shop');

