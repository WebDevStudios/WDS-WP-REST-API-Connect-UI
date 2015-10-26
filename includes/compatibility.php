<?php
/**
 * WDS WP REST API Connect UI Compatibility
 * @version 0.1.0
 * @package WDS WP REST API Connect UI
 */

WDS_REST_Connect_UI::include_file( 'vendor/wds-wp-rest-api-connect/wds-wp-rest-api-connect' );

class WDSRESTCUI_Compatibility extends WDS_WP_REST_API_Connect {

	/**
	 * Whether plugin should operate on the network settings level.
	 *
	 * @var bool
	 * @since  0.1.0
	 */
	protected $is_network = false;

	/**
	 * Object constructor
	 *
	 * @since 0.1.0
	 *
	 * @param boolean $is_network
	 */
	public function __construct( $is_network = false ) {
		$this->is_network = (bool) $is_network;
		if ( $this->is_network ) {
			add_filter( 'pre_option_wp_rest_api_connect_error', array( $this, 'override_error_option_get' ), 10, 2 );
		}
	}

	/**
	 * Initate our connect object
	 *
	 * @since 0.1.0
	 *
	 * @param array $args Arguments containing 'consumer_key', 'consumer_secret', and the 'json_url'
	 */
	public function init( $args = array() ) {
		parent::__construct( $args );
		return $this;
	}

	/**
	 * Retrieve stored option
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $option Option array key
	 * @param  string  $key    Key for secondary array
	 * @param  boolean $force  Force a new call to get_option
	 *
	 * @return mixed           Value of option requested
	 */
	public function get_option( $option, $key = '', $force = false ) {
		if ( $this->is_network && ( empty( $this->options ) || $force ) ) {
			$this->options = get_site_option( $this->option_key, array() );
		}

		return parent::get_option( $option, $key );
	}

	/**
	 * Peform the option saving
	 *
	 * @since  0.1.0
	 *
	 * @return bool  Whether option was properly updated
	 */
	public function do_update() {
		if ( ! $this->is_network ) {
			return parent::do_update();
		}

		return get_site_option( $this->option_key )
			? update_site_option( $this->option_key, $this->options )
			: add_site_option( $this->option_key, $this->options );
	}

	/**
	 * Updates/replaces the wp_rest_api_connect_error option
	 *
	 * @since  0.1.3
	 *
	 * @param  string  $error Error message
	 *
	 * @return void
	 */
	public function update_stored_error( $error = '' ) {
		if ( ! $this->is_network ) {
			return parent::update_stored_error( $error );
		}

		delete_site_option( 'wp_rest_api_connect_error' );

		if ( ! is_null( $error ) ) {
			add_site_option( 'wp_rest_api_connect_error', array(
				'message'          => $error,
				'request_args'     => print_r( $this->args, true ),
				'request_response' => print_r( $this->response, true ),
			), '', 'no' );
		}
	}

	/**
	 * Fetches the wp_rest_api_connect_error option value
	 *
	 * @since  0.1.0
	 *
	 * @return mixed  Result of get_option or get_site_option
	 */
	public function get_stored_error() {
		return ! $this->is_network
			? get_option( 'wp_rest_api_connect_error' )
			: get_site_option( 'wp_rest_api_connect_error' );
	}

	/**
	 * Handles deleting the stored data for a connection
	 *
	 * @since  0.1.0
	 *
	 * @return bool  Result of delete_option or delete_site_option
	 */
	public function delete_entire_option() {
		$deleted = parent::delete_entire_option();

		if ( $this->is_network ) {
			$deleted = delete_site_option( $this->option_key );
		}

		return $deleted;
	}

	/**
	 * Replaces get_option with get_site_option
	 * @since  0.1.0
	 */
	public function override_error_option_get( $val, $key ) {
		return get_site_option( $key );
	}

}
