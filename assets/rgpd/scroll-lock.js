/**
 * Verrou scroll page pendant bannière / panneau cookies (lib-agnostic).
 * Gère scroll natif + conteneurs animés via transform (smooth scroll courant).
 */
(function (window, document) {
	var SHIELD_ID = "aky-rgpd-scroll-shield";
	var SKIP_IDS = { tarteaucitronRoot: 1, akyCookiesGestion: 1 };
	var DEFAULT_ROOT_SELECTORS = [
		"html",
		"body",
		"main",
		"#page",
		"#content",
		"#main",
		"#wrapper",
		"#app",
		"[data-scroll-container]",
		"[data-scroll-root]",
		"[data-scroll]",
		".scroll-container",
		".smooth-scroll",
	].join(", ");
	var scrollOpts = { passive: false, capture: true };
	var active = false;
	var applied = false;
	var savedScrollY = 0;
	var clampId = null;
	var motionFreeze = [];

	function setEarlyActive(locked) {
		if (window.__akyRgpdScroll) {
			window.__akyRgpdScroll.active = locked ? 1 : 0;
		}
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

	function hasScrollableAncestorInTac(target) {
		var el = target;
		while (el && el !== document.documentElement) {
			if (el.id === "tarteaucitronRoot") {
				break;
			}
			if (isInsideTac(el) && el.scrollHeight > el.clientHeight + 1) {
				return true;
			}
			el = el.parentElement;
		}
		return false;
	}

	function shouldAllowCmpScroll(event) {
		if (event.type === "wheel") {
			return !!findScrollableInTac(event.target, event.deltaY);
		}
		if (event.type === "touchmove") {
			return hasScrollableAncestorInTac(event.target);
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
	}

	function blockScrollKeys(event) {
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
	}

	function shouldSkipElement(el) {
		if (!el || el.nodeType !== 1) {
			return true;
		}
		if (SKIP_IDS[el.id] || el.id === SHIELD_ID) {
			return true;
		}
		return isInsideTac(el) && el.id !== "tarteaucitronServices";
	}

	function getMotionRoots() {
		var seen = Object.create(null);
		var roots = [];

		function add(el) {
			if (shouldSkipElement(el) || seen[el.id || el.tagName + roots.length]) {
				return;
			}
			seen[el.id || el.tagName + roots.length] = true;
			roots.push(el);
		}

		var extra = window.__akyRgpdScrollRoots;
		var selector = DEFAULT_ROOT_SELECTORS;
		if (Array.isArray(extra) && extra.length) {
			selector += ", " + extra.join(", ");
		}

		try {
			document.querySelectorAll(selector).forEach(add);
		} catch (e) {
			/* ponytail: sélecteur invalide ignoré */
		}

		Array.from(document.body.children).forEach(function (el) {
			if (shouldSkipElement(el)) {
				return;
			}
			var style = window.getComputedStyle(el);
			if (el.offsetHeight >= window.innerHeight * 0.85) {
				add(el);
			}
			if (style.transform !== "none" || style.willChange.indexOf("transform") !== -1) {
				add(el);
			}
		});

		return roots;
	}

	function captureMotionState() {
		releaseMotionState();
		motionFreeze = getMotionRoots().map(function (el) {
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

	function onScrollWhileLocked(event) {
		if (!active) {
			return;
		}
		resetScrollPosition();
		event.preventDefault();
		if (typeof event.stopImmediatePropagation === "function") {
			event.stopImmediatePropagation();
		}
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

	function getShield() {
		var shield = document.getElementById(SHIELD_ID);
		if (shield) {
			return shield;
		}
		shield = document.createElement("div");
		shield.id = SHIELD_ID;
		shield.setAttribute("aria-hidden", "true");
		shield.hidden = true;
		var tacRoot = document.getElementById("tarteaucitronRoot");
		if (tacRoot && tacRoot.parentNode) {
			tacRoot.parentNode.insertBefore(shield, tacRoot);
		} else {
			document.body.appendChild(shield);
		}
		return shield;
	}

	function showShield() {
		getShield().hidden = false;
	}

	function hideShield() {
		var shield = document.getElementById(SHIELD_ID);
		if (shield) {
			shield.hidden = true;
		}
	}

	function lockPage() {
		if (applied) {
			captureMotionState();
			return;
		}
		applied = true;
		savedScrollY = window.scrollY || window.pageYOffset || 0;
		document.documentElement.classList.add("aky-rgpd-scroll-lock");
		document.body.classList.add("aky-rgpd-scroll-lock");
		document.documentElement.style.setProperty("top", "-" + savedScrollY + "px");
		document.documentElement.style.setProperty("position", "fixed");
		document.documentElement.style.setProperty("left", "0");
		document.documentElement.style.setProperty("right", "0");
		document.documentElement.style.setProperty("width", "100%");
		captureMotionState();
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
		active = locked;
		setEarlyActive(locked);
		if (locked) {
			lockPage();
			return;
		}
		unlockPage();
	}

	window.addEventListener("wheel", blockScrollEvent, scrollOpts);
	window.addEventListener("touchmove", blockScrollEvent, scrollOpts);
	document.addEventListener("wheel", blockScrollEvent, scrollOpts);
	document.addEventListener("touchmove", blockScrollEvent, scrollOpts);
	window.addEventListener("scroll", onScrollWhileLocked, scrollOpts);
	document.addEventListener("scroll", onScrollWhileLocked, scrollOpts);
	window.addEventListener("keydown", blockScrollKeys, true);

	window.akyRgpdScrollLock = {
		setActive: setActive,
		isActive: function () {
			return active;
		},
	};
})(window, document);
