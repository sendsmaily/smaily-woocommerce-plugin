<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Rss;

/**
 * Handles RSS generation for Smaily newsletter
 */
class SmailyRss {

	/**
	 * Action hooks for RSS-feed
	 *
	 * @return void
	 */
	public function register() {
		/* Rewrite Rules */
		add_action( 'init', array( $this, 'smaily_rewrite_rules' ) );
		/* Query Vars */
		add_filter( 'query_vars', array( $this, 'smaily_register_query_var' ) );
		/* Template Include */
		add_filter( 'template_include', array( $this, 'smaily_rss_feed_template_include' ) );

	}

	/**
	 * Rewrite rule for url-handling
	 */
	public function smaily_rewrite_rules() {
		add_rewrite_rule(
			'smaily-rss-feed/?$',
			'index.php?smaily-rss-feed=true',
			'top'
		);
	}

	/**
	 * Adds query variable to list of query variables
	 *
	 * @param array $vars Current list of query variables.
	 * @return array $vars Updated list of query variables
	 */
	public function smaily_register_query_var( $vars ) {
		$vars[] = 'smaily-rss-feed';
		$vars[] = 'category';
		$vars[] = 'limit';
		return $vars;
	}

	/**
	 * Loads template file for RSS-feed page
	 *
	 * @param string $template Normal template.
	 * @return string Updated template location
	 */
	public function smaily_rss_feed_template_include( $template ) {
		global $wp_query; // Load $wp_query object.
		if ( isset( $wp_query->query_vars['smaily-rss-feed'] ) ) {
			// Check for query var "smaily-rss-feed".
			$page_value = $wp_query->query_vars['smaily-rss-feed'];
			// Verify "smaily-rss-feed" exists and value is "true".
			if ( $page_value && $page_value === 'true' ) {
				// Load your template or file.
				return SMAILY_PLUGIN_PATH . 'templates/smaily-rss-feed.php';
			}
		}
		// When rewrite rules database hasn't been refreshed yet.
		if ( array_key_exists( 'pagename', $wp_query->query ) && $wp_query->query['pagename'] === 'smaily-rss-feed' ) {
			return SMAILY_PLUGIN_PATH . 'templates/smaily-rss-feed.php';
		}
		// Load normal template as a fallback.
		return $template;
	}

}
