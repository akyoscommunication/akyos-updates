import { ReportCard } from "../../../report/ReportCard";

export function SmushNextGenCheck({ result, nextGenEnabled, setNextGenEnabled, nextGenFormat, setNextGenFormat, isFixBusy, onFix }) {
	const payload = result.payload || {};
	const availableFormats = Array.isArray(payload.availableFormats) && payload.availableFormats.length ? payload.availableFormats : ["webp", "avif"];

	return (
		<ReportCard result={result}>
			<div className="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
				<div className="mb-3 flex items-center justify-between gap-3">
					<div className="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Activer next-gen formats</div>
					<button
						type="button"
						onClick={() => setNextGenEnabled((previous) => !previous)}
						className={`relative inline-flex h-6 w-11 items-center rounded-full transition ${nextGenEnabled ? "bg-[#0052FF]" : "bg-slate-300"}`}
					>
						<span className={`inline-block h-5 w-5 transform rounded-full bg-white transition ${nextGenEnabled ? "translate-x-5" : "translate-x-1"}`} />
					</button>
				</div>
				{nextGenEnabled ? (
					<div className="mb-3">
						<label className="mb-1 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Format</label>
						<select
							className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900"
							value={nextGenFormat}
							onChange={(event) => setNextGenFormat(event.target.value === "avif" ? "avif" : "webp")}
						>
							{availableFormats.includes("webp") ? <option value="webp">WebP</option> : null}
							{availableFormats.includes("avif") ? <option value="avif">AVIF</option> : null}
						</select>
					</div>
				) : null}
				{nextGenEnabled ? (
					<button
						type="button"
						disabled={isFixBusy(result)}
						className="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d] disabled:cursor-not-allowed disabled:opacity-60"
						onClick={() => onFix(result, { ...(result.payload || {}), nextGenEnabled, activeFormat: nextGenFormat })}
					>
						{isFixBusy(result) ? <span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
						Appliquer la configuration
					</button>
				) : null}
			</div>
		</ReportCard>
	);
}
