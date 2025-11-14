<?php

/**
 * This Taxonomy Terms Order feature is forked from Terms Order WP plugin v1.0.4 by Designinvento
 * 
 * @link https://wordpress.org/plugins/terms-order-wp/
 * @since 6.3.3
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('TAX_TERMS_ORDER_VERSION', ASENHA_VERSION );
define('TTO_PATH', plugin_dir_path(__FILE__));
define('TTO_URL', plugins_url('/', __FILE__));
define('TTO_ASSETS_PATH', TTO_PATH . 'assets/');
define('TTO_ASSETS_URL', TTO_URL . 'assets/');

require plugin_dir_path( __FILE__ ) . 'includes/class-tax-terms-order.php';

function run_tax_terms_order() {

	$plugin = new Tax_Terms_Order();
	$plugin->run();

}

run_tax_terms_order();