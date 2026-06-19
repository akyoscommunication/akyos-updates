<?php
/**
 * Gabarit dataLayer add_to_cart (GA4). Variables attendues : $addToCartBtnClass.
 *
 * @var string $addToCartBtnClass
 */

if (! defined('ABSPATH')) {
    exit;
}

if (function_exists('is_product') && is_product()) {
    global $product;

    $product_data = \AkyosUpdates\Service\WooDataLayerService::getProductItemDataLayer($product);
    if ($product_data === []) {
        return;
    }
    ?>
    <script>
        jQuery(function ($) {
            const item = <?php echo wp_json_encode($product_data); ?>;

            $('form.cart').on('submit', function () {
                const quantity = parseInt($(this).find('input[name="quantity"]').val(), 10) || 1;
                window.AkyosWooTracking.push('add_to_cart', [Object.assign({}, item, { quantity: quantity })]);
            });
        });
    </script>
    <?php
    return;
}

$selector = $addToCartBtnClass !== '' ? $addToCartBtnClass : 'a.button.add_to_cart_button';
?>
<script>
    jQuery(function ($) {
        $(<?php echo wp_json_encode($selector); ?>).on('click', function () {
            window.AkyosWooTracking.push('add_to_cart', [window.AkyosWooTracking.itemFromElement(this)]);
        });
    });
</script>
