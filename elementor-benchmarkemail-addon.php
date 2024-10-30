<?php
/**
 * Plugin Name: Integration of BenchmarkEmail CRM For Elementor Pro Form
 * Description: Benchmark Email Integration for Elementor Pro allow you to send your Elementor Pro Form Widget entries directly to your Benchmark Email account.
 * Plugin URI:  https://wisersteps.com/docs/elementor-pro-form-widget-benchmark-email-integration/setup-the-plugin/
 * Version:     1.0.0
 * Author:      Omar Kasem
 * Author URI:  https://wisersteps.com/
 * Text Domain: elementor-benchmarkemail-addon
 * Developer: Omar Kasem
 * Developer URI: https://www.wisersteps.com
 *
 * @package Elementor_Benchmark
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Require Elementor and Pro
add_action( 'admin_init', 'ebma_require_elementor');
function ebma_require_elementor() {
	if ( ! in_array( 'elementor/elementor.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || ! in_array( 'elementor-pro/elementor-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="error"><p>Sorry, This Addon Requires Elementor and Elementor Pro to be installed and activated.</p></div>';
			}
		);
		deactivate_plugins( plugin_basename( __FILE__ ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}


// Define Name & Version.
define( 'ELEMENTOR_BENCHMARKEMAIL_ADDON', 'elementor-benchmarkemail-addon' );
define( 'ELEMENTOR_BENCHMARKEMAIL_ADDON_VERSION', '1.0.0' );

// Require Main Files.
require plugin_dir_path( __FILE__ ) . 'app/class-app.php';
new Elementor_Benchmark\App( ELEMENTOR_BENCHMARKEMAIL_ADDON, ELEMENTOR_BENCHMARKEMAIL_ADDON_VERSION );
