<?php
/*
Plugin Name: WooCommerce Gift Wrap
Description: Adds a gift wrap option with additional fields on the checkout page.
Version: 1.1
Author: Faisal
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('WC_GIFT_WRAP_PRICE', 20);

// Enqueue Scripts
function wc_gift_wrap_enqueue_scripts() {
    if (is_checkout()) {
        wp_enqueue_script(
            'wc-gift-wrap',
            plugin_dir_url(__FILE__) . 'assets/js/wc-gift-wrap.js',
            array('jquery', 'wc-checkout'),
            '1.1',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'wc_gift_wrap_enqueue_scripts');

// Add Checkout Fields
function wc_gift_wrap_checkout_fields($checkout) {

    echo '<div id="gift_wrap_options">';
    echo '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">';

    woocommerce_form_field('add_gift_wrap', array(
        'type'  => 'checkbox',
        'class' => array('form-row-wide'),
        'label' => __('Add Gift Wrap ($' . WC_GIFT_WRAP_PRICE . ') 🎁', 'woocommerce'),
    ), $checkout->get_value('add_gift_wrap'));

    echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . 'assets/images/gift-box.jpg') . '" style="max-width:100px;margin-left:10px;" />';

    echo '</div>';

    woocommerce_form_field('gift_wrap_from', array(
        'type'  => 'text',
        'class' => array('form-row-wide gift-wrap-field'),
        'label' => __('From', 'woocommerce'),
    ), $checkout->get_value('gift_wrap_from'));

    woocommerce_form_field('gift_wrap_to', array(
        'type'  => 'text',
        'class' => array('form-row-wide gift-wrap-field'),
        'label' => __('To', 'woocommerce'),
    ), $checkout->get_value('gift_wrap_to'));

    woocommerce_form_field('gift_wrap_message', array(
        'type'  => 'textarea',
        'class' => array('form-row-wide gift-wrap-field'),
        'label' => __('Message', 'woocommerce'),
    ), $checkout->get_value('gift_wrap_message'));

    echo '</div>';
}
add_action('woocommerce_before_order_notes', 'wc_gift_wrap_checkout_fields');

// Add Fee
function wc_gift_wrap_fee($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    $add_gift_wrap = isset($_POST['add_gift_wrap']) ? wc_clean($_POST['add_gift_wrap']) : '';

    if ($add_gift_wrap) {
        $cart->add_fee(__('Gift Wrap', 'woocommerce'), WC_GIFT_WRAP_PRICE);
    }
}
add_action('woocommerce_cart_calculate_fees', 'wc_gift_wrap_fee');

// Save Order Meta
function wc_gift_wrap_update_order_meta($order_id) {

    $add_gift_wrap = isset($_POST['add_gift_wrap']) ? wc_clean($_POST['add_gift_wrap']) : '';

    if ($add_gift_wrap) {

        update_post_meta($order_id, '_add_gift_wrap', 'yes');

        update_post_meta($order_id, '_gift_wrap_from',
            isset($_POST['gift_wrap_from']) ? sanitize_text_field($_POST['gift_wrap_from']) : ''
        );

        update_post_meta($order_id, '_gift_wrap_to',
            isset($_POST['gift_wrap_to']) ? sanitize_text_field($_POST['gift_wrap_to']) : ''
        );

        update_post_meta($order_id, '_gift_wrap_message',
            isset($_POST['gift_wrap_message']) ? sanitize_textarea_field($_POST['gift_wrap_message']) : ''
        );

        update_post_meta($order_id, '_gift_wrap_fee', WC_GIFT_WRAP_PRICE);
    }
}
add_action('woocommerce_checkout_update_order_meta', 'wc_gift_wrap_update_order_meta');

// Display in Admin
function wc_gift_wrap_display_admin_order_meta($order) {

    $add_gift_wrap = get_post_meta($order->get_id(), '_add_gift_wrap', true);

    if ($add_gift_wrap) {

        echo '<p><strong>' . esc_html__('Gift Wrap:', 'woocommerce') . '</strong> Yes</p>';
        echo '<p><strong>' . esc_html__('From:', 'woocommerce') . '</strong> ' . esc_html(get_post_meta($order->get_id(), '_gift_wrap_from', true)) . '</p>';
        echo '<p><strong>' . esc_html__('To:', 'woocommerce') . '</strong> ' . esc_html(get_post_meta($order->get_id(), '_gift_wrap_to', true)) . '</p>';
        echo '<p><strong>' . esc_html__('Message:', 'woocommerce') . '</strong><br>' . nl2br(esc_html(get_post_meta($order->get_id(), '_gift_wrap_message', true))) . '</p>';
    }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'wc_gift_wrap_display_admin_order_meta');

// Display on Thank You Page
function wc_gift_wrap_display_thankyou_page($order_id) {

    $add_gift_wrap = get_post_meta($order_id, '_add_gift_wrap', true);

    if ($add_gift_wrap) {

        echo '<h2>' . esc_html__('Gift Wrap Details', 'woocommerce') . '</h2>';
        echo '<p><strong>' . esc_html__('From:', 'woocommerce') . '</strong> ' . esc_html(get_post_meta($order_id, '_gift_wrap_from', true)) . '</p>';
        echo '<p><strong>' . esc_html__('To:', 'woocommerce') . '</strong> ' . esc_html(get_post_meta($order_id, '_gift_wrap_to', true)) . '</p>';
        echo '<p><strong>' . esc_html__('Message:', 'woocommerce') . '</strong><br>' . nl2br(esc_html(get_post_meta($order_id, '_gift_wrap_message', true))) . '</p>';
    }
}
add_action('woocommerce_thankyou', 'wc_gift_wrap_display_thankyou_page');