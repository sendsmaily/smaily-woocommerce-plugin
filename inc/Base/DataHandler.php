<?php
/**
 * @package smaily_woocommerce_plugin
 */
namespace Inc\Base;

/**
 * Handles communication between WordPress database
 */
class DataHandler {

	public static function get_smaily_results() {
		global $wpdb;
		$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}smaily", 'ARRAY_A' );
		// If database is empty in beginning return
		if ( count( $result ) == 0 ) {
			return;
		}
		$result                = $result[0];
		$syncronize_additional = isset($result['syncronize_additional']) ? explode( ',', $result['syncronize_additional'] ) : null;
		return compact( 'result', 'syncronize_additional' );
	}

	public static function generateRssFeed( $limit = 50 ) {

		$products       = self::getLatestProducts( $limit );
		$baseUrl        = get_site_url();
		$currencysymbol = get_woocommerce_currency_symbol();
		$items          = [];
		foreach ( $products as $prod ) {
			if ( function_exists( 'get_product' ) ) {
				$product = wc_get_product( $prod->ID );
			} else {
				$product = new WC_Product( $prod->ID );
			}

			$price     = $product->get_regular_price();
			$splcPrice = $product->get_sale_price();
			$discount  = 0;
			if ( $splcPrice == 0 ) {
				$splcPrice = $price;
			}

			if ( $splcPrice < $price && $price > 0 ) {
				$discount = ceil( ( $price - $splcPrice ) / $price * 100 );
			}

			$price     = '$' . number_format( $price, 2, '.', ',' );
			$splcPrice = '$' . number_format( $splcPrice, 2, '.', ',' );

			$url   = get_permalink( $prod->ID );
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $prod->ID ), 'single-post-thumbnail' );

			$image        = $image[0];
			$createTime   = strtotime( $prod->post_date );
			$price_fields = '';
			if ( $discount > 0 ) {
				$price_fields = '
			  <smly:old_price>' . $price . '</smly:old_price>
			  <smly:discount>-' . $discount . '%</smly:discount>';
			}

			$items[] = '<item>
			  <title>' . $prod->post_title . '</title>
			  <link>' . $url . '</link>
			  <guid isPermaLink="True">' . $url . '</guid>
			  <pubDate>' . date( 'D, d M Y H:i:s', $createTime ) . '</pubDate>
			  <description>' . htmlentities( $prod->post_content ) . '</description>
			  <enclosure url="' . $image . '" />
			  <smly:price>' . $splcPrice . '</smly:price>' . $price_fields . '
			</item>
			';
		}
		 $rss = '<?xml version="1.0" encoding="utf-8"?><rss xmlns:smly="https://sendsmaily.net/schema/editor/rss.xsd" version="2.0"><channel><title>Store</title><link>' . $baseUrl . '</link><description>Product Feed</description><lastBuildDate>' . date( 'D, d M Y H:i:s' ) . '</lastBuildDate>
			';
		$rss .= implode( ' ', $items );
		$rss .= '</channel></rss>';
		header( 'Content-Type: application/xml' );
		echo $rss;
	}

	public static function getLatestProducts( $limit = 50 ) {
		global $wpdb;
		$products = $wpdb->get_results(
			"
            SELECT * FROM {$wpdb->prefix}posts
            WHERE post_type = 'product' AND post_status='publish'
            ORDER BY post_date DESC
            LIMIT $limit
        "
		);

		return $products;
	}

	/**
	 * Get WooCommerce user data from database
	 *
	 * @param   int $user_id    User ID
	 * @return  array               Available user data
	 */
	public static function getUserData( $user_id ) {
		// Collect user data from database
		$user_data = get_userdata( $user_id );
		$user_meta = get_user_meta( $user_id );

		// Get admin panel "Syncronize additional fields"
		$result                = self::get_smaily_results();
		$syncronize_additional = $result['syncronize_additional'];

		// Gather user information into variables if available
		$email          = isset( $user_data->user_email ) ? $user_data->user_email : '';
		$birthday       = isset( $user_meta['user_dob'][0] ) ? $user_meta['user_dob'][0] : '';
		$customer_group = isset( $user_data->roles[0] ) ? $user_data->roles[0] : '';
		$firstname      = isset( $user_meta['first_name'][0] ) ? $user_meta['first_name'][0] : '';
		$gender         = isset( $user_meta['user_gender'][0] ) ? $user_meta['user_gender'][0] : '';
		// User friendly representation of gender
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
		// All user data
		$all_user_data = array(
			'email'            => $email,
			'store'            => get_site_url(),
			'customer_group'   => $customer_group,
			'customer_id'      => $user_id,
			'first_registered' => $first_registered,
			'first_name'       => $firstname,
			'last_name'        => $lastname,
			'nickname'         => $nickname,
			'birthday'         => $birthday,
			'gender'           => $gender,
			'website'          => $website,
			'phone'            => $phone,
		);

		// Only sync fields selected from admin panel
		$admin_sync_fields = array( 'email' => $email );
		// If some fields selected add to return value
		if ( ! empty ( $syncronize_additional ) ) {
			foreach ( $syncronize_additional as $sync_option ) {
				$admin_sync_fields[ $sync_option ] = $all_user_data[ $sync_option ];
			}
		}
		
		return $admin_sync_fields;

	}

}
