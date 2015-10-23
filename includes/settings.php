<?php
/**
 * WDS WP REST API Connect UI Settings
 * @version 0.1.0
 * @package WDS WP REST API Connect UI
 */

class WDSRESTCUI_Settings {

	/**
	 * Plugin's basename
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $plugin_basename;

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
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Additional args for cmb2_metabox_form
	 *
	 * @var array
	 */
	protected $cmb2_form_args = array();

	/**
	 * Which admin menu hook to use for displaying the options page
	 *
	 * @var string
	 */
	protected $admin_menu_hook = 'admin_menu';

	/**
	 * Which plugin action links hook to use for displaying the options page
	 *
	 * @var string
	 */
	protected $plugin_action_links_hook = 'plugin_action_links_';

	/**
	 * Constructor
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function __construct( $plugin_basename, WDS_WP_REST_API_Connect $api ) {
		$this->plugin_basename = $plugin_basename;
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
		add_action( $this->admin_menu_hook, array( $this, 'add_options_page' ) );
		add_filter( $this->plugin_action_links_hook . $this->plugin_basename, array( $this, 'settings_link' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
	}

	/**
	 * Register our setting to WP
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function admin_init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function add_options_page() {
		$this->options_page = add_menu_page(
			$this->title,
			$this->title,
			'manage_options',
			$this->key,
			array( $this, 'admin_page_display' )
		);

		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

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
	 * Add custom fields to the options page.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function add_options_page_metabox() {

		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

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

		$this->check_maybe_check_api( $cmb );
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
		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'wds-rest-connect-ui' ), 'updated' );
		settings_errors( $this->key . '-notices' );

		delete_option( 'wp_rest_api_connect_error' );

		echo '
		<script type="text/javascript">
		window.location.href = "' . esc_url( add_query_arg( 'check_credentials', 1 ) ) . '";
		</script>
		';
	}

	public function check_maybe_check_api() {
		if ( ! $this->api()->key ) {
			return;
		}
		error_log( __METHOD__ );
		if ( isset( $_GET['re-auth'] ) && wp_verify_nonce( $_GET['re-auth'], 'reauth' ) ) {
			delete_option( $this->api()->option_key );
			delete_transient( 'apiconnect_desc_'. $this->api()->option_key );
			wp_redirect( $this->settings_url( array( 'check_credentials' => 1 ) ) );
			exit();
		}

		if ( isset( $_GET['check_credentials'] ) ) {
			if ( $this->do_api_check() ) {
				return;
			}
		}

		if ( $this->check_for_errors() ) {
			return;
		}

		$check_button = '
		<a class="button-secondary" href="'. add_query_arg( 'check_credentials', 1 ) .'">' . __( 'Check API Connection', 'wds-rest-connect-ui' ) . '</a>
		';

		$this->cmb2_form_args['form_format'] = '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<input type="submit" name="submit-cmb" value="%4$s" class="button-primary">&nbsp;&nbsp;'. $check_button .'</form>';
		// $this->fields['consumer_secret']['after_row'] = $check_button;
	}

	public function do_api_check() {
		$request = $this->api()->auth_get_request();
		$this->fields();

		if ( ! is_wp_error( $request ) ) {
			$message =
			'<div id="message" class="updated">
				<h3 class="error">' . __( 'SUCCESS:', 'wds-rest-connect-ui' ) . '</h3>
				<h4>'. __( 'Connected Site Name:', 'wds-rest-connect-ui' ) .'</h4>
				<p>'. esc_html( $request->name ) .'</p>
				<h4>'. __( 'Connected Site Description:', 'wds-rest-connect-ui' ) .'</h4>
				<p>'. esc_html( $request->description ) .'</p>
				<h4>'. __( 'Available Routes:', 'wds-rest-connect-ui' ) .'</h4>
				<xmp>'. print_r( array_keys( get_object_vars( $request->routes ) ), true ) .'</xmp>
				<p><a class="button-secondary" href="'. $this->settings_url() .'">' . __( 'Dismiss', 'wds-rest-connect-ui' ) . '</a>&nbsp;&nbsp;<a class="button-secondary" href="'. wp_nonce_url( $this->settings_url(), 'reauth', 're-auth' ) .'">' . __( 'Re-authenticate', 'wds-rest-connect-ui' ) . '</a></p>
			</div>
			';

			$this->fields['url']['before_row'] = $message;
			return true;
		} elseif ( 'wp_rest_api_missing_token_data' == $request->get_error_code() ) {
			$authenticate =
			'<div id="message" class="updated">
				<h3 class="error">' . __( "You're almost there.", 'wds-rest-connect-ui' ) . '</h3>
				<p><a class="button-secondary" href="'. esc_url( $request->get_error_data() ) .'">' . __( 'Click here to authenticate', 'wds-rest-connect-ui' ) . '</a></p>
			</div>
			';
			$this->fields['url']['before_row'] = $authenticate;
		} else {
			$error =
			'<div id="message" class="error">
				<h3 class="error">' . __( 'ERROR:', 'wds-rest-connect-ui' ) . '</h3>
				<h4>'. $request->get_error_message() .'</h4>
				<h4>'. $request->get_error_code() .'</h4>
				<xmp>Error Data: '. print_r( $request->get_error_data(), true ) .'</xmp>
				<p><a class="button-secondary" href="'. add_query_arg( 'wds_network_options_dismiss_errrors', 1 ) .'">' . __( 'Dismiss Errors', 'wds-rest-connect-ui' ) . '</a></p>
			</div>
			';
			$this->fields['url']['before_row'] = $error;
		}
	}

	public function check_for_errors() {
		if ( isset( $_GET['wds_network_options_dismiss_errrors'] ) ) {
			delete_option( 'wp_rest_api_connect_error' );
			wp_redirect( $this->settings_url() );
			exit();
		}

		$errors = get_option( 'wp_rest_api_connect_error' );
		if ( $errors && $errors['message'] ) {
			$error =
			'<div id="message" class="error">
				<h3 class="error">' . __( 'ERROR:', 'wds-rest-connect-ui' ) . '</h3>
				<h4>'. $errors['message'] .'</h4>
				<xmp>request args: '. print_r( $errors['request_args'], true ) .'</xmp>
				<xmp>request response: '. print_r( $errors['request_response'], true ) .'</xmp>
				<p><a class="button-secondary" href="'. add_query_arg( 'wds_network_options_dismiss_errrors', 1 ) .'">' . __( 'Dismiss Errors', 'wds-rest-connect-ui' ) . '</a></p>
			</div>
			';
			$this->fields['url']['before_row'] = $error;
			return true;
		}
	}

	public function settings_url( $args = array() ) {
		$args['page'] = $this->key;
		return esc_url_raw( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	}

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

		return $this->api->init( $args );
	}
}
