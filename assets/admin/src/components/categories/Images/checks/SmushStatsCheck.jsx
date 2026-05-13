import { ReportCard } from "../../../report/ReportCard";

export function SmushStatsCheck({ result }) {
	const payload = result.payload || {};
	const remaining = Number(payload.remainingCount || 0);
	const total = Number(payload.totalCount || 0);
	const optimized = Number(payload.optimizedCount || Math.max(0, total - remaining));
	const dashboardUrl = typeof payload.smushDashboardUrl === "string" ? payload.smushDashboardUrl : "";
	const showOptimiser = dashboardUrl !== "";

	const actions =
		<a
			className="mt-2 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d]"
			href={dashboardUrl}
			target="_blank"
			rel="noopener noreferrer"
		>
			Optimiser les images
		</a>


	return (
		<ReportCard result={result} actions={actions}>
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
