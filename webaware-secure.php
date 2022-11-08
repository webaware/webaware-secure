<?php
/*
Plugin Name: WebAware Secure
Plugin URI: https://github.com/webaware/webaware-secure
Update URI: webaware-secure
Description: some simple security measures without all the performance traps
Version: 1.5.0
Author: WebAware
Author URI: https://shop.webaware.com.au/
*/

/*
copyright (c) 2014-2022 WebAware Pty Ltd (email : support@webaware.com.au)

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

define('WEBAWARE_SECURE_FILE', __FILE__);
define('WEBAWARE_SECURE_ROOT', __DIR__ . '/');
define('WEBAWARE_SECURE_NAME', basename(__DIR__) . '/' . basename(__FILE__));
define('WEBAWARE_SECURE_VERSION', '1.5.0');

const WEBAWARE_SECURE_OPTIONS = 'webaware_secure';

require WEBAWARE_SECURE_ROOT . '/includes/bootstrap.php';
