# hide wp-login.php and replace with custom slug - Apache < 2.4
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{ENV:REDIRECT_STATUS} 200
RewriteRule . - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^<?php echo $options['login_slug']; ?>/?$ wp-login.php [QSA,L]
RewriteCond %{REQUEST_URI} /wp-login\.php
RewriteRule . - [F]
</IfModule>
