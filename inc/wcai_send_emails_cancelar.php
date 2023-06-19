<?php
/*
* Envia correos al Admin y al usuario sobre una peticion de CANCELAR una orden de alquiler
* File: wcai_send_emails_cancelar.php
*/

$site_url = '';
$site_dir = '';

if(isset($_POST['site_url'])) $site_url = $_POST['site_url'];
if(isset($_POST['site_dir'])) $site_dir = $_POST['site_dir'];  

/// PARA USAR LAS FUNCIONES Y VENTAJAS DEL WORDPRESS
 define('WP_USE_THEMES', false);

 /**
 * Loads the WordPress environment and template.
 *
 * @package WordPress
 */

 if ( !isset($wp_did_header) ) {

  	$wp_did_header = true;

	// Load the WordPress library.
  	require_once( $site_dir . '/wp-load.php' );
 
	// Set up the WordPress query
   	//wp();
   	
	// Load the theme template.
   	require_once( ABSPATH . WPINC . '/template-loader.php' );
 
  }

if(!defined('ABSPATH')) exit;

global $wpdb;
$nro_orden = '';
$usuario_actual = '';

if(isset($_POST['nro_orden'])) $nro_orden = $_POST['nro_orden'];
if(isset($_POST['usuario_actual'])) $usuario_actual = $_POST['usuario_actual'];

// Envia el correo al cliente
    $wcai_send_to_client = wcai_send_email_cancelar_notification($nro_orden, 'cliente');

 if ($wcai_send_to_client) {
    $mensaje = 'SE ENVIO EL CORREO AL CLIENTE EXITOSAMENTE, - ';
    $estatus = 'exito';
 } else {
       $mensaje = 'HAY ERRORES AL ENVIAR EL CORREO AL CLIENTE, NO FUE ENVIADO, - ';
       $estatus = 'error'; 
 }
 
// Envia el correo al admin
    $wcai_send_to_admin = wcai_send_email_cancelar_notification($nro_orden, 'admin');

 if ($wcai_send_to_admin) {
    $mensaje .= 'SE ENVIO EL CORREO AL ADMINISTRADOR EXITOSAMENTE';
    $estatus .= '-exito';
 } else {
       $mensaje .= 'HAY ERRORES AL ENVIAR EL CORREO AL ADMINISTRADOR, NO FUE ENVIADO';
       $estatus .= '-error'; 
 }

// Crea el objeto con la respuesta
 $respuesta = ['estatus' =>$estatus,
               'nro_orden' => $nro_orden,
               'mensaje' => $mensaje
               ];

// Codifica el objeto para trasmision
$respuesta = json_encode($respuesta, JSON_FORCE_OBJECT);

echo $respuesta; 


/**
* Send Notification Email to Admin & Client about an order alquiler cancelation
* @param int $order_id, string $send_to
*/
function  wcai_send_email_cancelar_notification( $order_id, $send_to ) {

    $order = wc_get_order( $order_id ); 
    // load the mailer class
    $mailer = WC()->mailer();

    // Envia el correo al cliente
    if ($send_to == 'cliente') {
        $recipient = $order->get_billing_email();
        $subject = 'Tu Solicitud de Cancelar el Alquiler #'.$order_id.' en ['. get_bloginfo().']';
        $content =  wcai_get_email_client_cancelar_content( $order, $subject, $mailer );
     } 

    // Envia el correo al admin del sitio
    if ($send_to == 'admin') {
        $recipient = get_option( 'admin_email' );
        $subject = 'Nueva Solicitud de Cancelar el Alquiler #'.$order_id.' en ['. get_bloginfo().']';
        $content =  wcai_get_email_admin_cancelar_content( $order, $subject, $mailer );
     } 
        
    $headers = "Content-Type: text/html\r\n";
    return $mailer->send( $recipient, $subject, $content, $headers );
}

/**
* Get content html for client.
* @param WC_Order $order
* @param str $heading
* @param obj $mailer
* @return string
*/
function  wcai_get_email_client_cancelar_content( $order, $heading = false, $mailer ) {
    $template = 'emails/customer-processing-order.php';
    $wcai_email_content_template = wc_get_template_html( $template, array(
        'order'         => $order,
        'email_heading' => $heading,
        'sent_to_admin' => true,
        'plain_text'    => false,
        'email'         => $mailer,
        'additional_content' => 'Gracias por su atención'
    ) );
    
    // Ajusta el mensaje del correo 
    $wcai_email_content_template = str_ireplace ( 'hemos recibido tu pedido' , 'hemos recibido Tu Solicitud de Cancelar el Alquiler ' , $wcai_email_content_template); 

    return $wcai_email_content_template;
}

/**
* Get content html for admin.
* @param WC_Order $order
* @param str $heading
* @param obj $mailer
* @return string
*/
function  wcai_get_email_admin_cancelar_content( $order, $heading = false, $mailer ) {
    $template = 'emails/admin-cancelled-order.php';
    $wcai_email_content_template = wc_get_template_html( $template, array(
        'order'         => $order,
        'email_heading' => $heading,
        'sent_to_admin' => true,
        'plain_text'    => false,
        'email'         => $mailer,
        'additional_content' => 'Gracias por su atención'
    ) );
    
    // Ajusta el mensaje del correo 
    $wcai_email_content_template = str_ireplace ( 'el pedido' , 'el Alquiler ' , $wcai_email_content_template); 
    $wcai_email_content_template = str_ireplace ( 'ha sido cancelado:' , 'tiene una Solicitud de <b>CANCELAR el Alquiler</b>. Los datos de este alquiler son: ' , $wcai_email_content_template); 
   
    return $wcai_email_content_template;
}

