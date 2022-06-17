<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

use Smaily_Inc\Api\Api;

/**
 * Handles communication between WordPress database
 */
class DataHandler {

	/**
	 * Get Smaily plugin settings from database
	 *
	 * @return array Results from database
	 */
	public static function get_smaily_results() {
		global $wpdb;
		// Stop if no table exists. Required during activation hook.
		$table_name = $wpdb->prefix . 'smaily';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
			return;
		}
		$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}smaily", 'ARRAY_A' );
		// If database is empty in beginning return.
		if ( count( $result ) === 0 ) {
			return;
		}
		$un_escaped_result = $result[0];

		// Escape db values for output and usage.
		$result = [];
		foreach ( $un_escaped_result as $key => $value ) {
			// Smaily may include & in generated password. Don't convert & to HTML entity as it is used for Base64 authorization.
			if ($key === 'password') {
				$result[ $key ] = $value;
				continue;
			}
			$result[ $key ] = esc_html( $value );
		}

		$un_escaped_syncronize_additional = ! empty( $result['syncronize_additional'] ) ? explode( ',', $result['syncronize_additional'] ) : array();

		// Escape syncronize_additional fields.
		$syncronize_additional = [];
		foreach ( $un_escaped_syncronize_additional as $key => $value ) {
			$syncronize_additional[ $key ] = esc_html( $value );
		}

		$un_escaped_cart_options = ! empty( $result['cart_options'] ) ? explode( ',', $result['cart_options'] ) : array();

		// Escape cart option values.
		$cart_options = [];
		foreach ( $un_escaped_cart_options as $key => $value ) {
			$cart_options [ $key ] = esc_html( $value );
		}
		return compact( 'result', 'syncronize_additional', 'cart_options' );
	}

	/**
	 * Generates RSS-feed based on products in WooCommerce store.
	 *
	 * @param string  $category Filter by products category.
	 * @param integer $limit Default value 50.
	 * @return void $rss Rss-feed for Smaily template.
	 */
	public static function generate_rss_feed( $category, $limit, $order_by, $order ) {

		$products       = self::get_products( $category, $limit, $order_by, $order );
		$base_url       = get_site_url();
		$currencysymbol = get_woocommerce_currency_symbol();
		$items          = [];
		foreach ( $products as $prod ) {
			if ( function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( $prod->get_id() );
			} else {
				$product = new \WC_Product( $prod->get_id() );
			}

			$price = floatval( $product->get_price() );
			$price = number_format( floatval( $price ), 2, '.', ',' ) . html_entity_decode( $currencysymbol );

			$discount = 0;
			// Get product price when on sale.
			if ( $product->is_on_sale() ) {
				// Regular price.
				$regular_price = (float) $product->get_regular_price();
				if ( $regular_price > 0 ) {
					// Active price (the "Sale price" when on-sale).
					$sale_price   = (float) $product->get_price();
					$saving_price = $regular_price - $sale_price;
					// Discount precentage.
					$discount     = round( 100 - ( $sale_price / $regular_price * 100 ), 2 );
				}
				// Format price and add currency symbol.
				$regular_price = number_format( floatval( $regular_price ), 2, '.', ',' ) . html_entity_decode( $currencysymbol );
			}

			$url   = get_permalink( $prod->get_id() );
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $prod->get_id() ), 'single-post-thumbnail' );

			$image        = $image[0];
			$create_time  = strtotime( $prod->get_date_created() );
			$price_fields = '';
			if ( $discount > 0 ) {
				$price_fields = '
			  <smly:old_price>' . esc_attr( $regular_price ) . '</smly:old_price>
			  <smly:discount>-' . esc_attr( $discount ) . '%</smly:discount>';
			}
			// Parse image to form element.
			$description = do_shortcode( $prod->get_description() );

			$items[] = '<item>
			  <title><![CDATA[' . $prod->get_title() . ']]></title>
			  <link>' . esc_url( $url ) . '</link>
			  <guid isPermaLink="True">' . esc_url( $url ) . '</guid>
			  <pubDate>' . date( 'D, d M Y H:i:s', $create_time ) . '</pubDate>
			  <description><![CDATA[' . $description . ']]></description>
			  <enclosure url="' . esc_url( $image ) . '" />
			  <smly:price>' . esc_attr( $price ) . '</smly:price>' . $price_fields . '
			</item>
			';
		}
		$rss  = '<?xml version="1.0" encoding="utf-8"?><rss xmlns:smly="https://sendsmaily.net/schema/editor/rss.xsd" version="2.0"><channel><title>Store</title><link>' . esc_url( $base_url ) . '</link><description>Product Feed</description><lastBuildDate>' . date( 'D, d M Y H:i:s' ) . '</lastBuildDate>';
		$rss .= implode( ' ', $items );
		$rss .= '</channel></rss>';
		header( 'Content-Type: application/xml' );
		echo $rss; // All values escaped before.
	}

	/**
	 * Get published products from WooCommerce database.
	 *
	 * @param string  $category Limit products by category.
	 * @param integer $limit Maximum number of products fetched.
	 * @param string  $order_by Order products by this.
	 * @param string  $order Ascending/Descending.
	 * @return array $products WooCommerce products.
	 */
	public static function get_products( $category, $limit, $order_by, $order ) {
		// Initial query.
		$product = array(
			'status'  => 'publish',
			'limit'   => $limit,
			'orderby' => 'none',
			'order'   => 'DESC',
		);

		if ( ! empty( $order_by ) ) {
			$product['orderby'] = $order_by;
		}

		if ( ! empty( $order ) ) {
			$product['order'] = $order;
		}
		// Get category to limit results if set.
		if ( ! empty( $category ) ) {
			$product['category'] = array( $category );
		}
		$wprod = wc_get_products( $product );
		return $wprod;
	}

	/**
	 * Get WooCommerce user data from database
	 *
	 * @param int $user_id User ID.
	 * @return array Available user data.
	 */
	public static function get_user_data( $user_id ) {
		// Collect user data from database.
		$user_data = get_userdata( $user_id );
		$user_meta = get_user_meta( $user_id );

		// Get admin panel "Syncronize additional fields".
		$result                = self::get_smaily_results();
		$syncronize_additional = $result['syncronize_additional'];

		// Gather user information into variables if available.
		$email          = isset( $user_data->user_email ) ? $user_data->user_email : '';
		$birthday       = isset( $user_meta['user_dob'][0] ) ? $user_meta['user_dob'][0] : '';
		$customer_group = isset( $user_data->roles[0] ) ? $user_data->roles[0] : '';
		$firstname      = isset( $user_meta['first_name'][0] ) ? $user_meta['first_name'][0] : '';
		$gender         = isset( $user_meta['user_gender'][0] ) ? $user_meta['user_gender'][0] : '';
		// User friendly representation of gender.
		if ( $gender === '0' ) {
			$gender = 'Female';
		} elseif ( $gender === '1' ) {
			$gender = 'Male';
		}
		$lastname         = isset( $user_meta['last_name'][0] ) ? $user_meta['last_name'][0] : '';
		$nickname         = isset( $user_meta['nickname'][0] ) ? $user_meta['nickname'][0] : '';
		$first_registered = isset( $user_data->user_registered ) ? $user_data->user_registered : '';
		$phone            = isset( $user_meta['user_phone'][0] ) ? $user_meta['user_phone'][0] : '';
		$site_title       = get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : '';
		// All user data.
		$all_user_data = array(
			'email'            => $email,
			'customer_group'   => $customer_group,
			'customer_id'      => $user_id,
			'first_registered' => $first_registered,
			'first_name'       => $firstname,
			'last_name'        => $lastname,
			'nickname'         => $nickname,
			'user_dob'         => $birthday,
			'user_gender'      => $gender,
			'user_phone'       => $phone,
			'site_title'       => $site_title,
		);

		// Default values that are always synced.
		$user_sync_data = array(
			'email' => $email,
			'store' => get_site_url(),
		);
		// Sync also fields selected from admin panel.
		if ( ! empty( $syncronize_additional ) ) {
			foreach ( $syncronize_additional as $sync_option ) {
				$user_sync_data[ $sync_option ] = $all_user_data[ $sync_option ];
			}
		}

		return $user_sync_data;
	}

	/**
	 * Gets form_submitted autoresponders list from Smaily if user has validated API credentials.
	 *
	 * @return array
	 */
	public static function get_autoresponder_list() {
		$response = [];
		// Get settings from db.
		$settings = self::get_smaily_results();
		$result   = $settings['result'];
		// Get autoresponders if credentials available.
		if ( empty( $result['subdomain'] ) ||
			empty( $result['username'] ) ||
			empty( $result['password'] )
		) {
			return $response;
		}

		// Get Smaily autoresponders.
		$autoresponders = Api::ApiCall( 'workflows', '?trigger_type=form_submitted' );

		// Return empty array in case of error.
		if ( array_key_exists( 'error', $autoresponders ) ) {
			return $response;
		}

		// Return autoresponders list if available.
		if ( ! empty( $autoresponders ) ) {
			$autoresponders_list = [];
			foreach ( $autoresponders as $autoresponder ) {
				$element               = [];
				$element['id']         = $autoresponder['id'];
				$element['name']       = $autoresponder['title'];
				$autoresponders_list[] = $element;
			}
			$response = $autoresponders_list;
			// If no autoresponders created return empty.
		} else {
			$response = [ 'empty' => true ];
		}

		return $response;
	}

	/**
	 * Get all WooCommerce product categories.
	 *
	 * @return array Product categories
	 */
	public static function get_woocommerce_categories_list() {
		$cat_args = array(
			'orderby'    => 'name',
			'order'      => 'asc',
			'hide_empty' => false,
		);

		$product_categories = get_terms( 'product_cat', $cat_args );
		return $product_categories;
	}

	/**
	 * Get Product RSS Feed URL.
	 *
	 * @param string $rss_category Category slug.
	 * @param int $rss_limit Limit of products.
	 * @param string $rss_order_by Order products by.
	 * @param string $rss_order ASC/DESC order
	 * @return string
	 */
	public static function make_rss_feed_url( $rss_category = null, $rss_limit = null, $rss_order_by = null, $rss_order = null ) {
		global $wp_rewrite;

		$site_url   = get_site_url( null, 'smaily-rss-feed' );
		$parameters = array();

		if ( isset( $rss_category ) && $rss_category !== '' ) {
			$parameters['category'] = $rss_category;
		}
		if ( isset( $rss_limit ) ) {
			$parameters['limit'] = $rss_limit;
		}
		if ( isset( $rss_order_by ) && $rss_order_by !== 'none' ) {
			$parameters['order_by'] = $rss_order_by;
		}
		if ( isset( $rss_order ) && $rss_order_by !== 'none' && $rss_order_by !== 'rand' ) {
			$parameters['order'] = $rss_order;
		}

		// Handle URL when permalinks have not been enabled.
		if ( $wp_rewrite->using_permalinks() === false ) {
			$site_url                      = get_site_url();
			$parameters['smaily-rss-feed'] = 'true';
		}

		return add_query_arg( $parameters, $site_url );
	}

}
