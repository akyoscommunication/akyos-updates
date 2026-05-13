import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Appliquer Advanced Hummingbird";

export function HummingbirdAdvancedCheck({ result, isFixBusy, onFix }) {
	const payload = result.payload || {};
	const prefetchMissing = Array.isArray(payload.prefetchMissing) ? payload.prefetchMissing : [];
	const prefetchOk = prefetchMissing.length === 0;
	const preconnectMissing = Array.isArray(payload.preconnectMissing) ? payload.preconnectMissing : [];
	const preconnectOk = preconnectMissing.length === 0;
	const rows = [
		["Query strings retirés des assets", Boolean(payload.queryStringEnabled)],
		["Emoji JS/CSS WordPress retirés", Boolean(payload.emojiEnabled)],
		["Prefetch DNS (liste recommandée)", prefetchOk],
		["Preconnect (liste recommandée)", preconnectOk],
	];
	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			<div className="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
				{rows.map(([label, enabled]) => (
					<div key={label} className="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
						<span className="font-semibold text-slate-800">{label}</span>
						<span className={`rounded-full px-2 py-1 font-semibold ${enabled ? "bg-emerald-500/15 text-emerald-700" : "bg-amber-500/15 text-amber-700"}`}>{enabled ? "OK" : "À faire"}</span>
					</div>
				))}
			</div>
		</ReportCard>
	);
}
