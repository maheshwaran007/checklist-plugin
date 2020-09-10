function checkboxevent(checkboxid,ajax_url,page_id){	
	var checkboxvalue = '';	
	if(jQuery('#'+checkboxid).is(':checked')) {          
		jQuery('#'+checkboxid).attr('checked');
		checkboxvalue = 'checked';
	}else{
		checkboxvalue = 'unchecked';
	}	
	var ajaxData = {
	'action': 'custom_checkbox_action',
	'checkboxname': checkboxid,
	'checkboxvalue':checkboxvalue,
	'page_id':page_id
	}		  
	jQuery.post(ajax_url, ajaxData, function(response){	
	  //alert(response);
	});		  	
}		