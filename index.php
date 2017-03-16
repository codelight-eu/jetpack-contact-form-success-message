<?php
/*
Plugin Name: JetPack Contact Form Success Message
Plugin URI: https://wordpress.org/plugins/jetpack-contact-form-success-message/
Description: JetPack Contact Form Success Message replaces the custom message showing after successfully submitting a contact form, with your custom message.
Author: Samuel Elh
Version: 0.3
Author URI: https://samelh.com
Text Domain: jpcfsm
Donate link: https://go.samelh.com/buy-me-a-coffee
*/

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) || !defined( 'ABSPATH' ) ) {
    die;
}

// PHP requirements check
if ( !version_compare( PHP_VERSION, '5.3', '>=' ) ) {
    function jpcfsm_php_notice() {
        print '<div class="error notice is-dismissible"><p>';
        print 'JetPack Contact Form Success Message requires at least PHP 5.3. Please upgrade your PHP to start using this plugin.';
        print '</p></div>';
    }

    if ( is_network_admin() ) {
        add_action('network_admin_notices', 'jpcfsm_php_notice');
    }

    add_action('admin_notices', 'jpcfsm_php_notice');
    
    // bail
    return;
}

if ( !defined('JPCFSM_FILE') ) {
    define('JPCFSM_FILE', __FILE__);
}

if ( !defined('JPCFSM_VERSION') ) {
    define('JPCFSM_VERSION', '0.4');
}

register_activation_hook(JPCFSM_FILE, 'jp_cf_success_message_upgrade');

/**
  * Upgrade settings to a new option
  *
  * @since 0.3
  */
function jp_cf_success_message_upgrade() {
    include ( 'includes/jpcfsm-upgrade.php' );
}

include ( 'includes/class-jpcfsm.php' );

function jp_cf_success_message() {
    return JPCFSM::instance();
}

add_action('plugins_loaded', 'jp_cf_success_message');

if ( is_admin() ) {
    include ( 'includes/class-jpcfsm-admin.php' );

    function jp_cf_success_message_admin() {
        return JPCFSMAdmin::instance();
    }
    
    add_action('plugins_loaded', 'jp_cf_success_message_admin');
}