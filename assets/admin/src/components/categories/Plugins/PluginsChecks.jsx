import { useEffect, useState } from "react";
import { ReportCard } from "../../report/ReportCard";
import { buildDefaultAction } from "../../report/ReportCheckAction";

const DEFAULT_PLUGINS_FIX_LABEL = "Corriger";
const PLUGINS_ACTION_LABELS = {
	"plugins.generate_composer_json": "Générer le composer.json",
	"plugins.generate_composer_guidance": "Mettre à jour composer.json",
	"plugins.generate_gitignore_exceptions": "Mettre à jour .gitignore",
};

function pluginsFixLabel(actionId) {
	return PLUGINS_ACTION_LABELS[actionId] ?? DEFAULT_PLUGINS_FIX_LABEL;
}
import { PluginsInventoryCheck } from "./checks/PluginsInventoryCheck";
import { PluginsComposerAuditCheck } from "./checks/PluginsComposerAuditCheck";

export function PluginsChecks({ categoryResults, visibleResults, isFixBusy, onFix }) {
	const [composerAuditBadgeDismissed, setComposerAuditBadgeDismissed] = useState({
		composer: false,
		gitignore: false,
	});
	const composerAuditMissingKey =
		categoryResults
			.filter((r) => r.id === "plugins.composer_audit")
			.map((r) => (Array.isArray(r.payload?.missingPlugins) ? r.payload.missingPlugins.join("\0") : ""))
			.join("|") ?? "";

	useEffect(() => {
		setComposerAuditBadgeDismissed({ composer: false, gitignore: false });
	}, [composerAuditMissingKey]);

	return (
		<div className="grid gap-3">
			{visibleResults.map((result) => {
				if (result.id === "plugins.inventory") {
					return <PluginsInventoryCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
				}
				if (result.id === "plugins.composer_audit" && Array.isArray(result.payload?.missingPlugins) && result.payload.missingPlugins.length > 0) {
					return <PluginsComposerAuditCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} composerAuditBadgeDismissed={composerAuditBadgeDismissed} setComposerAuditBadgeDismissed={setComposerAuditBadgeDismissed} />;
				}
				return (
					<ReportCard
						key={result.id}
						result={result}
						actions={buildDefaultAction(result, isFixBusy, onFix, pluginsFixLabel(result.actionId))}
					/>
				);
			})}
		</div>
	);
}
