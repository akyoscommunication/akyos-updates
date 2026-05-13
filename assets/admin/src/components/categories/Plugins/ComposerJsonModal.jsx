export function ComposerJsonModal({ open, content, targetPath, onClose, onCopy }) {
	if (!open) {
		return null;
	}

	return (
		<div className="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/55 p-4">
			<div className="max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
				<div className="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
					<div>
						<h3 className="m-0 text-xl font-semibold text-slate-900">Générer le composer.json</h3>
						<p className="mt-1 text-sm text-slate-500">Chemin cible: {targetPath || "composer.json"}</p>
					</div>
					<button
						type="button"
						className="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-900 shadow-sm transition hover:border-[#0052FF4d]"
						onClick={onClose}
					>
						Fermer
					</button>
				</div>
				<div className="max-h-[62vh] overflow-auto bg-slate-950 p-5">
					<pre className="m-0 whitespace-pre-wrap break-words text-xs leading-relaxed text-slate-100">{content}</pre>
				</div>
				<div className="flex items-center justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4">
					<button
						type="button"
						className="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d]"
						onClick={onCopy}
					>
						Copier le fichier
					</button>
				</div>
			</div>
		</div>
	);
}
