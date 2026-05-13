export function WordpressUpdatedModal({ open, onClose }) {
	if (!open) {
		return null;
	}

	return (
		<div className="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
			<div
				className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
				role="dialog"
				aria-modal="true"
				aria-labelledby="akyos-wordpress-updated-title"
			>
				<h2
					id="akyos-wordpress-updated-title"
					className="mb-3 text-2xl font-normal text-slate-900"
					style={{ fontFamily: "Calistoga, serif" }}
				>
					WordPress mis à jour
				</h2>
				<p className="mb-5 text-sm leading-relaxed text-slate-600">
					La mise à jour est terminée et le rapport affiché correspond maintenant à la version en place.
				</p>
				<div className="flex justify-end">
					<button
						type="button"
						className="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#0052FF] bg-[#0052FF] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0047db]"
						onClick={onClose}
					>
						Fermer
					</button>
				</div>
			</div>
		</div>
	);
}
