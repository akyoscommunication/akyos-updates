/**
 * Helpers dataLayer GA4 pour le tracking WooCommerce Akyos Updates.
 */
(function ($) {
	"use strict";

	function itemFromElement(el) {
		const d = $(el).data();
		const item = {
			item_name: d.productName || "",
			item_id: String(d.productId || ""),
			price: parseFloat(d.productPrice) || 0,
			quantity: parseInt(d.productQuantity, 10) || 1,
			item_variant: d.productVariant || "",
		};

		Object.keys(d).forEach(function (key) {
			const match = key.match(/^itemCategory(\d+)$/);
			if (!match) {
				return;
			}
			const catKey = match[1] === "1" ? "item_category" : "item_category" + match[1];
			item[catKey] = d[key];
		});

		return item;
	}

	function push(event, items, ecommerceExtra) {
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push({
			event: event,
			ecommerce: Object.assign({ items: items }, ecommerceExtra || {}),
		});
	}

	window.AkyosWooTracking = { itemFromElement: itemFromElement, push: push };
})(jQuery);
