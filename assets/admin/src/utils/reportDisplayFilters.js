const BRANDA_BACKOFFICE_CHECK_IDS = new Set([
	"backoffice.branda_smtp",
	"backoffice.branda_dashboard_widgets",
	"backoffice.admin_lite_user",
]);

const HUMMINGBIRD_CHECK_PREFIX = "performance.hummingbird_";
const SEO_FALLBACK_CHECK_IDS = new Set(["seo.legal_pages_noindex"]);

export function filterDisplayedCheckResults(selectedResults, safeStep, presence) {
	if (!presence) {
		return selectedResults;
	}
	if (safeStep === "Sécurité" && presence.defenderActive === false) {
		return [];
	}
	if (safeStep === "Back-office" && presence.brandaActive === false) {
		return selectedResults.filter((result) => !BRANDA_BACKOFFICE_CHECK_IDS.has(result.id));
	}
	if (safeStep === "Performance" && presence.hummingbirdActive === false) {
		return selectedResults.filter((result) => !String(result.id || "").startsWith(HUMMINGBIRD_CHECK_PREFIX));
	}
	if (safeStep === "SEO") {
		const withoutLegacySitemap = selectedResults.filter((result) => result.id !== "seo.sitemap");
		if (presence.seoPluginActive === false) {
			return withoutLegacySitemap.filter((result) => SEO_FALLBACK_CHECK_IDS.has(result.id));
		}
		return withoutLegacySitemap;
	}
	if (safeStep === "Images" && presence.smushActive === false) {
		return [];
	}
	return selectedResults;
}

export function findSeoPluginProviderLabel(selectedResults) {
	const row = selectedResults.find((result) => result.id === "seo.plugin") || null;
	return row?.payload?.provider?.label || "";
}
