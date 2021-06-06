<?php
/*
Plugin Name: Contact Form 7 reCAPTCHA Filter 
Plugin URI: https://quaira.com/
Description: Load Google reCAPTCHA v3 script only in those pages where <strong>Contact Form 7 shortcode exists</strong>.
Version: 1.0
Author: Quaira 
Author URI: https://quaira.com/
License: GPLv2 or later
Text Domain: quaira
*/

add_action('admin_init', 'child_plugin_has_parent_plugin');
function child_plugin_has_parent_plugin()
{
   if (is_admin() && current_user_can('activate_plugins') &&  !is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
      add_action('admin_notices', 'child_plugin_notice');

      deactivate_plugins(plugin_basename(__FILE__));

      if (isset($_GET['activate'])) {
         unset($_GET['activate']);
      }
   }
}

function child_plugin_notice()
{
   echo '<div class="error">
      <p>Sorry, but Contact Form 7 reCAPTCHA v3 Filter requires Contact Form 7 to be installed and active.</p>
   </div>';
}

function contactform_dequeue_scripts()
{
   if (is_singular()) {
      $post = get_post();
      $shortcode=get_option('cf7r_filter_shortcode') ?: 'contact-form-7';
      if (!has_shortcode($post->post_content, $shortcode)) {
         wp_dequeue_script('google-recaptcha');
         add_filter('wpcf7_load_js', '__return_false');
         add_filter('wpcf7_load_css', '__return_false');
         remove_action('wp_enqueue_scripts', 'wpcf7_recaptcha_enqueue_scripts', 20);
      }
   }
}
add_action('wp_enqueue_scripts', 'contactform_dequeue_scripts');

function cf7r_settings_page(){

   add_submenu_page( 'wpcf7','reCAPTCHA Filter','reCAPTCHA Filter Settings', 'manage_options', 'cf7r-filter','cf7r_view_page');

}
add_action('admin_menu', 'cf7r_settings_page');

function cf7r_view_page(){
   
	echo '<div class="wrap">
	<h1>Contact Form 7 reCAPTCHA Filter Settings</h1>
	<form method="post" action="options.php">';
			
		settings_fields( 'cf7r-filter-setting' ); // settings group name
		do_settings_sections( 'cf7r-filter' ); // just a page slug
		submit_button();

	echo '</form></div>';
}

add_action( 'admin_init',  'cf7r_filter_setting' );

function cf7r_filter_setting(){

	register_setting(
		'cf7r-filter-setting', // settings group name
		'cf7r_filter_shortcode', // option name
		'sanitize_text_field' // sanitization function
	);

	add_settings_section(
		'some_settings_section_id', // section ID
		'', // title (if needed)
		'', // callback function (if needed)
		'cf7r-filter' // page slug
	);

	add_settings_field(
		'cf7r_filter_shortcode',
		'Contact form 7 Shortcode',
		'cf7r_filter_text_field_html', // function which prints the field
		'cf7r-filter', // page slug
		'some_settings_section_id', // section ID
		array( 
			'label_for' => 'cf7r_filter_shortcode',
			'class' => 'cf7r-filter-class', // for <tr> element
		)
	);

}

function cf7r_filter_text_field_html(){

	$text = get_option( 'cf7r_filter_shortcode' )?: 'contact-form-7';

	printf(
		'<input type="text" id="cf7r_filter_shortcode" name="cf7r_filter_shortcode" value="%s" />',
		esc_attr( $text )
	);
   echo "<label>Google reCAPTCHA script will only be loaded in pages and posts where [$text] shortcode exists</label>";

}
