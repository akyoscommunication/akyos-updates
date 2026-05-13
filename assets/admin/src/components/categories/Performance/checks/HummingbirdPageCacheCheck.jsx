import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Appliquer config Hummingbird";

export function HummingbirdPageCacheCheck({ result, isFixBusy, onFix }) {
	const payload = result.payload || {};
	const rows = [
		["Page Caching", Boolean(payload.pageCachingEnabled)],
		["Préchargement", Boolean(payload.preloadEnabled)],
		["Varnish", Boolean(payload.varnishEnabled)],
		["Clear Interval", Boolean(payload.clearIntervalEnabled)],
		["Clear cache maj page/article", Boolean(payload.clearOnUpdateEnabled)],
	];
	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			<div className="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
				{rows.map(([label, enabled]) => (
					<div key={label} className="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
						<span className="font-semibold text-slate-800">{label}</span>
						<span className={`rounded-full px-2 py-1 font-semibold ${enabled ? "bg-emerald-500/15 text-emerald-700" : "bg-amber-500/15 text-amber-700"}`}>{enabled ? "Actif" : "Inactif"}</span>
					</div>
				))}
			</div>
		</ReportCard>
	);
}
