<?php
/**
 * WDS WP REST API Connect UI Compatibility
 * @version 0.1.0
 * @package WDS WP REST API Connect UI
 */

class WDSRESTCUI_Compatibility extends WDS_WP_REST_API_Connect {
	/**
	 * Parent plugin class
	 *
	 * @var   class
	 * @since 0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function hooks() {
	}
}
