<?php
/*
Plugin Name: Job Offers - Show jobs easily from ANY of 79 available countries
Description: Display modern job board on any post or page of your website by adding the shortcode provided. Add top listings from our biggest international directory. We have jobs in 79 countries.
Author: JobSearchine
Author URI: https://www.jobsearchine.com/
Version: 1.0.0
*/


if (!class_exists('WC_Atomixstar')) {

    /**
     * Localisation
     **/
    load_plugin_textdomain('wc_atomixstar', false, dirname(plugin_basename(__FILE__)) . '/');

    class WC_Atomixstar
    {
        public function __construct()
        {

            define('JOBSEARCHINE_DIR', __DIR__ . '/');
            // called after all plugins have loaded
            add_action('plugins_loaded', array(&$this, 'plugins_loaded'));

            require JOBSEARCHINE_DIR . 'widget.php';
            require JOBSEARCHINE_DIR . 'shortcode.php';

            function atomixstar_scripts()
            {
                // Register the script
                wp_register_script('atomixstar-custom', plugin_dir_url(__FILE__) . '/js/admin-jobsearchine.js');

                // Localize the script with new data
                $script_data_array = array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                );
                wp_localize_script('atomixstar-custom', 'atomixstar', $script_data_array);

                // Enqueued script with localized data.
                wp_enqueue_script('atomixstar-custom');

            }

            add_action('admin_enqueue_scripts', 'atomixstar_scripts');


            function jobsearchine_frontend()
            {

                wp_register_style('jobsearchine-css', plugin_dir_url(__FILE__) . '/css/jobsearchine.css');
                wp_enqueue_style('jobsearchine-css');

                wp_enqueue_script('jquery');
                wp_register_script('jobsearchine_frontend', plugin_dir_url(__FILE__) . 'js/jobsearchine-frontend.js');
                // Localize the script with new data
                $script_data_array2 = array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                );
                wp_localize_script('jobsearchine_frontend', 'jobsearchine_frontend', $script_data_array2);
                wp_enqueue_script('jobsearchine_frontend');
            }

            add_action('wp_enqueue_scripts', 'jobsearchine_frontend');


            // indicates we are running the admin
            if (is_admin()) {


                // You can use this if you want.
                add_action('wp_ajax_test_api', 'test_api_callback');
                add_action('wp_ajax_nopriv_test_api', 'test_api_callback');

                function test_api_callback()
                {

                    $api_key = sanitize_text_field($_POST['api']);
                    $query = sanitize_text_field($_POST['keyword']);
                    $location = sanitize_text_field($_POST['location']);
                    $country = sanitize_text_field($_POST['country']);

                    $get_data = wp_remote_retrieve_body(wp_remote_get('http://api.jobsearchine.com/apicall?publisher=' . $api_key . '&q=' . $query . '&l=' . $location . '&country=' . $country . '&language=en&start=0&limit=4&ip=1.1.1.1&useragent=JobSearchine'));

                    if ($get_data) {
                        $jobs = new SimpleXMLElement($get_data);

                        $result = '';

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

                        echo $result;

                    } else {
                        echo 'Something goes wrong.';
                    }

                    wp_die();
                }

                add_action('wp_ajax_login_api', 'login_api_callback');
                add_action('wp_ajax_nopriv_login_api', 'login_api_callback');

                function login_api_callback()
                {

                    $password = $_POST['password'];
                    $email = sanitize_email($_POST['email']);
                    $nonce = sanitize_text_field($_POST['nonce']);

                    if (isset($nonce) && $nonce !== null && wp_verify_nonce($nonce, 'jobsearchine_admin_nonce')) {

                        $get_data = wp_remote_post('http://api.jobsearchine.com/login_plugin', array(
                                'method' => 'POST',
                                'timeout' => 45,
                                'body' => array(
                                    'email' => $email,
                                    'password' => $password
                                )
                            )
                        );
                        $return_key = '';


                        if ($get_data == 'user-notfound') {

                            $response = array(
                                'status' => 'error',
                                'msg' => 'User not found.'
                            );

                        } elseif ($get_data == 'password-notmatch') {
                            $response = array(
                                'status' => 'error',
                                'msg' => 'Password does not match.'
                            );
                        } elseif ($get_data == 'error') {
                            $response = array(
                                'status' => 'error',
                                'msg' => 'An error has been occurred.'
                            );
                        } else {

                            $return_key = wp_remote_retrieve_body($get_data);

                            $response = array(
                                'status' => 'success',
                                'msg' => 'Success',
                                'api_key' => $return_key
                            );
                            update_option('jobsearchine_api', serialize($return_key));
                            update_option('jobsearchine_email', serialize($email));
                        }


                        curl_close($ch);

                        echo json_encode($response);

                        wp_die();

                    } else {

                        $response = array(
                            'status' => 'error',
                            'msg' => 'Session Expired.'
                        );

                        echo json_encode($response);
                    }
                }

                add_action('wp_ajax_get_api', 'get_api_callback');
                add_action('wp_ajax_nopriv_get_api', 'get_api_callback');

                function get_api_callback()
                {

                    $current_user = wp_get_current_user();
                    $name = sanitize_text_field($_POST['name']);
                    $email = sanitize_email($_POST['email']);
                    $domain = sanitize_text_field($_POST['domain']);
                    $password = $_POST['password'];
                    $c_password = $_POST['c_password'];
                    $nonce = sanitize_text_field($_POST['nonce']);

                    if (isset($nonce) && $nonce !== null && wp_verify_nonce($nonce, 'jobsearchine_admin_nonce')) {

                        $get_data = wp_remote_post('http://api.jobsearchine.com/register_plugin', array(
                                'method' => 'POST',
                                'timeout' => 45,
                                'body' => array(
                                    'name' => $name,
                                    'domain' => $domain,
                                    'email' => $email,
                                    'password' => $password
                                )
                            )
                        );

                        if ($get_data == 'email-exist') {

                            $response = array(
                                'status' => 'error',
                                'msg' => 'Email <strong>' . $email . '</strong> already exists.'
                            );

                        } elseif ($get_data == 'error') {
                            $response = array(
                                'status' => 'error',
                                'msg' => 'An error has been occurred.'
                            );
                        } else {
                            $response = array(
                                'status' => 'success',
                                'msg' => 'Success',
                                'api_key' => $get_data
                            );
                            update_option('jobsearchine_api', serialize($get_data));
                            update_option('jobsearchine_email', serialize($email));
                        }


                        echo json_encode($response);

                        wp_die();


                    } else {

                        $response = array(
                            'status' => 'error',
                            'msg' => 'Session Expired.'
                        );

                        echo json_encode($response);

                        wp_die();

                    }
                }


                function jobsearchine_page()
                {
                    add_options_page('Job Offers', 'Job Offers', 'manage_options', 'jobsearchine-setup', 'jobsearchine_setup');
                }

                add_action('admin_menu', 'jobsearchine_page');

                function jobsearchine_setup()
                {

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                        $nonce = $_POST['jobsearchine_admin_nonce'];

                        if ($nonce !== null && wp_verify_nonce($nonce, 'jobsearchine_admin_nonce')) {

                            delete_option('jobsearchine_api');

                        } else {

                            echo 'Session Expired. Please refresh page.';
                            wp_die();
                        }
                    }


                    $show = '
	<style>
	
	.jobsearchine_container {
background: #fff;
padding: 50px;
border-radius: 10px;
text-align: center;
display: block;
-webkit-box-shadow: 0px 2px 6px rgba(0,0,0,.15);
-moz-box-shadow: 0px 2px 6px rgba(0,0,0,.15);
box-shadow: 0px 2px 6px rgba(0,0,0,.15);
-webkit-transition: box-shadow .2s ease-in-out;
-moz-transition: box-shadow .2s ease-in-out;
-o-transition: box-shadow .2s ease-in-out;
transition: box-shadow .2s ease-in-out;
}

.jobsearchine_container h2{
font-size:1.5rem;
letter-spacing: 1px;
}

.jobsearchine_container form {
display: block;
margin-bottom: 25px;
}

.form-control{
padding: .375rem .75rem;
font-size: 1rem;
font-weight: 400;
line-height: 1.5;
color: #495057;
background-color: #fff;
background-clip: padding-box;
border: 1px solid #ced4da;
border-radius: .25rem;
transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
border: 0;
height: 49px;
border: 1px solid #ccc;
}

.btn-jobsearchine, .btn-jobsearchine-login, .btn-jobsearchine-view {
background: #e20046;
border: 0;
border-radius: 250px;
height: 35px;
color: #fff;
display: inline-block;
line-height: 35px;
padding: 0 30px;
font-size: 17px;
margin-bottom: 15px;
cursor: pointer;
}

.btn-jobsearchine:hover, .btn-jobsearchine-login:hover, .btn-jobsearchine-view:hover {
background: #af1d4a;
}

#msg {
font-size: 20px;
display: block;
margin-top: 40px;
}

.jobsearchine_form input {
display:block;
margin:5px auto;
min-width: 500px;
padding: 0 15px;
}

.mark {
display:inline-block;
background: #e20046;
color: #fff;
padding: 4px;
font-size: 15px;
font-weight: bold;
}
#shortcode_for_copy {
background: #ececec;
color: #545454;
display: inline-block;
padding: 10px 30px;
letter-spacing: 0px;
margin-top: 20px;
border: 1px solid #c1c1c1;
width:100%;
text-align:center;
}

.shortcode_generate {
background: #f2f2f2;
border: 1px dashed #bbb7b7;
padding: 20px;
position: sticky;
top: 52px;
}

.shortcode_generate label {
display: block;
text-align: left;
padding-bottom: 10px;
font-weight: bold;
padding-left: 5px;
}


.jobsearchine_container .jobsearchine_job h2{
font-weight: normal;
margin-top: 10px;
margin-bottom: 10px;
font-size: 16px;
padding:0;
clear: both;
color: #24292e;
font-weight: 600;
letter-spacing:0;
}

.jobsearchine_container .jobsearchine_job a{
text-decoration:none;
color: #24292e;
}

.jobsearchine_container .jobsearchine_job {
margin: 15px 0;
}

.jobsearchine_container .jobsearchine_job .jobcompany_logo {
width: 64px;
margin-right: 20px;
vertical-align: top;
display: inline-block;
border-radius: 4px;
float: left;
}

.jobsearchine_container .jobsearchine_job .jobcompany_logo img {
width:100%;
}

.jobsearchine_container .jobsearchine_job .jobcontent {
position: relative;
width: calc(100% - 88px);
display: inline-block;
text-align:left;
}

.jobsearchine_container .jobsearchine_job .jobcompany {
color: #b3b3b3;
text-transform: uppercase;
font-weight: 600;
font-size: 15px;
}

.jobsearchine_container .jobsearchine_job .jobaddress {
color: #e20046;
display: block;
font-weight: 700;
font-size: 15px;
}

.jobsearchine_container .jobsearchine_job .jobdescription {
display: block;
color: #333;
padding-top: 8px;
font-size: 13px;
font-weight: normal;
}


.jobsearchine_container h2:before, .jobsearchine_container h2:after {
display:none;
}
.row {
display: -ms-flexbox;
display: flex;
-ms-flex-wrap: wrap;
flex-wrap: wrap;
margin-right: -15px;
margin-left: -15px;
border-top: 1px solid #f2f2f2;
padding-top: 20px;
}
.col-8 {
-ms-flex: 0 0 66.666667%;
flex: 0 0 66.666667%;
max-width: 66.666667%;
}

.col-4 {
-ms-flex: 0 0 33.333333%;
flex: 0 0 33.333333%;
max-width: 33.333333%;
padding: 0 15px;
box-sizing: border-box;
}

.lds-roller {
display: inline-block;
position: relative;
width: 80px;
height: 80px;
}
.lds-roller div {
animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
transform-origin: 40px 40px;
}
.lds-roller div:after {
content: " ";
display: block;
position: absolute;
width: 7px;
height: 7px;
border-radius: 50%;
background: #000;
margin: -4px 0 0 -4px;
}
.lds-roller div:nth-child(1) {
animation-delay: -0.036s;
}
.lds-roller div:nth-child(1):after {
top: 63px;
left: 63px;
}
.lds-roller div:nth-child(2) {
animation-delay: -0.072s;
}
.lds-roller div:nth-child(2):after {
top: 68px;
left: 56px;
}
.lds-roller div:nth-child(3) {
animation-delay: -0.108s;
}
.lds-roller div:nth-child(3):after {
top: 71px;
left: 48px;
}
.lds-roller div:nth-child(4) {
animation-delay: -0.144s;
}
.lds-roller div:nth-child(4):after {
top: 72px;
left: 40px;
}
.lds-roller div:nth-child(5) {
animation-delay: -0.18s;
}
.lds-roller div:nth-child(5):after {
top: 71px;
left: 32px;
}
.lds-roller div:nth-child(6) {
animation-delay: -0.216s;
}
.lds-roller div:nth-child(6):after {
top: 68px;
left: 24px;
}
.lds-roller div:nth-child(7) {
animation-delay: -0.252s;
}
.lds-roller div:nth-child(7):after {
top: 63px;
left: 17px;
}
.lds-roller div:nth-child(8) {
animation-delay: -0.288s;
}
.lds-roller div:nth-child(8):after {
top: 56px;
left: 12px;
}
@keyframes lds-roller {
0% {
transform: rotate(0deg);
}
100% {
transform: rotate(360deg);
}
}


	
	</style>
	
	<h1>Job Offers - API Setup</h1>
	<div class="jobsearchine_container">';

                    $nonce = wp_create_nonce('jobsearchine_admin_nonce');

                    $api_key = get_option('jobsearchine_api', null);
                    $api_email = get_option('jobsearchine_email', true);
                    if ($api_key !== null) {
                        $api_key = unserialize($api_key);
                        $show .= '<h3 style="display:inline-block;">You are logged-in as <strong>' . unserialize($api_email) . '</strong></h3>
	<form action="" method="post" style="margin-left:15px;display:inline-block;">
	<input type="hidden" value="' . $nonce . '" id="jobsearchine_admin_nonce" name="jobsearchine_admin_nonce">
	<input type="hidden" value="' . $api_key . '" id="api_key">
	<button type="submit" class="btn-jobsearchine" style="">Log out</button>
	</form>
	
	<div class="row">
	<div class="col-8">
	<h2 style="text-align:left;">Generate Shortcode</h2>
	<div class="shortcode_generate">
	<div style="display:inline-block;">
	<label>Keyword</label>
	<input id="shortcode_keyword" class="form-control shortcode_gen" type="text" name="shortcode_keyword" value="Manager" placeholder="Keyword">
	</div>
	<div style="display:inline-block;">
	<label>Location (required)</label>
	<input id="shortcode_location" class="form-control shortcode_gen" type="text" name="shortcode_location" value="New York" placeholder="Location">
	</div>
	<div style="display:inline-block;">
	<label>Country (required)</label>
	<input id="shortcode_country" class="form-control shortcode_gen" type="text" name="shortcode_country" value="US" placeholder="Country">
	</div>
	<div style="display:inline-block;">
	<label>Language</label>
	<input id="shortcode_language" class="form-control shortcode_gen" type="text" name="shortcode_language" value="en" placeholder="Language">
	</div>
	<div style="display:inline-block;">
	<label>Limit (Max. 15)</label>
	<input id="shortcode_limit" class="form-control shortcode_gen" type="text" name="shortcode_limit" value="15" placeholder="Limit">
	</div>
	
	<pre id="shortcode_show" style="display:none;"><span>[jobs q="<strong id="keyword_txt">Manager</strong>" l="<strong id="location_txt">New York</strong>" country="<strong id="country_txt">US</strong>" language="<strong id="language_txt">en</strong>" limit="<strong id="limit_txt">15</strong>"]</span></pre>
	<input type="text" value="" id="shortcode_for_copy">
	
	<small>Copy the above shortcode and place it on your page or post.</small>
	<br/><br/>
	<span class="btn-jobsearchine-view">View</span>
	<br/>
	<span id="view-msg" style="color:red;font-weight:bold;"></span>
	</div>
	
	
	</div>
	<div class="col-4">
	<h2>Preview</h2>
	<div class="lds-roller" style="display:none;"><div></div> <div></div> <div></div> <div></div></div>
	<div id="jobs-view"></div>
	</div>
	</div>
	
	
	';
                    } else {
                        $show .= '<div id="login_register">
	<h2>Login</h2>
	<form action="" method="post">
	<input type="hidden" value="' . $nonce . '" id="jobsearchine_admin_nonce" name="jobsearchine_admin_nonce">
	<input id="email" class="form-control" type="text" name="email" value="" placeholder="Email">
	<input id="password" class="form-control" type="password" name="password" value="" placeholder="Password">
	<span class="btn-jobsearchine-login">Login</span><br/>
	<span id="msg_login"></span>
	</form>
	
	<h2>Or Register</h2>
	<form class="jobsearchine_form" action="" method="post">
	<input id="rname" class="form-control" type="text" name="r_name" value="" placeholder="Name">
	<input id="remail" class="form-control" type="text" name="r_email" value="" placeholder="Email">
	<input id="domain" class="form-control" type="text" name="domain" value="" placeholder="Domain">
	<input id="rpassword" class="form-control" type="password" name="r_password" required value="" placeholder="Password">
	<input id="c_password" class="form-control" type="password" name="c_password" required value="" placeholder="Confirm Password">
	</form>
	<span class="btn-jobsearchine">Get your key</span><br/>
	<a href="http://api.jobsearchine.com/" target="_blank">OR get your API key from here.</a>
	</div>
	<span id="msg"></span>';
                    }
                    $show .= '</div><p style="text-align:center;font-size:12px;padding-top:5px;">Got any question OR feedback to give? We would be happy to hear at <a href="mailto:plugin@jobsearchine.com">plugin@jobsearchine.com</a></p>';

                    echo $show;
                }


            }

            // indicates we are being served over ssl
            if (is_ssl()) {
                // ...
            }

            // take care of anything else that needs to be done immediately upon plugin instantiation, here in the constructor
        }


        /**
         * Take care of anything that needs all plugins to be loaded
         */
        public function plugins_loaded()
        {
            // ...
        }


    }

    // finally instantiate our plugin class and add it to the set of globals
    $GLOBALS['wc_atomixstar'] = new WC_Atomixstar();
}
