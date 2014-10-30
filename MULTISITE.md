1. Add WP_ALLOW_MULTISITE to config/application.php
2. Start the multisite install process in the admin normally.
3. Copy the generated .htaccess values into your htaccess files
4. Add the required multisite constants to development.php, staging.php or production.php, taking care to remove references to subdirectories. At least DOMAIN_CURRENT_SITE should probably be defined as an environment variable in your .env file.
5. IMPORTANT: Check the home and siteurl values in wp_options and wp_sitemeta, removing any reference to subdirectories (yoursite.com/wp should be changed to yoursite.com). This is because the .htaccess rules take care of hiding the /wp in URL's. Make sure there are no trailing slashes, otherwise stuff will break.
6. Read through point 5 again and verify you did it properly.
7. Login to your site at yoursite.com/wp-login.php (NOT yoursite.com/wp/wp-login.php)
8. Remove the silly '/blog' from your permalink structure and save.
