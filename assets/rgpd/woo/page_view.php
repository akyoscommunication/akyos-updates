<?php
/**
 * Gabarit dataLayer view_item (GA4).
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('is_product') || ! is_product()) {
    return;
}

global $product;
$product_data = \AkyosUpdates\Service\WooDataLayerService::getProductItemDataLayer($product);
if ($product_data === []) {
    return;
}
?>
<script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        event: 'view_item',
        ecommerce: { items: [<?php echo wp_json_encode($product_data); ?>] }
    });
</script>
