<?php
/*
* Plugin Name: Woocommerce Alquiler Instrumentos
* Description: Alquiler de Instrumentos Musicales Con WooCommerce
* Version: 0.91
* Author: Marta Torre
* Author URI: https://martatorre.dev/
* Licence : GPLv3 o posterior
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
* WC requires at least: 3.0.9
* WC tested up to: 4.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Se detiene si se ejecuta directamente
}

global $wcai_page_name, $wcai_page_content, $wcai_page_url, $wcai_error_obj, $endpoints, $wcai_mensaje;
global $wcai_meses_alquiler, $wcai_precio_alquiler, $wcai_periodo_alquiler;
global $wpdb, $wcai_slug, $wp_rewrite, $woocommerce, $cart_item_data, $order_id, $order, $wcai_cuenta_nueva;
global $wcai_id_product, $wcai_nro_orden, $wcai_date_from, $wcai_date_to, $wcai_total_alquiler, $wcai_nombre_producto_alquiler;  

// Define la clase principal del Objeto "Alquiler de Instrumentos"
if ( ! class_exists( 'Alquiler_instrumentos' ) ) :
    

class Alquiler_instrumentos {

    protected static $_instance = null;
    public $allowed_types, $wcai_page_name, $wcai_page_content, $wcai_page_url, $wcai_error_obj, $wcai_slug, $wp_rewrite;
    ///public $wcai_meses_alquiler, $wcai_precio_alquiler, $wcai_mensaje;
    
    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function  wcai_current_user_id() {
        return wp_get_current_user()->ID;
    }

    public function __construct() {
        global $endpoints, $wcai_current_user_id, $wcai_precio_alquiler, $wcai_mensaje;
        $plugin = plugin_basename( __FILE__ );
        $wcai_page_name = '';
        $wcai_page_content ='';
        $wcai_page_url = '';
        $wcai_cuenta_nueva = '';

        // Chequea si WooCommerce esta activo
        if ( $this->wcai_woocommerce_is_active() ) {

            // Inicia plugin
            add_action( 'plugins_loaded', array( $this, 'wcai_init' ), 10 );

            // Agrega enlace de ajustes
            add_filter( 'plugin_action_links_' . $plugin, array( $this, 'wcai_add_settings_link' ) );

            register_activation_hook( __FILE__, array( $this, 'wcai_activate' ) );
          
        }

    }
    

    /**
     * Run this on activation
     * Set a transient so that we know we've just activated the plugin
     */
    public function wcai_activate() {
        set_transient( 'wcai_activated', 1 );
    }

    /**
    * Get the current plugin version
    * @return str
    **/
    public function wcai_get_version() {
        return '0.9';
    }

    /**
    * Check if WooCommerce is active
    * @return bool
    **/
    private function wcai_woocommerce_is_active() {

        $active_plugins = (array) get_option( 'active_plugins', array() );
        if ( is_multisite() ) {
            $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
        }
        return ( array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) || in_array( 'woocommerce/woocommerce.php', $active_plugins ) );
    }

    /**
	* Inicia plugin
	**/
    public function wcai_init() {
        global $woocommerce, $wpdb, $wcai_page_content, $wcai_page_url, $wcai_page_name, $wcai_slug, $endpoints;
        
        // Define constantes
        $this->wcai_define_constants();
        
        //Tipos de productos permitidos para alquilar
        $this->allowed_types = apply_filters(
            'alquiler_instrumentos_allowed_product_types',
            array(
                'simple',
                'variable',
                'grouped',
                'bundle'
            )
        );

        // Common includes
        // Functions
           include_once( 'inc/wcai_functions.php');
        
        // ENQUEUED STYLES AND JAVASCRIPTS
            wp_enqueue_scripts( 'wcai-common-js',  WCAI_PLUGIN_URL . 'js/wcai-common.js', array( 'jquery' ), WCAI_PLUGIN_VERSION );
            wp_enqueue_script( 'wcai-common-js',  WCAI_PLUGIN_URL . 'js/wcai-common.js', array( 'jquery' ), WCAI_PLUGIN_VERSION,true );
            wp_enqueue_style( 'wcai-css', WCAI_PLUGIN_URL . 'css/wcai_styles.css', array(), WCAI_PLUGIN_VERSION +2*rand(1,2) );

        // Admin includes
        if ( is_admin() ) {
            // activa el panel administrativo del plugin
            add_action( 'admin_menu', array( $this, 'wcai_startup' ), 10);
			include plugin_dir_path(__FILE__) .'inc/wcai_admin_product.php';
			add_action( 'wp_print_scripts', array( $this, 'load_wcai_admin_js'), 10 );
			
        }
        
        // Frontend includes
        include_once( WCAI_PLUGIN_PATH . 'inc/wcai_product.php' );
        
        //Cuando se complete un pedido (se pague) se generan y almacenan los pagos futuros
			add_action( 'woocommerce_payment_complete', array( $this, 'call_wcai_add_future_payments' ), 10); 
        
        //Agrega mensaje de plan de pagos futuros en la pagina de agradecimiento   
			add_action( 'woocommerce_order_details_after_customer_details', array( $this, 'add_message_future_payments' ), 10);
           
        //Cuando se LIQUIDE UN ALQUILER (se pague) se ACTUALIZAN los pagos pendientes a "pagados" ("wc_completed")
			add_action( 'woocommerce_payment_complete', array( $this, 'call_wcai_complete_future_payments' ), 10); 
		   
		// Agrega la opcion de ver alquileres en el menu de navegacion de la pagina de "mi-cuenta"  
			add_filter( 'woocommerce_account_menu_items', array( $this, 'wcai_add_alquileres_menu'), 10 ); 
		
		// Substituye "Añadir al carrito" por "Más Información" en la Tienda 	
			add_action('woocommerce_loop_add_to_cart_link', array( $this, 'wcai_show_more_information' ), 10);
		//	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		//	remove_filter( 'woocommerce_loop_add_to_cart_link', 'woocommerce_template_loop_add_to_cart', 80 );
		//	add_action( 'woocommerce_after_shop_loop_item', array( $this, 'wcai_show_more_information'), 10);

           
       // Chequea si existen las paginas del carrito de alquiler, de finalizar alquiler 
       // y de gracias por alquilar, que será usadas exclusivamente por este plugin
       // si NO EXISTEN, las genera

       //Datos para crear la pagina del carrito de alquiler
         $wcai_page_name = 'Carrito Alquiler'; //echo 'Wcai Page Name = '.$wcai_page_name ;//die;
         $wcai_page_content = '<!-- wp:shortcode -->[wcai_carrito_alquiler]<!-- /wp:shortcode -->';
         $wcai_error_obj = true;
         $wcai_slug = 'carrito-alquiler';
         $wcai_page_url = site_url().'/'.$wcai_slug;
      
         if (! $this->existe_la_pagina( $wcai_slug )) {
             $resultado = $this->wcai_crear_pagina();
         }
   
        //Datos para crear la pagina de FACTURAR alquiler
         $wcai_page_name = 'Facturar Alquiler';
         $wcai_page_content = '<!-- wp:shortcode -->[wcai_facturar_alquiler]<!-- /wp:shortcode -->';
         $wcai_error_obj = true;
         $wcai_slug = 'facturar-alquiler';
         $wcai_page_url = site_url().'/'.$wcai_slug;
      
         if (! $this->existe_la_pagina( $wcai_slug )) {
             $resultado = $this->wcai_crear_pagina();
         }
         
         //Datos para crear la pagina de finalizar alquiler
         $wcai_page_name = 'Finalizar Alquiler';
         $wcai_page_content = '<!-- wp:shortcode -->[wcai_finalizar_alquiler]<!-- /wp:shortcode -->';
         $wcai_error_obj = true;
         $wcai_slug = 'finalizar-alquiler';
         $wcai_page_url = site_url().'/'.$wcai_slug;
      
         if (! $this->existe_la_pagina( $wcai_slug )) {
             $resultado = $this->wcai_crear_pagina();
         }
         
   
        //Datos para crear la pagina de gracias por alquilar
         $wcai_page_name = 'Gracias Por Alquilar';
         $wcai_page_content = '<!-- wp:shortcode -->[wcai_gracias_por_alquilar]<!-- /wp:shortcode -->';
         $wcai_error_obj = true;
         $wcai_slug = 'gracias-por-alquilar';
         $wcai_page_url = site_url().'/'.$wcai_slug;
      
         if (! $this->existe_la_pagina( $wcai_slug )) {
            // $resultado = $this->wcai_crear_pagina();
         }
         
         //Datos para crear la pagina del carrito de LIQUIDAR alquiler
         $wcai_page_name = 'Carrito Liquidar Alquiler';
         $wcai_page_content = '<!-- wp:shortcode -->[wcai_carrito_liquidar_alquiler]<!-- /wp:shortcode -->';
         $wcai_error_obj = true;
         $wcai_slug = 'carrito-liquidar-alquiler';
         $wcai_page_url = site_url().'/'.$wcai_slug;
      
         if (! $this->existe_la_pagina( $wcai_slug )) {
             $resultado = $this->wcai_crear_pagina();
         }
         
         //Datos para crear la pagina de FACTURAR la LIQUIDACION del alquiler
         $wcai_page_name = 'Facturar Liquidar Alquiler';
         $wcai_page_content = '<!-- wp:shortcode -->[wcai_facturar_liquidar_alquiler]<!-- /wp:shortcode -->';
         $wcai_error_obj = true;
         $wcai_slug = 'facturar-liquidar-alquiler';
         $wcai_page_url = site_url().'/'.$wcai_slug;
      
         if (! $this->existe_la_pagina( $wcai_slug )) {
             $resultado = $this->wcai_crear_pagina();
         }
         
         //Datos para crear la pagina de PAGAR LA LIQUIDACION DEL ALQUILER 
         $wcai_page_name = 'Liquidar Alquiler';
         $wcai_page_content = '<!-- wp:shortcode -->[wcai_liquidar_alquiler]<!-- /wp:shortcode -->';
         $wcai_error_obj = true;
         $wcai_slug = 'liquidar-alquiler'; 
         $wcai_page_url = site_url().'/'.$wcai_slug;
      
         if (! $this->existe_la_pagina( $wcai_slug )) {   //echo '<br/>Wcai Page Name = '.$wcai_slug ; //die;
             $resultado = $this->wcai_crear_pagina(); 
         }
         
        /**
                     * Crea los datos de pagos futuros del alquiler del producto EN LA BASE DE DATOS
                    **/
        // SI NO EXISTE, CREA LA TABLA DE LOS PAGOS MENSUALES
        $wpdb->query("
              CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wcai_pagos_mensuales ( 
                pago_id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT, 
                pago_user_id MEDIUMINT UNSIGNED NOT NULL , 
                pago_product_id MEDIUMINT UNSIGNED NOT NULL , 
                pago_order_id MEDIUMINT UNSIGNED NOT NULL, 
                pago_amount FLOAT NOT NULL, 
                pago_descripcion VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
                pago_fecha VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
                pago_fecha_completo VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
                pago_status VARCHAR(35) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
                pago_notas VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
                PRIMARY KEY (pago_id),
	             KEY pago_user_id (pago_user_id),
	              KEY pago_product_id (pago_product_id),
	               KEY pago_order_id (pago_order_id)) 
	          ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci;
			" );

		// Hook para agregar el ENDPOINT para la pagina "/mi-cuenta/alquileres
            add_action( 'init', array( $this, 'my_account_new_endpoints'), 10 );
    
        // Hook para agregar el  TEMPLATE o CONTENIDO para la pagina "/mi-cuenta/alquileres
         add_action( 'woocommerce_account_alquileres_endpoint', array( $this, 'alquileres_endpoint_content'), 10 ); 	         

    }
	
	/*
	* Agrega la columna de pago de alquileres al menu de la pagina de "mi-cuenta"  
	*/
    function wcai_add_alquileres_menu() { 
      $items = array(
        'dashboard'       => __( 'Dashboard', 'woocommerce' ),
        'orders'          => __( 'Orders', 'woocommerce' ),
        'alquileres'          => 'Alquileres', //Agrega esta opcion al menu en "mi-cuenta"
        'downloads'       => __( 'Downloads', 'woocommerce' ),
        'edit-address'    => __( 'Addresses', 'woocommerce' ),
        'payment-methods' => __( 'Payment methods', 'woocommerce' ),
        'edit-account'    => __( 'Account details', 'woocommerce' ),
        'customer-logout' => __( 'Logout', 'woocommerce' ),
        );
       return $items;   
     } 

	
	// Agrega el ENDPOINT para la pagina "/mi-cuenta/alquileres (Register Permalink Endpoint)
    public function my_account_new_endpoints() {
            add_rewrite_endpoint( 'alquileres', EP_ROOT | EP_PAGES );
     }
	
	 
    // Agrega el TEMPLATE o CONTENIDO para la pagina "/mi-cuenta/alquileres
    public function alquileres_endpoint_content() { 
          $ruta = WCAI_PLUGIN_PATH.'inc/views/html-wcai-alquileres.php';
          include($ruta);
    }
 
	//Carga el javascript para el admin
	public function load_wcai_admin_js() {
	    wp_enqueue_style( 'wp-color-picker' );
	    wp_enqueue_script( 'wcai-admin-js',  WCAI_PLUGIN_URL . 'js/wcai-admin.js', array( 'jquery' ), WCAI_PLUGIN_VERSION );
	    wp_enqueue_script( 'mi-color-picker-script', WCAI_PLUGIN_URL . 'js/wcai-admin.js', array( 'wp-color-picker' ), false, true );
		
	 }

    // Agrega la pagina/menu para el admin
	public function wcai_startup() {
        if(function_exists("add_options_page")) {
	 
	       // agrega la pagina de opciones del Alquiler al menu del Woocommerce
	       add_submenu_page( 'woocommerce', 
	  	                  'Alquiler Instrumentos', 
	  	                  '<font color="#A6C307">Alquiler</font>',  
	  	                  'manage_woocommerce', 
	  	                  'wcai-setting', 
	  	                  array( $this, 'wcai_settings_page' 
	  	                  ) );

      }
      
    }    
	
	# Muestra la pagina de ajustes o configuracion
    public function wcai_settings_page() {
        include plugin_dir_path(__FILE__) .'inc/wcai_admin_page.php';
    } 

    /**
           * Define constants
           * wcai_PLUGIN_FILE - Plugin directory
            **/
    private function wcai_define_constants() {
        // Plugin directory
        define( 'WCAI_PLUGIN_FILE', __FILE__ );
		define( 'WCAI_PLUGIN_PATH' , plugin_dir_path( __FILE__ ) );
		define( 'WCAI_PLUGIN_URL', plugin_dir_url(  __FILE__  ) );
		define( 'WCAI_PLUGIN_VERSION', $this->wcai_get_version() );
		require_once(ABSPATH . 'wp-admin/includes/file.php'); // Para obtener el home path. Sin esto lo de abajo no funciona y da error fatal
		define( 'SITE_HOME_DIR' , get_home_path() );
		define( 'SITE_URL' , site_url() );
        
     }
    

    /**
    * Add settings link
    **/
    public function wcai_add_settings_link( $links ) {
        $settings_link = '<a href="admin.php?page=alquiler-instrumentos">' . esc_html__( 'Settings', 'woocommerce-alquiler-instrumentos' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }
    
    /**
    *  Cambia "Añadir al carrito" por "Más Información" en la Tienda 
    */
    public function wcai_show_more_information() {
        global $product;
	    $precio_alquiler = $product->get_meta( '_precio_alquiler', true );
        echo '<span class="wcai_price"><span class="woocommerce-Price-amount amount">'.$precio_alquiler.' <span class="woocommerce-Price-currencySymbol">'.get_woocommerce_currency_symbol().'</span></span><small class="woocommerce-price-suffix"> al mes.</small></span>';
        
        echo '<a href="' .$product->get_permalink(). '" class="button addtocartbutton">Más Información</a>';
        echo '<script>
                // Javascript para esconder el precio de compra
                if (document.querySelectorAll(".price")) {
                    price_item = document.querySelectorAll(".price");
                    PriceLen = price_item.length;
        
                    for (i=0; i < PriceLen; i++ ) { //alert("Price Item = "+i);
                        //Oculta el Price Item
                        //price_item[i].style.display="none";
                     }
                }
             </script>';
	 }
		
    
    /**
    * Indica si un instrumentos es alquilable
    */
    public function alquiler_instrumentos_es_alquilable( $product_id, $variation_id = '' ) {
        $product = wc_get_product( empty( $variation_id ) ? $product_id : $variation_id );
        return wcai_is_alquilable( $product );
    }


    /* 
    * Crea una pagina con wp_insert_post() de Wordpress  
    */
    public function wcai_crear_pagina() {
        global  $wcai_page_content, $wcai_page_url, $wcai_page_name, $wcai_slug, $wcai_error_obj, $woocommerce, $wp_rewrite ;
        
	    $wcai_page_data = array(
		    'post_title'    => wp_strip_all_tags( $wcai_page_name ),
		    'post_content'  => $wcai_page_content,
		    'post_status'   => 'publish',
		    'post_type'     => 'page',
		    'post_author'   => 1,
		    'post_category' => array(1),
		    'page_template' => 'page.php',
		    'guid'          => $wcai_page_url,
	    ); 
	    $post_id = wp_insert_post( $wcai_page_data, $wcai_error_obj); 
	    if(!is_wp_error($post_id)){
            //the post is valid
         }else{
               //there was an error in the post insertion, 
                  echo '<br><br>'.$post_id->get_error_message().'<br><br>';
         }
    }

    /**
    * Busca si existe la pagina por el nombre o slug.
    * @return boolean falso si no existe la pagina.
    */
    public function existe_la_pagina( $wcai_slug ) {
        global $woocommerce, $wp_rewrite;
        $args_page = array(
            'post_type'      => 'page',
            'pagename'           => $wcai_slug
        );
        $loop_posts = new WP_Query( $args_page );
        if ( $loop_posts->have_posts() ) {
            // Reset the `$post` data to the current post in main query.
            wp_reset_postdata();
            return true;
        } else {
            // Reset the `$post` data to the current post in main query.
            wp_reset_postdata();
            return false; 
        }
    }
    
    /**
    * Busca y prepara los parámetros para crear los pagos futuros del alquiler 
    */
    public function call_wcai_add_future_payments($wcai_nro_orden) {///echo '<script>alert("LLEGUE A call_wcai_add_future_payments");</script>';
       
        global $woocommerce, $wpdb; 
        
        if ( $wcai_nro_orden =='') return false;
        
        $wcai_orden = wc_get_order($wcai_nro_orden);
        
        // Elimina valores anteriores 
        $wcai_nombre_producto_alquiler = '';
        $wcai_id_product = '';
        $wcai_date_from  = '';
        $wcai_date_to    = '';
        $wcai_total_alquiler = '';
        $wcai_total_alquiler = $wcai_orden->get_subtotal( 'view' ) + $wcai_orden->get_total_tax( 'view' ) - $wcai_orden->get_shipping_tax( 'view' );
        $wcai_date_from = date ("d/m/Y");
        
        // Busca autor o usuario de la orden
        $wcai_customer_id = get_post_meta( $wcai_nro_orden, $key = '_customer_user', $single = true ); 
        
        foreach ( $wcai_orden->get_items( 'line_item' ) as $item ) {
			if ( ! is_object( $item ) ) {
				continue;
			}

			if ( $item->is_type( 'line_item' ) ) {
				$wcai_nombre_producto_alquiler   = $item->get_name();
				$wcai_id_product = $item->get_product_id( 'view' );
				$wcai_tipo_alquiler = $item->get_meta( '_alquiler_type', true, 'view' );
			}
		}
        
        // Si no es un alquiler normal, se devuelve
        if ( $wcai_tipo_alquiler !=	'__alquiler__') return false;
        
        $product_data = wc_get_product($wcai_id_product);
        
        // Chequea si el producto es alquilable, si no es se regresa
        if ( ! wcai_is_alquilable( $product_data ))  return false;
        
        // Extrae la fecha final del nombre del item
        $wcai_date_to = substr($wcai_nombre_producto_alquiler,-10 );
/*        
  echo ' DESDE CALL_WCAI CON wcai_date_from = '.$wcai_date_from.' CON wcai_date_to = '.$wcai_date_to.' CON wcai_total_alquiler = '.$wcai_total_alquiler.' CON wcai_nombre_producto_alquiler = '.$wcai_nombre_producto_alquiler.'<br><br>';

  echo '<BR><BR>CON wcai_id_product = '.$wcai_id_product.' CON wcai_nombre_producto_alquiler = '.$wcai_nombre_producto_alquiler.'<br><br>';
echo 'CON wcai_nro_orden = '.$wcai_nro_orden . '=========> CUSTOMER_id = '. $wcai_customer_id  .'<br><br>';
/**/ 
        $this->wcai_add_future_payments($wcai_customer_id, $wcai_id_product, $wcai_nro_orden, $wcai_date_from, $wcai_date_to, $wcai_total_alquiler, $wcai_nombre_producto_alquiler);
    }

        
    /**
    * Crea y guarda el plan de pagos futuros del alquiler en la
    * base de datos 
    */
    public function wcai_add_future_payments($wcai_customer_id, $wcai_id_product, $wcai_nro_orden, $wcai_date_from, $wcai_date_to, $wcai_total_alquiler, $wcai_nombre_producto_alquiler) {
        global $woocommerce, $wpdb, $wcai_cuenta_nueva, $wcai_meses_alquiler, $wcai_precio_alquiler, $wcai_periodo_alquiler, $wcai_mensaje ; 

        // Chequeo de seguridad, si falta un dato se regresa
        if ( ($wcai_id_product=='') || ($wcai_nro_orden=='') || ($wcai_date_from=='') || ($wcai_date_to=='') || ($wcai_total_alquiler==0) || ($wcai_nombre_producto_alquiler=='')  ) 
            return false;
        
        /// Almacena los datos del plan de pagos para el alquiler del producto 
        $product_data = wc_get_product($wcai_id_product);
        
        // Chequea si el producto es alquilable, si no es se regresa
        if ( ! wcai_is_alquilable( $product_data ))  return false;
        
        $wcai_meses_alquiler = 0;
        $wcai_precio_alquiler  = $product_data->get_meta( '_precio_alquiler', true );
        $wcai_periodo_alquiler = $product_data->get_meta( '_periodo_alquiler', true );
        /*
        if ($wcai_periodo_alquiler == 0) $wcai_meses_alquiler = 12;
        if ($wcai_periodo_alquiler == 1) $wcai_meses_alquiler = 18;
        if ($wcai_periodo_alquiler == 2) $wcai_meses_alquiler = 24;
        */
        
       $wcai_meses_alquiler = $wcai_periodo_alquiler ;
       
      // SUBSTITUYE LOS '/' POR GUIONES
      $wcai_date_from = str_ireplace('/','-',$wcai_date_from);
      $wcai_date_to = str_ireplace('/','-',$wcai_date_to);
      $startdate=strtotime($wcai_date_from);
      $enddate=strtotime($wcai_date_to);
      $i = 0 ;

      while ($i < $wcai_meses_alquiler ) {
        $i ++;
        $fecha_vencimiento = date("d/m/Y", $startdate);
        $startdate = strtotime("+1 month", $startdate);
        ///echo $i.' ==> '.$fecha_vencimiento. "<br>";
        $pago_actual = 'Cuota del mes Nro:'.$i;
        $descripcion_del_pago  = str_replace('CUOTA INICIAL', $pago_actual, $wcai_nombre_producto_alquiler);
     	$pago_fecha_completo = 'por pagar';
     	$pago_status = 'wc_pending';
     	$pago_notas = ' ';
     	if ($i ==1) {
     	    $pago_fecha_completo = date('d/m/Y',  time());
     	    $pago_status = 'wc_completed';
     	    $pago_notas = ' Pagada al iniciar el contrato de alquiler';
     	    
     	 }
     	
        $resultado = $wpdb->get_results( "INSERT INTO {$wpdb->prefix}wcai_pagos_mensuales (pago_id, pago_user_id, pago_product_id, pago_order_id, pago_amount, pago_descripcion, pago_fecha, pago_fecha_completo, pago_status, pago_notas) 
                 VALUES (NULL, $wcai_customer_id, $wcai_id_product, $wcai_nro_orden, $wcai_total_alquiler,'$descripcion_del_pago', '$fecha_vencimiento', '$pago_fecha_completo', '$pago_status', '$pago_notas' )", OBJECT );

     }
     
     // Agrega una nota a la orden 
        $wcai_orden = wc_get_order($wcai_nro_orden);
        $wcai_note  = 'Se ha creado un plan de pagos mensuales por los próximos '.$wcai_meses_alquiler. ' meses por '.$wcai_precio_alquiler. ' '.get_woocommerce_currency_symbol().' cada uno, por el Plugin de Alquiler (by wcai_add_future_payments)';
        $wcai_orden->add_order_note( $wcai_note );
        
    if ($wcai_cuenta_nueva == 'yes') {
        // Agrega una nota a la orden 
             $wcai_note  = 'Se ha creado un Cuenta Nueva y los datos de acceso fueron enviados al correo: '.$data['billing_email'] ;
             $wcai_orden->add_order_note( $wcai_note );
         }
         

     // VACIA EL CARRITO DE COMPRAS PARA ASEGURAR INICIO DESDE CERO 
        WC()->cart->empty_cart();

    }
    
    /**
    * Agrega mensaje especial en la pagina de agradecimiento por el alquiler del producto 
    */
    public function add_message_future_payments($wcai_nro_orden) {
        global $woocommerce, $wpdb, $wcai_cuenta_nueva; 
        
        if ( $wcai_nro_orden =='') return false;
        
        $wcai_orden = wc_get_order($wcai_nro_orden);
        
        foreach ( $wcai_orden->get_items( 'line_item' ) as $item ) {
			if ( ! is_object( $item ) ) {
				continue;
			}

			if ( $item->is_type( 'line_item' ) ) {
				$wcai_id_product = $item->get_product_id( 'view' );
				$wcai_tipo_alquiler = $item->get_meta( '_alquiler_type', true, 'view' );
			}
		}
		
		// Chequea si el producto es alquilable, si no es se regresa
		$product_data = wc_get_product($wcai_id_product);
        if ( ! wcai_is_alquilable( $product_data ))  return false;
        
        // Si no es un alquiler normal, o no es una liquidacion se devuelve
        if (($wcai_tipo_alquiler !=	'__alquiler__') && ($wcai_tipo_alquiler !=	'__liquidar__')) return false;
        
        /// Agrega mensajes a la pantalla del cliente.
        $wcai_mensaje = '<br/><br/><p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">';
        
        if ($wcai_tipo_alquiler ==	'__alquiler__') 
                       $wcai_mensaje .= '<b>Se ha creado un plan de pagos mensuales por los próximos meses  de alquiler.</b><br/>';
                       
        $wcai_mensaje .=  'Para revisar su cuenta, sus pedidos y otros datos use este boton ===> <a class="button " href="'.SITE_URL.'/mi-cuenta/" >Ver Mi Cuenta</a><br>';
        $wcai_mensaje .=  '</p>';
        
        echo $wcai_mensaje;
        
     }
       
    
    /**
    * Busca y prepara los parámetros para LIQUIDAR (ACTUALIZAR) los pagos futuros del alquiler 
    */
    public function call_wcai_complete_future_payments($wcai_nro_orden) {
        global $woocommerce, $wpdb; 
        $wcai_orden = wc_get_order($wcai_nro_orden);
        
        // Elimina valores anteriores 
        $wcai_nombre_producto_alquiler = '';
        $wcai_id_product = '';
        
        // Busca autor o usuario de la orden
        $wcai_customer_id = get_post_meta( $wcai_nro_orden, $key = '_customer_user', $single = true ); ////$wcai_customer_id = get_post($wcai_nro_orden)->post_author;
        
        // Busca el nombre del alquiler, el id del producto alquilado y la nro. orden a liquidar 
        foreach ( $wcai_orden->get_items( 'line_item' ) as $item ) {
			if ( ! is_object( $item ) ) {
				continue;
			}

			if ( $item->is_type( 'line_item' ) ) {
				$wcai_nombre_producto_alquiler   = $item->get_name();
				$wcai_id_product = $item->get_product_id( 'view' );
				$wcai_nro_orden_liquidar = $item->get_meta( '_order_liquidar_id', true, 'view' );
				$wcai_tipo_alquiler = $item->get_meta( '_alquiler_type', true, 'view' );
			}
		}
        
        // Si no es una LIQUIDACION DEL ALQUILER, se devuelve
           if ( $wcai_tipo_alquiler !=	'__liquidar__') return false;
/*        
  echo ' DESDE CALL_WCAI_COMPLETE CON wcai_nro_orden_liquidar = '.$wcai_nro_orden_liquidar.' CON wcai_nro_orden = '.$wcai_nro_orden.'<br>';

  echo '<BR>CON wcai_id_product = '.$wcai_id_product.' CON wcai_nombre_producto_alquiler = '.$wcai_nombre_producto_alquiler.'<br><br>';
echo 'CON =========> CUSTOMER_id = '. $wcai_customer_id  .'<br><br>';

/**/ 
        $this->wcai_complete_future_payments($wcai_customer_id, $wcai_id_product, $wcai_nro_orden_liquidar, $wcai_nro_orden);
        
        // Agrega una nota a la orden 
        $wcai_note  = 'Se ha LIQUIDADO (pagado en su totalidad) el Alquiler de la orden Nro. '.$wcai_nro_orden_liquidar;
        $wcai_orden->add_order_note( $wcai_note );
    }


    /**
    * ACTUALIZA (liquida) los pagos futuros del alquiler en la
    * base de datos (los pone "pagados")
    */
    public function wcai_complete_future_payments($wcai_customer_id, $wcai_id_product, $wcai_nro_orden_liquidar, $wcai_nro_orden ) {
        global $woocommerce, $wpdb ; 

        // Chequeo de seguridad, si falta un dato se regresa
        if ( ($wcai_id_product=='') || ($wcai_nro_orden_liquidar=='')  ) 
            return false;
        
        /// Almacena los datos del plan de pagos para el alquiler del producto 
        $product_data = wc_get_product($wcai_id_product);
        
        // Chequea si el producto es alquilable, si no es se regresa
        if ( ! wcai_is_alquilable( $product_data ))  return false;
        
        $fecha_de_pago = date('d/m/Y',  time()); 
 
        /// Actualiza los datos del plan de pagos para el alquiler del producto 
        /// pasa cada cuota pendiente al estado "pagado" y pone la fecha del pago
        /// Actualiza los registros de este usuario, de este producto, de esta orden y que ESTEN PENDIENTES DE PAGO
        $resultado = $wpdb->get_results( "UPDATE {$wpdb->prefix}wcai_pagos_mensuales SET pago_fecha_completo = '$fecha_de_pago', pago_status = 'wc_completed', pago_notas = ' Pagada al Liquidar el alquiler según pedido Nro:$wcai_nro_orden' WHERE pago_user_id = $wcai_customer_id AND pago_product_id = $wcai_id_product AND pago_order_id = $wcai_nro_orden_liquidar AND pago_status LIKE 'wc_pending'", OBJECT );

    } 


}  //// FINAL DE LA CLASE Alquiler_instrumentos 

function wcai() {
    return Alquiler_instrumentos::instance();
}

$wcai = wcai();  // Activa o inicia la clase del plugin

endif;

    
    //ShortCodes
    // activa el shortcode para la pagina del "carrito de alquiler" exclusiva de este plugin  <!-- wp:shortcode --><!-- /wp:shortcode -->
        add_shortcode ('wcai_carrito_alquiler',  'wcai_presenta_carrito');
                    
    // activa el shortcode para la pagina de "FACTURAR alquiler" exclusiva de este plugin facturar-alquiler
        add_shortcode ('wcai_facturar_alquiler',  'wcai_presenta_facturar_alquiler');
        
    // activa el shortcode para la pagina de "FINALIZAR alquiler" exclusiva de este plugin 
        add_shortcode ('wcai_finalizar_alquiler',  'wcai_presenta_finalizar_alquiler');
        
        
    // activa el shortcode para la pagina de "gracias por alquilar" exclusiva de este plugin
       // add_shortcode ('wcai_gracias_por_alquilar',  'wcai_presenta_gracias_por_alquilar');
        
    // activa el shortcode para la pagina de "carrito liquidar alquiler" exclusiva de este plugin
        add_shortcode ('wcai_carrito_liquidar_alquiler',  'wcai_presenta_carrito_liquidar_alquiler');
        
    // activa el shortcode para la pagina de "pagar liquidar alquiler" exclusiva de este plugin
        add_shortcode ('wcai_facturar_liquidar_alquiler',  'wcai_presenta_facturar_liquidar_alquiler');
 
    // activa el shortcode para la pagina de "gracias por liquidar" exclusiva de este plugin
        add_shortcode ('wcai_liquidar_alquiler',  'wcai_presenta_liquidar_alquiler');
 
    /*
    * Presenta la página del carrito con las datos del alquiler del instrumento o producto 
    */
    function wcai_presenta_carrito() {
        global $woocommerce;
        include_once( WCAI_PLUGIN_PATH .'inc/wcai_add_to_cart.php' ); 
      }

                   
    /*
    * Presenta la página de FACTURAR con las datos del alquiler del instrumento o producto 
    */
    function wcai_presenta_facturar_alquiler() {
        global $woocommerce;
        include_once( WCAI_PLUGIN_PATH .'inc/wcai_checkout_page.php' ); 
      }

    /*
    * Presenta la página de FINALIZAR o PAGAR con las datos del alquiler del instrumento o producto 
    */
    function wcai_presenta_finalizar_alquiler() {
        global $woocommerce;
        //echo 'GRACIAS POR COMPRAR O PAGAR ESTE ALQUILER';
        include_once( WCAI_PLUGIN_PATH .'inc/wcai_order_pay_page.php' ); 
      }


    /*
    * Presenta la página del carrito con las datos para LIQUIDAR el alquiler del instrumento o producto 
    */
    function wcai_presenta_carrito_liquidar_alquiler() {
        global $woocommerce;
        include_once( WCAI_PLUGIN_PATH .'inc/wcai_liquidar_add_to_cart.php' ); 
      }

    /*
    * Presenta la página de FACTURAR la LIQUIDACION del alquiler del instrumento o producto 
    */
    function wcai_presenta_facturar_liquidar_alquiler() {
        global $woocommerce;
        include_once( WCAI_PLUGIN_PATH .'inc/wcai_liquidar_checkout_page.php' ); 
      }

    /*
    * Presenta la página de FINALIZAR o PAGAR LA LIQUIDACION del alquiler del instrumento o producto 
    */
    function wcai_presenta_liquidar_alquiler() {
        global $woocommerce;
        ///echo 'GRACIAS POR LIQUIDAR O PAGAR TOTALMENTE ESTE ALQUILER';
        include_once( WCAI_PLUGIN_PATH .'inc/wcai_order_liquidar_pay_page.php' ); //wcai_order_liquidar_pay_page.php
      }



