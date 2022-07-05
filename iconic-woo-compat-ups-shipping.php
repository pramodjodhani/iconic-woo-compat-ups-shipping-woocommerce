<?php
/**
 * Plugin Name:       Delivery Slots by Iconic [WooCommerce UPS Shipping]
 * Plugin URI:        https://iconicwp.com/products/woocommerce-delivery-slots/?utm_source=iconicwp&utm_medium=plugin&utm_campaign=iconic-woo-compat-ups-shipping-woocommerce
 * Description:       Compatibility between Delivery Slots by Iconic and WooCommerce UPS Shipping.
 * Author:            Iconic
 * Author URI:        https://iconicwp.com/?utm_source=iconicwp&utm_medium=plugin&utm_campaign=iconic-woo-compat-ups-shipping-woocommerce
 * Text Domain:       iconic-compat-12004
 * Domain Path:       /languages
 * Version:           0.1.0
 * GitHub Plugin URI: iconicwp/iconic-woo-compat-template
 *
 * Use a random 5 digit number to prevent conflicts. This is used
 * for function name prefixes (iconic_compat_{54494}_) and the
 * textdomain. https://numbergenerator.org/random-5-digit-number-generator
 *
 * In order to enable automatic updates for the customer, recommend installing
 * Git Updater: https://github.com/afragen/git-updater
 *
 * Make sure to update the git URL for this plugin in the plugin headers.
 *
 * Replace anything in curly brackets {}.
 */

/**
 * Modify shipping methods.
 *
 * @param array $options Shipping method options.
 */
function iconic_compat_12004_modify_shipping_method( $options ) {
	if ( ! class_exists( 'WC_Shipping_UPS_Init' ) ) {
		return $options;
	}

	$ups = WC_Shipping_UPS_Init::get_instance();
	$ups->includes();

	foreach ( $options as $method_key => $method_name ) {
		if ( 0 === strpos( $method_key, 'ups:' ) ) {
			if ( 1 === preg_match( '/.*ups:\d*:\d*$/', $method_key ) ) {
				continue;
			}

			$instance_id = str_replace( 'ups:', '', $method_key );

			$ups_method = new WC_Shipping_UPS( $instance_id );

			if ( empty( $ups_method->instance_settings['services'] ) ) {
				unset( $options[ $method_key ] );
				continue;
			}

			foreach ( $ups_method->instance_settings['services'] as $service_code => $service ) {
				if ( ! $service['enabled'] ) {
					continue;
				}

				$key             = sprintf( 'ups:%s:%s', $instance_id, $service_code );
				$options[ $key ] = sprintf( '%s: %s', $method_name, $service['name'] );
			}

			unset( $options[ $method_key ] );
		}

		unset( $options['wc_shipping_ups'] );
	}

	return $options;
}


add_filter( 'iconic_wds_shipping_method_options', 'iconic_compat_12004_modify_shipping_method' );