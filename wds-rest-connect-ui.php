<?php
/**
 * Plugin Name: WDS WP REST API Connect UI
 * Plugin URI:  http://webdevstudios.com
 * Description: Provides UI for connecting from one WordPress installation to another via the WP REST API over <a href="https://github.com/WP-API/OAuth1">OAuth1</a>
 * Version:     0.1.0
 * Author:      WebDevStudios
 * Author URI:  http://webdevstudios.com
 * Donate link: http://webdevstudios.com
 * License:     GPLv2
 * Text Domain: wds-rest-connect-ui
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2015 WebDevStudios (email : contact@webdevstudios.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using generator-plugin-wp
 */

/**
 * Autoloads files with classes when needed
 *
 * @since  0.1.0
 * @param  string $class_name Name of the class being requested
 * @return void
 */
function wds_rest_connect_ui_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'WDSRESTCUI_' ) ) {
		return;
	}

	$filename = strtolower( str_replace(
		'_', '-',
		substr( $class_name, strlen( 'WDSRESTCUI_' ) )
	) );

	WDS_REST_Connect_UI::include_file( $filename );
}
spl_autoload_register( 'wds_rest_connect_ui_autoload_classes' );


/**
 * Main initiation class
 *
 * @since  0.1.0
 * @var  string $version  Plugin version
 * @var  string $basename Plugin basename
 * @var  string $url      Plugin URL
 * @var  string $path     Plugin Path
 */
class WDS_REST_Connect_UI {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  0.1.0
	 */
	const VERSION = '0.1.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var WDS_REST_Connect_UI
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Instance of WDSRESTCUI_Settings
	 *
	 * @var WDSRESTCUI_Settings
	 */
	protected $settings;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @return WDS_REST_Connect_UI A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		$this->plugin_classes();
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function plugin_classes() {
		$class = defined( 'WDSRESTCUI_NETWORK_SETTINGS' ) ? 'WDSRESTCUI_Network_Settings' : 'WDSRESTCUI_Settings';
		$this->settings = new $class( $this );
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'wds-rest-connect-ui', false, dirname( $this->basename ) . '/languages/' );
		}
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  0.1.0
	 * @return boolean
	 */
	public static function meets_requirements() {
		// Plugin requires CMB2
		return defined( 'CMB2_LOADED' );
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.1.0
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {

			// Add a dashboard notice
			add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

			// Deactivate our plugin
			deactivate_plugins( $this->basename );

			return false;
		}

		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function requirements_not_met_notice() {
		// Output our error
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'WDS WP REST API Connect UI requires the <a href="https://wordpress.org/plugins/cmb2/">CMB2 plugin</a>, so it has been <a href="%s">deactivated</a>.', 'wds-network-require-login' ), admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'settings':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory
	 *
	 * @since  0.1.0
	 * @param  string  $filename Name of the file to be included
	 * @return bool    Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/'. $filename .'.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory
	 *
	 * @since  0.1.0
	 * @param  string $path (optional) appended path
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @since  0.1.0
	 * @param  string $path (optional) appended path
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the WDS_REST_Connect_UI object and return it.
 * Wrapper for WDS_REST_Connect_UI::get_instance()
 *
 * @since  0.1.0
 * @return WDS_REST_Connect_UI  Singleton instance of plugin class.
 */
function wds_rest_connect_ui() {
	return WDS_REST_Connect_UI::get_instance();
}

// Kick it off
add_action( 'plugins_loaded', array( wds_rest_connect_ui(), 'hooks' ) );
