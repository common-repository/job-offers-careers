jQuery(document).ready(function($) {


	function getjobs() {
		
	var q = jQuery('#jobsearchine_query').val();
	var l = jQuery('#jobsearchine_location').val();
	var country = jQuery('#jobsearchine_country').val();
	var lang = jQuery('#jobsearchine_language').val();
	var page = jQuery('#jobsearchine_jobs_page').val();
	var start = jQuery('#jobsearchine_jobs_start').val();
	var limit = jQuery('#jobsearchine_jobs_limit').val();
	var nonce = jQuery('#jobsearchine_nonce').val();
                             
    jQuery.ajax({
        url: jobsearchine_frontend.ajaxurl, 
        cache: false,
        data: {action:'get_jobs',query:q,location:l,country:country,language:lang,page:page,start:start,limit:limit,nonce:nonce},                         
        type: 'POST',
		dataType: 'json',
        success: function(data){
		
		if(data.jobs) {
        jQuery('#jobsearchine_jobs').html(data.jobs);
		jQuery('#jobsearchine_jobs_pagination').html(data.pagination);
		}else{
		jQuery('#jobsearchine_jobs').html('<p>No data has been found.</p>');	
		}

        }
     });

	}
	
	

jQuery(document).on("click", '.page-number', function(event) { 

jQuery([document.documentElement, document.body]).animate({
        scrollTop: $("#jobsearchine_jobs").offset().top
    }, 1000);
		var page = jQuery(this).data('pg');
		var start = jQuery(this).data('start');
		jQuery('#jobsearchine_jobs_page').val(page);
		jQuery('#jobsearchine_jobs_start').val(start);
getjobs();

	});
	
	
	jQuery(document).on("click", '#search_jobs', function(event) { 
	jQuery('#jobsearchine_jobs_page').val(0);
	jQuery('#jobsearchine_jobs_start').val(0);
	jQuery('#jobsearchine_query').val(jQuery('#job_keyword').val());
	jQuery('#jobsearchine_location').val(jQuery('#job_location').val());
	getjobs();
	
	});
	
	
	
	
	getjobs();
	
	
	
	
	
	
	
	function widget_getjobs() {
		
	var q = jQuery('#widget_jobsearchine_query').val();
	var l = jQuery('#widget_jobsearchine_location').val();
	var country = jQuery('#widget_jobsearchine_country').val();
	var lang = jQuery('#widget_jobsearchine_language').val();
	var page = jQuery('#widget_jobsearchine_jobs_page').val();
	var start = jQuery('#widget_jobsearchine_jobs_start').val();
	var limit = jQuery('#widget_jobsearchine_jobs_limit').val();
	var nonce = jQuery('#jobsearchine_widget_nonce').val();

                             
    jQuery.ajax({
        url: jobsearchine_frontend.ajaxurl, 
        cache: false,
        data: {action:'widget_get_jobs',query:q,location:l,country:country,language:lang,page:page,start:start,limit:limit,nonce:nonce},                         
        type: 'POST',
		dataType: 'json',
        success: function(data){
		
		if(data.jobs) {
        jQuery('#widget_jobsearchine_jobs').html(data.jobs);
		jQuery('#widget_jobsearchine_jobs_pagination').html(data.pagination);
		}else{
		jQuery('#widget_jobsearchine_jobs').html('<p>No data has been found.</p>');	
		}

        }
     });

	}
	
	

jQuery(document).on("click", '.widget_page-number', function(event) { 

jQuery([document.documentElement, document.body]).animate({
        scrollTop: $("#widget_jobsearchine_jobs_con").offset().top
    }, 1000);

		var page = jQuery(this).data('pg');
		var start = jQuery(this).data('start');
		jQuery('#widget_jobsearchine_jobs_page').val(page);
		jQuery('#widget_jobsearchine_jobs_start').val(start);
widget_getjobs();

	});
	
	
	jQuery(document).on("click", '.widget_search_jobs', function(event) { 
	jQuery('#widget_jobsearchine_jobs_page').val(0);
	jQuery('#widget_jobsearchine_jobs_start').val(0);
	jQuery('#widget_jobsearchine_query').val(jQuery('#widget_job_keyword').val());
	jQuery('#widget_jobsearchine_location').val(jQuery('#widget_job_location').val());
	widget_getjobs();
	
	});
	
	
	
	
	widget_getjobs();
	
	
	
	
	
	
	
	
	
	
	
	
	
	

});