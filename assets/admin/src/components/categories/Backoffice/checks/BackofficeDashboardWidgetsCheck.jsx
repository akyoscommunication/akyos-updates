import { ReportCard } from "../../../report/ReportCard";

export function BackofficeDashboardWidgetsCheck({ result, isFixBusy, onFix }) {
	const p = result.payload || {};
	const brandaActive = Boolean(p.brandaActive);
	const moduleActive = Boolean(p.moduleActive);
	const availableCount = Number(p.availableCount || 0);
	const allHidden = Boolean(p.allHidden);
	return (
		<ReportCard result={result} actions={null}>
			<div className="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">
				<div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
					<div className="flex justify-between rounded-lg border border-slate-200 bg-white px-3 py-2"><span className="font-semibold text-slate-800">Module Branda</span><span className={moduleActive ? "font-semibold text-emerald-700" : "font-semibold text-amber-700"}>{moduleActive ? "Actif" : "Inactif"}</span></div>
					<div className="flex justify-between rounded-lg border border-slate-200 bg-white px-3 py-2"><span className="font-semibold text-slate-800">Widgets masqués</span><span className="font-semibold text-slate-900">{p.hiddenCount ?? 0} / {availableCount || "—"}</span></div>
				</div>
				{brandaActive && !moduleActive ? <button type="button" disabled={isFixBusy(result)} className="mt-3 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d] disabled:cursor-not-allowed disabled:opacity-60" onClick={() => onFix(result)}>{isFixBusy(result) ? <span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}Corriger (activer le module)</button> : null}
				{brandaActive && moduleActive && availableCount > 0 && !allHidden ? <button type="button" disabled={isFixBusy(result)} className="mt-3 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d] disabled:cursor-not-allowed disabled:opacity-60" onClick={() => onFix(result)}>{isFixBusy(result) ? <span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}Masquer tous les widgets</button> : null}
				{brandaActive && moduleActive && allHidden ? <p className="mt-2 text-xs text-slate-600">Tous les widgets répertoriés sont masqués. Tu peux relancer une analyse après avoir visité le dashboard pour rafraîchir la liste.</p> : null}
			</div>
		</ReportCard>
	);
}
