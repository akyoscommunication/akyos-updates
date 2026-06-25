import { ReportCard } from "../../../report/ReportCard";

export function DefenderMaskLoginCheck({ result, maskLoginValue, setMaskLoginValue, isFixBusy, onFix }) {
	return (
		<ReportCard result={result} actions={null}>
			<div className="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
				<label className="mb-1 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Slug Mask Login</label>
				<input
					type="text"
					className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900"
					value={maskLoginValue}
					onChange={(event) => setMaskLoginValue(event.target.value)}
					placeholder="ex: mon-espace-admin"
				/>
				<button
					type="button"
					className="mt-2 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[rgb(var(--au-primary-rgb)/0.3)] disabled:cursor-not-allowed disabled:opacity-60"
					onClick={() => onFix(result, { ...(result.payload || {}), maskUrl: maskLoginValue })}
					disabled={!maskLoginValue.trim() || isFixBusy(result)}
				>
					{isFixBusy(result) ? <span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
					Enregistrer
				</button>
			</div>
		</ReportCard>
	);
}
