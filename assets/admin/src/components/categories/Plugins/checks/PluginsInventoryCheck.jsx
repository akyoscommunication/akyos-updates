import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";
import { PluginsTable } from "../PluginsTable";

const PRIMARY_FIX_LABEL = "Générer le composer.json";

export function PluginsInventoryCheck({ result, isFixBusy, onFix }) {
	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			<PluginsTable plugins={result.payload?.plugins || []} composerJsonReadable={result.payload?.composerJsonReadable === true} />
		</ReportCard>
	);
}
