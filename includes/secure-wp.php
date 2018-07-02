<?php

$options = webaware_secure_options();

/**
* disable XML-RPC protocol, not required on most websites and is a security risk!
* @link http://wordpress.stackexchange.com/a/78783/24260
* @link http://blog.sucuri.net/2014/03/more-than-162000-wordpress-sites-used-for-distributed-denial-of-service-attack.html
*/
if (!empty($options['disable_xmlrpc'])) {
	if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
		exit;
	}

	add_filter('xmlrpc_enabled', '__return_false');
}

/**
* disable RSD link, not required on most websites
*/
if (!empty($options['disable_rsd'])) {
	remove_action('wp_head', 'rsd_link');
}

/**
* disable wlwmanifest link, not required on most websites
*/
if (!empty($options['disable_wlwmanifest'])) {
	remove_action('wp_head', 'wlwmanifest_link');
}

/**
* stop sending X-Pingback headers
* @param array $filters
* @return array
*/
if (!empty($options['disable_pingback'])) {
	add_filter('wp_headers', function($headers) {
		unset($headers['X-Pingback']);
		return $headers;
	});

	// Enfold and other themes from Kriesi
	add_filter('avf_pingback_head_tag', '__return_empty_string');
}

/**
* disable auto-update of plugins (e.g. security fix pushes)
*/
if (empty($options['auto_update_plugin'])) {
	add_filter('auto_update_plugin', '__return_false');
}

/**
* disable auto-update of themes (e.g. security fix pushes)
*/
if (empty($options['auto_update_plugin'])) {
	add_filter('auto_update_theme', '__return_false');
}

/**
* redirect login script
*/
if (!empty($options['login_slug'])) {

	function webaware_secure_site_url($url) {
		list($bare_path) = explode('?', $url);
		$bare_path = basename($bare_path);

		if ($bare_path === 'wp-login.php') {
			$options = webaware_secure_options();
			$url = str_replace('wp-login.php', trailingslashit($options['login_slug']), $url);
		}
		return $url;
	}

	add_filter('site_url', 'webaware_secure_site_url');
	add_filter('network_site_url', 'webaware_secure_site_url');

	add_filter('logout_redirect', function($redirect_to, $requested_redirect_to) {
		if ($requested_redirect_to === '') {
			$options = webaware_secure_options();
			$redirect_to = home_url(trailingslashit($options['login_slug']) . '?loggedout=true');
		}

		return $redirect_to;
	}, 10, 2);

	add_filter('lostpassword_redirect', function($url) {
		if (empty($url)) {
			$options = webaware_secure_options();
			$url = home_url(trailingslashit($options['login_slug']) . '?checkemail=confirm');
		}

		return $url;
	}, 10, 2);

}

unset($options);
