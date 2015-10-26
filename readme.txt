=== WDS WP REST API Connect UI ===
Contributors:      WebDevStudios
Donate link:       http://webdevstudios.com
Tags:
Requires at least: 4.3
Tested up to:      4.3
Stable tag:        0.1.0
License:           GPLv2
License URI:       http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

Provides UI for connecting from one WordPress installation to another via the [WordPress REST AP](http://wp-api.org/) over <a href="https://github.com/WP-API/OAuth1">OAuth1</a>. This plugin is a UI wrapper for [WDS WP REST API Connect](https://github.com/WebDevStudios/WDS-WP-REST-API-Connect).

The OAuth1 plugin still requires consumer credentials to be generated via WP-CLI. [See instructions here](How to get consumer credentials via WPCLI).

#### Caveats:

* [CMB2](https://github.com/WebDevStudios/CMB2) is required.
* The OAuth1 plugin still requires consumer credentials to be generated via WP-CLI. [See instructions here](How to get consumer credentials via WPCLI).
* Be sure to recursively clone this repo (git clone --recursive https://github.com/WebDevStudios/WDS-Twitter-Widget.git) in order to dowload the required submodule.

#### Usage:

Once you've created a successful API connection via the Settings screen, you can use the the plugin's API helper function/filter. If the connection is successful, The helper function and filter both return a WDS_WP_REST_API_Connect object ([example usage here](https://github.com/WebDevStudios/WDS-WP-REST-API-Connect/blob/master/example.php)), which you can use to make your API requests.

The filter is an alternative to the helper function provided so that you can use in other plugins or themes without having to check if `function_exists`. To do that, simply use `$api = apply_filters( 'wds_rest_connect_ui_api_object', false );`. If the `wds_rest_connect_ui_api_object` function isn't available, you're original value, `false` will be returned. Whether using the function or the filter, you'll want to check if the `$api` object returned is a `WP_Error` object (`is_wp_error`) or a `WDS_WP_REST_API_Connect` object (`is_a( $api, 'WDS_WP_REST_API_Connect' )`) before proceeding with making requests.

```
// Get API object
$api = apply_filters( 'wds_rest_connect_ui_api_object', false );

// If WP_Error, find out what happened.
if ( is_wp_error( $api ) ) {
	echo '<xmp>'. print_r( $api->get_error_message(), true ) .'</xmp>';
}

// If a WDS_WP_REST_API_Connect object is returned, you're good to go.
if ( is_a( $api, 'WDS_WP_REST_API_Connect' ) ) {

	$schema = $api->auth_get_request();

	// Let's take a look at the API schema
	echo '<xmp>$schema: '. print_r( $schema, true ) .'</xmp>';
}
```

== Installation ==

= Manual Installation =

1. Upload the entire `/wds-rest-connect-ui` directory to the `/wp-content/plugins/` directory.
2. Activate WDS WP REST API Connect UI through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==


== Screenshots ==

1. Settings
2. After settings are saved, authentication is required.
3. Successful authentication notice which demonstrates available routes.

== Changelog ==

= 0.1.0 =
* First release

== Upgrade Notice ==

= 0.1.0 =
First Release
