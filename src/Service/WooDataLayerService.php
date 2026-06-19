<?php

namespace AkyosUpdates\Service;

/**
 * Helper de construction des objets « item » dataLayer GA4 pour WooCommerce.
 */
final class WooDataLayerService
{
    /**
     * @param mixed $product Instance WC_Product.
     * @return array<string, mixed>
     */
    public static function getProductItemDataLayer($product, int $quantity = 1): array
    {
        if (! is_object($product) || ! method_exists($product, 'get_id')) {
            return [];
        }

        $productId = $product->get_id();
        $item = [
            'item_name' => $product->get_name(),
            'item_id' => (string) $productId,
            'price' => (float) $product->get_price(),
            'quantity' => $quantity,
            'item_variant' => self::variantLabel($product),
        ];

        $sku = method_exists($product, 'get_sku') ? (string) $product->get_sku() : '';
        if ($sku !== '') {
            $item['item_sku'] = $sku;
        }

        $categories = wp_get_post_terms($productId, 'product_cat');
        if (! is_wp_error($categories) && is_array($categories)) {
            foreach ($categories as $index => $category) {
                if (! is_object($category) || ! isset($category->name)) {
                    continue;
                }
                $key = $index === 0 ? 'item_category' : 'item_category' . ($index + 1);
                $item[$key] = $category->name;
            }
        }

        return $item;
    }

    /**
     * Attributs HTML data-* pour le tracking côté client (boutons, listes produits).
     *
     * @param mixed $product
     * @return array<string, string>
     */
    public static function datasetAttrs($product, int $quantity = 1): array
    {
        $item = self::getProductItemDataLayer($product, $quantity);
        if ($item === []) {
            return [];
        }

        $attrs = [
            'data-product-name' => esc_attr((string) $item['item_name']),
            'data-product-id' => esc_attr((string) $item['item_id']),
            'data-product-price' => esc_attr((string) $item['price']),
            'data-product-quantity' => esc_attr((string) $quantity),
            'data-product-variant' => esc_attr((string) ($item['item_variant'] ?? '')),
        ];

        foreach ($item as $key => $value) {
            if (! str_starts_with($key, 'item_category')) {
                continue;
            }
            $suffix = $key === 'item_category' ? '1' : substr($key, strlen('item_category'));
            $attrs['data-item-category-' . $suffix] = esc_attr((string) $value);
        }

        return $attrs;
    }

    /** @param mixed $product */
    public static function datasetAttrsInline($product, int $quantity = 1): string
    {
        $attrs = self::datasetAttrs($product, $quantity);

        return array_reduce(
            array_keys($attrs),
            static fn (string $carry, string $key): string => $carry . ' ' . $key . '="' . $attrs[$key] . '"',
            ''
        );
    }

    /** @param mixed $product */
    private static function variantLabel($product): string
    {
        if (! method_exists($product, 'is_type')) {
            return '';
        }

        if ($product->is_type('variation') && function_exists('wc_get_formatted_variation')) {
            return (string) wc_get_formatted_variation($product, true, false, true);
        }

        if ($product->is_type('variable') && method_exists($product, 'get_variation_attributes')) {
            return (string) wp_json_encode($product->get_variation_attributes());
        }

        return '';
    }
}
