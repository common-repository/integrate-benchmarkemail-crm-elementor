<?php
/**
 * Main class of the plugin
 *
 * @package Elementor_Benchmark
 */

namespace Elementor_Benchmark;

/**
 * Class App
 */
class App {

	private $plugin_name;
	private $version;

	/**
	 * Register plugin name,version and hooks
	 *
	 * @param string $plugin_name
	 * @param string $version
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->register_hooks();
	}

	/**
	 * Register app hooks
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'elementor_pro/init', array( $this, 'add_action' ) );
		add_action( 'admin_menu', array( $this, 'add_sub_menu_page' ), 999 );
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_get_benchmarkemail_custom_fields', array( $this, 'get_benchmarkemail_custom_fields' ) );
	}

	/**
	 * Ajax callback to return custom fields
	 *
	 * @return array
	 */
	public function get_benchmarkemail_custom_fields() {
		if ( isset( $_GET['list_id'] ) ) {
			$list_id = intval( $_GET['list_id'] );
			if ( $list_id === 0 ) {
				return;
			}
		}

		$action = new Action();
		$fields = $action->get_custom_fields( $list_id );
		if ( ! empty( $fields ) ) {
			echo wp_json_encode(
				array(
					'status'        => 'success',
					'custom_fields' => $fields,
				)
			);
			die();
		}
		echo wp_json_encode(
			array(
				'status'        => 'fail',
				'custom_fields' => array(),
			)
		);
		die();
	}

	/**
	 * Enqueue Scripts and localize it
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/elementor-benchmarkemail-addon.js', array(), $this->version, true );
		wp_localize_script(
			$this->plugin_name,
			'php_vars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Add submenu page to elementor
	 *
	 * @return void
	 */
	public function add_sub_menu_page() {
		add_submenu_page( 'elementor', 'BenchmarkEmail CRM', 'BenchmarkEmail CRM', 'manage_options', 'benchmarkemail-elementor-integration', array( $this, 'display_option_page' ) );
	}

	/**
	 * Sub menu page callback
	 *
	 * @return void
	 */
	public function display_option_page() {
		include_once 'option-page.php';
	}

	/**
	 * Add action class and register it into elementor
	 *
	 * @return void
	 */
	public function add_action() {
		include_once 'class-action.php';
		$action = new Action();
		\ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $action->get_name(), $action );
	}

	/**
	 * Register option page settings
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting( 'ebma_option_page', 'ebma_api_key' );
	}

	/**
	 * Validate API Key in option page
	 *
	 * @return array
	 */
	public function validate_api_key() {
		$action = new Action();
		return $action->get_lists();
	}


}
