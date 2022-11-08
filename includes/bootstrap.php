<?php
namespace webawareau\secure;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * load secure steps, unless requested not to
 */
if (!defined('WEBAWARE_SECURE_IGNORE')) {
	require WEBAWARE_SECURE_ROOT . '/includes/secure-wp.php';
}

/**
 * hook in the admin manager
 */
if (is_admin() && !wp_doing_ajax()) {
	require WEBAWARE_SECURE_ROOT . '/includes/admin.php';
}

/**
 * hook in the plugin update manager
 */
add_action('init', function() : void {
	if (is_admin() || wp_doing_cron() || (defined('WP_CLI') && WP_CLI)) {
		require WEBAWARE_SECURE_ROOT . 'includes/class.Updater.php';
		new Updater(WEBAWARE_SECURE_NAME, WEBAWARE_SECURE_FILE, 'webaware-secure',
			'https://updates.webaware.net.au/webaware-secure/webaware-secure-latest.json');
	}
});

/**
 * get the plugin options
 */
function get_options() : array {
	static $defaults = [
		'disable_xmlrpc'		=> 1,
		'disable_pingback'		=> 1,
		'disable_rsd'			=> 1,
		'disable_wlwmanifest'	=> 1,
		'disable_iter_users'	=> 1,
		'auto_update_plugin'	=> 1,
		'auto_update_theme'		=> 1,
		'login_slug'			=> '',
		'apache_version'		=> '2.4',
	];

	return wp_parse_args(get_option(WEBAWARE_SECURE_OPTIONS, []), $defaults);
}
