/**
 * Bouton flottant cookies + layout bannière tarteaucitron.
 */
(function () {
	var scrollThreshold = 100;

	function updateCookieButton() {
		var el = document.getElementById("akyCookiesGestion");
		if (!el) {
			return;
		}
		var nearBottom =
			window.scrollY + window.innerHeight > document.documentElement.scrollHeight - scrollThreshold;
		el.classList.toggle("active", nearBottom);
	}

	window.addEventListener("scroll", updateCookieButton, { passive: true });
	window.addEventListener("resize", updateCookieButton, { passive: true });
	updateCookieButton();

	function wrapBannerButtons() {
		var banner = document.getElementById("tarteaucitronAlertBig");
		if (!banner || banner.querySelector(".tarteaucitron-aky-actions")) {
			return;
		}

		var buttons = Array.from(banner.querySelectorAll("button"));
		if (buttons.length === 0) {
			return;
		}

		var row = document.createElement("div");
		row.className = "tarteaucitron-aky-actions";

		// Ordre UX : Refuser · Personnaliser · [spacer] · Accepter
		var order = ["tarteaucitronAllDenied2", "tarteaucitronCloseAlert", "tarteaucitronPersonalize", "tarteaucitronPersonalize2"];
		var byId = {};
		buttons.forEach(function (btn) {
			byId[btn.id] = btn;
		});

		order.forEach(function (id) {
			if (byId[id]) {
				row.appendChild(byId[id]);
				delete byId[id];
			}
		});

		Object.keys(byId).forEach(function (id) {
			row.appendChild(byId[id]);
		});

		banner.appendChild(row);
	}

	function adjustCookieButtonForBanner() {
		var banner = document.getElementById("tarteaucitronAlertBig");
		var btn = document.getElementById("akyCookiesGestion");
		if (!banner || !btn) {
			return;
		}
		var visible =
			banner.classList.contains("tarteaucitron-display-block") ||
			(banner.style.display && banner.style.display !== "none");
		btn.style.opacity = visible ? "0" : "";
		btn.style.pointerEvents = visible ? "none" : "";
		if (visible) {
			wrapBannerButtons();
		}
	}

	function setupPanelScroll() {
		var services = document.getElementById("tarteaucitronServices");
		if (!services || services.__akyPanelScrollReady) {
			return;
		}
		services.__akyPanelScrollReady = true;
		services.setAttribute("tabindex", "-1");
		services.addEventListener(
			"wheel",
			function (event) {
				event.stopPropagation();
			},
			{ passive: true }
		);
		services.addEventListener(
			"touchmove",
			function (event) {
				event.stopPropagation();
			},
			{ passive: true }
		);
	}

	function focusPanelScroll() {
		setupPanelScroll();
		var services = document.getElementById("tarteaucitronServices");
		if (services && typeof services.focus === "function") {
			services.focus({ preventScroll: true });
		}
	}

	function watchBanner() {
		var banner = document.getElementById("tarteaucitronAlertBig");
		if (!banner) {
			return;
		}
		wrapBannerButtons();
		adjustCookieButtonForBanner();
		if (typeof MutationObserver !== "undefined") {
			new MutationObserver(adjustCookieButtonForBanner).observe(banner, {
				attributes: true,
				attributeFilter: ["style", "class"],
				childList: true,
			});
		}
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", watchBanner);
	} else {
		watchBanner();
	}

	document.addEventListener("tac.open_alert", wrapBannerButtons);

	document.addEventListener("tac.open_panel", function () {
		var banner = document.getElementById("tarteaucitronAlertBig");
		if (banner) {
			banner.style.display = "none";
		}
		var btn = document.getElementById("akyCookiesGestion");
		if (btn) {
			btn.style.opacity = "0";
			btn.style.pointerEvents = "none";
		}
		focusPanelScroll();
	});

	document.addEventListener("tac.close_panel", adjustCookieButtonForBanner);

	window.addEventListener("tac.root_available", function () {
		setupPanelScroll();
		watchBanner();
	});
})();
