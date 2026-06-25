import { CategorySwitches } from "../ui/CategorySwitches";

export function RelaunchAnalysisModal({ open, onClose, categoryNames, selection, onToggle, onConfirm, running }) {
	if (!open) {
		return null;
	}

	return (
		<div
			className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-[2px]"
			role="presentation"
			onMouseDown={(e) => {
				if (e.target === e.currentTarget && !running) {
					onClose();
				}
			}}
		>
			<div
				className="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
				role="dialog"
				aria-modal="true"
				aria-labelledby="akyos-relaunch-title"
			>
				<h2 id="akyos-relaunch-title" className="text-xl font-semibold text-slate-900">
					Relancer l’analyse
				</h2>
				<p className="mt-2 text-sm text-slate-600">
					Choisis les catégories à analyser. Les catégories déjà incluses dans ton dernier rapport sont activées par défaut.
				</p>
				<CategorySwitches names={categoryNames} value={selection} onToggle={onToggle} disabled={running} />
				<div className="mt-6 flex flex-wrap justify-end gap-2">
					<button
						type="button"
						className="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 font-semibold text-slate-800 shadow-sm"
						onClick={onClose}
						disabled={running}
					>
						Annuler
					</button>
					<button
						type="button"
						className="inline-flex min-h-11 items-center justify-center rounded-xl border border-[var(--au-primary)] bg-[var(--au-primary)] px-4 py-2 font-semibold text-white shadow-sm"
						onClick={onConfirm}
						disabled={running}
					>
						{running ? "Analyse…" : "Lancer"}
					</button>
				</div>
			</div>
		</div>
	);
}
