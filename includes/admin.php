<?php
namespace webawareau\secure;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * admin_init action
 */
add_action('admin_init', function() : void {
	add_settings_section(WEBAWARE_SECURE_OPTIONS, false, false, WEBAWARE_SECURE_OPTIONS);
	register_setting(WEBAWARE_SECURE_OPTIONS, WEBAWARE_SECURE_OPTIONS, __NAMESPACE__ . '\\settingsValidate');
});

/**
 * admin menu items
 */
add_action('admin_menu', function() : void {
	add_options_page('WebAware Secure', 'Simple Security', 'manage_options', 'webaware-secure', __NAMESPACE__ . '\\settingsPage');
});

/**
 * add plugin action links
 */
add_filter('plugin_action_links_' . WEBAWARE_SECURE_NAME, function(array $links) : array {
	if (current_user_can('manage_options')) {
		$url = admin_url('options-general.php?page=webaware-secure');
		$settings_link = sprintf('<a href="%s">Settings</a>', esc_url($url));
		array_unshift($links, $settings_link);
	}
	return $links;
});

/**
 * settings admin
 */
function settingsPage() : void {
	$options = get_options();

	if (!empty($options['login_slug'])) {
		ob_start();
		if (isset($options['apache_version']) && $options['apache_version'] === '2.4') {
			require WEBAWARE_SECURE_ROOT . 'views/htaccess-login-2.4.php';
		}
		else {
			// @link http://stackoverflow.com/a/12823245/911083 how to simulate END flag in Apache 2.2
			require WEBAWARE_SECURE_ROOT . 'views/htaccess-login.php';
		}
		$login_htaccess = ob_get_clean();
	}

	require WEBAWARE_SECURE_ROOT . 'views/settings-form.php';
}

/**
 * validate settings on save
 */
function settingsValidate(array $input) : array {
	$output = [];

	$output['disable_xmlrpc']		= empty($input['disable_xmlrpc']) ? 0 : 1;
	$output['disable_pingback']		= empty($input['disable_pingback']) ? 0 : 1;
	$output['disable_rsd']			= empty($input['disable_rsd']) ? 0 : 1;
	$output['disable_wlwmanifest']	= empty($input['disable_wlwmanifest']) ? 0 : 1;
	$output['disable_iter_users']	= empty($input['disable_iter_users']) ? 0 : 1;
	$output['auto_update_plugin']	= empty($input['auto_update_plugin']) ? 0 : 1;
	$output['auto_update_theme']	= empty($input['auto_update_theme']) ? 0 : 1;
	$output['login_slug']			= trim(sanitize_text_field($input['login_slug'] ?? ''), '/');
	$output['apache_version']		= sanitize_text_field($input['apache_version'] ?? '');

	return $output;
}
