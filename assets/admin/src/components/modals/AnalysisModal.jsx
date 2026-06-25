function resolveStepTitle(event) {
	if (typeof event?.nextCheck?.title === "string" && event.nextCheck.title.trim() !== "") {
		return event.nextCheck.title.trim();
	}
	if (typeof event?.title === "string" && event.title.trim() !== "") {
		return event.title.trim();
	}
	return "";
}

function AnalysisLoader() {
	return (
		<div className="relative mx-auto h-16 w-16" aria-hidden="true">
			<div className="absolute inset-0 rounded-full border-2 border-[rgb(var(--au-primary-rgb)/0.15)" />
			<div className="au-analysis-loader-ring absolute inset-0 rounded-full border-2 border-transparent border-t-[var(--au-primary)] border-r-[var(--au-primary-light)/0.7" />
			<div className="absolute inset-[18px] rounded-full bg-[rgb(var(--au-primary-rgb)/0.1)" />
			<div className="absolute inset-0 grid place-items-center">
				<svg viewBox="0 0 24 24" className="h-5 w-5 fill-[var(--au-primary)]" aria-hidden="true">
					<path d="M13 2L4 14h6l-1 8 9-12h-6l1-8z" />
				</svg>
			</div>
		</div>
	);
}

export function AnalysisModal({ open, event, cursor, total }) {
	if (!open) {
		return null;
	}

	const progress = total > 0 ? Math.min(100, Math.round((cursor / total) * 100)) : 0;
	const indeterminate = total === 0;
	const stepTitle = resolveStepTitle(event);
	const category = typeof event?.category === "string" ? event.category.trim() : "";
	const stepMessage = typeof event?.message === "string" ? event.message.trim() : "";
	const displayCursor = total > 0 ? Math.min(cursor, total) : cursor;

	return (
		<div
			className="fixed inset-0 z-[90] flex items-center justify-center bg-slate-900/45 p-4 backdrop-blur-sm"
			role="dialog"
			aria-modal="true"
			aria-labelledby="akyos-analysis-title"
			aria-busy="true"
		>
			<div className="relative w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xl shadow-[rgb(var(--au-primary-rgb)/0.1)">
				<div className="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-[rgb(var(--au-primary-rgb)/0.07) au-float" />
				<div className="pointer-events-none absolute -bottom-12 -left-8 h-28 w-28 rounded-full bg-[rgb(var(--au-primary-rgb)/0.1) au-float [animation-delay:-3s]" />

				<div className="relative border-b border-slate-100 bg-gradient-to-br from-[rgb(var(--au-primary-rgb)/0.06) via-white to-white px-6 pb-5 pt-6">
					<span className="mb-3 inline-flex items-center rounded-full border border-[rgb(var(--au-primary-rgb)/0.25) bg-[rgb(var(--au-primary-rgb)/0.08) px-3 py-1 font-mono text-[11px] font-medium uppercase tracking-[0.14em] text-[var(--au-primary)]">
						Analyse
					</span>
					<h2
						id="akyos-analysis-title"
						className="text-[28px] font-normal leading-tight text-slate-900 sm:text-3xl"
						style={{ fontFamily: "Calistoga, serif" }}
					>
						Analyse en cours
					</h2>
				</div>

				<div className="relative px-6 py-5">
					<div className="mb-4 flex items-end justify-between gap-4">
						<div>
							<p className="font-mono text-[11px] uppercase tracking-[0.12em] text-slate-500">Progression</p>
							<p className="mt-0.5 text-3xl font-bold tabular-nums tracking-tight text-slate-900">
								{indeterminate ? "—" : `${progress}%`}
							</p>
						</div>
						<p className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-right font-mono text-sm tabular-nums text-slate-700">
							<span className="font-semibold text-slate-900">{displayCursor}</span>
							<span className="text-slate-400"> / </span>
							<span>{total || "…"}</span>
						</p>
					</div>

					<div
						className="relative h-2.5 overflow-hidden rounded-full bg-slate-100 ring-1 ring-inset ring-slate-200/80"
						role="progressbar"
						aria-valuemin={0}
						aria-valuemax={100}
						aria-valuenow={indeterminate ? undefined : progress}
						aria-label="Progression de l'analyse"
					>
						{indeterminate ? (
							<div className="au-analysis-progress-indeterminate absolute inset-y-0 left-0 w-2/5 min-w-[4.5rem] rounded-full bg-gradient-to-r from-[var(--au-primary)] to-[var(--au-primary-light)]" />
						) : (
							<div
								className={`au-analysis-progress-fill relative h-full rounded-full bg-gradient-to-r from-[var(--au-primary)] via-[var(--au-primary-light)] to-[var(--au-primary-light)] shadow-[0_0_12px_rgb(var(--au-primary-rgb)/0.35)] transition-[width] duration-500 ease-out ${progress > 0 ? "au-analysis-progress-shimmer" : ""}`}
								style={{ width: `${Math.max(progress, progress > 0 ? 4 : 0)}%` }}
							/>
						)}
					</div>

					<div className="mt-6 flex flex-col items-center gap-5">
						<AnalysisLoader />

						<div className="w-full rounded-xl border border-slate-200 bg-slate-50/80 p-4 shadow-sm">
							{event ? (
								<div className="space-y-2 text-center">
									{category ? (
										<span className="inline-flex items-center rounded-full border border-[rgb(var(--au-primary-rgb)/0.2) bg-white px-2.5 py-0.5 font-mono text-[10px] font-semibold uppercase tracking-[0.1em] text-[var(--au-primary)]">
											{category}
										</span>
									) : null}
									<p className="text-base font-semibold text-slate-900 sm:text-lg">{stepTitle || "…"}</p>
									{stepMessage ? <p className="text-sm text-slate-600">{stepMessage}</p> : null}
								</div>
							) : (
								<p className="text-center text-sm text-slate-600">Initialisation de l&apos;analyse…</p>
							)}
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}
