<?php
/*
Plugin Name: WebAware Secure
Plugin URI: https://github.com/webaware/webaware-secure
Update URI: webaware-secure
Description: some simple security measures without all the performance traps
Version: 1.5.2
Author: WebAware
Author URI: https://shop.webaware.com.au/
*/

/*
copyright (c) 2014-2023 WebAware Pty Ltd (email : support@webaware.com.au)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if (!defined('ABSPATH')) {
	exit;
}

define('WEBAWARE_SECURE_FILE', __FILE__);
define('WEBAWARE_SECURE_ROOT', __DIR__ . '/');
define('WEBAWARE_SECURE_NAME', basename(__DIR__) . '/' . basename(__FILE__));
define('WEBAWARE_SECURE_VERSION', '1.5.2');

const WEBAWARE_SECURE_OPTIONS = 'webaware_secure';

require WEBAWARE_SECURE_ROOT . '/includes/bootstrap.php';
