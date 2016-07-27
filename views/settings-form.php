<?php
// settings form
?>

<style>
#login_slug_htaccess {
	width: 90%;
	font-family: Consolas, Monaco, monospace;
}
</style>

<div class="wrap">
	<h2>Simple security settings</h2>

	<p>some simple security measures without all the performance traps</p>

	<form action="<?php echo admin_url('options.php'); ?>" method="POST">
		<?php settings_fields(WEBAWARE_SECURE_OPTIONS); ?>

		<table class="form-table">

			<tr valign="top">
				<th scope="row">Disable XML-RPC</th>
				<td>
					<input type="checkbox" name="webaware_secure[disable_xmlrpc]" id="webaware_secure_disable_xmlrpc" value="1" <?php checked($options['disable_xmlrpc']); ?> />
					<label for="webaware_secure_disable_xmlrpc">not required on most websites and is a security risk; required for the WordPress mobile app</label>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">Disable X-Pingback headers</th>
				<td>
					<input type="checkbox" name="webaware_secure[disable_pingback]" id="webaware_secure_disable_pingback" value="1" <?php checked($options['disable_pingback']); ?> />
					<label for="webaware_secure_disable_pingback">pingbacks use the XML-RPC protocol, and are abused by spammers</label>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">Disable RSD link</th>
				<td>
					<input type="checkbox" name="webaware_secure[disable_rsd]" id="webaware_secure_disable_rsd" value="1" <?php checked($options['disable_rsd']); ?> />
					<label for="webaware_secure_disable_rsd">not required on most websites</label>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">Disable wlwmanifest link</th>
				<td>
					<input type="checkbox" name="webaware_secure[disable_wlwmanifest]" id="webaware_secure_disable_wlwmanifest" value="1" <?php checked($options['disable_wlwmanifest']); ?> />
					<label for="webaware_secure_disable_wlwmanifest">not required on most websites</label>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">Allow plugin auto-update</th>
				<td>
					<input type="checkbox" name="webaware_secure[auto_update_plugin]" id="webaware_secure_auto_update_plugin" value="1" <?php checked($options['auto_update_plugin']); ?> />
					<label for="webaware_secure_auto_update_plugin">sometimes WordPress pushes out plugin updates for security reasons</label>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">Allow theme auto-update</th>
				<td>
					<input type="checkbox" name="webaware_secure[auto_update_theme]" id="webaware_secure_auto_update_theme" value="1" <?php checked($options['auto_update_theme']); ?> />
					<label for="webaware_secure_auto_update_theme">sometimes WordPress pushes out theme updates for security reasons</label>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">Change login location</th>
				<td>
					<input type="text" class="regular-text" name="webaware_secure[login_slug]" id="webaware_secure_login_slug" value="<?php echo esc_attr($options['login_slug']); ?>" />
					<br/><em>enter the new login page slug, e.g. 'login-page'</em>
					<?php if (!empty($login_htaccess)): ?>
					<p>Please copy this code to your .htaccess file:</p>
					<textarea id="login_slug_htaccess" readonly="readonly" cols="40" rows="10"><?php echo $login_htaccess; ?></textarea>
					<?php endif; ?>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">Apache version</th>
				<td>
					<input type="checkbox" name="webaware_secure[apache_version]" id="webaware_secure_apache_version" value="2.4" <?php checked($options['apache_version'], '2.4'); ?> />
					<label for="webaware_secure_apache_version">Apache 2.4 or higher (changes the .htaccess rules for login location)</label>
				</td>
			</tr>

		</table>

		<?php submit_button(); ?>
	</form>
</div>
