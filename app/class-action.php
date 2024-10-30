<?php
/**
 * Action class of the plugin
 *
 * @package Elementor_Benchmark
 */

namespace Elementor_Benchmark;

/**
 * Add Action after submit to elementor form
 */
class Action extends \ElementorPro\Modules\Forms\Classes\Action_Base {
	/**
	 * Get Name
	 *
	 * Return the action name
	 *
	 * @access public
	 * @return string
	 */
	public function get_name() {
		return 'benchmarkemail';
	}

	/**
	 * Get Label
	 *
	 * Returns the action label
	 *
	 * @access public
	 * @return string
	 */
	public function get_label() {
		return __( 'BenchmarkEmail', 'elementor-benchmarkemail-addon' );
	}

	/**
	 * Run
	 *
	 * Runs the action after submit
	 *
	 * @access public
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public function run( $record, $ajax_handler ) {
		$settings = $record->get( 'form_settings' );

		if ( empty( $settings['ebma_list'] ) ) {
			return;
		}
		$raw_fields = $record->get( 'fields' );

		$fields = array();
		foreach ( $raw_fields as $id => $field ) {
			$fields[ $id ] = $field['value'];
		}

		$benchmarkemail_fields = array();
		for ( $i = 0; $i < 25; $i++ ) {
			$key = 'ebma_form_' . $i;
			if ( ! empty( $settings[ $key ] ) ) {
				$ckey                                = str_replace( 'label_', '', trim( explode( '-', $settings[ $key ], 2 )[1], '()' ) );
				$key_field                           = $this->get_field_key_from_custom_fields( $settings['ebma_list'], intval( $ckey ) );
				$val                                 = explode( '-', $settings[ $key ], 2 )[0];
				$benchmarkemail_fields[ $key_field ] = $val;
			}
		}


		$custom_fields = array();
		foreach ( $settings['form_fields'] as $form ) {
			foreach ( $benchmarkemail_fields as $key => $value ) {
				if ( stripos( $value, $form['custom_id'] ) !== false ) {
					$cvalue                = $fields[ $form['custom_id'] ];
					$custom_fields[ $key ] = $cvalue;
				}
			}
		}

	

		if ( empty( $custom_fields ) ) {
			return;
		}
		$custom_fields['EmailPerm'] = '1';
		$custom_fields['IPAddress'] = $this->get_client_ip();

		$data = array(
			'Data' => $custom_fields,
		);

		$response = $this->request( 'Contact/' . $settings['ebma_list'] . '/ContactDetails', wp_json_encode( $data ) );
		
		if(intval($response->Response->Status) === -1){
			// Email Already exist error
			if($response->Response->Errors[0]->Field === 'Email'){
				$ajax_handler->add_error_message( 'BenchmarkEmail - Email already exists.' );
			}else{
				$ajax_handler->add_error_message( 'BenchmarkEmail ' . $response->Response->Errors[0]->Message );
			}
		}

	}



	/**
	 * Register Settings Section
	 *
	 * Registers the Action controls
	 *
	 * @access public
	 * @param \Elementor\Widget_Base $widget
	 */
	public function register_settings_section( $widget ) {
		$lists = $this->get_lists();

		if ( empty( $lists ) ) {
			$widget->start_controls_section(
				'elementor_benchmarkemail_section',
				array(
					'label'     => __( 'BenchmarkEmail', 'elementor-benchmarkemail-addon' ),
					'condition' => array(
						'submit_actions' => $this->get_name(),
					),
				)
			);

			$widget->add_control(
				'ebma_error_no_lists',
				array(
					'raw'             => __( 'You don\' have any lists in BenchmarkEmail, please create lists <a href="https://app.benchmarkemail.com/list">From Here</a>', 'elementor-benchmarkemail-addon' ),
					'type'            => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'elementor-descriptor',
				)
			);

			$widget->end_controls_section();

			return;
		}

		$widget->start_controls_section(
			'elementor_benchmarkemail_section',
			array(
				'label'     => __( 'BenchmarkEmail', 'elementor-benchmarkemail-addon' ),
				'condition' => array(
					'submit_actions' => $this->get_name(),
				),
			)
		);

		$widget->add_control(
			'ebma_list',
			array(
				'label'       => __('Select a List','elementor-benchmarkemail-addon'),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => $lists,
				'label_block' => true,
			)
		);

		$widget->add_control(
			'ebma_loader',
			array(
				'raw'  => '<img style="width: 25px;display:none;" class="ebma_loader_gif" src="' . admin_url() . '/images/spinner-2x.gif">',
				'type' => \Elementor\Controls_Manager::RAW_HTML,
			)
		);

		$widget->add_control(
			'ebma_mapping',
			array(
				'label'       => __('Field Mapping','elementor-benchmarkemail-addon'),
				'separator'   => 'before',
				'type'        => \Elementor\Controls_Manager::HEADING,
				'label_block' => true,
			)
		);

		for ( $i = 0; $i < 29; $i++ ) {
			$widget->add_control(
				'ebma_form_' . $i,
				array(
					'label'       => 'label_' . $i,
					'show_label'  => true,
					'type'        => \Elementor\Controls_Manager::SELECT,
					'render_type' => 'template',
					'separator'   => 'after',
					'options'     => array(),
					'default'     => 0,
				)
			);

		}
		$widget->end_controls_section();

	}

	/**
	 * On Export
	 *
	 * Clears form settings on export
	 *
	 * @access Public
	 * @param array $element
	 */
	public function on_export( $element ) {
		unset(
			$element['ebma_list']
		);
		for ( $i = 0;$i < 28;$i++ ) {
			if ( isset( $element[ 'ebma_form_' . $i ] ) ) {
				unset( $element[ 'ebma_form_' . $i ] );
			}
		}
	}

	/**
	 * Request client
	 *
	 * @param string $base
	 * @param array  $body
	 * @param string $method
	 * @return array
	 */
	public function request( $base, $body = array(), $method = 'POST' ) {
		if ( get_option( 'ebma_api_key' ) === '' ) {
			return;
		}
		$args     = array(
			'method'  => $method,
			'headers' => array(
				'Content-Type' => 'application/json; charset=utf-8',
				'AuthToken'    => '' . get_option( 'ebma_api_key' ),
			),
			'body'    => $body,
		);
		$url      = 'https://clientapi.benchmarkemail.com/' . $base;
		$response = wp_remote_request( $url, $args );
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return;}
		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Get lists
	 *
	 * @return array
	 */
	public function get_lists() {
		$response = $this->request( 'Contact/', array(), 'GET' );
		if ( $response ) {
			$final = array();
			foreach ( $response->Response->Data as $list ) {
				$final[ $list->ID ] = $list->Name;
			}
			return $final;
		}
	}

	/**
	 * Get field key from custom fields
	 *
	 * @param integer $list_id
	 * @param boolean $key
	 * @return string
	 */
	public function get_field_key_from_custom_fields( $list_id, $key ) {
		$fields = $this->get_custom_fields( $list_id, true );
		return $fields[ $key ];
	}

	/**
	 * Get custom fields
	 *
	 * @param string  $list
	 * @param boolean $only_keys
	 * @return array
	 */
	public function get_custom_fields( $list, $only_keys = false ) {
		$response = $this->request( 'Contact/' . $list, array(), 'GET' );
		if ( $response ) {
			$final = array();

			foreach ( $response->Response->Data as $key => $list ) {
				if ( false !== strpos( $key, 'Field' ) && ! is_numeric( $list ) ) {
					$int                     = (int) filter_var( $key, FILTER_SANITIZE_NUMBER_INT );
					$final[ 'Field' . $int ] = $list;
				}
			}
			ksort( $final, SORT_NATURAL );
	
			$final               =  $this->main_fields() + $final;
			if ( true === $only_keys ) {
				return array_keys( $final );
			} else {
				return array_values( $final );
			}
		}
	}

	public function main_fields(){
		$main = [];
		$main['Email']      = 'Email';
		$main['FirstName']  = 'First Name';
		$main['LastName']   = 'Last Name';
		$main['MiddleName'] = 'Midlle Name';
		return $main;
	}

	/**
	 * Get client ip address
	 *
	 * @return string
	 */
	public function get_client_ip() {
		$ipaddress = '';
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED'] ) );
		} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		} else {
			$ipaddress = 'UNKNOWN';
		}
		return $ipaddress;
	}


}
