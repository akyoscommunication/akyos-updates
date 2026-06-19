<?php
/**
 * Gabarit dataLayer select_item (GA4).
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
<script>
    jQuery(function ($) {
        $('.woocommerce-LoopProduct-link, .woocommerce-loop-product__link').on('click', function (e) {
            const href = $(this).attr('href');
            const tracker = $(this).find('.aky-gdpr-tracking-product');
            if (!tracker.length) {
                return;
            }

            e.preventDefault();
            window.AkyosWooTracking.push('select_item', [window.AkyosWooTracking.itemFromElement(tracker)]);
            window.location = href;
        });
    });
</script>
