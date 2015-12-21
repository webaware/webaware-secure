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
	add_filter('site_url', function($url) {
		if (strpos(basename($url), 'wp-login.php') === 0) {
			$options = webaware_secure_options();
			$url = str_replace('wp-login.php', trailingslashit($options['login_slug']), $url);
		}
		return $url;
	});
}

unset($options);
