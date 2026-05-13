import { ReportCard } from "../../../report/ReportCard";

export function SeoLegalPagesCheck({ result, isFixBusy, onFix }) {
	const pages = Array.isArray(result.payload?.pages) ? result.payload.pages : [];
	return (
		<ReportCard result={result} actions={null}>
			{pages.length ? (
				<div className="mt-3 overflow-hidden rounded-xl border border-slate-200">
					<table className="min-w-full divide-y divide-slate-200 text-sm">
						<thead className="bg-slate-50 text-left text-xs uppercase tracking-[0.06em] text-slate-500">
							<tr>
								<th className="px-3 py-2">Nom</th>
								<th className="px-3 py-2">État SEO</th>
								<th className="px-3 py-2 text-right">Action</th>
							</tr>
						</thead>
						<tbody className="divide-y divide-slate-200 bg-white">
							{pages.map((page) => (
								<tr key={page.id}>
									<td className="px-3 py-2 font-medium text-slate-900">{page.title || `Page #${page.id}`}</td>
									<td className="px-3 py-2">
										<span className={`rounded-full px-2 py-1 text-xs font-semibold ${page.noindex ? "bg-emerald-500/15 text-emerald-700" : "bg-amber-500/15 text-amber-700"}`}>
											{page.noindex ? "Noindex" : "Indexable"}
										</span>
									</td>
									<td className="px-3 py-2 text-right">
										{!page.noindex ? (
											<button
												type="button"
												disabled={isFixBusy({ actionId: "seo.toggle_indexing" })}
												className="inline-flex min-h-9 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-900 shadow-sm transition hover:border-[#0052FF4d] disabled:cursor-not-allowed disabled:opacity-60"
												onClick={() => onFix({ actionId: "seo.toggle_indexing" }, { kind: "post", postId: page.id, indexable: false })}
											>
												{isFixBusy({ actionId: "seo.toggle_indexing" }) ? <span className="inline-block h-3.5 w-3.5 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
												Passer noindex
											</button>
										) : null}
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
