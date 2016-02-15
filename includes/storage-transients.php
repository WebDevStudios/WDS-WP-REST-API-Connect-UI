<?php
use WDS_WP_REST_API\Storage\Transients;

/**
 * WDS WP REST API Connect UI Storage Transients
 * @version 0.1.0
 * @package WDS WP REST API Connect UI
 */
class WDSRESTCUI_Storage_Transients extends Transients {
	protected function get_from_db() {
		return call_user_func_array( 'get_site_transient', func_get_args() );
	}

	protected function delete_from_db() {
		return call_user_func_array( 'delete_site_transient', func_get_args() );
	}

	protected function update_db() {
		return call_user_func_array( 'set_site_transient', func_get_args() );
	}
}
