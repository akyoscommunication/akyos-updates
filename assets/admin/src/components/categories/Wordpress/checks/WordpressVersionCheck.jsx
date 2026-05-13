import { ReportCard } from "../../../report/ReportCard";

export function WordpressVersionCheck({ result, selectedWordPressVersion, setSelectedWordPressVersion, isFixBusy, onFix }) {
	const payload = result.payload || {};
	const isBedrock = Boolean(payload.isBedrock);
	const currentVersion = payload.currentVersion || "n/a";
	const latestVersion = payload.latestVersion || "n/a";
	const versions = Array.isArray(payload.availableVersions)
		? payload.availableVersions.filter((version) => typeof version === "string" && version.trim() !== "")
		: [];
	const canApply = !isBedrock && selectedWordPressVersion && selectedWordPressVersion !== currentVersion;

	return (
		<ReportCard
			result={result}
			actions={
				!isBedrock ? (
					<button
						type="button"
						className="ml-2 mt-2 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d] disabled:cursor-not-allowed disabled:opacity-60"
						onClick={() => onFix(result, { targetVersion: selectedWordPressVersion })}
						disabled={!canApply || isFixBusy(result)}
					>
						{isFixBusy(result) ? <span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
						Appliquer la version
					</button>
				) : null
			}
		>
			<div className="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
				<div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
					<div className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs">
						<div className="text-slate-500">Version actuelle</div>
						<div className="text-sm font-semibold text-slate-900">{currentVersion}</div>
					</div>
					<div className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs">
						<div className="text-slate-500">Dernière version</div>
						<div className="text-sm font-semibold text-slate-900">{latestVersion}</div>
					</div>
				</div>
				{isBedrock ? (
					<div className="mt-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600">Installation Bedrock détectée, changement de version bloqué ici.</div>
				) : (
					<div className="mt-3">
						<label className="mb-1 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Choisir une version WordPress</label>
						<select
							className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900"
							value={selectedWordPressVersion}
							onChange={(event) => setSelectedWordPressVersion(event.target.value)}
						>
							{versions.map((version) => (
								<option key={version} value={version}>
									{version}
								</option>
							))}
						</select>
					</div>
				)}
			</div>
		</ReportCard>
	);
}
