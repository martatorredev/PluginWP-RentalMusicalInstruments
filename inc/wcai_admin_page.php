<?php
/**
 * settings page for Woocommerce Alquiler De Instrumentos
 * File: wcai_admin_page.php
 */

/// GUARDA LOS VALORES DE CONFIGURACION EN LA BASE DE DATOS DEL WORDPRESS
///---------------------------------------------------------------------- 
 if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "guardar_opciones"){
 /// SANA LOS DATOS
    
   if (!isset($_POST['wcai_open_new_window'])) $_POST['wcai_open_new_window'] = 'no';
  
/* echo '<br/>LLEGA a wcai_admin_page.php UNA PETICION CON <b>' .json_encode($_POST).'</b></br>';
   var_dump($_POST);
   //die();
/**/
 
// $wcai_enabled      = sanitize_text_field($_POST['wcai_enabled']); 
 $wcai_open_new_window  = sanitize_text_field($_POST['wcai_open_new_window']); 
 $wcai_descuento_01 = sanitize_text_field($_POST['wcai_descuento_01']);
 $wcai_descuento_02 = sanitize_text_field($_POST['wcai_descuento_02']);
 $wcai_bgcolor_alquiler = sanitize_text_field($_POST['wcai_bgcolor_alquiler']);
 $wcai_font_color_alquiler = sanitize_text_field($_POST['wcai_font_color_alquiler']);
 $wcai_show_buy_button = sanitize_text_field($_POST['wcai_show_buy_button']);
 
 /// ALMACENA EN WORDPRESS LOS DATOS DE LOS MATERIALES DE ESTE MODULO
//    update_option( 'wcai_enabled', $wcai_enabled); 
    update_option( 'wcai_open_new_window', $wcai_open_new_window);
	update_option( 'wcai_descuento_01', $wcai_descuento_01 ); 
	update_option( 'wcai_descuento_02', $wcai_descuento_02 );
	update_option( 'wcai_bgcolor_alquiler', $wcai_bgcolor_alquiler);
    update_option( 'wcai_font_color_alquiler', $wcai_font_color_alquiler);
    update_option( 'wcai_show_buy_button', $wcai_show_buy_button);

echo '<div id="mensaje_01" class="updated notice is-dismissible" style="padding: 5px; margin:0px; ">
            <p>La Nueva Configuración Ha Sido Guardada.</p>
            <button type="button" class="notice-dismiss">
               <span class="screen-reader-text">Descartar este aviso.</span>
            </button>
          </div>';  
 }	 
?>

	<div class="wrap ">
	  <form method="post" id="mainform" action="" enctype="multipart/form-data">
        <h2 class="same_line">Ajustes de Alquiler de Instrumentos </h2>
		<img class="same_line" src="<?php echo dirname(plugin_dir_url(__FILE__)) . '/images/logo_rentara.jpg'; ?>" alt="RentaraMusic Logo" width="80">
		<p>La Forma Más Fácil de Alquilar Instrumentos Musicales con Woocommerce</p>
	    <table class="form-table" border="0" role="presentation">		
		<!--   <tr valign="top">
			   <th scope="row" class="title_desc" >
				 <label for="wcai_enabled">Habilitar/Deshabilitar</label>
			   </th>
			   <td class="forminp">
		       <fieldset>
					<legend class="screen-reader-text"><span>Habilitar/Deshabilitar</span></legend>
					  <?php /*
					      //Woocommerce Alquiler Instrumentos DESHABILITADO
						  $checked = '';
						  $value_checkbox = 0;
					      if (get_option('wcai_enabled') =='1') {
							 //Woocommerce Alquiler Instrumentos ESTA HABILITADO
							 $checked = ' checked="checked" '; 
							 $value_checkbox = 1;
						  }
					      echo '<input id="wcai_enabler" onclick="CheckEnabled();" class="input-text regular-input " type="checkbox" name="wcai_enabler" value="'.$value_checkbox.'" '.$checked.' /> Habilita Este Plugin para Alquilar Instrumentos Musicales en Woocommerce.<br/><br/>';
						  //JAVASCRIPT PARA LEER EL TILDADO DEL CHECKBOX
						  echo '<script>
						          function CheckEnabled() {
	                    		   if (document.getElementById("wcai_enabler").checked) {
										   document.getElementById("wcai_enabled").value="1";
									 } else {
		                          	      document.getElementById("wcai_enabled").value="0";
									}
                                  };
								</script>';
						echo '<br/><br/><br/>'; */
                          ?>
                          
                        <input id="wcai_enabled" class="input-text regular-input " type="hidden" name="wcai_enabled" value="<?php echo $value_checkbox.'" ';?> />
                    </fieldset>
					<p class="description">Puedes Activar/Desactivar este plugin aqui mismo.</p>
				
			</td>
		   </tr> -->

		<tr valign="top">
			<th scope="row" class="titledesc" >
				<label for="wcai_texto_descuento_01" >Descuento hasta 6 meses: <span class="wcai_help_tip" data-tip="Este es el % de descuento entre uno y seis meses de alquiler." ></span></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span>Descuento hasta 6 meses:</span></legend>
					<input class="input-text regular-input " type="text" name="wcai_descuento_01" id="wcai_descuento_01" value="<?php echo esc_attr(get_option('wcai_descuento_01'));?>" title="Coloque el % de descuento para el cliente de 1 a 6 meses de alquiler." style="width: 34px; cursor: pointer;"  /> % 
				</fieldset>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" class="titledesc" >
				<label for="wcai_texto_descuento_02">Descuento de 7 a 12 meses: <span class="wcai_help_tip" data-tip="Este es el % de descuento entre siete y doce meses de alquiler."></span></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span>Descuento de 7 a 12 meses:</span></legend>
					<input class="input-text regular-input " type="text" name="wcai_descuento_02" id="wcai_descuento_02" value="<?php echo esc_attr(get_option('wcai_descuento_02'));?>" title="Coloque el % de descuento para el cliente de 7 a 12 meses de alquiler."  style="width: 34px; cursor: pointer;" /> %
				</fieldset>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wcai_show_buy_button">Mostrar el Botón de Comprar<span class="woocommerce-help-tip" data-tip="Oculta o Muestra el botón de comprar en la página de venta."></span></label>
			</th>
			<td class="forminp forminp-radio">
				<fieldset>
					<ul> <?php $wcai_show_buy_button = get_option('wcai_show_buy_button'); ?>
						<li><label><?php echo '<input name="wcai_show_buy_button" id="wcai_show_buy_button" value="yes" type="radio" style="" class="" '.($wcai_show_buy_button == 'yes' ? 'checked="checked"' : '' ).' /> Sí, Muestra el botón de comprar en la página de venta.</label>';?>
						</li>
						<li><label><?php echo '<input name="wcai_show_buy_button" id="wcai_show_buy_button" value="no" type="radio" style="" class="" '.($wcai_show_buy_button == 'no' ? 'checked="checked"' : '' ).' /> No, Sólo Muestra el <b>Botón de ALQUILAR</b> en la página de venta.</label>';?>
						</li>
					</ul>
				</fieldset>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" class="title_desc" >
			   <label for="wcai_open_new_window">Abrir Ventanas Nuevas:</label>
			</th>
			<td class="forminp">
		       <fieldset>
					<legend class="screen-reader-text"><span>Habilitar/Deshabilitar</span></legend>
					  <?php 
					      //Woocommerce Abrir Ventanas Nuevas DESHABILITADO
						  $checked_new_window = '';
						  $value_checkbox_new_window = 'no';
					      if (get_option('wcai_open_new_window') =='yes') {
							 //Woocommerce Abrir Ventanas Nuevas ESTA HABILITADO
							 $checked_new_window = ' checked="checked" '; 
							 $value_checkbox_new_window = 'yes';
						  }
					      echo '<input id="wcai_open_new_window" onclick="CheckEnabled(\'wcai_open_new_window\');" class="input-text regular-input " type="checkbox" name="wcai_open_new_window" value="'.$value_checkbox_new_window.'" '.$checked_new_window.' /> Hacer el Proceso de Compra en Ventanas Nuevas o en La Misma Ventana.';
						  //JAVASCRIPT PARA LEER EL TILDADO DEL CHECKBOX
						  echo '<script>
						          function CheckEnabled(id_elemento) {
						          ///alert("ID_ELEMENTO = " + id_elemento);
	                    		   if (document.getElementById(id_elemento).checked) {
										   document.getElementById(id_elemento).value="yes";
									 } else {
		                          	      document.getElementById(id_elemento).value="no"; 
									}
                                  };
								</script>';
						
                          ?>
                    <p class="description">Puedes Abrir el Proceso de Compra en Ventanas Nuevas o en La Actual.</p>
                </fieldset>
			</td>
		   </tr> 
		   
          <tr valign="top">
			<th scope="row" class="titledesc" >
				<label for="wcai_bgcolor_alquiler" >Color de Fondo Ventana de Alquileres: <span class="wcai_help_tip" data-tip="Este es el Color de la ventana emergente con el detalle del alquiler." ></span></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span>Color de Fondo Ventana de Alquileres:</span></legend>
					<input class="my-color-field" type="text" name="wcai_bgcolor_alquiler" id="wcai_bgcolor_alquiler" value="<?php echo esc_attr(get_option('wcai_bgcolor_alquiler'));?>" title="Coloque el Color de Fondo de la ventana emergente con el detalle del alquiler." style=""  /> 
				</fieldset>
			</td>
		</tr>

        <tr valign="top">
			<th scope="row" class="titledesc" >
				<label for="wcai_font_color_alquiler" >Color de Fuentes en Ventana de Alquileres: <span class="wcai_help_tip" data-tip="Este es el Color de las letras en la ventana emergente con el detalle del alquiler." ></span></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span>Color de Fondo Ventana de Alquileres:</span></legend>
					<input class="my-color-field" type="text" name="wcai_font_color_alquiler" id="wcai_font_color_alquiler" value="<?php echo esc_attr(get_option('wcai_font_color_alquiler'));?>" title="Coloque el Color de las letras en la ventana emergente con el detalle del alquiler." style=""  /> 
				</fieldset>
			</td>
		</tr>

		</table>		
		<p class="submit">
			<button name="save" class="button-primary woocommerce-save-button" type="submit" value="Guardar los cambios">Guardar los cambios</button>
			<input type="hidden" name="action" value="guardar_opciones" />
		</p> 
	</form>
</div>


<?php 
 
 
 
 