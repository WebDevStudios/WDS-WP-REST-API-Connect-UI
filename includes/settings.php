<?php
/**
 * WDS WP REST API Connect UI Settings
 * @version 0.1.0
 * @package WDS WP REST API Connect UI
 */

class WDSRESTCUI_Settings {

	/**
	 * Option key, and option page slug
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $key = 'wds_rest_connect_ui_settings';

	/**
	 * Options page metabox id
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $metabox_id = 'wds_rest_connect_ui_settings_metabox';

	/**
	 * Options Page title
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $title = '';

	/**
	 * WDS_WP_REST_API_Connect object
	 *
	 * @var    WDS_WP_REST_API_Connect
	 * @since  0.1.0
	 */
	protected $api;

	/**
	 * Settings page hook
	 * @var string
	 */
	protected $page_hook = '';

	/**
	 * Which admin menu hook to use for displaying the settings page
	 *
	 * @var string
	 */
	protected $admin_menu_hook = 'admin_menu';

	/**
	 * Which plugin action links hook to use for displaying the settings page
	 *
	 * @var string
	 */
	protected $plugin_action_links_hook = 'plugin_action_links_';

	/**
	 * Constructor
	 *
	 * @since 0.1.0
	 *
	 * @param string                  $plugin_basename The plugin's basename.
	 * @param WDS_WP_REST_API_Connect $api             The API object.
	 */
	public function __construct( $plugin_basename, WDS_WP_REST_API_Connect $api ) {
		$this->plugin_action_links_hook .= $plugin_basename;
		$this->api             = $api;
		$this->title           = __( 'WP REST API Connect', 'wds-rest-connect-ui' );
	}

	/**
	 * Initiate our hooks
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( $this->admin_menu_hook, array( $this, 'add_settings_page' ) );
		add_filter( $this->plugin_action_links_hook, array( $this, 'settings_link' ) );
		add_action( 'cmb2_admin_init', array( $this, 'register_settings_page_metabox' ) );
	}

	/**
	 * Register our setting to WP
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function admin_init() {
		register_setting( $this->key, $this->key );

		add_action( 'all_admin_notices', array( $this, 'output_notices' ) );
		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		if ( $this->api()->key ) {
			$this->check_api();
		}
	}

	/**
	 * Add menu settings page
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function add_settings_page() {
		$this->page_hook = add_menu_page(
			$this->title,
			$this->title,
			'manage_options',
			$this->key,
			array( $this, 'admin_page_display' )
		);

		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->page_hook}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	 * Add a settings link to the plugin page.
	 *
	 * @since  0.1.0
	 *
	 * @param  array  $links Array of links
	 *
	 * @return array         Modified array of links
	 */
	public function settings_link( $links ) {
		$setting_link = sprintf( '<a href="%s">%s</a>', $this->settings_url(), __( 'Settings', 'wds-rest-connect-ui' ) );
		array_unshift( $links, $setting_link );

		return $links;
	}

	/**
	 * Admin page markup. Mostly handled by CMB2
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
		</div>
		<?php
	}

	/**
	 * Register the CMB2 instance and fields to the settings page.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register_settings_page_metabox() {

		$cmb = new_cmb2_box( array(
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		$cmb->add_field( array(
			'name' => __( 'WordPress Site URL', 'wds-rest-connect-ui' ),
			'desc' => sprintf( __( 'Site must have the %s and %s plugins installed.', 'wds-rest-connect-ui' ), '<a target="_blank" href="https://github.com/WP-API/WP-API">WP-API</a>', '<a target="_blank" href="https://github.com/WP-API/OAuth1">OAuth1</a>' ),
			'id'   => 'url',
			'type' => 'text_url',
			'attributes' => array(
				'class' => 'cmb2-text-url regular-text',
			),
		) );

		$cmb->add_field( array(
			'name'    => __( 'WP-API Endpoint', 'wds-rest-connect-ui' ),
			'desc'    => __( 'The API endpoint on the WP-API server. If empty, this defaults to "/wp-json/".', 'wds-rest-connect-ui' ),
			'id'      => 'endpoint',
			'type'    => 'text',
			'default' => '/wp-json/',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Consumer Key', 'wds-rest-connect-ui' ),
			'before_row' => '<p><a target="_blank" href="https://github.com/WP-API/client-cli#step-1-creating-a-consumer">' . __( 'How to get consumer credentials via WPCLI', 'wds-rest-connect-ui' ) . '</a></p>',
			'id'         => 'consumer_key',
			'type'       => 'text',
		) );

		$cmb->add_field( array(
			'name' => __( 'Consumer Secret', 'wds-rest-connect-ui' ),
			'id'   => 'consumer_secret',
			'type' => 'text',
		) );

		$cmb->add_field( array(
			'name' => __( 'Optional Headers', 'wds-rest-connect-ui' ),
			'desc' => __( 'If the WordPress API requires a Header Key/Token for access, i.e. <a href="https://github.com/WebDevStudios/WDS-Allow-REST-API">WDS Allow REST API</a>.', 'wds-rest-connect-ui' ),
			'id'   => 'header_title',
			'type' => 'title',
		) );

		$cmb->add_field( array(
			'name' => __( 'Header Key', 'wds-rest-connect-ui' ),
			'id'   => 'header_key',
			'type' => 'text',
		) );

		$cmb->add_field( array(
			'name' => __( 'Header Token', 'wds-rest-connect-ui' ),
			'id'   => 'header_token',
			'type' => 'text',
		) );

	}

	/**
	 * Displays registered admin notices
	 *
	 * @since  0.1.0
	 * @uses   get_settings_errors()
	 *
	 * @return void
	 */
	public function output_notices() {
		$settings_errors = get_settings_errors( $this->key . '-notices' );

		if ( empty( $settings_errors ) ) {
			return;
		}

		$output = '';
		foreach ( $settings_errors as $key => $details ) {
			$css_id = 'setting-error-' . $details['code'];
			$css_class = $details['type'] . ' settings-error notice';
			$output .= "<div id='$css_id' class='$css_class'> \n";
			$output .= $details['message'];
			$output .= "</div> \n";
		}
		echo $output;
	}

	/**
	 * Register settings notices for display
	 *
	 * @since  0.1.0
	 *
	 * @param  int    $object_id Option key
	 * @param  array  $updated   Array of updated fields
	 *
	 * @return void
	 */
	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}

		// Setup our save notice
		$this->register_notice( __( 'Settings updated.', 'wds-rest-connect-ui' ), false, '' );
		$this->output_notices();

		// Delete stored errors
		$this->api()->update_stored_error();

		// Add redirect to re-check credentials
		echo '
		<script type="text/javascript">
		window.location.href = "' . esc_url_raw( add_query_arg( 'check_credentials', 1 ) ) . '";
		</script>
		';
	}

	/**
	 * API checks
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function check_api() {
		// Setup reauth if requested.
		if ( isset( $_GET['re-auth'] ) && wp_verify_nonce( $_GET['re-auth'], 'reauth' ) ) {
			return $this->reauth_and_redirect();
		}

		// Check auth credentials if requested.
		if ( isset( $_GET['check_credentials'] ) && $this->verify_api_connection_successful() ) {
			return;
		}

		// Dismiss authentication errors if requested.
		if ( isset( $_GET['dismiss_errrors'] ) ) {
			return $this->dismiss_errrors_and_redirect();
		}

		// Output any connection errors that may exist.
		if ( $this->check_for_stored_connection_errors() ) {
			return;
		}

		// Add a "check credentials" button next to the "save" button.
		add_filter( 'cmb2_get_metabox_form_format', array( $this, 'add_check_connection_button' ), 10, 2 );
	}

	/**
	 * Deletes stored API connection data and redirects to setup reauthentication
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function reauth_and_redirect() {
		$this->api()->delete_entire_option();
		$this->redirect( array( 'check_credentials' => 1 ) );
	}

	/**
	 * Deletes stored API connection errors and redirects, removing any query params.
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function dismiss_errrors_and_redirect() {
		$this->api()->update_stored_error();
		$this->redirect();
	}

	/**
	 * Determines if API connection credentials provide a successful connection.
	 *
	 * @since  0.1.0
	 *
	 * @return bool  Whether API conneciton is successful.
	 */
	public function verify_api_connection_successful() {
		$request = $this->api()->auth_get_request();

		if ( ! is_wp_error( $request ) ) {
			return $this->success_message( $request );
		}

		if ( 'wp_rest_api_missing_token_data' == $request->get_error_code() ) {
			return $this->need_to_authenticate_message( $request );
		}

		return $this->oops_error_message( $request );
	}

	/**
	 * Register a notice for any connection errors that may exist.
	 *
	 * @since  0.1.0
	 *
	 * @return bool  Whether stored connection errors exist.
	 */
	public function check_for_stored_connection_errors() {
		$errors = $this->api()->get_stored_error();

		if ( ! $errors || ! isset( $errors['message'] ) || ! $errors['message'] ) {
			return false;
		}

		$message = '
		<h4>'. $errors['message'] .'</h4>
		<xmp>request args: '. print_r( $errors['request_args'], true ) .'</xmp>
		<xmp>request response: '. print_r( $errors['request_response'], true ) .'</xmp>
		<p><a class="button-secondary" href="'. add_query_arg( 'dismiss_errrors', 1 ) .'">' . __( 'Dismiss Errors', 'wds-rest-connect-ui' ) . '</a></p>
		';
		$this->register_notice( $message );

		return true;
	}

	/**
	 * Register a notice for a successful API connection, and display API data.
	 *
	 * @since  0.1.0
	 *
	 * @return bool  Successful connection.
	 */
	public function success_message( $request ) {
		$message = '
		<h4>'. __( 'Connected Site Name:', 'wds-rest-connect-ui' ) .'</h4>
		<p>'. esc_html( $request->name ) .'</p>
		<h4>'. __( 'Connected Site Description:', 'wds-rest-connect-ui' ) .'</h4>
		<p>'. esc_html( $request->description ) .'</p>
		<h4>'. __( 'Available Routes:', 'wds-rest-connect-ui' ) .'</h4>
		<xmp>'. print_r( array_keys( get_object_vars( $request->routes ) ), true ) .'</xmp>
		<p><a class="button-secondary" href="'. $this->settings_url() .'">' . __( 'Dismiss', 'wds-rest-connect-ui' ) . '</a>&nbsp;&nbsp;<a class="button-secondary" href="'. wp_nonce_url( $this->settings_url(), 'reauth', 're-auth' ) .'">' . __( 'Re-authenticate', 'wds-rest-connect-ui' ) . '</a></p>
		';

		$this->register_notice( $message, false );

		return true;
	}

	/**
	 * Register a notice when re-authentication is required.
	 *
	 * @since  0.1.0
	 *
	 * @param  WP_Error $request WP_Error object
	 *
	 * @return bool              Failed connection.
	 */
	public function need_to_authenticate_message( $request ) {
		$authenticate = '<p><a class="button-secondary" href="'. esc_url( $request->get_error_data() ) .'">' . __( 'Click here to authenticate', 'wds-rest-connect-ui' ) . '</a></p>';

		$this->register_notice( $authenticate, false, __( "You're almost there.", 'wds-rest-connect-ui' ) );

		return false;
	}

	/**
	 * Register a notice when authentication failed.
	 *
	 * @since  0.1.0
	 *
	 * @param  WP_Error $request WP_Error object
	 *
	 * @return bool              Failed connection.
	 */
	public function oops_error_message( $request ) {
		$message = '
		<h4>'. $request->get_error_message() .'</h4>
		<h4>'. $request->get_error_code() .'</h4>
		<xmp>Error Data: '. print_r( $request->get_error_data(), true ) .'</xmp>
		<p><a class="button-secondary" href="'. add_query_arg( 'dismiss_errrors', 1 ) .'">' . __( 'Dismiss Errors', 'wds-rest-connect-ui' ) . '</a></p>
		';

		$this->register_notice( $message );

		return false;
	}

	/**
	 * Add a "check credentials" button next to the "save" button.
	 *
	 * @since 0.1.0
	 *
	 * @param string  $format    Form format
	 * @param string  $object_id CMB2 object ID
	 */
	public function add_check_connection_button( $format, $object_id ) {
		if ( $object_id != $this->key ) {
			return $format;
		}

		$url = str_replace( '%', '%%', esc_url( add_query_arg( 'check_credentials', 1 ) ) );

		// Add a check-api button to the form
		$format = str_replace(
			'</form>',
			'&nbsp;&nbsp;&nbsp;<a class="button-secondary" href="'. $url .'">' . __( 'Check API Connection', 'wds-rest-connect-ui' ) . '</a></form>',
			$format
		);

		return $format;
	}

	/**
	 * Registers a notice to be output later.
	 *
	 * @since  0.1.0
	 * @uses   add_settings_error()
	 *
	 * @param  string      $message Text of output notice
	 * @param  boolean     $error   Whether notice is an error notice
	 * @param  string|null $title   Optional title
	 *
	 * @return void
	 */
	public function register_notice( $message, $error = true, $title = null ) {
		if ( is_null( $title ) ) {
			$title = $error
				? __( 'ERROR:', 'wds-rest-connect-ui' )
				: __( 'SUCCESS:', 'wds-rest-connect-ui' );
		}

		$type  = $error ? 'error' : 'updated';

		if ( $title ) {
			$title = $title ? '<h3>' . $title . '</h3>' : '';
			$message = $title . $message;
		}

		add_settings_error( $this->key . '-notices', $this->key, $message, $type );
	}

	/**
	 * Redirects to our settings page w/ any specified query args
	 *
	 * @since  0.1.0
	 * @uses   wp_redirect()
	 *
	 * @param  array   $args Optional array of query args
	 *
	 * @return void
	 */
	public function redirect( $args = array() ) {
		wp_redirect( $this->settings_url( $args ) );
		exit();
	}

	/**
	 * This settings page's URL with any specified query args
	 *
	 * @since  0.1.0
	 *
	 * @param  array   $args Optional array of query args
	 *
	 * @return string        Settings page URL.
	 */
	public function settings_url( $args = array() ) {
		$args['page'] = $this->key;
		return esc_url_raw( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	}

	/**
	 * Get a setting from the stored settings values.
	 *
	 * @since  0.1.0
	 * @see    cmb2_get_option()
	 *
	 * @param  string  $key Specifies the setting to retrieve.
	 *
	 * @return mixed        Setting value.
	 */
	public function get( $key = '' ) {
		return cmb2_get_option( $this->key, $key );
	}

	/**
	 * Return (and initiate) API object.
	 *
	 * @return WDS_Network_Connect_API_Connect
	 */
	public function api() {
		if ( ! empty( $this->api->key ) ) {
			// Has already been initated
			return $this->api;
		}

		$all = $this->get( 'all' );
		$all = is_array( $all ) ? array_filter( $all ) : false;

		// Make sure we have the bare minimums saved for making a connection.
		if (
			empty( $all )
			|| ! $this->get( 'url' )
			|| ! $this->get( 'consumer_key' )
			|| ! $this->get( 'consumer_secret' )
		) {
			return $this->api;
		}

		$args['consumer_key']       = $this->get( 'consumer_key' );
		$args['consumer_secret']    = $this->get( 'consumer_secret' );
		$args['json_url'] = trailingslashit( $this->get( 'url' ) );
		$args['json_url'] .= ltrim( trailingslashit( $this->get( 'endpoint', '/wp-json/' ) ), '/' );

		if ( $this->get( 'header_key' ) && $this->get( 'header_token' ) ) {
			$args['headers'] = array( $this->get( 'header_key' ) => $this->get( 'header_token' ) );
		}

		// Initate the API.
		return $this->api->init( $args );
	}
}
