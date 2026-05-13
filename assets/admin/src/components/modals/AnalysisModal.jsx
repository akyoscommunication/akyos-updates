export function AnalysisModal({ open, event, cursor, total }) {
	if (!open) {
		return null;
	}

	const progress = total > 0 ? Math.round((cursor / total) * 100) : 0;
	const stepTitle =
		(typeof event?.nextCheck?.title === "string" && event.nextCheck.title.trim() !== ""
			? event.nextCheck.title
			: typeof event?.title === "string"
				? event.title
				: "") || "";

	return (
		<div className="fixed inset-0 z-[90] grid place-items-center bg-slate-900/50 p-4 backdrop-blur">
			<div className="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-2xl">
				<h2 className="mb-4 text-center text-3xl font-normal text-slate-900" style={{ fontFamily: "Calistoga, serif" }}>
					Analyse en cours
				</h2>
				<div className="mb-3">
					<div className="h-3.5 w-full overflow-hidden rounded-full bg-slate-100">
						<div className="h-full rounded-full bg-gradient-to-r from-[#0052FF] to-[#4D7CFF] transition-[width] duration-200" style={{ width: `${progress}%` }} />
					</div>
					<div className="mt-1.5 text-center text-sm text-slate-500">
						{cursor} / {total}
					</div>
				</div>
				<div className="mx-auto my-5 h-14 w-14 animate-spin rounded-full border-8 border-[#0052FF1f] border-t-[#0052FF]" />
				<div className="grid min-h-[56px] content-center rounded-xl border border-slate-200 bg-white p-4">
					{event ? (
						<div className="text-center text-base font-semibold text-slate-900 sm:text-lg">{stepTitle || "…"}</div>
					) : (
						<div className="text-center text-slate-500">Initialisation de l'analyse...</div>
					)}
				</div>
			</div>
		</div>
	);
}
