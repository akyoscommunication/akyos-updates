<?php

namespace AkyosUpdates\Frontend;

use AkyosUpdates\Service\RgpdSettingsService;
use AkyosUpdates\Service\WooDataLayerService;

/**
 * Tracking WooCommerce GA4 (dataLayer) piloté par les réglages RGPD.
 *
 * Ne s'active que si le sous-réglage woo_tracking.enabled est vrai ET WooCommerce est chargé.
 */
final class RgpdWooTracking
{
    public const SCRIPT_HANDLE = 'akyos-updates-woo-tracking';

    private string $addToCartBtnClass = '';
    private string $removeFromCartBtnClass = '';

    public function __construct(private RgpdSettingsService $settings)
    {
    }

    public function register(): void
    {
        add_action('init', [$this, 'maybeBoot']);
    }

    public function maybeBoot(): void
    {
        if (! class_exists('WooCommerce')) {
            return;
        }

        $settings = $this->settings->get();
        $woo = is_array($settings['woo_tracking'] ?? null) ? $settings['woo_tracking'] : [];
        if (empty($woo['enabled'])) {
            return;
        }

        $this->addToCartBtnClass = (string) ($woo['add_to_cart_btn_class'] ?? '');
        $this->removeFromCartBtnClass = (string) ($woo['remove_from_cart_btn_class'] ?? '');

        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_filter('woocommerce_loop_add_to_cart_args', [$this, 'addToCartBtnDataset'], 10, 2);
        add_filter('woocommerce_cart_item_remove_link', [$this, 'removeFromCartBtn'], 10, 2);
        add_action('woocommerce_before_shop_loop_item_title', [$this, 'productListDataset']);

        add_action('woocommerce_before_single_product', [$this, 'pageView']);
        add_action('wp_footer', [$this, 'addToCart'], 999);
        add_action('wp_footer', [$this, 'checkoutProgress'], 999);
        add_action('wp_footer', [$this, 'selectItem'], 999);
        add_action('wp_footer', [$this, 'removeFromCart'], 999);
        add_action('woocommerce_thankyou', [$this, 'purchase'], 999);
        add_action('woocommerce_before_cart', [$this, 'beginCheckout'], 999);
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            AKYOS_UPDATES_PLUGIN_URL . 'assets/rgpd/woo/tracking.js',
            ['jquery'],
            AKYOS_UPDATES_VERSION,
            true
        );
    }

    /**
     * @param array<string, mixed> $links
     * @param mixed $product
     * @return array<string, mixed>
     */
    public function addToCartBtnDataset($links, $product)
    {
        if (is_array($links) && isset($links['attributes']) && is_array($links['attributes'])) {
            $links['attributes'] = array_merge($links['attributes'], WooDataLayerService::datasetAttrs($product));
        }

        return $links;
    }

    /** @param string $link @param string $cartItemKey */
    public function removeFromCartBtn($link, $cartItemKey)
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return $link;
        }

        $cartItem = WC()->cart->get_cart_item($cartItemKey);
        if (! isset($cartItem['data']) || ! $cartItem['data'] instanceof \WC_Product) {
            return $link;
        }

        $quantity = isset($cartItem['quantity']) ? (int) $cartItem['quantity'] : 1;
        $attrs = WooDataLayerService::datasetAttrsInline($cartItem['data'], $quantity);
        $link = str_replace(
            ['<a ', 'class="'],
            ['<a ' . $attrs . ' ', 'class="aky-gdpr-tracking-remove-from-cart '],
            $link
        );

        return $link;
    }

    public function productListDataset(): void
    {
        global $product;
        if (is_a($product, 'WC_Product')) {
            $attrs = WooDataLayerService::datasetAttrsInline($product);
            echo '<div class="aky-gdpr-tracking-product"' . $attrs . '></div>';
        }
    }

    public function pageView(): void
    {
        include AKYOS_UPDATES_PLUGIN_DIR . 'assets/rgpd/woo/page_view.php';
    }

    public function addToCart(): void
    {
        $addToCartBtnClass = $this->addToCartBtnClass;
        include AKYOS_UPDATES_PLUGIN_DIR . 'assets/rgpd/woo/add_to_cart.php';
    }

    public function beginCheckout(): void
    {
        include AKYOS_UPDATES_PLUGIN_DIR . 'assets/rgpd/woo/begin_checkout.php';
    }

    public function checkoutProgress(): void
    {
        if (! function_exists('is_checkout') || ! is_checkout()) {
            return;
        }

        include AKYOS_UPDATES_PLUGIN_DIR . 'assets/rgpd/woo/checkout_progress.php';
    }

    /** @param int $order_id */
    public function purchase($order_id): void
    {
        $order_id = (int) $order_id;
        include AKYOS_UPDATES_PLUGIN_DIR . 'assets/rgpd/woo/purchase.php';
    }

    public function selectItem(): void
    {
        if (! function_exists('is_woocommerce') || ! is_woocommerce() || is_product()) {
            return;
        }

        include AKYOS_UPDATES_PLUGIN_DIR . 'assets/rgpd/woo/select_item.php';
    }

    public function removeFromCart(): void
    {
        if (! function_exists('is_cart') || ! is_cart()) {
            return;
        }

        $removeFromCartBtnClass = $this->removeFromCartBtnClass;
        include AKYOS_UPDATES_PLUGIN_DIR . 'assets/rgpd/woo/remove_from_cart.php';
    }
}
