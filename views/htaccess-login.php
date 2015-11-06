# hide wp-login.php and replace with custom slug
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^<?php echo $options['login_slug']; ?>/?$ wp-login.php [QSA,END]
RewriteCond %{REQUEST_URI} /wp-login\.php
RewriteRule . - [F]
</IfModule>
