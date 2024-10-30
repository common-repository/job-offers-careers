jQuery(document).ready(function () {
	
	jQuery( ".btn-jobsearchine-login" ).click(function() {
		
		var email = jQuery( "#email" ).val();
		var password = jQuery( "#password" ).val();
		var nonce = jQuery('#jobsearchine_admin_nonce').val();

    if(password) {				  
    jQuery.ajax({
        url: atomixstar.ajaxurl, 
        cache: false,
        data: {action:'login_api',email:email,password:password,nonce:nonce},                         
        type: 'POST',
		dataType: 'json',
        success: function(data){
		if(data.status == 'success') {
			 location.reload();
		jQuery( "#api_key" ).val(data.api_key);
		jQuery( "#msg_login" ).html(data.msg);
		
		}else if(data.status == 'error') {
		jQuery( "#msg_login" ).html(data.msg);	
		}
        }
     });
	 
	 
	
	}else{
	jQuery( "#msg_login" ).html('Password cannot be empty.');	
	}
});


	jQuery( ".btn-jobsearchine" ).click(function() {
		
		var name = jQuery( "#rname" ).val();
		var email = jQuery( "#remail" ).val();
		var domain = jQuery( "#domain" ).val();
		var password = jQuery( "#rpassword" ).val();
		var c_password = jQuery( "#c_password" ).val();
		var nonce = jQuery('#jobsearchine_admin_nonce').val();
 
    if(password) {
    if(password === c_password) {					  
    jQuery.ajax({
        url: atomixstar.ajaxurl, 
        cache: false,
        data: {action:'get_api',name:name,email:email,domain:domain,password:password,nonce:nonce},                         
        type: 'POST',
		dataType: 'json',
        success: function(data){
		if(data.status == 'success') {
		jQuery( "#api_key" ).val(data.api_key);
		jQuery( "#msg" ).html(data.msg);
		location.reload();
		}else if(data.status == 'error') {
		jQuery( "#msg" ).html(data.msg);	
		}
        }
     });
	 
	 
	}else{
	jQuery( "#msg" ).html('Password does not match.');	
	}
	}else{
	jQuery( "#msg" ).html('Password cannot be empty.');	
	}
});

var gettext = jQuery( "#shortcode_show" ).text();
jQuery( "#shortcode_for_copy" ).val(gettext);
jQuery(document).on('input', '.shortcode_gen', function(e) {

var this_id = jQuery( this ).attr('id');
var the_value = jQuery( this ).val();
if(this_id == 'shortcode_keyword') {
	jQuery( "#keyword_txt" ).html(the_value);
	
}else if(this_id == 'shortcode_location') {
	jQuery( "#location_txt" ).html(the_value);
	
}else if(this_id == 'shortcode_country') {
	jQuery( "#country_txt" ).html(the_value);
	
}else if(this_id == 'shortcode_language') {
	jQuery( "#language_txt" ).html(the_value);
	
}else if(this_id == 'shortcode_limit') {
	jQuery( "#limit_txt" ).html(the_value);
	
}

var gettext = jQuery( "#shortcode_show" ).text();
jQuery( "#shortcode_for_copy" ).val(gettext);


});




jQuery( ".btn-jobsearchine-view" ).click(function() {
	
	jQuery( ".lds-roller" ).show();
		
		var api = jQuery( "#api_key" ).val();
		var keyword = jQuery( "#shortcode_keyword" ).val();
		var location = jQuery( "#shortcode_location" ).val();
		var country = jQuery( "#shortcode_country" ).val();
		var nonce = jQuery('#jobsearchine_admin_nonce').val();
 
    if(api && location && country) {
					  
    jQuery.ajax({
        url: atomixstar.ajaxurl, 
        cache: false,
        data: {action:'test_api',api:api,keyword:keyword,location:location,country:country,nonce:nonce},                         
        type: 'POST',
        success: function(data){
		jQuery( "#jobs-view" ).html(data);
		jQuery( ".lds-roller" ).hide();
        }
     });
	 
	 
	
	}else{
		jQuery( ".lds-roller" ).hide();
	jQuery( "#view-msg" ).html('Please fill all the required fields.');	
	}
});











});