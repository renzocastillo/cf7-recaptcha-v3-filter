<?php
/*
Plugin Name: Script Filter for Contact Form 7 Google reCAPTCHA
Plugin URI:
Description: Load Google reCAPTCHA v3 script only in those pages where <strong>Contact Form 7 shortcode exists</strong>.
Version: 1.0.0
Author: Quaira 
Author URI: https://quaira.com/
License: GPLv2 or later
Text Domain: quaira
*/

function sfcf7r_child_plugin_has_parent_plugin()
{
    if ( is_admin() && current_user_can( 'activate_plugins' ) && !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
        add_action( 'admin_notices', 'sfcf7r_child_plugin_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }

}

function sfcf7r_child_plugin_notice()
{
    echo '<div class="error">
      <p>Sorry, but Script Filter for Contact Form 7 Google reCAPTCHA requires Contact Form 7 to be installed and active.</p>
   </div>';
}

add_action( 'admin_init', 'sfcf7r_child_plugin_has_parent_plugin' );

function sfcf7r_contactform_dequeue_scripts()
{
    if ( is_singular() ) {
        $post = get_post();
        $shortcode = get_option( 'sfcf7r_shortcode' ) ? : 'contact-form-7';
        if ( !has_shortcode( $post->post_content, $shortcode ) ) {
            wp_dequeue_script( 'google-recaptcha' );
            add_filter( 'wpcf7_load_js', '__return_false' );
            add_filter( 'wpcf7_load_css', '__return_false' );
            remove_action( 'wp_enqueue_scripts', 'wpcf7_recaptcha_enqueue_scripts', 20 );
        }
    }
}

add_action( 'wp_enqueue_scripts', 'sfcf7r_contactform_dequeue_scripts' );

function sfcf7r_settings_page()
{

    add_submenu_page( 'wpcf7', 'reCAPTCHA Filter', 'reCAPTCHA Filter Settings', 'manage_options', 'sfcf7r', 'sfcf7r_view_page' );
}

function sfcf7r_view_page()
{

    echo '<div class="wrap">
	<h1>Script Filter for Contact Form 7 Google reCAPTCHA Settings</h1>
	<form method="post" action="options.php">';

    settings_fields( 'sfcf7r-setting' ); // settings group name
    do_settings_sections( 'sfcf7r' ); // just a page slug
    submit_button();

    echo '</form></div>';
}

add_action( 'admin_menu', 'sfcf7r_settings_page' );

function sfcf7r_setting()
{

    register_setting(
        'sfcf7r-setting', // settings group name
        'sfcf7r_shortcode' // option name
    );

    add_settings_section(
        'some_settings_section_id', // section ID
        '', // title (if needed)
        '', // callback function (if needed)
        'sfcf7r' // page slug
    );

    add_settings_field(
        'sfcf7r_shortcode',
        'Contact form 7 Shortcode',
        'sfcf7r_text_field_html', // function which prints the field
        'sfcf7r', // page slug
        'some_settings_section_id', // section ID
        array(
            'label_for' => 'sfcf7r_shortcode',
            'class' => 'sfcf7r-class',
            // for <tr> element
        )
    );
}

add_action( 'admin_init', 'sfcf7r_setting' );

function sfcf7r_text_field_html()
{

    $text = get_option( 'sfcf7r_shortcode' ) ? : 'contact-form-7';

    printf(
        '<input type="text" id="sfcf7r_shortcode" name="sfcf7r_shortcode" value="%s" />',
        esc_attr( $text )
    );
    echo "<label>Google reCAPTCHA script will only be loaded in pages and posts where [$text] shortcode exists</label>";
}
