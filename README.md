# Smaily for WooCommerce

Simple and flexible Smaily newsletter and RSS-feed integration for WooCommerce.

## Description

Smaily email marketing and automation plugin for WooCommerce.

Automatically synchronize customers to Smaily, easily import products into templates using RSS-feed and collect newsletter subscribers using Newsletter Subscription widget.

## Features

### WooCommerce Newsletter Subscribers

- Add subscribers to Smaily after updating user settings
- Add option to subscribe when registering new user
- Add option to subscribe in checkout area
- Add option to subscribe customer in admin panel

### WooCommerce Products RSS-feed

- Generate RSS-feed with products for easy import to Smaily template
- Option to customize generated RSS-feed based on products categories
- Option to limit generated RSS-feed products amount with prefered value
- Option to order generated RSS-feed products by several categories

### Subscription Widget

- Smaily subscriber sign up form with built in captcha
- Easy to use form
- No user authentication need for widget usage

### Two-way synchronization between Smaily and WooCommerce

- Get unsubscribers from Smaily unsubscribed list
- Update unsubscribed status in WooCommerce users database
- Collect new user data for subscribed users
- Generate data log for each update
- Default daily synchronization

### Abandoned cart reminder emails

- Automatically notify customers about their abandoned cart
- Send abandoned cart information to smaily for easy use on templates
- Set delay time when cart is considered abadoned

## Requirements

Smaily for WooCommerce requires PHP 5.6+ (PHP 7.0+ recommended). You'll also need to be running WordPress 4.5+ and have WooCommerce 2.2+.

## Documentation & Support

Online documentation and code samples are available via our [Help Center](https://smaily.com/help/user-manuals/).

## Contribute

All development for Smaily for WooCommerce is [handled via GitHub](https://github.com/sendsmaily/smaily-woocommerce-plugin). Opening new issues and submitting pull requests are welcome.

## Installation

1. Upload or extract the `smaily-for-woocommerce` folder to your site's `/wp-content/plugins/` directory. You can also use the **Add new** - option found in the **Plugins** - menu in WordPress.
2. Activate the plugin from the **Plugins** - menu in WordPress.

## Usage

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

## Frequently Asked Questions

## How to set up widget for signup form?

1. Validate your smaily credentials in Smaily settings menu.
2. Move to **Appearance -> Widget** menu from admin page sidepanel.
3. Add Smaily for WooCommerce Form to your prefered location on page.
4. If you want you can select Title for your subscribe newsletter form.
5. Optional autoresponder:

If you have added Form Submitted automation triggers from Smaily site under Automation tab you can see all availabe autoresponders in your widget settings.

There is no need to select autoresponder for widget form, but if you want to customize different approach from opt-in automation trigger you can do that.

When no autoresponder selected regular opt-in workflow will run. You can add delay, filter by field and send email after subscription. For that edit settings in Smaily automation page.
6. Optional layout design modifications:

Change layout, email field text, name field text, button text, button color and button text color.

### Why RSS-feed is displaying "page not found"?

Try re-saving permalinks.
Go to Admin panel -> Settings -> Permalinks.
Scroll to bottom and click "Save Changes" without modifing anything

### Where I can find data-log for Cron?

Cron update data-log is stored in the root folder of Smaily plugin, inside "smaily-cron.txt" file.

### How can I access additional Abandoned cart parameters in Smaily template editor?

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

## Screenshots found in /assets

1. WooCommerce Smaily general settings screen.
2. WooCommerce Smaily customer synchronization settings screen.
3. WooCommerce Smaily abadoned cart settings screen.
4. Woocommerce Smaily checkout opt-in screen.
5. WooCommerce Smaily RSS screen.
6. WooCommerce Smaily RSS-feed screen.
7. WooCommerce Smaily widget settings screen.
8. WooCommerce Smaily widget front screen.
