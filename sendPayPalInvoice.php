<?php
/**
 * Plugin Name:       Pay Pal Invoices Create and Send Functions
 * Description:       little plugin to contain your theme customisation snippets and PayPal Invoices Create and Send Functions.
 * Plugin URI:        http://github.com/woothemes/theme-customisations
 * Version:           1.0.0
 * Author:            IURII PAIMURZIN
 * Author URI:        https://sell.systems/
 * Requires at least: 3.0.0
 * Tested up to:      4.4.2
 *
 * @package sendPayPalInvoice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main sendPayPalInvoice Class
 *
 * @class sendPayPalInvoice
 * @version	1.0.0
 * @since 1.0.0
 * @package	sendPayPalInvoice
*/

/*  Copyright 2018 IURII PAIMURZIN  (email: ufxcss@gmail.com)
 This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

final class sendPayPalInvoice {

	/**
	 * Set up the plugin
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'sendPayPalInvoice' ), -1 );
		require_once( 'sendPayPalInvoice/functions.php' );
	}

	/**
	 * Setup all the things
	 */
	public function sendPayPalInvoice() {
		add_action( 'wp_enqueue_scripts', array( $this, 'sendPayPalInvoice_css' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'sendPayPalInvoice_js' ) );
		add_filter( 'template_include',   array( $this, 'sendPayPalInvoice_template' ), 11 );
		add_filter( 'wc_get_template',    array( $this, 'sendPayPalInvoice_wc_get_template' ), 11, 5 );
	}

	/**
	 * Enqueue the CSS
	 *
	 * @return void
	 */
	public function sendPayPalInvoice_css() {
		wp_enqueue_style( 'custom-css', plugins_url( '/sendPayPalInvoice/style.css', __FILE__ ) );
	}

	/**
	 * Enqueue the Javascript
	 *
	 * @return void
	 */
	public function sendPayPalInvoice_js() {
		wp_enqueue_script( 'custom-js', plugins_url( '/sendPayPalInvoice/custom.js', __FILE__ ), array( 'jquery' ) );
	}

	/**
	 * Look in this plugin for template files first.
	 * This works for the top level templates (IE single.php, page.php etc). However, it doesn't work for
	 * template parts yet (content.php, header.php etc).
	 *
	 * Relevant trac ticket; https://core.trac.wordpress.org/ticket/13239
	 *
	 * @param  string $template template string.
	 * @return string $template new template string.
	 */
	public function sendPayPalInvoice_template( $template ) {
		if ( file_exists( untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/sendPayPalInvoice/templates/' . basename( $template ) ) ) {
			$template = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/sendPayPalInvoice/templates/' . basename( $template );
		}

		return $template;
	}

	/**
	 * Look in this plugin for WooCommerce template overrides.
	 *
	 * For example, if you want to override woocommerce/templates/cart/cart.php, you
	 * can place the modified template in <plugindir>/sendPayPalInvoice/templates/woocommerce/cart/cart.php
	 *
	 * @param string $located is the currently located template, if any was found so far.
	 * @param string $template_name is the name of the template (ex: cart/cart.php).
	 * @return string $located is the newly located template if one was found, otherwise
	 *                         it is the previously found template.
	 */
	public function sendPayPalInvoice_wc_get_template( $located, $template_name, $args, $template_path, $default_path ) {
		$plugin_template_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/sendPayPalInvoice/templates/woocommerce/' . $template_name;

		if ( file_exists( $plugin_template_path ) ) {
			$located = $plugin_template_path;
		}

		return $located;
	}
} // End Class

/**
 * The 'main' function
 *
 * @return void
 */
function sendPayPalInvoice_main() {
	new sendPayPalInvoice();
}

/**
 * Initialise the plugin
 */
add_action( 'plugins_loaded', 'sendPayPalInvoice_main' );
