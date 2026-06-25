/**
 * Verrou scroll page pendant bannière / panneau cookies (lib-agnostic).
 */
(function (window, document) {
	var SHIELD_ID = "aky-rgpd-scroll-shield";
	var scrollOpts = { passive: false, capture: true };
	var active = false;
	var applied = false;
	var savedScrollY = 0;
	var clampId = null;
	var pollId = null;
	var motionFreeze = [];
	var inertSaved = [];

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
		if (banner.classList.contains("tarteaucitron-display-block")) {
			return true;
		}
		if (banner.style.display === "block") {
			return true;
		}
		return isVisible(banner);
	}

	function isInsideTac(target) {
		return !!(target && target.closest && target.closest("#tarteaucitronRoot"));
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
			if (el.id === "tarteaucitronRoot" || !isInsideTac(el)) {
				break;
			}
			if (canScrollElement(el, deltaY)) {
				return el;
			}
			el = el.parentElement;
		}
		return null;
	}

	function shouldAllowCmpScroll(event) {
		if (event.type === "wheel") {
			return !!findScrollableInTac(event.target, event.deltaY);
		}
		if (event.type === "touchmove") {
			var el = event.target;
			while (el && el !== document.documentElement) {
				if (el.id === "tarteaucitronRoot") {
					break;
				}
				if (isInsideTac(el) && el.scrollHeight > el.clientHeight + 1) {
					return true;
				}
				el = el.parentElement;
			}
		}
		return false;
	}

	function blockScrollEvent(event) {
		if (!active || shouldAllowCmpScroll(event)) {
			return;
		}
		event.preventDefault();
		event.stopPropagation();
		if (typeof event.stopImmediatePropagation === "function") {
			event.stopImmediatePropagation();
		}
		return false;
	}

	function shouldSkipMotionEl(el) {
		if (!el || el.nodeType !== 1) {
			return true;
		}
		if (el.id === SHIELD_ID || el.id === "tarteaucitronRoot" || el.id === "akyCookiesGestion") {
			return true;
		}
		return isInsideTac(el);
	}

	function collectMotionRoots() {
		var roots = [];
		var seen = new Set();
		var extra = window.__akyRgpdScrollRoots;

		function add(el) {
			if (!el || shouldSkipMotionEl(el) || seen.has(el)) {
				return;
			}
			seen.add(el);
			roots.push(el);
		}

		["html", "body", "main", "#page", "#content", "#main", "#wrapper", "#app"].forEach(function (sel) {
			try {
				document.querySelectorAll(sel).forEach(add);
			} catch (e) {
				/* ponytail: ignore */
			}
		});

		if (Array.isArray(extra)) {
			extra.forEach(function (sel) {
				try {
					document.querySelectorAll(sel).forEach(add);
				} catch (e) {
					/* ponytail: ignore */
				}
			});
		}

		Array.from(document.body.children).forEach(add);

		try {
			document.body.querySelectorAll("*").forEach(function (el) {
				if (shouldSkipMotionEl(el)) {
					return;
				}
				var style = window.getComputedStyle(el);
				if (style.transform !== "none" || style.willChange.indexOf("transform") !== -1) {
					add(el);
				}
			});
		} catch (e) {
			/* ponytail: ignore */
		}

		return roots;
	}

	function captureMotionState() {
		releaseMotionState();
		motionFreeze = collectMotionRoots().map(function (el) {
			var style = window.getComputedStyle(el);
			return {
				el: el,
				styleTransform: el.style.transform,
				styleOverflow: el.style.overflow,
				styleTouchAction: el.style.touchAction,
				frozenTransform: style.transform,
				frozenScrollTop: el.scrollTop,
				frozenScrollLeft: el.scrollLeft,
			};
		});

		motionFreeze.forEach(function (snap) {
			snap.el.style.overflow = "hidden";
			snap.el.style.touchAction = "none";
			if (snap.frozenTransform !== "none") {
				snap.el.style.transform = snap.frozenTransform;
			}
		});
	}

	function enforceMotionFreeze() {
		motionFreeze.forEach(function (snap) {
			var el = snap.el;
			if (el.scrollTop !== snap.frozenScrollTop) {
				el.scrollTop = snap.frozenScrollTop;
			}
			if (el.scrollLeft !== snap.frozenScrollLeft) {
				el.scrollLeft = snap.frozenScrollLeft;
			}
			if (snap.frozenTransform !== "none") {
				var current = window.getComputedStyle(el).transform;
				if (current !== snap.frozenTransform) {
					el.style.transform = snap.frozenTransform;
				}
			}
		});
	}

	function releaseMotionState() {
		motionFreeze.forEach(function (snap) {
			snap.el.style.transform = snap.styleTransform;
			snap.el.style.overflow = snap.styleOverflow;
			snap.el.style.touchAction = snap.styleTouchAction;
		});
		motionFreeze = [];
	}

	function applyInert() {
		releaseInert();
		Array.from(document.body.children).forEach(function (el) {
			if (el.id === "tarteaucitronRoot" || el.id === "akyCookiesGestion") {
				return;
			}
			inertSaved.push({ el: el, inert: el.inert });
			el.inert = true;
		});
	}

	function releaseInert() {
		inertSaved.forEach(function (entry) {
			entry.el.inert = entry.inert;
		});
		inertSaved = [];
	}

	function resetScrollPosition() {
		if (!active || !applied) {
			return;
		}
		if (window.scrollY !== 0) {
			window.scrollTo(0, 0);
		}
		if (document.documentElement.scrollTop !== 0) {
			document.documentElement.scrollTop = 0;
		}
		if (document.body.scrollTop !== 0) {
			document.body.scrollTop = 0;
		}
		enforceMotionFreeze();
	}

	function startScrollClamp() {
		if (clampId !== null) {
			return;
		}
		function clamp() {
			if (!active) {
				clampId = null;
				return;
			}
			resetScrollPosition();
			clampId = window.requestAnimationFrame(clamp);
		}
		clampId = window.requestAnimationFrame(clamp);
	}

	function stopScrollClamp() {
		if (clampId !== null) {
			window.cancelAnimationFrame(clampId);
			clampId = null;
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
		shield.addEventListener("wheel", blockScrollEvent, scrollOpts);
		shield.addEventListener("touchmove", blockScrollEvent, scrollOpts);
		var tacRoot = document.getElementById("tarteaucitronRoot");
		if (tacRoot) {
			tacRoot.insertBefore(shield, tacRoot.firstChild);
		} else {
			document.body.appendChild(shield);
		}
		return shield;
	}

	function showShield() {
		ensureShield().hidden = false;
	}

	function hideShield() {
		var shield = document.getElementById(SHIELD_ID);
		if (shield) {
			shield.hidden = true;
		}
	}

	function lockPage() {
		if (!applied) {
			applied = true;
			savedScrollY = window.scrollY || window.pageYOffset || 0;
			document.documentElement.classList.add("aky-rgpd-scroll-lock");
			document.body.classList.add("aky-rgpd-scroll-lock");
			document.documentElement.style.setProperty("top", "-" + savedScrollY + "px");
			document.documentElement.style.setProperty("position", "fixed");
			document.documentElement.style.setProperty("left", "0");
			document.documentElement.style.setProperty("right", "0");
			document.documentElement.style.setProperty("width", "100%");
		}
		captureMotionState();
		applyInert();
		showShield();
		startScrollClamp();
		resetScrollPosition();
	}

	function unlockPage() {
		if (!applied) {
			return;
		}
		applied = false;
		stopScrollClamp();
		hideShield();
		releaseInert();
		releaseMotionState();
		document.documentElement.classList.remove("aky-rgpd-scroll-lock");
		document.body.classList.remove("aky-rgpd-scroll-lock");
		document.documentElement.style.removeProperty("position");
		document.documentElement.style.removeProperty("top");
		document.documentElement.style.removeProperty("left");
		document.documentElement.style.removeProperty("right");
		document.documentElement.style.removeProperty("width");
		window.scrollTo(0, savedScrollY);
	}

	function setActive(locked) {
		if (locked === active) {
			if (locked) {
				captureMotionState();
			}
			return;
		}
		active = locked;
		setEarlyActive(locked);
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
		// ponytail: filet de sécurité si tarteaucitron ouvre sans event
		pollId = window.setInterval(syncFromDom, 200);
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
	document.addEventListener("wheel", blockScrollEvent, scrollOpts);
	document.addEventListener("touchmove", blockScrollEvent, scrollOpts);
	window.addEventListener(
		"scroll",
		function (event) {
			if (!active) {
				return;
			}
			resetScrollPosition();
			event.preventDefault();
			if (typeof event.stopImmediatePropagation === "function") {
				event.stopImmediatePropagation();
			}
		},
		scrollOpts
	);
	window.addEventListener(
		"keydown",
		function (event) {
			if (!active) {
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
			if (typeof event.stopImmediatePropagation === "function") {
				event.stopImmediatePropagation();
			}
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
