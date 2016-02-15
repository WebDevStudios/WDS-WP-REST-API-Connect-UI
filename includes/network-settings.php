<?php
/**
 * WDS WP REST API Connect UI Network Settings
 * @version 0.1.0
 * @package WDS WP REST API Connect UI
 */

class WDSRESTCUI_Network_Settings extends WDSRESTCUI_Settings {

	/**
	 * Which admin menu hook to use for displaying the options page
	 *
	 * @var string
	 */
	protected $admin_menu_hook = 'network_admin_menu';

	/**
	 * Which plugin action links hook to use for displaying the options page
	 *
	 * @var string
	 */
	protected $plugin_action_links_hook = 'network_admin_plugin_action_links_';

	/**
	 * Initiate our hooks
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function hooks() {
		parent::hooks();

		add_filter( 'pre_option_'. $this->key, array( $this, 'get_option_override' ) );
		// Override CMB's getter
		add_filter( 'cmb2_override_option_get_'. $this->key, array( $this, 'get_override' ), 10, 2 );
		// Override CMB's setter
		add_filter( 'cmb2_override_option_save_'. $this->key, array( $this, 'update_override' ), 10, 2 );
	}

	/**
	 * Deletes all settings and connection settings.
	 *
	 * @since  0.2.0
	 */
	public function delete_all_and_redirect() {
		$this->api()->reset_connection();
		delete_site_option( $this->key );
		$this->redirect();
	}

	/**
	 * Replaces get_option with get_site_option
	 * @since  0.1.0
	 */
	public function get_option_override() {
		return get_site_option( $this->key );
	}

	/**
	 * Replaces get_option with get_site_option
	 * @since  0.1.0
	 */
	public function get_override( $test, $default = false ) {
		return get_site_option( $this->key, $default );
	}

	/**
	 * Replaces update_option with update_site_option
	 * @since  0.1.0
	 */
	public function update_override( $test, $option_value ) {
		return update_site_option( $this->key, $option_value );
	}

	/**
	 * This network settings page's URL with any specified query args
	 *
	 * @since  0.1.0
	 *
	 * @param  array   $args Optional array of query args
	 *
	 * @return string        Settings page URL.
	 */
	public function settings_url( $args = array() ) {
		$args['page'] = $this->key;
		return esc_url_raw( add_query_arg( $args, network_admin_url( 'admin.php' ) ) );
	}
}
