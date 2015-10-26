# WDS WP REST API Connect UI #
**Contributors:**      WebDevStudios  
**Donate link:**       http://webdevstudios.com  
**Tags:**  
**Requires at least:** 4.3  
**Tested up to:**      4.3  
**Stable tag:**        0.1.0  
**License:**           GPLv2  
**License URI:**       http://www.gnu.org/licenses/gpl-2.0.html  

## Description ##

Provides UI for connecting from one WordPress installation to another via the [WordPress REST AP](http://wp-api.org/) over <a href="https://github.com/WP-API/OAuth1">OAuth1</a>. This plugin is a UI wrapper for [WDS WP REST API Connect](https://github.com/WebDevStudios/WDS-WP-REST-API-Connect).

**Caveats:**

* [CMB2](https://github.com/WebDevStudios/CMB2) is required. 
* The OAuth1 plugin still requires consumer credentials to be generated via WP-CLI. [See instructions here](How to get consumer credentials via WPCLI).
* Be sure to recursively clone this repo (git clone --recursive https://github.com/WebDevStudios/WDS-Twitter-Widget.git) in order to dowload the required submodule.

## Installation ##

### Manual Installation ###

1. Upload the entire `/wds-rest-connect-ui` directory to the `/wp-content/plugins/` directory.
2. Activate WDS WP REST API Connect UI through the 'Plugins' menu in WordPress.

## Frequently Asked Questions ##


## Screenshots ##

1. Settings
![Settings](https://raw.githubusercontent.com/WebDevStudios/WDS-WP-REST-API-Connect-UI/master/screenshot-1.png)

2. After settings are saved, authentication is required.
![After settings are saved, authentication is required.](https://raw.githubusercontent.com/WebDevStudios/WDS-WP-REST-API-Connect-UI/master/screenshot-2.png)

3. Successful authentication notice which demonstrates available routes.
![Successful authentication notice which demonstrates available routes.](https://raw.githubusercontent.com/WebDevStudios/WDS-WP-REST-API-Connect-UI/master/screenshot-3.png)

## Changelog ##

### 0.1.0 ###
* First release
