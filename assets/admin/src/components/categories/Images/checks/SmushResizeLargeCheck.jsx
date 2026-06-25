import { ReportCard } from "../../../report/ReportCard";

export function SmushResizeLargeCheck({
	result,
	resizeLargeEnabled,
	setResizeLargeEnabled,
	resizeLargeWidth,
	setResizeLargeWidth,
	resizeLargeHeight,
	setResizeLargeHeight,
	isFixBusy,
	onFix,
}) {
	const clampDimension = (value) => {
		const n = Math.round(Number(value));
		if (!Number.isFinite(n)) {
			return 1920;
		}
		return Math.min(9999, Math.max(1, n));
	};

	return (
		<ReportCard result={result}>
			<div className="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
				<div className="mb-3 flex items-center justify-between gap-3">
					<div className="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Activer Resize large images</div>
					<button
						type="button"
						onClick={() => setResizeLargeEnabled((previous) => !previous)}
						className={`relative inline-flex h-6 w-11 items-center rounded-full transition ${resizeLargeEnabled ? "bg-[var(--au-primary)]" : "bg-slate-300"
							}`}
						aria-pressed={resizeLargeEnabled}
					>
						<span className={`inline-block h-5 w-5 transform rounded-full bg-white transition ${resizeLargeEnabled ? "translate-x-5" : "translate-x-1"}`} />
					</button>
				</div>
				{resizeLargeEnabled ? (
					<div className="flex flex-wrap items-end gap-3">
						<label className="flex min-w-[140px] flex-col gap-1 text-xs">
							<span className="font-semibold text-slate-600">Largeur max (px)</span>
							<input
								type="number"
								min={1}
								max={9999}
								className="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900"
								value={resizeLargeWidth}
								onChange={(event) => setResizeLargeWidth(clampDimension(event.target.value))}
							/>
						</label>
						<label className="flex min-w-[140px] flex-col gap-1 text-xs">
							<span className="font-semibold text-slate-600">Hauteur max (px)</span>
							<input
								type="number"
								min={1}
								max={9999}
								className="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900"
								value={resizeLargeHeight}
								onChange={(event) => setResizeLargeHeight(clampDimension(event.target.value))}
							/>
						</label>
					</div>
				) : null}
				{resizeLargeEnabled ? (
					<button
						type="button"
						disabled={isFixBusy({ id: "images.smush_resize_large", actionId: "images.smush_apply_resize_large" })}
						className="mt-3 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[rgb(var(--au-primary-rgb)/0.3)] disabled:cursor-not-allowed disabled:opacity-60"
						onClick={() =>
							onFix(
								{ id: "images.smush_resize_large", actionId: "images.smush_apply_resize_large" },
								{ width: clampDimension(resizeLargeWidth), height: clampDimension(resizeLargeHeight) }
							)
						}
					>
						{isFixBusy({ id: "images.smush_resize_large", actionId: "images.smush_apply_resize_large" }) ? (
							<span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden />
						) : null}
						Appliquer la configuration
					</button>
				) : null}
			</div>
		</ReportCard>
	);
}
