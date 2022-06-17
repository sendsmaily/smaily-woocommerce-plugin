<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Rss;

use Smaily_Inc\Base\DataHandler;
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
		add_action( 'init', array( $this, 'smaily_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'smaily_register_query_var' ) );
		add_filter( 'template_include', array( $this, 'smaily_rss_feed_template_include' ), 100 );
		add_filter( 'smaily_settings', array( $this, 'smaily_rss_settings' ) );
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
		$vars[] = 'order_by';
		$vars[] = 'order';
		return $vars;
	}

	/**
	 * Loads template file for RSS-feed page
	 *
	 * @param string $template Normal template.
	 * @return string Updated template location
	 */
	public function smaily_rss_feed_template_include( $template ) {
		$render_rss_feed = get_query_var( 'smaily-rss-feed', false );
		$render_rss_feed = $render_rss_feed === 'true' ? '1' : $render_rss_feed;
		$render_rss_feed = (bool) (int) $render_rss_feed;

		$pagename = get_query_var( 'pagename' );

		// Render products RSS feed, if requested.
		if ( $render_rss_feed === true ) {
			return SMAILY_PLUGIN_PATH . 'templates/smaily-rss-feed.php';
		} elseif ( $pagename === 'smaily-rss-feed' ) {
			return SMAILY_PLUGIN_PATH . 'templates/smaily-rss-feed.php';
		}

		// Load normal template as a fallback.
		return $template;
	}

	/**
	 * Add RSS settings to settings array.
	 *
	 * @param array $settings
	 * @return array
	 */
	public function smaily_rss_settings( $settings ) {
		$settings['rss_feed_url'] = DataHandler::make_rss_feed_url();
		return $settings;
	}

}
