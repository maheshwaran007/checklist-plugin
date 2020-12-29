<?php
/*
Plugin Name: Custom WP Plugin
Description: Custom WP Plugin
Text Domain: custom-wp-plugin
Version: 1.0.0
*/

define( 'WPCC_PLUGIN', __FILE__ );
define( 'WPCC_PLUGIN_BASENAME', plugin_basename( WPCC_PLUGIN ) );
define( 'WPCC_PLUGIN_NAME', trim( dirname( WPCC_PLUGIN_BASENAME ), '/' ) );
define( 'WPCC_PLUGIN_DIR', untrailingslashit( dirname( WPCC_PLUGIN ) ) );
define( 'WPCC_PLUGIN_URL',
	untrailingslashit( plugins_url( '', WPCC_PLUGIN ) ) );

/*
 * Creating custom_checkbox_details table --Start
 */
function custom_checkbox_details() {
	global $wpdb;
	global $db_version;
	$table_name = $wpdb->prefix . 'custom_checkbox_details';
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		userid varchar(255) NOT NULL,
		checkboxname varchar(255) NOT NULL,
		checkboxvalue varchar(255) DEFAULT '' NOT NULL,
		pageid varchar(200) NOT NULL,
		created_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		updated_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );	
	add_option( 'db_version', $db_version );
}
register_activation_hook( __FILE__, 'custom_checkbox_details' );
/*
 * Creating custom_checkbox_details table --End
 * Delete custom_checkbox_details table --Start
 */
function delete_custom_checkbox_details() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'custom_checkbox_details';	
	$sql = "DROP TABLE IF EXISTS $table_name";
	$wpdb->query($sql); 	
}
register_deactivation_hook( __FILE__, 'delete_custom_checkbox_details' );
/*
 * Delete custom_checkbox_details table --End
 */
function delete_checkbox($user_id){
	global $wpdb;
	$table_name = $wpdb->prefix . 'custom_checkbox_details';	
	$result = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE userid = ".$user_id."");	
	if($wpdb->num_rows > 0 ){	
	  $wpdb->get_results("DELETE FROM ".$table_name." WHERE userid = ".$user_id.""); 	
	}
}	
add_action( 'delete_user', 'delete_checkbox' );
/*
 * Delete custom_checkbox_details table --End
 * Adding style and script file --Start
 */
add_action( 'wp_enqueue_scripts', 'custom_checkbox_callback', 20, 0 );
function custom_checkbox_callback() {	
	   wp_register_style('checkboxstyle', WPCC_PLUGIN_URL.'/css/style.css');
       wp_enqueue_style('checkboxstyle');	
	   wp_enqueue_script('checkboxscript', WPCC_PLUGIN_URL.'/js/common-function.js'); 
	   //wp_enqueue_script('checkboxjquery', WPCC_PLUGIN_URL.'/js/jquery.js');
}
require_once WPCC_PLUGIN_DIR . '/checkboxfunction.php';
/*
 * Adding style and script file --End
 */