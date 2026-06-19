<?php
/**
 * Gabarit dataLayer purchase (GA4). Variables attendues : $order_id.
 *
 * @var int $order_id
 */

if (! defined('ABSPATH')) {
    exit;
}

if (empty($order_id) || ! function_exists('wc_get_order')) {
    return;
}

$order = wc_get_order($order_id);
if (! $order) {
    return;
}

// ponytail: meta flag évite le double comptage au refresh de la page merci
if ($order->get_meta('_akyos_woo_purchase_tracked')) {
    return;
}

$coupons = $order->get_coupon_codes();
$products = [];
foreach ($order->get_items() as $item) {
    $product = $item->get_product();
    if (! $product) {
        continue;
    }
    $products[] = \AkyosUpdates\Service\WooDataLayerService::getProductItemDataLayer($product, $item->get_quantity());
}

$purchase_event = [
    'event' => 'purchase',
    'ecommerce' => [
        'transaction_id' => (string) $order_id,
        'currency' => $order->get_currency(),
        'value' => (float) $order->get_total(),
        'tax' => (float) $order->get_total_tax(),
        'shipping' => (float) $order->get_shipping_total(),
        'coupon' => $coupons !== [] ? implode(', ', $coupons) : '',
        'items' => $products,
    ],
];

$order->update_meta_data('_akyos_woo_purchase_tracked', '1');
$order->save();
?>
<script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(<?php echo wp_json_encode($purchase_event); ?>);
</script>
