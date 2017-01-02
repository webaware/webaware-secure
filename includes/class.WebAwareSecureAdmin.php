<?php

if (!defined('ABSPATH')) {
	exit;
}

class WebAwareSecureAdmin {

	const TRANSIENT_UPDATE_INFO		= 'webaware_secure_update_info';
	const URL_UPDATE_INFO			= 'https://updates.webaware.net.au/webaware-secure/webaware-secure-latest.json';

	public function __construct() {
		add_action('admin_init', array($this, 'adminInit'));
		add_action('admin_menu', array($this, 'adminMenu'));
		add_action('plugin_action_links_' . WEBAWARE_SECURE_NAME, array($this, 'pluginActionLinks'));

		// check for plugin updates
		add_filter('pre_set_site_transient_update_plugins', array($this, 'checkPluginUpdates'));
		add_filter('plugins_api', array($this, 'getPluginInfo'), 10, 3);
		add_action('plugins_loaded', array($this, 'clearPluginInfo'));

		// on multisite, must add new version notification ourselves...
		if (is_multisite() && !is_network_admin()) {
			add_action('after_plugin_row_' . WEBAWARE_SECURE_NAME, array($this, 'showUpdateNotification'), 10, 2);
		}
	}

	/**
	* admin_init action
	*/
	public function adminInit() {
		add_settings_section(WEBAWARE_SECURE_OPTIONS, false, false, WEBAWARE_SECURE_OPTIONS);
		register_setting(WEBAWARE_SECURE_OPTIONS, WEBAWARE_SECURE_OPTIONS, array($this, 'settingsValidate'));

		if (!empty($_REQUEST['webaware_secure_changelog']) && !empty($_REQUEST['plugin']) && !empty($_REQUEST['slug'])) {
			$this->showChangelog();
		}
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
	* @param array $input
	* @return array
	*/
	public function settingsValidate($input) {
		$output = array();

		$output['disable_xmlrpc']		= empty($input['disable_xmlrpc']) ? 0 : 1;
		$output['disable_pingback']		= empty($input['disable_pingback']) ? 0 : 1;
		$output['disable_rsd']			= empty($input['disable_rsd']) ? 0 : 1;
		$output['disable_wlwmanifest']	= empty($input['disable_wlwmanifest']) ? 0 : 1;
		$output['auto_update_plugin']	= empty($input['auto_update_plugin']) ? 0 : 1;
		$output['auto_update_theme']	= empty($input['auto_update_theme']) ? 0 : 1;
		$output['login_slug']			= empty($input['login_slug']) ? '' : trim(trim($input['login_slug']), '/');
		$output['apache_version']		= empty($input['apache_version']) ? '' : trim($input['apache_version']);

		return $output;
	}

	/**
	* check for plugin updates, every so often
	* @param object $plugins
	* @return object
	*/
	public function checkPluginUpdates($plugins) {
		if (empty($plugins->last_checked)) {
			return $plugins;
		}

		$current = $this->getPluginData();
		$latest = $this->getLatestVersionInfo();

		if ($latest && version_compare($current['Version'], $latest->version, '<')) {
			$update = new stdClass;
			$update->id				= '0';
			$update->url			= $latest->homepage;
			$update->slug			= $latest->slug;
			$update->new_version	= $latest->version;
			$update->package		= $latest->download_link;

			$plugins->response[WEBAWARE_SECURE_NAME] = $update;
		}

		return $plugins;
	}

	/**
	* return plugin info for update pages, plugins list
	* @param boolean $false
	* @param array $action
	* @param object $args
	* @return bool|object
	*/
	public function getPluginInfo($false, $action, $args) {
		if (isset($args->slug) && $args->slug === basename(WEBAWARE_SECURE_NAME, '.php')) {
			return $this->getLatestVersionInfo();
		}

		return $false;
	}

	/**
	* if user asks to force an update check, clear our cached plugin info
	*/
	public function clearPluginInfo() {
		global $pagenow;

		if (!empty($_GET['force-check']) && !empty($pagenow) && $pagenow == 'update-core.php') {
			delete_site_transient(self::TRANSIENT_UPDATE_INFO);
		}
	}

	/**
	* show update nofication row -- needed for multisite subsites, because WP won't tell you otherwise!
	* @param string $file
	* @param array $plugin
	*/
	public function showUpdateNotification($file, $plugin) {
		if (!current_user_can('update_plugins')) {
			return;
		}

		$update_cache = get_site_transient('update_plugins');
		if (!is_object($update_cache)) {
			// refresh update info
			wp_update_plugins();
		}

		$current = $this->getPluginData();
		$info = $this->getLatestVersionInfo();

		if ($info && version_compare($current['Version'], $info->new_version, '<')) {
			// build a plugin list row, with update notification
			$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
			echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';

			$changelog_link = self_admin_url("index.php?webaware_secure_changelog=1&plugin={$info->slug}&slug={$info->slug}&TB_iframe=true");

			printf(
				__('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s">update now</a>.'),
				esc_html($info->name),
				esc_url($changelog_link),
				esc_html($info->name),
				esc_html($info->new_version),
				esc_url(wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=' . WEBAWARE_SECURE_NAME), 'upgrade-plugin_' . WEBAWARE_SECURE_NAME))
			);

			echo '</div></td></tr>';
		}
	}

	/**
	* get current plugin data (cached so that we only ask once, because it hits the file system)
	* @return array
	*/
	protected function getPluginData() {
		if (empty($this->pluginData)) {
			$this->pluginData = get_plugin_data(WEBAWARE_SECURE_FILE);
		}

		return $this->pluginData;
	}

	/**
	* get plugin version info from remote server
	* @param bool $cache set false to ignore the cache and fetch afresh
	* @return stdClass
	*/
	protected function getLatestVersionInfo($cache = true) {
		$info = false;
		if ($cache) {
			$info = get_site_transient(self::TRANSIENT_UPDATE_INFO);
		}

		if (empty($info)) {
			delete_site_transient(self::TRANSIENT_UPDATE_INFO);

			$url = add_query_arg(array('v' => time()), self::URL_UPDATE_INFO);
			$response = wp_remote_get($url, array('timeout' => 60));

			if (is_wp_error($response)) {
				return false;
			}

			if ($response && isset($response['body'])) {
				// load and decode JSON from response body
				$info = json_decode($response['body']);

				if ($info) {
					$sections = array();
					foreach ($info->sections as $name => $data) {
						$sections[$name] = $data;
					}
					$info->sections = $sections;

					set_site_transient(self::TRANSIENT_UPDATE_INFO, $info, HOUR_IN_SECONDS * 6);
				}
			}
		}

		return $info;
	}

	/**
	* pop-up the plugin changelog
	*/
	public function showChangelog() {
		if (!current_user_can('update_plugins')) {
			wp_die(__('You do not have sufficient permissions to update plugins for this site.'), __('Error'), array('response' => 403));
		}

		global $tab, $body_id;
		$body_id = $tab = 'plugin-information';
		$_REQUEST['section'] = 'changelog';

		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		wp_enqueue_style('plugin-install');
		wp_enqueue_script('plugin-install');
		set_current_screen();
		install_plugin_information();

		exit;
	}

}
