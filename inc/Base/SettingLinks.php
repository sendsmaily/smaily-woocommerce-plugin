<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

/**
 * Controlls Plugin panel links list
 */
class SettingLinks {

	/**
	 * Adds settings link to plugin page
	 */
	public function register() {

		// add settings link to plugin menu.
		add_filter( 'plugin_action_links_' . SMAILY_PLUGIN_NAME, array( $this, 'settings_link' ) );
	}

	/**
	 * Adds setting link to plugin
	 *
	 * @param array $links Default links in plugin page.
	 * @return array    $links Updated array of links
	 */
	public function settings_link( $links ) {
		// receive all current links and add custom link to the list.
		$settings_link = '<a href="admin.php?page=smaily-settings">' . esc_html__( 'Settings', 'smaily' ) . '</a>';
		// Settings before disable.
		array_unshift( $links, $settings_link );
		return $links;
	}
}
