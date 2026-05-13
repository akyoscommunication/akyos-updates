import { Fragment } from "react";
import { ReportMissingPluginCard } from "../../report/ReportMissingPluginCard";
import { ReportCard } from "../../report/ReportCard";
import { buildDefaultAction } from "../../report/ReportCheckAction";

const DEFAULT_PERFORMANCE_FIX_LABEL = "Corriger";
const PERFORMANCE_ACTION_LABELS = {
	"performance.hummingbird_enable_gzip": "Activer Gzip Hummingbird",
};

function performanceFixLabel(actionId) {
	return PERFORMANCE_ACTION_LABELS[actionId] ?? DEFAULT_PERFORMANCE_FIX_LABEL;
}
import { HummingbirdPageCacheCheck } from "./checks/HummingbirdPageCacheCheck";
import { HummingbirdAdvancedCheck } from "./checks/HummingbirdAdvancedCheck";

export function PerformanceChecks({
	results,
	pluginPresence,
	onActivatePlugin,
	activatingPluginKey,
	isFixBusy,
	onFix,
}) {
	return (
		<Fragment>
			{pluginPresence?.hummingbirdActive === false ? (
				<ReportMissingPluginCard
					title="Hummingbird"
					description={
						pluginPresence.hummingbirdActivateFile
							? "Hummingbird est installé mais désactivé. Active-le pour les contrôles Page cache, Gzip et Advanced."
							: "Hummingbird n'est pas installé sur ce site. Ajoute le plugin (WPMU DEV), puis relance une analyse complète."
					}
					activateFile={pluginPresence.hummingbirdActivateFile || null}
					activateButtonLabel="Activer Hummingbird"
					onActivate={(file) => onActivatePlugin("hummingbird", file)}
					activating={activatingPluginKey === "hummingbird"}
				/>
			) : null}
			<div className="grid gap-3">
				{results.map((result) => {
					if (result.id === "performance.hummingbird_page_cache") {
						return <HummingbirdPageCacheCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
					}
					if (result.id === "performance.hummingbird_advanced") {
						return <HummingbirdAdvancedCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
					}
					return (
						<ReportCard
							key={result.id}
							result={result}
							actions={buildDefaultAction(result, isFixBusy, onFix, performanceFixLabel(result.actionId))}
						/>
					);
				})}
			</div>
		</Fragment>
	);
}
