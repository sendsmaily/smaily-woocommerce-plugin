=== Smaily for WooCommerce ===
Contributors: sendsmaily, kaarel, tomabel, marispulk
Tags: woocommerce, smaily, newsletter, email
Requires PHP: 5.6
Requires at least: 4.5
Tested up to: 5.8
WC tested up to: 4.7.0
Stable tag: 1.11.2
License: GPLv3

Simple and flexible Smaily newsletter and RSS-feed integration for WooCommerce.

== Description ==

Smaily email marketing and automation plugin for WooCommerce.

Automatically synchronize customers to Smaily, easily import products into templates using RSS-feed and collect newsletter subscribers using Newsletter Subscription widget.

= Features =

**WooCommerce Newsletter Subscribers**

- Add subscribers to Smaily after updating user settings
- Add option to subscribe when registering new user
- Add option to subscribe in checkout area
- Add option to subscribe customer in admin panel

**WooCommerce Products RSS-feed**

- Generate RSS-feed with products for easy import to Smaily template
- Option to customize generated RSS-feed based on products categories
- Option to limit generated RSS-feed products amount with prefered value
- Option to order generated RSS-feed products by several categories

**Subscription Widget**

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
- Send abandoned cart information to Smaily for easy use on templates
- Set delay time when cart is considered abadoned

= Requirements =

Smaily for WooCommerce requires PHP 5.6+ (PHP 7.0+ recommended). You'll also need to be running WordPress 4.5+ and have WooCommerce 2.2+.

= Documentation & Support =

Online documentation and code samples are available via our [Help Center](https://smaily.com/help/user-manuals/).

= Contribute =

All development for Smaily for WooCommerce is [handled via GitHub](https://github.com/sendsmaily/smaily-woocommerce-plugin). Opening new issues and submitting pull requests are welcome.

== Installation ==

1. Upload or extract the `woocommerce-smaily-newsletter` folder to your site's `/wp-content/plugins/` directory. You can also use the *Add new- option found in the *Plugins- menu in WordPress.
2. Activate the plugin from the *Plugins- menu in WordPress.

= Usage =

1. Open Smaily settings from admin menu sidepanel.
2. Insert your Smaily API authentication information to get started.
3. Next, click validate API information.
4. Select if you want to use cron for contact synchronization between WooCommerce and Smaily
5. Select if you want to use cron for abandoned cart remainder emails.
6. Select autoresponder for abandoned cart, fields to zynchronize and delay settings.
7. Click Save Changes
8. If you want to use Smaily Widget please fill out settings page before using.
9. Cron is set up to synchronize contacts daily. To view and manage cron settings use Cron plugins for example "WP Crontrol".
10. That's it, your WooCommerce store is now integrated with Smaily Plugin!

== Frequently Asked Questions ==

= How to set up widget for signup form? =

1. Validate your smaily credentials in Smaily settings menu.
2. Move to Appearance -> Widget menu from admin page sidepanel.
3. Add Smaily for WooCommerce Form to your prefered location on page.
4. Select Title for your subscribe newsletter form.
5. Optional autoresponder:

If you have added Form Submitted automation trigger from Smaily site under Automation tab you can see all availabe autoresponders in your widget settings.

There is no need to select autoresponder for widget form, but if you want to customize different approach from opt-in automation trigger you can do that.
When no autoresponder selected regular opt-in workflow will run. You can add delay, filter by field and send email after subscription. For that edit settings in Smaily automation page.
6. Optional layout design modifications:

Change layout, email field text, name field text, button text, button color and button text color.

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

Up to 10 products can be received in Smaily templating engine. You can reference each product with number 1-10 behind parameter name.

- Product name: `{{ product_name_[1-10] }}`.

- Product description: `{{ product_description_[1-10] }}`.

- Product SKU: `{{ product_sku_[1-10] }}`.

- Product quantity: `{{ product_quantity_[1-10] }}`.

- Products price: `{{ product_price_[1-10] }}`.

- Product base price : `{{ product_base_price_[1-10] }}`.

Also you can determine if customer had more than 10 items in cart

- More than 10 items: `{{ over_10_products }}`.


== Screenshots ==

1. WooCommerce Smaily general settings screen.
2. WooCommerce Smaily customer synchronization settings screen.
3. WooCommerce Smaily abadoned cart settings screen.
4. Woocommerce Smaily checkout opt-in screen.
5. WooCommerce Smaily RSS screen.
6. WooCommerce Smaily RSS-feed screen.
7. WooCommerce Smaily in widgets view.
8. WooCommerce Smaily in widget view when clicked open.
9. WooCommerce Smaily widget front screen.

== Changelog ==

= 1.11.2 =

- Gracefully handle abandoned cart missing product

= 1.11.1 =

- Handle missing customer data to reduce logged warnings and notices

= 1.11.0 =

- Reset subscriber's opt-outed status on checkout newsletter subscribe

= 1.10.0 =

- Rework checkout newsletter sign up checkbox rendering

= 1.9.2 =

- Generating RSS feed URL takes account permalinks enabled state.
- RSS feed template is rendered at a later stage to ensure RSS is rendered.
- Use common practice on formatting User-Agent string.

= 1.9.1 =

- Update user manual links.

= 1.9.0 =

- Test compatibility with WordPress 5.8.

= 1.8.1 =

- Bugfix - improve widget responsive design.

= 1.8.0 =

- Feature - user can customize the appearance of the subscription form.

= 1.7.2 =

- Rework admin settings form.

= 1.7.1 =

- Test compatibility with WordPress 5.7.

= 1.7.0 =

- Test compatibility with WordPress 5.6.
- Smaily plugin settings "General" tab refinement.
- Improve plugin description to better describe the plugin's features.
- Dequeue 3rd party styles in module settings page.
- Update "required at least" WordPress version to 4.5 in README.md.
- Fix API credentials validation messages.

= 1.6.1 =
- Improve naming of widget so it's more distinguishable.
- Bugfix - validate credentials fails if password contains '&' character.

= 1.6.0 =

- Feature - generate RSS URL from form options.
- Feature - add options to order products in RSS feed.

= 1.5.0 =

- Feature - add more options to manipulate opt-in checkbox on checkout page.

= 1.4.2 =

- Bugfix - provide translations for widget responses.

= 1.4.1 =
- Admin page now shows error message in case of deleting API credentials in Smaily

= 1.4.0 =
- Standardize Abandoned Cart email template parameters across integrations
- Removed `product_description_short`, `cart_url` and `product_subtotal` parameters
- Added `product_base_price` and `product_price` parameters

= 1.3.5 =
- Add `Site title` field to available synchronize additional fields
- Store URL field is now always sent with subscriber data

= 1.3.4 =
- Compatibility with Wordpress 5.3 and WooCommerce 3.8.0

= 1.3.3 =
- Support for PHP 5.6

= 1.3.2 =
- Add Estonian language support.

= 1.3.1 =
- Fixes plugin styles affecting admin panel styles (#22)

= 1.3.0 =
- Optimization and bug removal due to new automation workflows.

= 1.2.3 =
- Admin panel changed for better customer experience.

= 1.2.2 =
- Bugfix. RSS-feed shows correct discount price and precentage.
- Bugfix. RSS-feed link works even when not refreshing permalinks.

= 1.2.1 =
- Bugfix. RSS-feed now displays special characters.

= 1.2.0 =
- New feature. RSS-feed now supports category and limit parameters from URL.

= 1.1.0 =
- New feature. Abandoned Cart remainder emails.
- Bugfix. Displaying RSS-feed price.

= 1.0.3 =
- Settings form credentials are being validated automatically when allready in database

= 1.0.2 =
- Fixed a bug where custom fields are not showing in checkout page.

= 1.0.1 =
- Folder structure changed for CSS and JS

= 1.0.0 =
- This is the first public release.

== Upgrade Notice ==

= 1.4.0 =
This update will change abandoned cart exported fields. Please check your settings after update!
