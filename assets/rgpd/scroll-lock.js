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
		document.body.appendChild(shield);
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
			return;
		}
		applied = true;
		savedScrollY = window.scrollY || window.pageYOffset || 0;
		document.documentElement.classList.add("aky-rgpd-scroll-lock");
		document.documentElement.style.setProperty("top", "-" + savedScrollY + "px");
		document.documentElement.style.setProperty("position", "fixed");
		document.documentElement.style.setProperty("left", "0");
		document.documentElement.style.setProperty("right", "0");
		document.documentElement.style.setProperty("width", "100%");
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
		document.documentElement.classList.remove("aky-rgpd-scroll-lock");
		document.documentElement.style.removeProperty("position");
		document.documentElement.style.removeProperty("top");
		document.documentElement.style.removeProperty("left");
		document.documentElement.style.removeProperty("right");
		document.documentElement.style.removeProperty("width");
		window.scrollTo(0, savedScrollY);
	}

	function setActive(locked) {
		active = locked;
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
