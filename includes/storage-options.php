<?php
use WDS_WP_REST_API\Storage\Options;

/**
 * WDS WP REST API Connect UI Storage Options
 * @version 0.1.0
 * @package WDS WP REST API Connect UI
 */
class WDSRESTCUI_Storage_Options extends Options {
	protected function get_from_db() {
		return call_user_func_array( 'get_site_option', func_get_args() );
	}

	protected function delete_from_db() {
		return call_user_func_array( 'delete_site_option', func_get_args() );
	}

	protected function update_db() {
		return call_user_func_array( 'update_site_option', func_get_args() );
	}

	protected function add_db() {
		return call_user_func_array( 'add_site_option', func_get_args() );
	}
}
