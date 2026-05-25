import { Fragment } from "react";
import { ReportMissingPluginCard } from "../../report/ReportMissingPluginCard";
import { ReportCard } from "../../report/ReportCard";
import { buildDefaultAction } from "../../report/ReportCheckAction";

const DEFAULT_SEO_FIX_LABEL = "Corriger";
import { SeoSitemapSection } from "./checks/SeoSitemapSection";
import { SeoLegalPagesCheck } from "./checks/SeoLegalPagesCheck";
import { SeoIndexabilityCheck } from "./checks/SeoIndexabilityCheck";

export function SEOChecks({
	results,
	pluginPresence,
	onActivatePlugin,
	activatingPluginKey,
	isFixBusy,
	onFix,
}) {
	return (
		<Fragment>
			{pluginPresence?.seoPluginActive === false ? (
				<ReportMissingPluginCard
					title="Plugin SEO"
					description={
						pluginPresence.seoActivateFile && pluginPresence.seoActivateLabel
							? `${pluginPresence.seoActivateLabel} est installé mais désactivé. Active-le pour retrouver les contrôles SEO complets (plugin détecté, indexation).`
							: `Aucun Yoast SEO ni SmartCrawl n'est installé ou actif. Seul le contrôle « Pages légales noindex » reste affiché ; installe un plugin SEO depuis ton catalogue d'extensions si besoin.`
					}
					activateFile={pluginPresence.seoActivateFile || null}
					activateButtonLabel={
						pluginPresence.seoActivateLabel ? `Activer ${pluginPresence.seoActivateLabel}` : "Activer le plugin"
					}
					onActivate={(file) => onActivatePlugin("seo", file)}
					activating={activatingPluginKey === "seo"}
				/>
			) : null}
			{pluginPresence?.seoSitemapUrl ? <SeoSitemapSection sitemapUrl={pluginPresence.seoSitemapUrl} /> : null}
			<div className="grid gap-3">
			{results.map((result) => {
				if (result.id === "seo.sitemap") {
					return null;
				}
				if (result.id === "seo.legal_pages_noindex") {
					return <SeoLegalPagesCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
				}
				if (result.id === "seo.indexability") {
					return <SeoIndexabilityCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
				}
				return (
					<ReportCard key={result.id} result={result} actions={buildDefaultAction(result, isFixBusy, onFix, DEFAULT_SEO_FIX_LABEL)} />
				);
			})}
			</div>
		</Fragment>
	);
}
