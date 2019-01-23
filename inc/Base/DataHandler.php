<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

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
	 * Generates RSS-feed based on 50 last products in WooCommerce store
	 *
	 * @param integer $limit Default value 50.
	 * @return void $rss Rss-feed for Smaily template.
	 */
	public static function generate_rss_feed( $limit = 50 ) {

		$products       = self::get_latest_products( $limit );
		$base_url       = get_site_url();
		$currencysymbol = get_woocommerce_currency_symbol();
		$items          = [];
		foreach ( $products as $prod ) {
			if ( function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( $prod->ID );
			} else {
				$product = new \WC_Product( $prod->ID );
			}

			$price      = $product->get_regular_price();
			$splc_price = $product->get_sale_price();
			$discount   = 0;
			if ( $splc_price === 0 ) {
				$splc_price = $price;
			}

			if ( $splc_price < $price && $price > 0 ) {
				$discount = ceil( ( $price - $splc_price ) / $price * 100 );
			}

			$price      = number_format( floatval( $price ), 2, '.', ',' ) . html_entity_decode( $currencysymbol );
			$splc_price = number_format( floatval( $splc_price ), 2, '.', ',' ) . html_entity_decode( $currencysymbol );

			$url   = get_permalink( $prod->ID );
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $prod->ID ), 'single-post-thumbnail' );

			$image        = $image[0];
			$create_time  = strtotime( $prod->post_date );
			$price_fields = '';
			if ( $discount > 0 ) {
				$price_fields = '
			  <smly:old_price>' . esc_attr( $price ) . '</smly:old_price>
			  <smly:discount>-' . esc_attr( $discount ) . '%</smly:discount>';
			}

			$items[] = '<item>
			  <title>' . esc_attr( $prod->post_title ) . '</title>
			  <link>' . esc_url( $url ) . '</link>
			  <guid isPermaLink="True">' . esc_url( $url ) . '</guid>
			  <pubDate>' . date( 'D, d M Y H:i:s', $create_time ) . '</pubDate>
			  <description>' . htmlentities( $prod->post_content ) . '</description>
			  <enclosure url="' . esc_url( $image ) . '" />
			  <smly:price>' . esc_attr( $splc_price ) . '</smly:price>' . $price_fields . '
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
	 * Get latest published products from WooCommerce database.
	 * Syncs only fields selected from Settings->Additional fields.
	 *
	 * @param integer $limit Maximum number of products fetched.
	 * @return array $products WooCommerce products.
	 */
	public static function get_latest_products( $limit = 50 ) {
		global $wpdb;
		$products = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * FROM {$wpdb->prefix}posts
				WHERE post_type = 'product' AND post_status='publish'
				ORDER BY post_date DESC
				LIMIT %d
				",
				$limit
			)
		);

		return $products;
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
		$website          = isset( $user_data->user_url ) ? $user_data->user_url : '';
		$phone            = isset( $user_meta['user_phone'][0] ) ? $user_meta['user_phone'][0] : '';
		// All user data.
		$all_user_data = array(
			'email'            => $email,
			'store'            => get_site_url(),
			'customer_group'   => $customer_group,
			'customer_id'      => $user_id,
			'first_registered' => $first_registered,
			'first_name'       => $firstname,
			'last_name'        => $lastname,
			'nickname'         => $nickname,
			'user_dob'         => $birthday,
			'user_gender'      => $gender,
			'user_url'         => $website,
			'user_phone'       => $phone,
		);

		// Only sync fields selected from admin panel.
		$admin_sync_fields = array( 'email' => $email );
		// If some fields selected add to return value.
		if ( ! empty( $syncronize_additional ) ) {
			foreach ( $syncronize_additional as $sync_option ) {
				$admin_sync_fields[ $sync_option ] = $all_user_data[ $sync_option ];
			}
		}

		return $admin_sync_fields;

	}

}
