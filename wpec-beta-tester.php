<?php

/*
Plugin Name: WP e-Commerce Beta Tester
Plugin URI: http://blog.meloniq.net/
Description: Update your WP e-Commerce plugin straight from the GitHub repository and run the bleeding edge version. ** This is not recommended for production sites.
Version: 1.0
Author: MELONIQ.NET
Author URI: http://www.meloniq.net/
License: GPLv2
*/

/**
 * Initialize Updater
 */ 
function wpec_beta_tester_init() {
	global $wpec_beta_tester;

	if ( ! class_exists( 'WP_GitHub_Updater' ) )
		require_once( 'classes/updater.php' );

	if ( ! class_exists( 'WPEC_GitHub_Updater' ) )
		require_once( 'classes/wpec-updater.php' );

	if ( is_admin() ) {

		$wpec_beta_tester = new WPEC_GitHub_Updater();

	}

}
add_action( 'plugins_loaded', 'wpec_beta_tester_init' );


/**
 * Clear plugins update transient
 */ 
function wpec_beta_tester_clear() {
	delete_site_transient( 'update_plugins' );
	delete_site_transient( 'wpec_beta_tester_pull_source' );
	delete_site_transient( 'wpec_beta_tester_last_commit' );
	delete_site_transient( 'wpec_beta_tester_remote_version' );
}
register_activation_hook( __FILE__, 'wpec_beta_tester_clear' );
register_deactivation_hook( __FILE__, 'wpec_beta_tester_clear' );
