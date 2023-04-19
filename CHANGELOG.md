# Changelog

### 1.11.2

- Gracefully handle abandoned cart missing product [[#138](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/138)]

### 1.11.1

- Handle missing customer data to reduce logged warnings and notices [[#135](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/135)]

### 1.11.0

- Reset subscriber's opt-outed status on checkout newsletter subscribe [[#130](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/130)]

### 1.10.0

- Rework checkout newsletter sign up checkbox rendering [[#126](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/126)]

### 1.9.2

- Generating RSS feed URL takes account permalinks enabled state [[#122](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/122)]
- RSS feed template is rendered at a later stage to ensure RSS is rendered [[#122](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/122)]
- Use common practice on formatting User-Agent string [[#123](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/123)]

### 1.9.1

- Update user manual links. [[#116](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/116)]

### 1.9.0

- Test compatibility with WordPress 5.8. [[#112](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/112)]

### 1.8.1

- Bugfix - improve button color and button color text responsive design. [[#107](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/107)]

### 1.8.0

- Feature - user can customize the appearance of the subscription form. [[#97](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/97)], [[#92](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/92)], [[#85](https://github.com/sendsmaily/smaily-woocommerce-plugin/issues/85)]

### 1.7.2

- Rework admin settings form [[#94](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/94)]

### 1.7.1

- Test compatibility with WordPress 5.7. [issue #88](https://github.com/sendsmaily/smaily-woocommerce-plugin/issues/88)

### 1.7.0

- Test compatibility with WordPress 5.6. [issue #70](https://github.com/sendsmaily/smaily-woocommerce-plugin/issues/70)
- Smaily plugin settings "General" tab refinement. [issue #72](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/72)
- Improve plugin description to better describe the plugin's features. [issue #75](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/75)
- Dequeue 3rd party styles in module settings page. [issue #77](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/77)
- Update "required at least" WordPress version to 4.5 in README.md. [issue #78](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/78)
- Fix API credentials validation messages. [issue #79](https://github.com/sendsmaily/smaily-woocommerce-plugin/pull/79)

### 1.6.1

- Improve naming of widget so it's more distinguishable. [issue #64](https://github.com/sendsmaily/smaily-woocommerce-plugin/issues/64)
- Bugfix - validate credentials fails if password contains '&' character. [issue #62](https://github.com/sendsmaily/smaily-woocommerce-plugin/issues/62)

### 1.6.0

- Feature - generate RSS URL from form options.
- Feature - add options to order products in RSS feed.

### 1.5.0

- Feature - add more options to manipulate opt-in checkbox on checkout page.

### 1.4.2

- Bugfix - provide translations for widget responses.

### 1.4.1

- Admin page now shows error message in case of deleting API credentials in Smaily

### 1.4.0

- Standardize Abandoned Cart email template parameters across integrations
- Removed `product_description_short`, `cart_url` and `product_subtotal` parameters
- Added `product_base_price` and `product_price` parameters

### 1.3.5

- Add `Site title` field to available synchronize additional fields
- Store URL field is now always sent with subscriber data

### 1.3.4

- Compatibility with Wordpress 5.3 and WooCommerce 3.8.0

### 1.3.3

- Support for PHP 5.6

### 1.3.2

- Add Estonian language support.

### 1.3.1

- Fixes plugin styles affecting admin panel styles (#22)

### 1.3.0

- Optimization and bug removal due to new automation workflows.

### 1.2.3

- Admin panel changed for better customer experience.

### 1.2.2

- Bugfix. RSS-feed shows correct discount price and precentage.
- Bugfix. RSS-feed link works even when not refreshing permalinks.

### 1.2.1

- Bugfix. RSS-feed now displays special characters.

### 1.2.0

- New feature. RSS-feed now supports category and limit parameters from URL.

### 1.1.0

- New feature. Abandoned Cart remainder emails.
- Bugfix. Displaying RSS-feed price.

### 1.0.3

- Settings form credentials are being validated automatically when allready in database

### 1.0.2

- Fixed a bug where custom fields are not showing in checkout page

### 1.0.1

- Folder structure changed for CSS and JS

### 1.0.0

- This is the first public release.
