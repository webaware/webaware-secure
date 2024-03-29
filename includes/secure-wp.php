<?php
namespace webawareau\secure;

if (!defined('ABSPATH')) {
	exit;
}

$options = get_options();

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

// maybe disable iterating over users
if (!empty($options['disable_iter_users'])) {

	/**
	 * disable iterating WordPress users: /?author=1
	 */
	add_filter('redirect_canonical', function($redirect_to) {
		if (is_author() && ctype_digit($_GET['author'] ?? '')) {
			wp_die('Not permitted.', 403);
		}
		return $redirect_to;
	});

	/**
	 * disable iterating WordPress users unless authenticated as a Contributor or above
	 * /wp-json/wp/v2/users
	 * /wp-json/wp/v2/users/1
	 */
	add_filter('rest_endpoints', function(array $endpoints) : array {
		if (!current_user_can('edit_posts')) {
			unset($endpoints['/wp/v2/users']);
			unset($endpoints['/wp/v2/users/(?P<id>[\\d]+)']);
		}

		return $endpoints;
	});

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

	function maybe_switch_login_url($url) {
		list($bare_path) = explode('?', $url);
		$bare_path = basename($bare_path);

		if ($bare_path === 'wp-login.php') {
			$options = get_options();
			$url = str_replace('wp-login.php', trailingslashit($options['login_slug']), $url);
		}
		return $url;
	}

	add_filter('site_url', __NAMESPACE__ . '\\maybe_switch_login_url');
	add_filter('network_site_url', __NAMESPACE__ . '\\maybe_switch_login_url');

	add_filter('logout_redirect', function($redirect_to, $requested_redirect_to) {
		if ($requested_redirect_to === '') {
			$options = get_options();
			$redirect_to = home_url(trailingslashit($options['login_slug']) . '?loggedout=true');
		}

		return $redirect_to;
	}, 10, 2);

	add_filter('lostpassword_redirect', function($url) {
		if (empty($url)) {
			$options = get_options();
			$url = home_url(trailingslashit($options['login_slug']) . '?checkemail=confirm');
		}

		return $url;
	}, 10, 2);

}

unset($options);
