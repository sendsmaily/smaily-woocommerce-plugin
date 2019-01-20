=== Smaily for WooCommerce ===
Contributors: sendsmaily, kaarel
Tags: woocommerce, smaily, newsletter, email
Requires PHP: 5.6
Requires at least: 4.0
Tested up to: 5.0.2
WC tested up to: 3.5.3
Stable tag: 1.1.0
License: GPLv3

Simple and flexible Smaily newsletter and rss-feed integration for WooCommerce.

== Description ==

Smaily email marketing and automation extension plugin for WooCommerce.

Automatically subscribe newsletter subscribers to a Smaily subscribers list, generate rss-feed based on products for easy template import and add Newsletter Subscribtion widget for opt-in sign-up form.

= Features =

**WooCommerce Newsletter Subscribers**

- Add subscribers to Smaily after updating user settings
- Add option to subscribe when registering new user
- Add option to subscribe in checkout area
- Add option to subscribe customer in admin panel

**WooCommerce Products RSS-feed**

- Generate RSS-feed with 50 latest products for easy import to Smaily template

**Subscribtion Widget**

- Smaily subscriber sign up form with built in captcha
- Easy to use form
- No user authentication need for widget usage

**Two-way synchronization between Smaily and WooCommerce**

- Get unsubscribers from Smaily unsubscribed list
- Update unsubscribed status in WooCommerce users database
- Collect new user data for subscribed users
- Generate data log for each update
- Default daily synchronization

**Abandoned cart reminder emails**

- Automatically notify customers about their abandoned cart
- Send abandoned cart information to smaily for easy use on templates
- Set delay time when cart is considered abadoned

= Requirements =

Smaily for WooCommerce requires PHP 5.6+ (PHP 7.0+ recommended). You'll also need to be running WordPress 4.0+ and have WooCommerce 2.2+.

= Documentation & Support =

Online documentation and code samples are available via our [Help Center](http://help.smaily.com/en/support/home).

= Contribute =

All development for Smaily for WooCommerce is [handled via GitHub](https://github.com/sendsmaily/smaily-woocommerce-plugin). Opening new issues and submitting pull requests are welcome.

== Installation ==

1. Upload or extract the `woocommerce-smaily-newsletter` folder to your site's `/wp-content/plugins/` directory. You can also use the *Add new- option found in the *Plugins- menu in WordPress.
2. Activate the plugin from the *Plugins- menu in WordPress.

= Usage =

1. Go to WooCommerce -> Smaily email marketing and automation
2. Insert your Smaily API authentication information to get started.
3. Select if you want to use Cron for contact synchronization between WooCommerce and Smaily
4. Next, click validate API information.
5. Select your autoresponder and fields you want to synchronize
6. Click Save Changes
7. If you want to use Smaily Widget please fill out settings page before using.
8. Cron is set up to synchronize contacts daily. To view and manage cron settings use Cron plugins for example "WP Crontrol".
9. That's it, your WooCommerce store is now integrated with Smaily Plugin!

== Frequently Asked Questions == 

= Why RSS-feed is displaying "page not found"? =

Try re-saving permalinks.
Go to admin panel -> Settings -> Permalinks.
Scroll to bottom and click "Save Changes" without modifing anything

= Where I can find data-log for Cron? =

Cron update data-log is stored in the root folder of Smaily plugin, inside "smaily-cron.txt" file.

= How can I access additional Abandoned cart parameters in Smaily template editor? =

List of all parameters available in Smaily email templating engine:

- Customer first name: `{{ first_name }}`.

- Customer last name: `{{ last_name }}`.

- Cart page url: `{{ cart_url }}`.

Up to 10 products can be received in Smaily templating engine. You can refrence each product with number 1-10 behind parameter name.

- Product name: `{{ product_name_[1-10] }}`.

- Product description: `{{ product_description_[1-10] }}`.

- Product short description: `{{product_description_short_[1-10] }}`.

- Product SKU: `{{ product_sku_[1-10] }}`.

- Product quantity: `{{ product_quantity_[1-10] }}`.

- Products row price subtotal: `{{ product_subtotal_[1-10] }}`.


== Screenshots ==

1. WooCommerce Smaily general settings screen.
2. WooCommerce Smaily abadoned cart settings screen.
3. WooCommerce Smaily RSS-feed screen.
4. WooCommerce Smaily widget settings screen.
5. WooCommerce Smaily widget front screen.

== Changelog ==

### 1.1.0

- New feature. Abandoned Cart remainder emails.
- Bugfix. Displaying rss-feed price.

### 1.0.3

- Settings form credentials are being validated automatically when allready in database

### 1.0.2

- Fixed a bug where custom fields are not showing in checkout page.

### 1.0.1

- Folder structure changed for CSS and JS

### 1.0.0

- This is the first public release.