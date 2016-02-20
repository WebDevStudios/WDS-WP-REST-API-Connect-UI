<?php

use WDS_WP_REST_API\OAuth1\Connect;

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
	 * Connect object
	 *
	 * @var    Connect
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
	 * @param Connect $api             The API object.
	 */
	public function __construct( $plugin_basename, Connect $api ) {
		$this->plugin_action_links_hook .= $plugin_basename;
		$this->api   = $api;
		$this->title = __( 'WP REST API Connect', 'wds-rest-connect-ui' );
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

		if ( isset( $_GET['reset_all'] ) ) {
 			$this->delete_all_and_redirect();
		}

		if ( $this->api()->key() ) {
			$this->check_api();
		} else {
			$this->check_for_stored_connection_errors();
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
		$args = array();
		if ( $this->get( 'api_url' ) && ! $this->get( 'consumer_key' ) ) {
			$args['save_button'] = __( 'Begin Authorization', 'wds-rest-connect-ui' );
		}
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key, $args ); ?>
		</div>
		<?php if ( $this->api()->connected() ) :
			$creds     = $this->api()->get_option( 'token_credentials' );
			$auth_urls = $this->api()->auth_urls;
			?>
			<br>
			<h3 style="color:green">Connected!</h3>
			<hr>
			<div class="extra-detail">
				<h3>OAuth endpoints</h3>
				<dl>
					<dt>Request Token Endpoint</dt>
					<dd><code><?php echo esc_attr( $auth_urls->request ); ?></code></dd>
					<dt>Authorize Endpoint</dt>
					<dd><code><?php echo esc_attr( $auth_urls->authorize ); ?></code></dd>
					<dt>Access Token Endpoint</dt>
					<dd><code><?php echo esc_attr( $auth_urls->access ); ?></code></dd>
				</dl>
				<h3>OAuth credentials</h3>
				<dl>
					<dt>Client Key</dt>
					<dd><code><?php echo esc_attr( $this->api()->client_key ); ?></code></dd>
					<dt>Client Secret</dt>
					<dd><code><?php echo esc_attr( $this->api()->client_secret ); ?></code></dd>
					<dt>Access Token</dt>
					<dd><code><?php echo esc_attr( $creds->getIdentifier() ); ?></code></dd>
					<dt>Access Token Secret</dt>
					<dd><code><?php echo esc_attr( $creds->getSecret() ); ?></code></dd>
				</dl>
			</div>
		<?php endif;
	}

	/**
	 * Register the CMB2 instance and fields to the settings page.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register_settings_page_metabox() {
		// Add a "reset" button next to the "save" button.
		add_filter( 'cmb2_get_metabox_form_format', array( $this, 'add_reset_connection_button' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'save_fields' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		// Save the metabox if it's been submitted
		// check permissions
		$do_save = (
			isset( $_POST['submit-cmb'], $_POST['object_id'] )
			// check nonce
			&& isset( $_POST[ $cmb->nonce() ] )
			&& wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() )
			&& $_POST['object_id'] == $this->key
		);

		if ( $do_save ) {
			// Save fields at the beginning of page-load, not at field-generation time
			add_action( 'cmb2_after_init', array( $this, 'process_fields' ), 11 );
		}



		$url     = $this->get_current_value( 'url', 'esc_url_raw' );
		$api_url = $this->get_current_value( 'api_url', 'esc_url_raw' );
		$key     = $this->get_current_value( 'consumer_key', 'sanitize_text_field' );

		if ( $url && ! $this->api()->discovered() ) {
			$header_key = $this->get_current_value( 'header_key', 'sanitize_text_field' );
			$header_token = $this->get_current_value( 'header_token', 'sanitize_text_field' );
			$result = $this->do_discovery( $url, $header_key, $header_token );

			if ( ! is_wp_error( $result ) ) {
				$api_url = $result;
			}
		}

		$args = array(
			'name' => __( 'WordPress Site URL', 'wds-rest-connect-ui' ),
			'desc' => sprintf( __( 'Site must have the %s and %s plugins installed.', 'wds-rest-connect-ui' ), '<a target="_blank" href="https://github.com/WP-API/WP-API">WP-API</a>', '<a target="_blank" href="https://github.com/WP-API/OAuth1">OAuth1</a>' ),
			'id'   => 'url',
			'type' => 'text_url',
			'attributes' => array(
				'class' => 'cmb2-text-url regular-text',
			),
		);

		if ( empty( $url ) || empty( $api_url ) ) {
			$args['before_row'] = '<h3>' . __( 'Step 1: Find the API', 'wds-rest-connect-ui' ) . '</h3>';
			$args['name'] = __( 'Enter WordPress Site URL to start discovery', 'wds-rest-connect-ui' );
		} elseif ( empty( $key ) ) {
			$args['before_row'] = '<h3>' . __( 'Step 2: Input Credentials', 'wds-rest-connect-ui' ) . '</h3>';
		}

		$cmb->add_field( $args );

		$cmb->add_field( array(
			'id'   => 'api_url',
			'type' => 'hidden',
		) );

		// No URL? then wait...
		if ( ! empty( $url ) && ! empty( $api_url ) ) {
			$cmb->add_field( array(
				'name'       => __( 'Client Key', 'wds-rest-connect-ui' ),
				'before_row' => '<p class="description"><a target="_blank" href="'. trailingslashit( $url ) .'wp-admin/users.php?page=rest-oauth1-apps">' . __( 'Manage registered applications', 'wds-rest-connect-ui' ) . '</a> or <a target="_blank" href="https://github.com/WP-API/client-cli#step-1-creating-a-consumer">' . __( 'learn how to get client credentials via WPCLI', 'wds-rest-connect-ui' ) . '</a>.</p><p >' . __( 'The application callback URL for the application registration needs to be: ', 'wds-rest-connect-ui' ) . '<br><code>' . $this->settings_url() . '</code></p>',
				'id'         => 'consumer_key',
				'type'       => 'text',
				'attributes' => array(
					'required' => 'required',
				),
			) );

			$cmb->add_field( array(
				'name' => __( 'Client Secret', 'wds-rest-connect-ui' ),
				'id'   => 'consumer_secret',
				'type' => 'text',
				'attributes' => array(
					'required' => 'required',
				),
			) );
		}

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
	 * Get the current value from the database or the POSTed data.
	 * Will be sanitized using $sanitizer if collecting from POSTed data.
	 *
	 * @since  0.2.3
	 *
	 * @param  string  $key       option key
	 * @param  string  $sanitizer Sanitizer function
	 *
	 * @return mixed              Value
	 */
	public function get_current_value( $key, $sanitizer ) {
		$value = $this->get( $key );
		if ( ! $value ) {
			$value = ! empty( $_POST[ $key ] )
				? $sanitizer( $_POST[ $key ] )
				: false;
		}

		return $value;
	}

	/**
	 * Save fields earlier in the load order (cmb2_after_init)
	 *
	 * @since  0.1.0
	 */
	public function process_fields() {
		$presave_key = $this->get( 'consumer_key' );

		if ( ! $this->get( 'url' ) && empty( $_POST['url'] ) ) {
			$_POST['api_url'] = null;
			$this->api()->delete_option();
		}

		if ( $this->get( 'url' ) && empty( $_POST['url'] ) || empty( $_POST['consumer_key'] ) ) {
			$this->api()->delete_option();
		}

		$api_url = false;
		if ( ! $this->get( 'api_url' ) && ! empty( $_POST['url'] ) ) {

			$header_key   = sanitize_text_field( $_POST['header_key'] );
			$header_token = sanitize_text_field( $_POST['header_token'] );
			$result = $this->do_discovery( $_POST['url'], $header_key, $header_token );

			if ( ! is_wp_error( $result ) ) {
				$_POST['api_url'] = $api_url = $result;
			}
		}

		// Save the fields
		$cmb = cmb2_get_metabox( $this->metabox_id );
		$cmb->save_fields( $this->key, $cmb->object_type( 'options-page' ), $_POST );

		// If we' don't have the right stuff, we need to redirect to get authorization
		if ( empty( $presave_key ) && ! empty( $_POST['consumer_key'] ) ) {
			$this->api()->redirect_to_login();
		}

		// Redirect after saving to prevent refresh-saving
		$this->redirect();
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

		$output = str_replace( 'updated settings-', 'is-dismissible updated settings-', $output );
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
		$this->api()->delete_stored_error();

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

		if ( $this->get( 'consumer_key' ) ) {
			// Add a "check credentials" button next to the "save" button.
			add_filter( 'cmb2_get_metabox_form_format', array( $this, 'add_check_connection_button' ), 10, 2 );
		}
	}

	/**
	 * Deletes all settings and connection settings.
	 *
	 * @since  0.2.0
	 */
	public function delete_all_and_redirect() {
		$this->api()->reset_connection();
		delete_option( $this->key );
		$this->redirect();
	}

	/**
	 * Deletes stored API connection data and redirects to setup reauthentication
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function reauth_and_redirect() {
		$this->api()->delete_option( 'token_credentials' );
		$this->api()->delete_stored_error();
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
		$this->api()->delete_stored_error();
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
		if ( ! $this->api()->connected() ) {
			$this->api()->redirect_to_login();
		}

		$user = $this->get_user();
		$desc = $user ? $this->get_api_description() : false;

		if ( $user && $desc ) {
			return $this->success_message( $user, $desc );
		}
	}

	/**
	 * Get the API Description object
	 *
	 * @since  0.2.0
	 *
	 * @return mixed  Description object or error.
	 */
	public function get_api_description() {
		$desc = $this->api()->get_api_description();

		if ( is_wp_error( $desc ) ) {
			if ( 'wp_rest_api_missing_client_data' == $desc->get_error_code() ) {
				return $this->need_to_authenticate_message( $desc );
			}

			return $this->oops_error_message( $desc );
		}

		return $desc;
	}

	/**
	 * Get's authorized user. Useful for testing authenticated connection.
	 *
	 * @since  0.2.0
	 *
	 * @return mixed  User object or WP_Error object.
	 */
	public function get_user() {
		$user = $this->api()->get_user();

		if ( is_wp_error( $user ) ) {

			if ( 'wp_rest_api_not_authorized' == $user->get_error_code() ) {
				return $this->need_to_authenticate_message( $user );
			}

			return $this->oops_error_message( $user );
		}

		return $user;
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
	 * Output the authenticated user's detail.
	 *
	 * @since  0.2.0
	 *
	 * @return string  HTML
	 */
	public function output_user() {
		$user = $this->api()->get_user();
		$html = '
		<table class="wp-list-table widefat user-card">
			<thead>
				<tr>
					<th>' . __( 'Authenticated User', 'wds-rest-connect-ui' ) . '</th>
					<th>' . __( 'Details', 'wds-rest-connect-ui' ) . '</th>
					<th>' . __( 'Description', 'wds-rest-connect-ui' ) . '</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<div class="avatar"><img src="' . esc_attr( $user->imageUrl ) .'" /></div>
						<p>' . esc_attr( $user->name ) .' (<code>' . esc_attr( $user->nickname ) .'</code>) <a href="' . esc_attr( $user->urls['permalink'] ) .'" target="_blank">' . __( 'View posts', 'wds-rest-connect-ui' ) . '</a></p>
					</td>
					<td>
						<dl>
							<dt>' . __( 'First Name', 'wds-rest-connect-ui' ) . '</dt>
							<dd>' . ( ! empty( $user->firstName ) ? esc_attr( $user->firstName ) : '' ) .'</dd>
							<dt>' . __( 'Last Name', 'wds-rest-connect-ui' ) . '</dt>
							<dd>' . ( ! empty( $user->lastName ) ? esc_attr( $user->lastName ) : '' ) .'</dd>
							<dt>' . __( 'Email', 'wds-rest-connect-ui' ) . '</dt>
							<dd>' . ( ! empty( $user->email ) ? esc_attr( $user->email ) : '' ) .'</dd>
						</dl>
					</td>
					<td>' . ( ! empty( $user->description ) ? esc_attr( $user->description ) : '' ) .'</dd>
				</tr>
			</tbody>
		</table>
		';
		return $html;
	}

	/**
	 * Register a notice for a successful API connection, and display API data.
	 *
	 * @since  0.1.0
	 *
	 * @return bool  Successful connection.
	 */
	public function success_message( $user, $desc ) {
		$message = '
		<br>
		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th>' . __( 'Connected Site Name', 'wds-rest-connect-ui' ) . '</th>
					<th>' . __( 'Connected Site Description', 'wds-rest-connect-ui' ) . '</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>'. esc_html( $desc->name ) .'</td>
					<td>'. esc_html( $desc->description ) .'</td>
				</tr>
			</tbody>
		</table>
		<br>
		'. $this->output_user( $user ) .'
		<br>
		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th>'. __( 'Available Routes:', 'wds-rest-connect-ui' ) .'</th>
				</tr>
			</thead>
			<tbody>
				<tr><td><xmp>'. print_r( array_keys( get_object_vars( $desc->routes ) ), true ) .'</xmp></td></tr>
			</tbody>
		</table>
		<br>
		<p><a class="button-secondary" href="'. $this->settings_url() .'">' . __( 'Dismiss', 'wds-rest-connect-ui' ) . '</a>&nbsp;&nbsp;<a class="button-secondary" href="'. $this->reauth_url() .'">' . __( 'Re-authenticate', 'wds-rest-connect-ui' ) . '</a></p>
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

		$url = $this->api()->get_authorization_url();
		if ( is_wp_error( $url ) ) {
			return false;
		}

		$authenticate = '<p><a class="button-secondary" href="'. esc_url( $url ) .'">' . __( 'Click here to authenticate', 'wds-rest-connect-ui' ) . '</a></p>';

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

		$check_button = '&nbsp;&nbsp;&nbsp;<a class="button-secondary" href="'. $url .'">' . __( 'Check API Connection', 'wds-rest-connect-ui' ) . '</a></form>';
		// Add a check-api button to the form
		$format = str_replace(
			'</form>',
			$check_button,
			$format
		);

		return $format;
	}

	/**
	 * Add a "reset" button next to the "save" button.
	 *
	 * @since 0.1.0
	 *
	 * @param string  $format    Form format
	 * @param string  $object_id CMB2 object ID
	 */
	public function add_reset_connection_button( $format, $object_id ) {
		if ( $object_id != $this->key ) {
			return $format;
		}

		$reset_url = str_replace( '%', '%%', esc_url( $this->reset_url() ) );

		$reset_button = '&nbsp;&nbsp;&nbsp;<a class="button-secondary" href="'. $reset_url .'">' . __( 'Reset All Settings', 'wds-rest-connect-ui' ) . '</a></form>';
		// Add a check-api button to the form
		$format = str_replace(
			'</form>',
			$reset_button,
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
	 * This settings page's URL with a reset query arg
	 *
	 * @since  0.2.0
	 */
	public function reset_url() {
		return wp_nonce_url( $this->settings_url(), 'reset_all', 'reset_all' );
	}

	/**
	 * This settings page's URL with a re-auth query arg
	 *
	 * @since  0.2.0
	 */
	public function reauth_url() {
		return wp_nonce_url( $this->settings_url(), 'reauth', 're-auth' );
	}

	/**
	 * Get a setting from the stored settings values.
	 *
	 * @since  0.1.0
	 * @see    get_option()
	 * @see    cmb2_get_option()
	 *
	 * @param  string  $field_id Specifies the setting to retrieve.
	 *
	 * @return mixed             Setting value.
	 */
	public function get( $field_id = '', $default = false ) {
		if ( function_exists( 'cmb2_get_option' ) ) {
			$value = cmb2_get_option( $this->key, $field_id, $default );
		} else {

			$opts = get_option( $this->key );
			$value = $default;

			if ( 'all' == $field_id ) {
				$value = $opts;
			} elseif ( array_key_exists( $field_id, $opts ) ) {
				$value = false !== $opts[ $field_id ] ? $opts[ $field_id ] : $default;
			}
		}

		if ( $value && 'api_url' == $field_id ) {
			$value = trailingslashit( $value );
		}

		return $value;
	}

	/**
	 * Wrapper for the api do_discovery method which sets the headers if we have them.
	 *
	 * @since  0.2.3
	 *
	 * @param  string  $url          URL for discovery
	 * @param  boolean $header_key   Value for header key if we have it
	 * @param  boolean $header_token Value for header token if we have it
	 *
	 * @return mixed                 Result of WDS_WP_REST_API\OAuth1\Connect::do_discovery
	 */
	public function do_discovery( $url, $header_key = false, $header_token = false ) {
		$api = $this->api();
		if ( $header_key && $header_token ) {
			$api->set_headers( array( $header_key => $header_token ) );
		}

		return $api->do_discovery( $url );
	}

	/**
	 * Return (and initiate) API object.
	 *
	 * @return WDS_Network_Connect_API_Connect
	 */
	public function api() {
		if ( $this->api->key() && $this->api->client_key ) {
			// Has already been initated
			return $this->api;
		}

		$all = $this->get( 'all' );
		$all = is_array( $all ) ? array_filter( $all ) : false;

		// Make sure we have the bare minimums saved for making a connection.
		if (
			empty( $all )
			|| ! $this->get( 'api_url' )
		) {
			if ( $this->get( 'header_key' ) && $this->get( 'header_token' ) ) {
				$this->api->set_headers( array( $this->get( 'header_key' ) => $this->get( 'header_token' ) ) );
			}

			return $this->api;
		}

		$args['client_key']    = $this->get( 'consumer_key' );
		$args['client_secret'] = $this->get( 'consumer_secret' );
		$args['api_url']       = $this->get( 'api_url' );
		// $args['auth_urls']  = get_option( $this->key . '_urls' );
		$args['callback_uri']  = $this->settings_url();

		if ( $this->get( 'header_key' ) && $this->get( 'header_token' ) ) {
			$args['headers'] = array( $this->get( 'header_key' ) => $this->get( 'header_token' ) );
		}

		// Initate the API.
		$this->api->init( $args );

		if ( $this->api->is_authorizing() ) {
			$this->redirect( array( 'check_credentials' => 1 ) );
		}

		return $this->api;
	}
}
