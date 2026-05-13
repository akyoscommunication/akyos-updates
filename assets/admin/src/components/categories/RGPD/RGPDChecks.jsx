import { useState } from "react";
import { ReportCard } from "../../report/ReportCard";
import { buildDefaultAction } from "../../report/ReportCheckAction";

const DEFAULT_RGPD_FIX_LABEL = "Corriger";
import { RgpdPluginCheck } from "./checks/RgpdPluginCheck";
import { RgpdLegalPagesPresenceCheck } from "./checks/RgpdLegalPagesPresenceCheck";
import { RgpdAnalyticsCheck } from "./checks/RgpdAnalyticsCheck";

export function RGPDChecks({ visibleResults, isFixBusy, onFix }) {
	const [rgpdLegalPageCreatingKind, setRgpdLegalPageCreatingKind] = useState(null);
	return (
		<div className="grid gap-3">
			{visibleResults.map((result) => {
				if (result.id === "rgpd.rgpd_plugin") {
					return <RgpdPluginCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
				}
				if (result.id === "rgpd.legal_pages_presence") {
					return <RgpdLegalPagesPresenceCheck key={result.id} result={result} rgpdLegalPageCreatingKind={rgpdLegalPageCreatingKind} setRgpdLegalPageCreatingKind={setRgpdLegalPageCreatingKind} onFix={onFix} />;
				}
				if (result.id === "rgpd.analytics_tracking") {
					return <RgpdAnalyticsCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
				}
				return (
					<ReportCard key={result.id} result={result} actions={buildDefaultAction(result, isFixBusy, onFix, DEFAULT_RGPD_FIX_LABEL)} />
				);
			})}
		</div>
	);
}
