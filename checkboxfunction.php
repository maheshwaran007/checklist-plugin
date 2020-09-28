<?php
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
		$result = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE checkboxname LIKE '%".$checkboxname."%' and pageid LIKE '%".$current_page_id."%' and userid LIKE '%".$usert_id."%'"); 		
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
 * Login user selected checkbox display  --Start
 */
 function checkbox_select_display($page_id,$checkboxid,$usert_id){
	global $wpdb;
	$table_name = $wpdb->prefix . 'custom_checkbox_details';		
	$result = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE checkboxname LIKE '%".$checkboxid."%' and pageid LIKE '%".$page_id."%' and userid LIKE '%".$usert_id."%'");
	if($wpdb->num_rows > 0 ){		
		$checkboxvalue = $result[0]->checkboxvalue;
		$checkbox_str = '';
		if($checkboxvalue == 'checked'){
			$checkbox_str = 'checked';
		}
		return $checkbox_str;		
	}	
}	
/*
 * Login user selected checkbox display  --End
 */

function custom_checkbox_button_new($atts,$content = null){
	
	$checkbox_button_attr = shortcode_atts( array(
		"name" => "",
		"group" => "",
	), $atts );	
	$checkbox_button_attr_str = '';	
	$checkbox_button_name = str_replace("'", '', $checkbox_button_attr['name']);
	$checkbox_name = str_replace("'", '', $checkbox_button_attr['group']);	
	if($checkbox_button_name !='' && $checkbox_name){
		if($checkbox_name){
		$checkboxid = trim($checkbox_name."_".$checkbox_button_name);
		$page_id = get_the_ID();
		$ajax_url = admin_url( 'admin-ajax.php' );
		$checkboxfunarg  = '"'.$checkboxid.'","'.$ajax_url.'","'.$page_id.'"'; 
		$usert_id = get_current_user_id();
		$selected_value = checkbox_select_display($page_id,$checkboxid,$usert_id);
		$checkbox_button_attr_str = "<label class='checkboxlabel' for='".$checkboxid."' onchange='checkboxevent(".$checkboxfunarg.")'>";
	 	$checkbox_button_attr_str .= "<input type='checkbox' id='".$checkboxid."' name='".$checkboxid."' value='".$checkbox_button_name."' ".$selected_value."></input><span class='checkmarkspan'></span></label>";
	 	}


	}else{
		
	   $checkbox_button_attr_str = '<p class="error_msg">Please add button shortcode name Attribute or group Attribute<p>';
	} 

	
	return $checkbox_button_attr_str;	
}
add_shortcode('ljimbutton', 'custom_checkbox_button_new');