export function shouldHideDefaultAction(result) {
	return (
		result.id === "images.smush_nextgen_config" ||
		result.id === "images.smush_resize_large" ||
		result.id === "security.defender_mask_login" ||
		result.id === "security.defender_recaptcha_keys" ||
		result.id === "seo.indexability" ||
		result.id === "wordpress.version" ||
		result.id === "backoffice.branda_smtp" ||
		result.id === "backoffice.admin_lite_user" ||
		result.id === "backoffice.branda_dashboard_widgets" ||
		result.id === "rgpd.legal_pages_presence"
	);
}

export function buildDefaultAction(result, isFixBusy, onFix, label, onBeforeClick = null) {
	if (!result.actionable || !result.actionId || shouldHideDefaultAction(result)) {
		return null;
	}
	return (
		<button
			type="button"
			disabled={isFixBusy(result)}
			className="mt-2 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d] disabled:cursor-not-allowed disabled:opacity-60"
			onClick={() => {
				if (typeof onBeforeClick === "function") {
					onBeforeClick();
				}
				onFix(result);
			}}
		>
			{isFixBusy(result) ? <span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
			{label}
		</button>
	);
}
