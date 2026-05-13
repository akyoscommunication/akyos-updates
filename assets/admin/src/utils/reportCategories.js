export const REPORT_CATEGORY_TAB_ORDER = [
	"WordPress",
	"Images",
	"Plugins",
	"Performance",
	"Sécurité",
	"SEO",
	"Back-office",
	"RGPD",
];

export function getVisibleReportCategoryNames(report) {
	if (!report) {
		return [];
	}
	const categories = report.categories || {};
	const presence = report.pluginPresence;
	const included =
		Array.isArray(report.includedCategories) && report.includedCategories.length > 0
			? new Set(report.includedCategories)
			: null;

	return REPORT_CATEGORY_TAB_ORDER.filter((name) => {
		if (!Object.prototype.hasOwnProperty.call(categories, name)) {
			return false;
		}
		if (included && !included.has(name)) {
			return false;
		}
		if (name === "Images") {
			if (included?.has("Images")) {
				return true;
			}
			return (categories[name]?.total || 0) > 0 || presence?.smushActive === false;
		}
		return true;
	});
}
