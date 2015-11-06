<?php

if (!defined('ABSPATH')) {
	exit;
}

class WebAwareSecureAdmin {

	public function __construct() {
		add_action('admin_init', array($this, 'adminInit'));
		add_action('admin_menu', array($this, 'adminMenu'));
		add_action('plugin_action_links_' . WEBAWARE_SECURE_NAME, array($this, 'pluginActionLinks'));
	}

	/**
	* admin_init action
	*/
	public function adminInit() {
		add_settings_section(WEBAWARE_SECURE_OPTIONS, false, false, WEBAWARE_SECURE_OPTIONS);
		register_setting(WEBAWARE_SECURE_OPTIONS, WEBAWARE_SECURE_OPTIONS, array($this, 'settingsValidate'));
	}

	/**
	* admin menu items
	*/
	public function adminMenu() {
		add_options_page('WebAware Secure', 'Simple Security', 'manage_options', 'webaware-secure', array($this, 'settingsPage'));
	}

	/**
	* add plugin action links
	*/
	public function pluginActionLinks($links) {
		if (current_user_can('manage_options')) {
			// add settings link
			$url = admin_url('options-general.php?page=webaware-secure');
			$settings_link = sprintf('<a href="%s">Settings</a>', esc_url($url));
			array_unshift($links, $settings_link);
		}

		return $links;
	}

	/**
	* settings admin
	*/
	public function settingsPage() {
		$options = webaware_secure_options();

		if (!empty($options['login_slug'])) {
			ob_start();
			require WEBAWARE_SECURE_ROOT . 'views/htaccess-login.php';
			$login_htaccess = ob_get_clean();
		}

		require WEBAWARE_SECURE_ROOT . 'views/settings-form.php';
	}

	/**
	* validate settings on save
	* @param array $input
	* @return array
	*/
	public function settingsValidate($input) {
		$output = array();

		$output['disable_xmlrpc']		= empty($input['disable_xmlrpc']) ? 0 : 1;
		$output['disable_pingback']		= empty($input['disable_pingback']) ? 0 : 1;
		$output['auto_update_plugin']	= empty($input['auto_update_plugin']) ? 0 : 1;
		$output['auto_update_theme']	= empty($input['auto_update_theme']) ? 0 : 1;
		$output['login_slug']			= empty($input['login_slug']) ? '' : trim(trim($input['login_slug']), '/');

		return $output;
	}

}
