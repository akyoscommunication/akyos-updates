<?php
/**
 * Gabarit dataLayer checkout_progress (shipping_method / add_payment_info).
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('is_checkout') || ! is_checkout() || ! function_exists('WC') || ! WC()->cart) {
    return;
}

$products = [];
foreach (WC()->cart->get_cart() as $cart_item) {
    $quantity = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
    $products[] = \AkyosUpdates\Service\WooDataLayerService::getProductItemDataLayer($cart_item['data'], $quantity);
}

$data_layer = [
    'currency' => function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : '',
    'value' => (float) WC()->cart->get_total('edit'),
    'items' => $products,
];
?>
<script>
    jQuery(function ($) {
        const baseEcommerce = <?php echo wp_json_encode($data_layer); ?>;

        $('form.checkout').on('change', 'input[name^="shipping_method"]', function () {
            const shippingMethod = $('input[name^="shipping_method"]:checked').val();
            window.AkyosWooTracking.push('add_shipping_info', baseEcommerce.items, {
                shipping_tier: shippingMethod,
                currency: baseEcommerce.currency,
                value: baseEcommerce.value,
            });
        });

        $('form.checkout').on('change', 'input[name^="payment_method"]', function () {
            const paymentMethod = $('input[name^="payment_method"]:checked').val();
            window.AkyosWooTracking.push('add_payment_info', baseEcommerce.items, {
                payment_type: paymentMethod,
                currency: baseEcommerce.currency,
                value: baseEcommerce.value,
            });
        });
    });
</script>
