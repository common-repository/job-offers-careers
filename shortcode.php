<?php

add_action('wp_ajax_get_jobs', 'get_jobsapi_callback');
add_action('wp_ajax_nopriv_get_jobs', 'get_jobsapi_callback');

function get_jobsapi_callback()
{

    $query = sanitize_text_field($_POST['query']);
    $location = sanitize_text_field($_POST['location']);
    $country = sanitize_text_field($_POST['country']);
    $language = sanitize_text_field($_POST['language']);
    $page = sanitize_text_field($_POST['page']);
    $start = sanitize_text_field($_POST['start']);
    $limit = sanitize_text_field($_POST['limit']);
	$nonce = sanitize_text_field($_POST['nonce']);
	
	if(isset($nonce) && $nonce !== null && wp_verify_nonce( $nonce, 'jobsearchine_jobs' )) {
		
	

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $api_key = get_option('jobsearchine_api', null);
    if ($api_key !== null) {
        $api_key = unserialize($api_key);
    }
	


    $get_data = wp_remote_get(esc_url_raw('http://api.jobsearchine.com/apicall?publisher=' . $api_key . '&q=' . $query . '&l=' . $location . '&country=' . $country . '&language=' . $language . '&start=' . $start . '&limit=' . $limit . '&ip=' . $ip . '&useragent=JobSearchine'));






    $jobs = new SimpleXMLElement(wp_remote_retrieve_body($get_data));

    $result = '
	<div class="jobsearchine_row" id="jobsearchine_jobs">
	<div class="jobsearchine-col-5">
    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="search" class="svg-inline--fa fa-search fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path></svg>
	<input id="job_keyword" class="jobsearchine_input" type="text" name="job_keyword" value="' . $query . '" placeholder="keywords, company, skills ..." autofocus="">
	</div>
	<div class="jobsearchine-col-5">
	<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="map-marker-alt" class="svg-inline--fa fa-map-marker-alt fa-w-12" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z"></path></svg>
	<input id="job_location" class="jobsearchine_input" type="text" name="job_location" value="' . $location . '" placeholder="city, region..." tabindex="2">
	</div>
	<div class="jobsearchine-col-2">
	<span id="search_jobs" class="btn btn-primary">Find jobs</span>
	</div>
	</div>';

    $i = 0;
    foreach ($jobs->result as $job) {

        $result .= '<div class="jobsearchine_job">
		<a href="' . $job->url . '" target="_blank">
		<div class="jobcompany_logo"><img src="' . $job->logo . '"></div>
		<div class="jobcontent">
		<h2>' . $job->jobtitle . '</h2>
		<span class="jobcompany">' . $job->company . '</span>
		<span class="jobaddress">' . $job->formattedLocation . '</span>
		<span class="jobdescription">' . $job->description . '</span>
		</div>
		</a>
		</div>';
    }

    $i = 0;
    $pagination = '<div class="pagination">';


    $pages = ceil($jobs->totalResults / $limit);

    $newstart = 0;
    $total_pages = ceil($jobs->totalResults / $limit);
    $start_loop = 1;


    $end_loop = $start_loop + $total_pages - 1;
    if ($page > 1) {
        $pagination .= '<a class="bg-page-number" data-pg="1" data-start="0" data-limit="' . $limit . '" style="padding-left:0;top: 5px;position: relative;">
<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="caret-left" class="svg-inline--fa fa-caret-left fa-w-6" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 512"><path fill="currentColor" d="M192 127.338v257.324c0 17.818-21.543 26.741-34.142 14.142L29.196 270.142c-7.81-7.81-7.81-20.474 0-28.284l128.662-128.662c12.599-12.6 34.142-3.676 34.142 14.142z"></path></svg></a>';

    }
    for ($i = $start_loop; $i <= $end_loop; $i++) {
        if ($i > 1) {
            $newstart = $newstart + $limit;
        }
        if ($page == $i) {
            $pagination .= "<a class='page-number active' data-pg='" . $i . "' data-start='" . $newstart . "' data-limit='" . $limit . "'>" . $i . "</a>";
        } else {
            $pagination .= "<a class='page-number' data-pg='" . $i . "' data-start='" . $newstart . "' data-limit='" . $limit . "'>" . $i . "</a>";
        }
    }
    if ($page <= $end_loop && $total_pages > 1) {
        $pagination .= '<a class="bg-page-number" data-pg="' . $i . '" data-start="' . $newstart . '" data-limit="' . $limit . '" style="top: 5px;position: relative;">
<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="caret-right" class="svg-inline--fa fa-caret-right fa-w-6" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 512"><path fill="currentColor" d="M0 384.662V127.338c0-17.818 21.543-26.741 34.142-14.142l128.662 128.662c7.81 7.81 7.81 20.474 0 28.284L34.142 398.804C21.543 411.404 0 402.48 0 384.662z"></path></svg></a>';

    }

    $pagination .= '</div>';


    $response = array('jobs' => $result, 'pagination' => $pagination, 'test' => $get_data);

    echo json_encode($response);

    wp_die();
	
	
	
	}else{
		
	$response = array('jobs' => 'Session Expired. Please refresh your page.', 'pagination' => '', 'test' => '');

    echo json_encode($response);
		
	wp_die();
		
		
	}
	
}

function jobs_shortcode($atts)
{
    global $post;
    $a = shortcode_atts(array(
        'q' => '',
        'l' => '',
        'country' => 'us',
        'lang' => '',
        'limit' => '15'
    ), $atts);

    if (isset($_GET['pg'])) {
        $page = sanitize_text_field($_GET['pg']);
    } else {
        $page = 0;
    }
	
	$nonce = wp_create_nonce( 'jobsearchine_jobs' );
	
    $result = '
	<div class="jobsearchine_container">
	<input type="hidden" value="' . esc_attr($nonce) . '" id="jobsearchine_nonce" name="jobsearchine_nonce">
	<input type="hidden" value="' . esc_attr($a['q']) . '" id="jobsearchine_query" name="jobsearchine_query">
	<input type="hidden" value="' . esc_attr($a['l']) . '" id="jobsearchine_location" name="jobsearchine_location">
	<input type="hidden" value="' . esc_attr($a['country']) . '" id="jobsearchine_country" name="jobsearchine_country">
	<input type="hidden" value="' . esc_attr($a['lang']) . '" id="jobsearchine_language" name="jobsearchine_language">
	<input type="hidden" value="' . esc_attr($page) . '" id="jobsearchine_jobs_page" name="jobsearchine_jobs_page">
	<input type="hidden" value="0" id="jobsearchine_jobs_start" name="jobsearchine_jobs_start">
	<input type="hidden" value="' . esc_attr($a['limit']) . '" id="jobsearchine_jobs_limit" name="jobsearchine_jobs_limit">
	<div id="jobsearchine_jobs"></div>
	<div id="jobsearchine_by"><span>Jobs by <a href="https://www.jobsearchine.com/" target="_blank"><strong style="color:#e20046;">Job</strong><strong style="color:#0a0256;">Searchine.com</strong></a></div>

	<div id="jobsearchine_jobs_pagination"></div>
	</div>
	
	';

    return $result;

}

add_shortcode('jobs', 'jobs_shortcode');