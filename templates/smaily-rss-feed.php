<?php
/**
 * @package smaily_for_woocommerce
 * Generates RSS-feed based on url-vars or gets last 50 products updated.
 */

use Smaily_Inc\Base\DataHandler;

// Get variables from url.
$category  = sanitize_text_field( get_query_var( 'category' ) );
$limit     = (int) sanitize_text_field( get_query_var( 'limit' ) );
$order_by  = sanitize_text_field( get_query_var( 'order_by' ) );
$rss_order = sanitize_text_field( get_query_var( 'order' ) );
// Generate RSS.feed. If no limit provided generates 50 products.
if ( $limit === 0 ) {
	DataHandler::generate_rss_feed( $category, 50, $order_by, $rss_order );
} else {
	DataHandler::generate_rss_feed( $category, $limit, $order_by, $rss_order );
}
