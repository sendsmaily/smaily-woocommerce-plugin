<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc;

/**
* Initializes all other classes and runs register( ) method if available
*/
final class Init {

	/**
	 * Store all the classes inside array
	 *
	 * @return array Full list of classes
	 */
	public static function get_services() {
		return array(
			Lifecycle::class,
			Pages\Admin::class,
			Pages\ProfileSettings::class,
			Base\Upgrade::class,
			Base\Enqueue::class,
			Base\SettingLinks::class,
			Base\SubscriberSynchronization::class,
			Base\Cart::class,
			Base\Cron::class,
			Api\Api::class,
			Widget\SmailyWidget::class,
			Rss\SmailyRss::class,
		);
	}

	/**
	 * Loop through the classes and instantiate them.
	 * Call register() method if it exists.
	 */
	public static function register_services() {
		foreach ( self::get_services() as $class ) {
			$service = self::instantiate( $class );
			if ( \method_exists( $service, 'register' ) ) {
				$service->register();
			}
		}
	}

	/**
	 * Initialize class
	 *
	 * @param class $class      class from the services array.
	 * @return class instance   new instanze of the class
	 */
	private static function instantiate( $class ) {
		$service = new $class();
		return $service;
	}
}
