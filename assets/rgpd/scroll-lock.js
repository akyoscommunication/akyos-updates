/**
 * Verrou scroll page pendant bannière / panneau cookies (lib-agnostic).
 */
(function (window, document) {
	var SHIELD_ID = "aky-rgpd-scroll-shield";
	var scrollOpts = { passive: false, capture: true };
	var active = false;
	var savedScrollY = 0;
	var pollId = null;

	function setEarlyActive(locked) {
		if (window.__akyRgpdScroll) {
			window.__akyRgpdScroll.active = locked ? 1 : 0;
		}
	}

	function isVisible(el) {
		if (!el) {
			return false;
		}
		var style = window.getComputedStyle(el);
		return style.display !== "none" && style.visibility !== "hidden" && el.offsetHeight > 0;
	}

	function isCmpOpen() {
		if (document.body.classList.contains("tarteaucitron-modal-open")) {
			return true;
		}
		var root = document.getElementById("tarteaucitronRoot");
		if (root && root.classList.contains("tarteaucitronBeforeVisible")) {
			return true;
		}
		var panel = document.getElementById("tarteaucitron");
		if (panel && (panel.style.display === "block" || isVisible(panel))) {
			return true;
		}
		var back = document.getElementById("tarteaucitronBack");
		if (back && (back.style.display === "block" || isVisible(back))) {
			return true;
		}
		var banner = document.getElementById("tarteaucitronAlertBig");
		if (!banner) {
			return false;
		}
		if (banner.classList.contains("tarteaucitron-display-block") || banner.style.display === "block") {
			return true;
		}
		return isVisible(banner);
	}

	function isPanelScrollTarget(target) {
		return !!(target && target.closest && target.closest("#tarteaucitronServices"));
	}

	function blockScrollEvent(event) {
		if (!active) {
			return;
		}
		if (isPanelScrollTarget(event.target)) {
			return;
		}
		event.preventDefault();
		if (typeof event.stopImmediatePropagation === "function") {
			event.stopImmediatePropagation();
		}
	}

	function ensureShield() {
		var shield = document.getElementById(SHIELD_ID);
		if (shield) {
			return shield;
		}
		shield = document.createElement("div");
		shield.id = SHIELD_ID;
		shield.setAttribute("aria-hidden", "true");
		shield.hidden = true;
		var tacRoot = document.getElementById("tarteaucitronRoot");
		if (tacRoot) {
			tacRoot.insertBefore(shield, tacRoot.firstChild);
		} else {
			document.body.appendChild(shield);
		}
		return shield;
	}

	function showShield() {
		var panel = document.getElementById("tarteaucitron");
		var shield = ensureShield();
		shield.hidden = !!(panel && (panel.style.display === "block" || isVisible(panel)));
	}

	function hideShield() {
		var shield = document.getElementById(SHIELD_ID);
		if (shield) {
			shield.hidden = true;
		}
	}

	function lockPage() {
		if (active) {
			showShield();
			return;
		}
		active = true;
		setEarlyActive(true);
		savedScrollY = window.scrollY || window.pageYOffset || 0;
		document.documentElement.classList.add("aky-rgpd-scroll-lock");
		document.body.classList.add("aky-rgpd-scroll-lock");
		showShield();
	}

	function unlockPage() {
		if (!active) {
			return;
		}
		active = false;
		setEarlyActive(false);
		hideShield();
		document.documentElement.classList.remove("aky-rgpd-scroll-lock");
		document.body.classList.remove("aky-rgpd-scroll-lock");
		var y = savedScrollY;
		window.scrollTo(0, y);
		window.requestAnimationFrame(function () {
			window.scrollTo(0, y);
		});
	}

	function setActive(locked) {
		if (locked) {
			lockPage();
			return;
		}
		unlockPage();
	}

	function syncFromDom() {
		setActive(isCmpOpen());
	}

	function startPolling() {
		if (pollId !== null) {
			return;
		}
		pollId = window.setInterval(syncFromDom, 250);
	}

	function watchTacRoot() {
		var root = document.getElementById("tarteaucitronRoot");
		if (!root || root.__akyRgpdObserved) {
			return;
		}
		root.__akyRgpdObserved = true;
		ensureShield();
		if (typeof MutationObserver !== "undefined") {
			new MutationObserver(syncFromDom).observe(root, {
				attributes: true,
				childList: true,
				subtree: true,
				attributeFilter: ["style", "class", "hidden"],
			});
		}
		syncFromDom();
	}

	window.addEventListener("wheel", blockScrollEvent, scrollOpts);
	window.addEventListener("touchmove", blockScrollEvent, scrollOpts);

	window.addEventListener(
		"keydown",
		function (event) {
			if (!active) {
				return;
			}
			if (isPanelScrollTarget(event.target)) {
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

	["tac.open_alert", "tac.close_alert", "tac.open_panel", "tac.close_panel", "tac.root_available"].forEach(
		function (name) {
			window.addEventListener(name, syncFromDom);
		}
	);

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", function () {
			watchTacRoot();
			startPolling();
		});
	} else {
		watchTacRoot();
		startPolling();
	}

	if (typeof MutationObserver !== "undefined") {
		new MutationObserver(function () {
			watchTacRoot();
		}).observe(document.documentElement, { childList: true, subtree: true });
	}

	window.akyRgpdScrollLock = {
		setActive: setActive,
		syncFromDom: syncFromDom,
		isActive: function () {
			return active;
		},
	};
})(window, document);
