<?php

/**
 * Plugin Name: Woo Redirect Payment
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Redirect payment to another website using woocommer REST APIs.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            ETMAT
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       wrp-text
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 */

if (!defined('ABSPATH')) exit;


if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // WooCommerce is active, so remove the action
    add_action('admin_notices', function () {
        $message = '<code>Woo Redirect Payment</code> required <code>WooCommerce</code> installed and activated.';
        printf('<div class="notice notice-error"><p>%s</p></div>', $message);
    });
    return;
}


define('WRP_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('WRP_URL', trailingslashit(plugin_dir_url(__FILE__)));

if (!class_exists('Woo_Redirect_Payment')) {
    class Woo_Redirect_Payment
    {
        function __construct()
        {
            add_action('admin_enqueue_scripts', [$this, 'wrp_admin_scripts']);
            add_action('wp_enqueue_scripts', [$this, 'wrp_public_scripts']);
        }

        function wrp_admin_scripts()
        {
            wp_enqueue_style('wrp-admin-css', WRP_URL . 'assets/admin.css');
            wp_enqueue_script('wrp-admin-js', WRP_URL . 'assets/admin.js', array(), time(), true);
        }

        function wrp_public_scripts()
        {
            wp_enqueue_style('wrp-public-css', WRP_URL . 'assets/public.css');
            wp_enqueue_script('wrp-public-js', WRP_URL . 'assets/public.js', array(), time(), true);
        }
    }

    new Woo_Redirect_Payment();
}


require_once(WRP_PATH . 'includes/payment_gateway.php');
