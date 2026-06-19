<?php
/**
 * Gabarit dataLayer begin_checkout (GA4).
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('WC') || ! WC()->cart) {
    return;
}

$cart_items = WC()->cart->get_cart();
if ($cart_items === []) {
    return;
}

$products = [];
foreach ($cart_items as $cart_item) {
    $quantity = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
    $products[] = \AkyosUpdates\Service\WooDataLayerService::getProductItemDataLayer($cart_item['data'], $quantity);
}

$begin_checkout_event = [
    'event' => 'begin_checkout',
    'ecommerce' => [
        'currency' => function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : '',
        'value' => (float) WC()->cart->get_total('edit'),
        'items' => $products,
    ],
];
?>
<script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(<?php echo wp_json_encode($begin_checkout_event); ?>);
</script>
