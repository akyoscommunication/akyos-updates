import { getVisibleReportCategoryNames } from "../../utils/reportCategories";
import { filterDisplayedCheckResults, findSeoPluginProviderLabel } from "../../utils/reportDisplayFilters";
import { useReportPluginActivation } from "../../hooks/useReportPluginActivation";
import { ReportCategoryTabs } from "./ReportCategoryTabs";
import { WordpressChecks } from "../categories/Wordpress/WordpressChecks";
import { ImagesChecks } from "../categories/Images/ImagesChecks";
import { SEOChecks } from "../categories/SEO/SEOChecks";
import { SecurityChecks } from "../categories/Security/SecurityChecks";
import { PerformanceChecks } from "../categories/Performance/PerformanceChecks";
import { BackofficeChecks } from "../categories/Backoffice/BackofficeChecks";
import { RGPDChecks } from "../categories/RGPD/RGPDChecks";
import { PluginsChecks } from "../categories/Plugins/PluginsChecks";

function makeFixBusyKey(partial) {
	if (!partial?.actionId) {
		return "";
	}
	const id = partial.id && String(partial.id).length > 0 ? String(partial.id) : "";
	return id ? `${id}::${String(partial.actionId)}` : `::${String(partial.actionId)}`;
}

export function ReportSection({
	report,
	currentStep,
	onStepChange,
	onFix,
	fixingKey = null,
	onPluginPresenceChange,
	onNotify,
	onCategoryRecheck,
}) {
	if (!report) {
		return null;
	}

	const presence = report.pluginPresence;
	const isFixBusy = (partial) => Boolean(fixingKey) && fixingKey === makeFixBusyKey(partial);

	const { activatingSection, handleActivatePlugin } = useReportPluginActivation({
		onPluginPresenceChange,
		onNotify,
		onCategoryRecheck,
	});

	const categories = report.categories || {};
	const orderedCategories = getVisibleReportCategoryNames(report);
	const safeStep = orderedCategories.includes(currentStep) ? currentStep : orderedCategories[0];
	const allRows = report.results || [];
	const selectedResults = allRows.filter((result) => result.category === safeStep);
	const displayResults = filterDisplayedCheckResults(selectedResults, safeStep, presence);

	const sliceCategory = (categoryName) => allRows.filter((r) => r.category === categoryName);
	const checksForCategory = (categoryName) =>
		filterDisplayedCheckResults(sliceCategory(categoryName), categoryName, presence);

	const pluginPresenceProps = {
		pluginPresence: presence,
		onActivatePlugin: handleActivatePlugin,
		activatingPluginKey: activatingSection,
	};

	return (
		<section className="relative z-10 my-5 grid grid-cols-1 gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-lg lg:grid-cols-[320px_minmax(0,1fr)]">
			<ReportCategoryTabs
				orderedCategories={orderedCategories}
				categories={categories}
				safeStep={safeStep}
				onStepChange={onStepChange}
				presence={presence}
			/>
			<div className="min-w-0">
				<div className="mb-3 flex items-baseline justify-between gap-3">
					<div className="flex flex-wrap items-center gap-2">
						<div className="text-[26px] font-normal text-slate-900" style={{ fontFamily: "Calistoga, serif" }}>
							{safeStep}
						</div>
					</div>
					<div className="text-xs text-slate-500">Total {displayResults.length} checks</div>
				</div>
				<div className={safeStep === "WordPress" ? "" : "hidden"}>
					<WordpressChecks
						categoryResults={sliceCategory("WordPress")}
						visibleResults={checksForCategory("WordPress")}
						isFixBusy={isFixBusy}
						onFix={onFix}
					/>
				</div>
				<div className={safeStep === "Images" ? "" : "hidden"}>
					<ImagesChecks
						categoryResults={sliceCategory("Images")}
						visibleResults={checksForCategory("Images")}
						isFixBusy={isFixBusy}
						onFix={onFix}
						{...pluginPresenceProps}
					/>
				</div>
				<div className={safeStep === "SEO" ? "" : "hidden"}>
					<SEOChecks
						results={checksForCategory("SEO")}
						isFixBusy={isFixBusy}
						onFix={onFix}
						{...pluginPresenceProps}
					/>
				</div>
				<div className={safeStep === "Sécurité" ? "" : "hidden"}>
					<SecurityChecks
						categoryResults={sliceCategory("Sécurité")}
						visibleResults={checksForCategory("Sécurité")}
						isFixBusy={isFixBusy}
						onFix={onFix}
						{...pluginPresenceProps}
					/>
				</div>
				<div className={safeStep === "Performance" ? "" : "hidden"}>
					<PerformanceChecks
						results={checksForCategory("Performance")}
						isFixBusy={isFixBusy}
						onFix={onFix}
						{...pluginPresenceProps}
					/>
				</div>
				<div className={safeStep === "Back-office" ? "" : "hidden"}>
					<BackofficeChecks
						categoryResults={sliceCategory("Back-office")}
						visibleResults={checksForCategory("Back-office")}
						isFixBusy={isFixBusy}
						onFix={onFix}
						{...pluginPresenceProps}
					/>
				</div>
				<div className={safeStep === "RGPD" ? "" : "hidden"}>
					<RGPDChecks visibleResults={checksForCategory("RGPD")} isFixBusy={isFixBusy} onFix={onFix} />
				</div>
				<div className={safeStep === "Plugins" ? "" : "hidden"}>
					<PluginsChecks
						categoryResults={sliceCategory("Plugins")}
						visibleResults={checksForCategory("Plugins")}
						isFixBusy={isFixBusy}
						onFix={onFix}
					/>
				</div>
			</div>
		</section>
	);
}
