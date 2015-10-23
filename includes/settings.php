<?php
/**
 * WDS WP REST API Connect UI Settings
 * @version 0.1.0
 * @package WDS WP REST API Connect UI
 */

class WDSRESTCUI_Settings {
	/**
	 * Parent plugin class
	 *
	 * @var    class
	 * @since  0.1.0
	 */
	protected $plugin;

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
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

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
		add_action( $this->admin_menu_hook, array( $this, 'add_options_page' ) );
		add_filter( $this->plugin_action_links_hook . $this->plugin->basename, array( $this, 'settings_link' ) );
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

		?>
		<script type="text/javascript">
		window.location.href = '<?php echo esc_url( add_query_arg( 'check_credentials', 1 ) ); ?>';
		</script>
		<?php
	}

	public function settings_url( $args = array() ) {
		$args['page'] = $this->key;
		return esc_url_raw( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	}
}
