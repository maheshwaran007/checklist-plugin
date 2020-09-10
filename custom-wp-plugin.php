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
 *Custom checkbox shortcode creation --Start
 */
function custom_checkbox($atts,$content = null){	
	$checkbox_attr = shortcode_atts( array(
		'name' => '',
	), $atts );	
	if ( has_shortcode( $content, 'button' ) ) {
		// The content has a short code, so this check returned true.		
		$sub_shortcode_parts = preg_split("/\[button /i", $content);
		$sub_shortcode_array = array();
		foreach($sub_shortcode_parts as $sub_shortcode){
			if($sub_shortcode !='' && !empty($sub_shortcode)){
				$sub_shortcode_array[] = $sub_shortcode;
			}	
		}			
		$sub_shortcode_str = '';
		$checkbox_attr_str = '';
		foreach($sub_shortcode_array as $sub_shortcode_arrays){	 
	       if($checkbox_attr['name'] !=''){
			  $sub_shortcode_str = '[button checkboxname="'.$checkbox_attr["name"].'" '.$sub_shortcode_arrays;
			  $checkbox_attr_str .= do_shortcode($sub_shortcode_str);
	       }
		} 
    }
			  
	return $checkbox_attr_str;
}
add_shortcode('customcheckbox', 'custom_checkbox');
/*
 * Custom checkbox shortcode creation --End
 * Button shortcode creation --Start
 */
 function custom_checkbox_button($atts,$content = null){
	
	$checkbox_button_attr = shortcode_atts( array(
		'name' => '',
		'checkboxname' => '',
	), $atts );
	$checkbox_button_attr_str = '';	
	if($checkbox_button_attr['name'] !='' && $checkbox_button_attr['checkboxname']){
		$checkboxid = $checkbox_button_attr['checkboxname']."_".$checkbox_button_attr['name'];
		$page_id = get_the_ID();
		$ajax_url = admin_url( 'admin-ajax.php' );
		$checkboxfunarg  = '"'.$checkboxid.'","'.$ajax_url.'","'.$page_id.'"'; 
		$selected_value = checkbox_select_display($page_id,$checkboxid);
	    $checkbox_button_attr_str = "<label for='".$checkboxid."' onchange='checkboxevent(".$checkboxfunarg.")'>";
		$checkbox_button_attr_str .= "<input type='checkbox' id='".$checkboxid."' name='".$checkboxid."' value='".$checkbox_button_attr['name']."' ".$selected_value.">".$checkbox_button_attr['name']."</input></label>";	
	}
	return $checkbox_button_attr_str;	
}
add_shortcode('button', 'custom_checkbox_button');
/*
 * Button shortcode creation --End
 * Custom checkbox action to save checkbox details --Start
 */
add_action( 'wp_ajax_custom_checkbox_action', 'custom_checkbox_action' );
function custom_checkbox_action() {
    global $wpdb,$wp_query;
	$checkboxname = $_REQUEST['checkboxname'];
	$checkboxvalue = $_REQUEST['checkboxvalue'];
	$current_page_id = $_REQUEST['page_id'];	
	$usert_id = get_current_user_id();	
	$current_date = date("Y-m-d h:i:s");
    if($checkboxname && $checkboxvalue && $usert_id){
		$table_name = $wpdb->prefix . 'custom_checkbox_details';		
		$result = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE checkboxname LIKE '%".$checkboxname."%' and pageid LIKE '%".$current_page_id."%'"); 		
		if($wpdb->num_rows > 0 ){
		   $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET checkboxvalue = %s , updated_date = %s
             WHERE checkboxname = %s and pageid=%s",$checkboxvalue,$current_date,$checkboxname,$current_page_id)
           );
		}else{				
			$wpdb->insert($table_name, array(
				'checkboxname' => $checkboxname,
				'checkboxvalue' => $checkboxvalue,
				'userid' => $usert_id,
				'pageid' => $current_page_id,
				'created_date' => $current_date
			));
		}
	}
	wp_die();
}
/*
 * Custom checkbox action to save checkbox details --End
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

function delete_custom_checkbox_details() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'custom_checkbox_details';
	$sql = "DROP TABLE IF EXISTS $table_name";
	$wpdb->query($sql); 
}
register_deactivation_hook( __FILE__, 'delete_custom_checkbox_details' );

add_action( 'wp_enqueue_scripts', 'custom_checkbox_callback', 20, 0 );
function custom_checkbox_callback() {	
	   wp_register_style('checkboxstyle', WPCC_PLUGIN_URL.'/css/style.css');
       wp_enqueue_style('checkboxstyle');	
	   wp_enqueue_script('checkboxscript', WPCC_PLUGIN_URL.'/js/common-function.js'); 
}

function checkbox_select_display($page_id,$checkboxid){
	global $wpdb;
	$table_name = $wpdb->prefix . 'custom_checkbox_details';		
	$result = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE checkboxname LIKE '%".$checkboxid."%' and pageid LIKE '%".$current_page_id."%'");
	if($wpdb->num_rows > 0 ){		
		$checkboxvalue = $result[0]->checkboxvalue;
		$checkbox_str = '';
		if($checkboxvalue == 'checked'){
			$checkbox_str = 'checked';
		}
        return $checkbox_str;		
	}	
}	