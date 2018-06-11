=== WooCommerce Smaily plugin ===
Contributors: smaily
Requires Wordpress at least: 4.0
Requires php at least: 5.6
Tested up to: 4.9.4
Stable tag: 4.0.2
Version 1.0.0
License: GPLv2 or later


Smaily email marketing and automation extension plugin for WooCommerce (set up opt-in form, client sync and output RSS-feed for easy product import into template).
 
== Description ==
 
Smaily email marketing and automation extension plugin for WooCommerce (set up opt-in form, client sync and output RSS-feed for easy product import into template).
 
== Installation ==
Requires Wordpress at least: 4.0
Requires php at least: 5.6

This section describes how to install the plugin and get it working.
1. Upload plugin in Plugins -> Add new.
   Note: Alternative to Upload Plugin :- Upload `Smaily` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure your smaily credentials in WooCommerce -> Smaily email marketing and  automation.
4. To add a subscribe form, use shortcode [smaily_newsletter] or you can use widget Smaily Newsletter in Appearence -> Widgets.
5. Configure your rss feed token and use [YOUR-DOMAIN]?token=[TOKEN].
6. To schedule automatic sync, set up CRON in your hosting and use URL [YOUR-DOMAIN]smaily-cron/."
7. Cron log will be created in your smaily plugin root directory.
