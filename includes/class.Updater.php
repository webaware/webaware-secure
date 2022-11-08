<?php
namespace webawareau\secure;

use stdClass;

if (!defined('ABSPATH')) {
	exit;
}

/**
* manage automatic updates and notifications
*/
class Updater {

	private string $name;			// plugin name in WordPress
	private string $file;			// plugin base file path
	private string $slug;			// slug for plugin
	private string $update_name;	// name of update transient
	private string $update_url;		// URL for update information
	private $plugin_data;			// cached plugin data from plugin file header

	public function __construct(string $name, string $file, string $slug, string $update_url) {
		$this->name			= $name;
		$this->file			= $file;
		$this->slug			= $slug;
		$this->update_name	= "{$this->slug}_update_info";
		$this->update_url	= $update_url;

		$this->maybeClearPluginInfo();

		// check for plugin updates
		add_filter('pre_set_site_transient_update_plugins', [$this, 'checkPluginUpdates']);
		add_filter('plugins_api', [$this, 'getPluginInfo'], 10, 3);
		add_action('admin_init', [$this, 'maybeShowChangelog']);

		// on multisite, must add new version notification ourselves...
		if (is_multisite() && !is_network_admin()) {
			add_action('after_plugin_row_' . $this->name, [$this, 'showUpdateNotification'], 10, 2);
		}
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
			$update->tested			= $latest->tested;
			$update->requires		= $latest->requires;
			$update->requires_php	= $latest->requires_php;
			$update->package		= $latest->download_link;
			$update->upgrade_notice	= $latest->upgrade_notice;

			$plugins->response[$this->name] = $update;
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
		if (isset($args->slug) && $args->slug === basename($this->name, '.php')) {
			return $this->getLatestVersionInfo();
		}

		return $false;
	}

	/**
	* if user asks to force an update check, clear our cached plugin info
	*/
	public function maybeClearPluginInfo() {
		global $pagenow;

		if (!empty($_GET['force-check']) && !empty($pagenow) && $pagenow === 'update-core.php') {
			delete_site_transient($this->update_name);
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
			$changelog_link = self_admin_url("index.php?{$this->slug}-changelog=1&plugin={$info->slug}&slug={$info->slug}&TB_iframe=true");

			// build a plugin list row, with update notification
			$wp_list_table = _get_list_table('WP_Plugins_List_Table');
			$plugin_name   = esc_html($info->name);
			$plugin_slug   = esc_html($info->slug);
			$new_version   = esc_html($info->new_version);

			$view   = empty($info->download_link) ? 'details' : 'upgrade';

			$root = dirname($this->file);
			include "{$root}/views/admin-plugin-update-{$view}.php";
		}
	}

	/**
	* get current plugin data (cached so that we only ask once, because it hits the file system)
	* @return array
	*/
	protected function getPluginData() {
		if (empty($this->plugin_data)) {
			$this->plugin_data = get_plugin_data($this->file);
		}

		return $this->plugin_data;
	}

	/**
	* get plugin version info from remote server
	* @param bool $cache set false to ignore the cache and fetch afresh
	* @return stdClass
	*/
	protected function getLatestVersionInfo($cache = true) {
		$info = false;
		if ($cache) {
			$info = get_site_transient($this->update_name);
		}

		if (empty($info)) {
			delete_site_transient($this->update_name);

			$url = add_query_arg(['v' => time()], $this->update_url);
			$response = wp_remote_get($url, ['timeout' => 10]);

			if (is_wp_error($response)) {
				return false;
			}

			if ($response && isset($response['body'])) {
				// load and decode JSON from response body
				$info = json_decode($response['body']);

				if ($info) {
					$sections = [];
					foreach ($info->sections as $name => $data) {
						$sections[$name] = $data;
					}
					$info->sections = $sections;

					set_site_transient($this->update_name, $info, HOUR_IN_SECONDS * 6);
				}
			}
		}

		return $info;
	}

	/**
	 * if the changelog was requested, show it
	 */
	public function maybeShowChangelog() {
		if (!empty($_REQUEST["{$this->slug}-changelog"]) && !empty($_REQUEST['plugin']) && !empty($_REQUEST['slug'])) {
			$this->showChangelog();
		}
	}

	/**
	* pop-up the plugin changelog
	*/
	public function showChangelog() {
		if (!current_user_can('update_plugins')) {
			wp_die(translate('You do not have sufficient permissions to update plugins for this site.'), translate('Error'), ['response' => 403]);
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
