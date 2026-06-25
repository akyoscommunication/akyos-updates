import { ReportCard } from "../../../report/ReportCard";

export function RgpdLegalPagesPresenceCheck({ result, rgpdLegalPageCreatingKind, setRgpdLegalPageCreatingKind, onFix }) {
	const requirements = Array.isArray(result.payload?.requirements) ? result.payload.requirements : [];
	return (
		<ReportCard result={result} actions={null}>
			{requirements.length ? (
				<div className="mt-3 overflow-hidden rounded-xl border border-slate-200">
					<table className="min-w-full divide-y divide-slate-200 text-sm">
						<thead className="bg-slate-50 text-left text-xs uppercase tracking-[0.06em] text-slate-500"><tr><th className="px-3 py-2">Page attendue</th><th className="px-3 py-2">État</th><th className="px-3 py-2 text-right">Action</th></tr></thead>
						<tbody className="divide-y divide-slate-200 bg-white">
							{requirements.map((req) => (
								<tr key={req.key}>
									<td className="px-3 py-2 font-medium text-slate-900">{req.label}</td>
									<td className="px-3 py-2">{req.found ? <span className="rounded-full bg-emerald-500/15 px-2 py-1 text-xs font-semibold text-emerald-700">Trouvée</span> : <span className="rounded-full bg-amber-500/15 px-2 py-1 text-xs font-semibold text-amber-700">Manquante</span>}</td>
									<td className="px-3 py-2 text-right">
										{req.found && req.page?.editUrl ? <a href={req.page.editUrl} className="inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-900 shadow-sm transition hover:border-[rgb(var(--au-primary-rgb)/0.3)]">Éditer</a> : !req.found ? (rgpdLegalPageCreatingKind === req.key ? <span className="inline-flex min-h-9 min-w-[88px] items-center justify-center rounded-lg bg-[rgb(var(--au-primary-rgb)/0.85)] px-3 py-1.5" aria-busy="true" aria-live="polite"><span className="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/35 border-t-white" aria-hidden /><span className="sr-only">Création en cours</span></span> : <button type="button" className="inline-flex min-h-9 items-center justify-center rounded-lg bg-[var(--au-primary)] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[var(--au-primary-hover)] disabled:opacity-70" disabled={rgpdLegalPageCreatingKind !== null} onClick={() => { setRgpdLegalPageCreatingKind(req.key); Promise.resolve(onFix({ id: "rgpd.legal_pages_presence", actionId: "rgpd.create_legal_page" }, { kind: req.key })).finally(() => { setRgpdLegalPageCreatingKind(null); }); }}>Créer</button>) : null}
									</td>
								</tr>
							))}
						</tbody>
					</table>
				</div>
			) : null}
		</ReportCard>
	);
}
