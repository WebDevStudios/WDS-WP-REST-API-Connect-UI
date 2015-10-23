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
	}

	/**
	 * Initate our connect object
	 *
	 * @since 0.1.0
	 *
	 * @param array $args Arguments containing 'consumer_key', 'consumer_secret', and the 'json_url'
	 */
	public function init( $args = array() ) {
		error_log( __METHOD__ );
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

}
