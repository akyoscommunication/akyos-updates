import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Optimiser maintenant";

export function SmushStatsCheck({ result, isFixBusy, onFix }) {
	const payload = result.payload || {};
	const remaining = Number(payload.remainingCount || 0);
	const total = Number(payload.totalCount || 0);
	const optimized = Number(payload.optimizedCount || Math.max(0, total - remaining));

	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			<div className="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-3">
				<div className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
					<div className="text-slate-500">Total suivies</div>
					<div className="text-sm font-semibold text-slate-900">{total}</div>
				</div>
				<div className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
					<div className="text-slate-500">Optimisées</div>
					<div className="text-sm font-semibold text-emerald-700">{optimized}</div>
				</div>
				<div className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
					<div className="text-slate-500">Restantes</div>
					<div className="text-sm font-semibold text-amber-700">{remaining}</div>
				</div>
			</div>
		</ReportCard>
	);
}
