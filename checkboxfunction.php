<?php
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
			  $page_id = get_the_ID();
			  //checkbox name checked with master table
			  echo $checkboxname_result = checkboxname_check($checkbox_attr["name"],$page_id);
			  if($checkboxname_result == 'new'){
			      $sub_shortcode_str = '[button checkboxname="'.$checkbox_attr["name"].'" '.$sub_shortcode_arrays;
			      $checkbox_attr_str .= do_shortcode($sub_shortcode_str);
			  }else{
				$checkbox_attr_str = '<p class="error_msg">Checkbox name already exit.<p>';  
			  } 
	       }else{
			  $checkbox_attr_str = '<p class="error_msg">Please add checkbox shortcode name Attribute.<p>';
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
		$usert_id = get_current_user_id();
		$selected_value = checkbox_select_display($page_id,$checkboxid,$usert_id);
	    $checkbox_button_attr_str = "<label class='checkboxlabel' for='".$checkboxid."' onchange='checkboxevent(".$checkboxfunarg.")'>";
		$checkbox_button_attr_str .= "<input type='checkbox' id='".$checkboxid."' name='".$checkboxid."' value='".$checkbox_button_attr['name']."' ".$selected_value.">".$checkbox_button_attr['name']."</input><span class='checkmarkspan'></span></label>";	
	}else{
	   $checkbox_button_attr_str = '<p class="error_msg">Please add button shortcode name Attribute<p>';
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
	$result = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE checkboxname LIKE '%".$checkboxid."%' and pageid LIKE '%".$current_page_id."%' and userid LIKE '%".$usert_id."%'");
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

function checkboxname_check($checkbox_name,$page_id){
	global $wpdb;
	$table_name = $wpdb->prefix . 'custom_master_checkbox';		
	 $result = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE checkboxname LIKE '%".$checkbox_name."%' and pageid LIKE '%".$page_id."%'");
	$checkbox_str = '';
	$current_date = date("Y-m-d h:i:s");	
	echo $wpdb->num_rows;
	if($wpdb->num_rows > 0 ){	
	     echo $checkbox_check = $result[0]->display;
		  if($checkbox_check == 'yes'){
			   $checkbox_str = 'exit';
			  /* $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET checkboxvalue = %s , updated_date = %s
             WHERE checkboxname = %s and pageid=%s",$checkboxvalue,$current_date,$checkboxname,$current_page_id)
           );*/
			  $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET display = %s WHERE checkboxname = %s and pageid=%s",'no',$checkbox_name,$page_id));
				
		  }else{
			 $checkbox_str = 'new';
			 $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET display = %s 
             WHERE checkboxname = %s and pageid=%s",'yes',$checkbox_name,$page_id));
          }   			  
		  //$checkbox_str = 'exit';
         /*if($wpdb->num_rows == 1){
			$checkbox_str = 'new';
		 }else{			 
		  
		 }*/
	}else{
		 $checkbox_str = 'new';
		 $wpdb->insert($table_name, array(	        
				'checkboxname' => $checkbox_name,				
				'pageid' => $page_id,
				'created_date' => $current_date,
				'display' => 'yes'
			));
	}	
	return $checkbox_str;	
}	

