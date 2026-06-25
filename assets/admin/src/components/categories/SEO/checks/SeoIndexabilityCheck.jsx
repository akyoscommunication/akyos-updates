import { ReportCard } from "../../../report/ReportCard";

export function SeoIndexabilityCheck({ result, isFixBusy, onFix }) {
	const rows = Array.isArray(result.payload?.rows) ? result.payload.rows : [];
	return (
		<ReportCard result={result} actions={null}>
			{rows.length ? (
				<div className="mt-3 overflow-hidden rounded-xl border border-slate-200">
					<table className="min-w-full divide-y divide-slate-200 text-sm">
						<thead className="bg-slate-50 text-left text-xs uppercase tracking-[0.06em] text-slate-500">
							<tr>
								<th className="px-3 py-2">Type</th>
								<th className="px-3 py-2">Nom</th>
								<th className="px-3 py-2">Réglage</th>
								<th className="px-3 py-2 text-right">Action</th>
							</tr>
						</thead>
						<tbody className="divide-y divide-slate-200 bg-white">
							{rows.map((row) => (
								<tr key={`${row.kind}:${row.name}`}>
									<td className="px-3 py-2 text-slate-500">{row.kind === "taxonomy" ? "Taxonomie" : "Post type"}</td>
									<td className="px-3 py-2 font-medium text-slate-900">{row.label || row.name}</td>
									<td className="px-3 py-2">
										<span
											className={
												row.indexable
													? "inline-flex rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-800"
													: "inline-flex rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-semibold text-amber-900"
											}
										>
											{row.indexable ? "Indexable" : "Noindex"}
										</span>
									</td>
									<td className="px-3 py-2 text-right">
										<button
											type="button"
											disabled={isFixBusy({ id: "seo.indexability", actionId: "seo.toggle_indexing" })}
											className="inline-flex min-h-9 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-900 shadow-sm transition hover:border-[rgb(var(--au-primary-rgb)/0.3)] disabled:cursor-not-allowed disabled:opacity-60"
											onClick={() => onFix(result, { kind: row.kind, name: row.name, indexable: !row.indexable })}
										>
											{isFixBusy({ id: "seo.indexability", actionId: "seo.toggle_indexing" }) ? <span className="inline-block h-3.5 w-3.5 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
											{row.indexable ? "Passer noindex" : "Passer indexable"}
										</button>
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
