export function GitignoreProposalsModal({ open, content, installationType, onClose, onCopy }) {
	if (!open) {
		return null;
	}

	const hasLines = typeof content === "string" && content.trim().length > 0;
	const isBedrock = installationType === "bedrock";
	const pathHint = isBedrock
		? "Bedrock : place ces lignes dans le .gitignore à la racine du dépôt (là où se trouve composer.json), après une règle du type « web/app/plugins/* » qui ignore les plugins."
		: "Installation classique : place ces lignes dans le .gitignore à la racine du dépôt (souvent la racine WordPress), après une règle qui ignore « wp-content/plugins/* » ou équivalent.";

	return (
		<div className="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/55 p-4">
			<div className="max-h-[90vh] w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
				<div className="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
					<div>
						<h3 className="m-0 text-xl font-semibold text-slate-900">Exceptions .gitignore</h3>
						<p className="mt-1 text-sm text-slate-500">{pathHint}</p>
					</div>
					<button
						type="button"
						className="inline-flex min-h-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-900 shadow-sm transition hover:border-[rgb(var(--au-primary-rgb)/0.3)]"
						onClick={onClose}
					>
						Fermer
					</button>
				</div>
				<div className="max-h-[50vh] overflow-auto px-5 py-4">
					<div className="mb-3 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
						{isBedrock ? "Type Bedrock" : "Type classique"}
					</div>
					{hasLines ? (
						<pre className="m-0 whitespace-pre-wrap break-words rounded-xl border border-slate-200 bg-slate-950 p-4 text-xs leading-relaxed text-slate-100">
							{content}
						</pre>
					) : (
						<p className="m-0 text-sm text-slate-600">Aucune ligne générée.</p>
					)}
				</div>
				<div className="flex items-center justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4">
					<button
						type="button"
						disabled={!hasLines}
						className="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--au-primary)] bg-[var(--au-primary)] px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--au-primary-hover)] disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-200 disabled:text-slate-500"
						onClick={onCopy}
					>
						Copier le texte
					</button>
				</div>
			</div>
		</div>
	);
}
