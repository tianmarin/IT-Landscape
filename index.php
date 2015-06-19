<?php
/*
 * Plugin Name: IT Administration
 * Plugin URI: http://intraeph.noviscorp.com:81/
 * Description: This Plugin, has been developed by Cristian Marin, towards the NovisCorp S.A. de C.V Intranet Administration.
 * Version: 1.0.0
 * Author: Cristian Marin
 * Author URI: http://twitter.com/cmarin
 * Text Domain: Optional. Plugin's text domain for localization. Example: mytextdomain
 * Domain Path: Optional. Plugin's relative directory path to .mo files. Example: /locale/
 */
defined('ABSPATH') or die("No script kiddies please!");

//Global variable for Class, useful for accessing class functions as well as a global variable store
require_once("functions.php");
require_once("ajax_methods.php");
require_once("js_methods.php");

//global $ITA_ADM;
//$ITA_ADM = "";
global $wpdb;
global $ita_vars;

$ita_vars = array(
//Plugin Conf. Variables
	'DEBUG'								=> TRUE,
	'plugin_option_name'				=> 'ita_options',							//Plugin Option (Wordpress default) name
	'plugin_post'						=> 'Y21hcmlu',								//(base64_encode(cmarin) security 'from' request
	'plugin_shortcode'					=> 'ita',									//used by plugin association in shortcodes
//DataBase Tables
	'country'				."_tbl_name"	=> 'z_ita_'	.'country',
	'customer'				."_tbl_name"	=> 'z_ita_'	.'customer',
	'landscape'				."_tbl_name"	=> 'z_ita_'	.'landscape',
	'system'				."_tbl_name"	=> 'z_ita_'	.'system',
	'environment'			."_tbl_name"	=> 'z_ita_'	.'environment',
	'system_type'			."_tbl_name"	=> 'z_ita_'	.'system_type',
	'vendor'				."_tbl_name"	=> 'z_ita_'	.'vendor',
	'product_type'			."_tbl_name"	=> 'z_ita_'	.'product_type',
	'product'				."_tbl_name"	=> 'z_ita_'	.'product',
	'system_has_db'			."_tbl_name"	=> 'z_ita_'	.'system_has_db',
	'db_engine'				."_tbl_name"	=> 'z_ita_'	.'db_engine',
	'db'					."_tbl_name"	=> 'z_ita_'	.'db',
	'interface'				."_tbl_name"	=> 'z_ita_'	.'interface',
	'instance'				."_tbl_name"	=> 'z_ita_'	.'instance',
	'instance_type'			."_tbl_name"	=> 'z_ita_'	.'instance_type',
	'host_conf'				."_tbl_name"	=> 'z_ita_'	.'host_conf',
	'host'					."_tbl_name"	=> 'z_ita_'	.'host',
	'host_type'				."_tbl_name"	=> 'z_ita_'	.'host_type',
	'interface'				."_tbl_name"	=> 'z_ita_'	.'interface',
	'host_has_interface'	."_tbl_name"	=> 'z_ita_'	.'host_has_interface',
	'so_type'				."_tbl_name"	=> 'z_ita_'	.'so_type',
	'so'					."_tbl_name"	=> 'z_ita_'	.'so',
	'kernel_release'		."_tbl_name"	=> 'z_ita_'	.'kernel_release',


//Menu Slugs
	'main'					."_menu_slug"		=> 'z_ita_'.'main',
	'admin'					."_menu_slug"		=> 'z_ita_'.'admin',
	'super'					."_menu_slug"		=> 'z_ita_'.'super',
	'country'				."_menu_slug"		=> 'z_ita_'.'country'				.'_menu',
	'customer'				."_menu_slug"		=> 'z_ita_'.'customer'				.'_menu',
	'landscape'				."_menu_slug"		=> 'z_ita_'.'landscape'				.'_menu',
	'system'				."_menu_slug"		=> 'z_ita_'.'system'				.'_menu',
	'environment'			."_menu_slug"		=> 'z_ita_'.'environment'			.'_menu',
	'system_type'			."_menu_slug"		=> 'z_ita_'.'system_type'			.'_menu',
	'vendor'				."_menu_slug"		=> 'z_ita_'.'vendor'				.'_menu',
	'product_type'			."_menu_slug"		=> 'z_ita_'.'product_type'			.'_menu',
	'product'				."_menu_slug"		=> 'z_ita_'.'product'				.'_menu',
	'system_has_db'			."_menu_slug"		=> 'z_ita_'.'system_has_db'			.'_menu',
	'db_engine'				."_menu_slug"		=> 'z_ita_'.'db_engine'				.'_menu',
	'db'					."_menu_slug"		=> 'z_ita_'.'db'					.'_menu',
	'interface'				."_menu_slug"		=> 'z_ita_'.'interface'				.'_menu',
	'instance'				."_menu_slug"		=> 'z_ita_'.'instance'				.'_menu',
	'instance_type'			."_menu_slug"		=> 'z_ita_'.'instance_type'			.'_menu',
	'host_conf'				."_menu_slug"		=> 'z_ita_'.'host_conf'				.'_menu',
	'host'					."_menu_slug"		=> 'z_ita_'.'host'					.'_menu',
	'host_type'				."_menu_slug"		=> 'z_ita_'.'host_type'				.'_menu',
	'host_type_conf'		."_menu_slug"		=> 'z_ita_'.'host_type_conf'		.'_menu',
	'interface'				."_menu_slug"		=> 'z_ita_'.'interface'				.'_menu',
	'host_has_interface'	."_menu_slug"		=> 'z_ita_'.'host_has_interface'	.'_menu',
	'so_type'				."_menu_slug"		=> 'z_ita_'.'so_type'				.'_menu',
	'so'					."_menu_slug"		=> 'z_ita_'.'so'					.'_menu',
	'kernel_release'		."_menu_slug"		=> 'z_ita_'.'kernel_release'		.'_menu',

//Menu Capabilities
	'main'					."_menu_cap"		=> 'administrators',
	'admin'					."_menu_cap"		=> 'administrators',
	'super'					."_menu_cap"		=> 'administrators',
	'customer'				."_menu_cap"		=> 'edit_others_pages',
	'landscape'				."_menu_cap"		=> 'edit_pages',
	'system'				."_menu_cap"		=> 'edit_pages',
	'environment'			."_menu_cap"		=> 'edit_others_pages',
	'system_type'			."_menu_cap"		=> 'edit_others_pages',
	'vendor'				."_menu_cap"		=> 'edit_others_pages',
	'product_type'			."_menu_cap"		=> 'edit_others_pages',
	'product'				."_menu_cap"		=> 'edit_others_pages',
	'system_has_db'			."_menu_cap"		=> 'edit_others_pages',
	'db_engine'				."_menu_cap"		=> 'edit_others_pages',
	'db'					."_menu_cap"		=> 'edit_pages',
	'interface'				."_menu_cap"		=> 'edit_pages',
	'instance'				."_menu_cap"		=> 'edit_pages',
	'instance_type'			."_menu_cap"		=> 'edit_others_pages',
	'host_conf'				."_menu_cap"		=> 'edit_others_pages',
	'host'					."_menu_cap"		=> 'edit_others_pages',
	'host_type'				."_menu_cap"		=> 'edit_others_pages',
	'host_type_conf'		."_menu_cap"		=> 'edit_others_pages',
	'interface'				."_menu_cap"		=> 'edit_others_pages',
	'host_has_interface'	."_menu_cap"		=> 'edit_others_pages',
	'so_type'				."_menu_cap"		=> 'edit_others_pages',
	'so'					."_menu_cap"		=> 'edit_others_pages',
	'kernel_release'		."_menu_cap"		=> 'edit_others_pages',
);





//---------------------------------------------------------------------------------------------------------------------------------------------------------
if($ita_vars['DEBUG']):
	define( 'DIEONDBERROR', true );
endif;




//---------------------------------------------------------------------------------------------------------------------------------------------------------

function ita_register_menu_style(){
	wp_register_style('ita_admin_style', plugins_url('css/style.css', __FILE__) );
	wp_enqueue_style('ita_admin_style');
//	wp_register_script("lesscss",plugins_url( 'js/less.js' , __FILE__ ));
//	wp_enqueue_script("lesscss");
//	echo '<link rel="stylesheet/less" type="text/css" media="all" href="'.plugins_url( 'css/style.css' , __FILE__ ).'">';

}
add_action( 'admin_menu', 'ita_register_menu_style' );
function ita_register_main_menu(){
	global $ita_vars;
	$page_title	="IT Admin - Landscape";
	$menu_title	="IT NOVIS";
	$capability	=$ita_vars['main_menu_cap'];
	$menu_slug	=$ita_vars['main_menu_slug'];
	$function	="ita_main_menu";	
	$icon_url	='dashicons-exerpt-view';
	$position	="100";
	add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}
add_action( 'admin_menu', 'ita_register_main_menu' );

function ita_main_menu() {
	global $ita_vars;
	echo '<div class="wrap">';
	echo '<h2>NOVIS</h2>';
	require_once("intro.php");
	echo '</div>';
}
//---------------------------------------------------------------------------------------------------------------------------------------------------------
function ita_register_admin_menu(){
	global $ita_vars;
	$page_title	="IT Admin";
	$menu_title	="IT Admin";
	$capability	=$ita_vars['admin_menu_cap'];
	$menu_slug	=$ita_vars['admin_menu_slug'];
	$function	="ita_admin_menu";
	$icon_url	='dashicons-businessman';
	$position	="101";	
	add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}
add_action( 'admin_menu', 'ita_register_admin_menu' );

function ita_admin_menu() {
	echo '<div class="wrap">';
	echo '<h2>NOVIS</h2>';
	require_once("intro.php");
	echo '</div>';
}

//---------------------------------------------------------------------------------------------------------------------------------------------------------


function ita_register_super_menu(){
	global $ita_vars;
	$page_title	="IT Super Administrador";
	$menu_title	="IT Super Admin";
	$capability	=$ita_vars['super_menu_cap'];
	$menu_slug	=$ita_vars['super_menu_slug'];
	$function	="ita_main_menu";	
	$icon_url	='dashicons-nametag';
	$position	="102";
	add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	wp_register_style("lesscss",plugins_url( 'js/less.js' , __FILE__ ));
		wp_enqueue_script("lesscss");

}
add_action( 'admin_menu', 'ita_register_super_menu' );

function ita_super_menu() {
}

//---------------------------------------------------------------------------------------------------------------------------------------------------------
require_once("ita_class.php");
require_once("ita_customer.php");
require_once("ita_landscape.php");
require_once("ita_environment.php");
require_once("ita_vendor.php");
require_once("ita_product_type.php");
require_once("ita_product.php");
require_once("ita_db_engine.php");
require_once("ita_db.php");
require_once("ita_kernel_release.php");
require_once("ita_system_type.php");
require_once("ita_so_type.php");
require_once("ita_so.php");
require_once("ita_host_type.php");
require_once("ita_interface.php");
require_once("ita_instance_type.php");
//require_once("ita_host_type.php");
//require_once("ita_host_type_conf.php");

require_once("ita_host.php");
require_once("ita_system.php");
require_once("ita_host_has_interface.php");
require_once("ita_instance.php");







require_once("shortcodes.php");

?>
