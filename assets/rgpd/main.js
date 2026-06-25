/**
 * Bouton flottant cookies + layout bannière tarteaucitron.
 */
(function () {
	var scrollThreshold = 100;
	var cmpScrollLockActive = false;

	function isTacPanelOpen() {
		var panel = document.getElementById("tarteaucitron");
		return !!panel && panel.style.display === "block";
	}

	function isTacAlertOpen() {
		var banner = document.getElementById("tarteaucitronAlertBig");
		if (!banner) {
			return false;
		}
		return (
			banner.classList.contains("tarteaucitron-display-block") ||
			banner.style.display === "block"
		);
	}

	function isCmpOpen() {
		return isTacPanelOpen() || isTacAlertOpen();
	}

	function canScrollElement(el, deltaY) {
		if (!el || el.scrollHeight <= el.clientHeight) {
			return false;
		}
		if (deltaY < 0) {
			return el.scrollTop > 0;
		}
		if (deltaY > 0) {
			return el.scrollTop + el.clientHeight < el.scrollHeight;
		}
		return false;
	}

	function findScrollableInTac(target, deltaY) {
		var el = target;
		while (el && el !== document.documentElement) {
			if (el.id === "tarteaucitronRoot" || !el.closest || !el.closest("#tarteaucitronRoot")) {
				break;
			}
			if (canScrollElement(el, deltaY)) {
				return el;
			}
			el = el.parentElement;
		}
		return null;
	}

	function markTacRootScrollHints() {
		var root = document.getElementById("tarteaucitronRoot");
		if (!root) {
			return;
		}
		root.setAttribute("data-lenis-prevent", "");
	}

	function hasScrollableAncestorInTac(target) {
		var el = target;
		while (el && el !== document.documentElement) {
			if (el.id === "tarteaucitronRoot") {
				break;
			}
			if (el.closest && el.closest("#tarteaucitronRoot") && el.scrollHeight > el.clientHeight + 1) {
				return true;
			}
			el = el.parentElement;
		}
		return false;
	}

	function blockBackgroundScroll(event) {
		if (!cmpScrollLockActive) {
			return;
		}

		if (event.type === "wheel") {
			if (findScrollableInTac(event.target, event.deltaY)) {
				return;
			}
		} else if (event.type === "touchmove" && hasScrollableAncestorInTac(event.target)) {
			return;
		}

		event.preventDefault();
	}

	function syncScrollLock() {
		cmpScrollLockActive = isCmpOpen();
		document.documentElement.classList.toggle("aky-rgpd-scroll-lock", cmpScrollLockActive);
		markTacRootScrollHints();
	}

	document.addEventListener("wheel", blockBackgroundScroll, { passive: false, capture: true });
	document.addEventListener("touchmove", blockBackgroundScroll, { passive: false, capture: true });
	document.addEventListener(
		"keydown",
		function (event) {
			if (!cmpScrollLockActive) {
				return;
			}
			var keys = [" ", "PageUp", "PageDown", "Home", "End", "ArrowUp", "ArrowDown"];
			if (keys.indexOf(event.key) === -1) {
				return;
			}
			var root = document.getElementById("tarteaucitronRoot");
			if (root && root.contains(document.activeElement)) {
				return;
			}
			event.preventDefault();
		},
		true
	);

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

	function watchBanner() {
		var banner = document.getElementById("tarteaucitronAlertBig");
		if (!banner) {
			return;
		}
		wrapBannerButtons();
		adjustCookieButtonForBanner();
		syncScrollLock();
		if (typeof MutationObserver !== "undefined") {
			new MutationObserver(function () {
				adjustCookieButtonForBanner();
				syncScrollLock();
			}).observe(banner, {
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

	document.addEventListener("tac.open_alert", function () {
		wrapBannerButtons();
		syncScrollLock();
	});
	document.addEventListener("tac.close_alert", syncScrollLock);
	document.addEventListener("tac.open_panel", function () {
		syncScrollLock();
		var banner = document.getElementById("tarteaucitronAlertBig");
		if (banner) {
			banner.style.display = "none";
		}
		var btn = document.getElementById("akyCookiesGestion");
		if (btn) {
			btn.style.opacity = "0";
			btn.style.pointerEvents = "none";
		}
	});

	document.addEventListener("tac.close_panel", function () {
		adjustCookieButtonForBanner();
		syncScrollLock();
	});

	window.addEventListener("tac.root_available", syncScrollLock);
})();
