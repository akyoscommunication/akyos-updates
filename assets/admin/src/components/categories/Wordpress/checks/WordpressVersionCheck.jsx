import { ReportCard } from "../../../report/ReportCard";

export function WordpressVersionCheck({ result }) {
	const payload = result.payload || {};
	const currentVersion = payload.currentVersion || "n/a";
	const latestVersion = payload.latestVersion || "n/a";
	const isLatest = payload.isLatest === true || result.status === "ok";

	return (
		<ReportCard result={result} actions={null}>
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
				<p
					className={`mt-3 rounded-lg border px-3 py-2 text-sm ${
						isLatest
							? "border-emerald-200 bg-emerald-50 text-emerald-900"
							: "border-amber-200 bg-amber-50 text-amber-900"
					}`}
				>
					{isLatest
						? "WordPress est à jour : aucune mise à jour nécessaire."
						: `Une mise à jour WordPress est recommandée (actuelle ${currentVersion}, dernière ${latestVersion}). Mettre à jour WordPress en dehors de cet outil.`}
				</p>
			</div>
		</ReportCard>
	);
}
