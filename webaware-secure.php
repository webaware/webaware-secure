<?php
/*
Plugin Name: WebAware Secure
Description: some simple security measures without all the performance traps
Version: 1.0.0
Author: WebAware
Author URI: http://webaware.com.au/
*/

/*
copyright (c) 2014-2015 WebAware Pty Ltd (email : support@webaware.com.au)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('ABSPATH')) {
	exit;
}

define('WEBAWARE_SECURE_ROOT', __DIR__ . '/');
define('WEBAWARE_SECURE_NAME', basename(__DIR__) . '/' . basename(__FILE__));

const WEBAWARE_SECURE_OPTIONS = 'webaware_secure';

/**
* get the plugin options
*/
function webaware_secure_options() {
	static $defaults = array(
		'disable_xmlrpc'		=> 1,
		'disable_pingback'		=> 1,
		'auto_update_plugin'	=> 1,
		'auto_update_theme'		=> 1,
		'login_slug'			=> '',
	);

	return get_option(WEBAWARE_SECURE_OPTIONS, $defaults);
}

/**
* load secure steps, unless requested not to
*/
if (!defined('WEBAWARE_SECURE_IGNORE')) {
	require WEBAWARE_SECURE_ROOT . '/includes/secure-wp.php';
}

/**
* launch admin for settings, if required
*/
if (is_admin() && !(defined('DOING_AJAX') && DOING_AJAX)) {
	require WEBAWARE_SECURE_ROOT . '/includes/class.WebAwareSecureAdmin.php';
	new WebAwareSecureAdmin();
}
