export function ComposerProposalsModal({ open, content, unavailableSlugs, onClose, onCopy }) {
	if (!open) {
		return null;
	}

	const list = Array.isArray(unavailableSlugs) ? unavailableSlugs.filter(Boolean) : [];
	const hasLines = typeof content === "string" && content.trim().length > 0;

	return (
		<div className="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/55 p-4">
			<div className="max-h-[90vh] w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
				<div className="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
					<div>
						<h3 className="m-0 text-xl font-semibold text-slate-900">Lignes pour composer.json</h3>
						<p className="mt-1 text-sm text-slate-500">
							Uniquement les plugins présents sur WordPress.org (installables via WPackagist). Colle-les dans la section{" "}
							<code className="rounded bg-slate-100 px-1 py-0.5 text-xs">require</code> de ton composer.json.
						</p>
					</div>
					<button
						type="button"
						className="inline-flex min-h-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-900 shadow-sm transition hover:border-[#0052FF4d]"
						onClick={onClose}
					>
						Fermer
					</button>
				</div>
				<div className="max-h-[50vh] overflow-auto px-5 py-4">
					{list.length > 0 ? (
						<div className="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
							<div className="font-semibold">Non listés sur WordPress.org (pas de ligne wpackagist ici)</div>
							<ul className="mt-2 list-inside list-disc text-amber-900/90">
								{list.map((s) => (
									<li key={s} className="font-mono text-xs">
										{s}
									</li>
								))}
							</ul>
						</div>
					) : null}
					{hasLines ? (
						<pre className="m-0 whitespace-pre-wrap break-words rounded-xl border border-slate-200 bg-slate-950 p-4 text-xs leading-relaxed text-slate-100">
							{content}
						</pre>
					) : (
						<p className="m-0 text-sm text-slate-600">
							Aucune ligne générée : tous les plugins manquants sont hors annuaire WordPress.org ou l’API n’a pas répondu.
						</p>
					)}
				</div>
				<div className="flex items-center justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4">
					<button
						type="button"
						disabled={!hasLines}
						className="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#0052FF] bg-[#0052FF] px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0047db] disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-200 disabled:text-slate-500"
						onClick={onCopy}
					>
						Copier le texte
					</button>
				</div>
			</div>
		</div>
	);
}
