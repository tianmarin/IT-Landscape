<?php
defined('ABSPATH') or die("No script kiddies please!");

/**
* Registers and enqueues plugin-specific scripts.
*/
function ita_register_ajaxmethods_scripts() {
	wp_register_script(
		'ita_ajax_methods',
		plugins_url( 'js/ajax_methods.js' , __FILE__),
		array('jquery', 'jquery-ui-core','jquery-ui-autocomplete','jquery-ui-tooltip','jquery-ui-button'),
		'1.0'
	);
	wp_enqueue_script(	'ita_ajax_methods');
	wp_localize_script(
		'ita_ajax_methods',
		'ita_ajax',
		array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))
	);
}

//------------------------------------------------------------------------------------------------------------------------------------
add_action( 'wp_ajax_ita_select_get_system_by_landscape', 'ita_select_get_system_by_landscape' );

function ita_select_get_system_by_landscape(){
	global $SYSTEM;
	if( isset( $_POST['landscape_id'] ) && is_numeric( $_POST['landscape_id'] ) ) {	
		$sql="SELECT id,sid FROM $SYSTEM->tbl_name WHERE landscape_id=".$_POST['landscape_id'];
		$systems= $SYSTEM->get_sql($sql);
		echo '<option value="0">Selecciona '.$SYSTEM->name_single.'</option>';
		foreach($systems as $system){
			$option = '<option value="'.$system['id'].'">'.$system['sid'].'</option>';
			echo $option;
		}
	}	
	die();
}
?>