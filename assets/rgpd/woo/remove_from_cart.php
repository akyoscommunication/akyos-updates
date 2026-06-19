<?php
/**
 * Gabarit dataLayer remove_from_cart (GA4). Variables attendues : $removeFromCartBtnClass.
 *
 * @var string $removeFromCartBtnClass
 */

if (! defined('ABSPATH')) {
    exit;
}

$selector = $removeFromCartBtnClass !== '' ? $removeFromCartBtnClass : '.aky-gdpr-tracking-remove-from-cart';
?>
<script>
    jQuery(function ($) {
        $(<?php echo wp_json_encode($selector); ?>).on('click', function () {
            window.AkyosWooTracking.push('remove_from_cart', [window.AkyosWooTracking.itemFromElement(this)]);
        });
    });
</script>
