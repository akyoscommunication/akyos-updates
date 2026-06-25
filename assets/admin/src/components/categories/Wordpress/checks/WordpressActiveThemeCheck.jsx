import { ReportCard } from "../../../report/ReportCard";

export function WordpressActiveThemeCheck({ result }) {
	const payload = result.payload || {};
	const themeName = payload.themeName || payload.stylesheet || "n/a";
	const stylesheet = payload.stylesheet || "n/a";
	const currentVersion = typeof payload.currentVersion === "string" ? payload.currentVersion.trim() : "";
	const isSageTheme = Boolean(payload.isSageTheme);
	const updateAvailable = Boolean(payload.updateAvailable);
	const latestAvailableVersion = typeof payload.latestAvailableVersion === "string" ? payload.latestAvailableVersion.trim() : "";
	const updateUrl = typeof payload.updateNoticeUrl === "string" ? payload.updateNoticeUrl : "";

	return (
		<ReportCard result={result}>
			<div className="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
				<div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
					<div className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs">
						<div className="text-slate-500">Thème affiché</div>
						<div className="text-sm font-semibold text-slate-900">{themeName}</div>
					</div>
					<div className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs">
						<div className="text-slate-500">Slug (stylesheet)</div>
						<div className="text-sm font-semibold text-slate-900">{stylesheet}</div>
					</div>
					{isSageTheme ? (
						<div className="rounded-lg border border-[rgb(var(--au-primary-rgb)/0.24)] bg-[rgb(var(--au-primary-rgb)/0.05)] px-3 py-2 text-xs sm:col-span-2">
							<div className="text-slate-600">Sage — version du thème</div>
							<div className="text-sm font-bold text-slate-900">{currentVersion || "non renseignée"}</div>
						</div>
					) : null}
					<div className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs sm:col-span-2">
						<div className="text-slate-500">Mise à jour WordPress.org</div>
						<div className="text-sm font-semibold text-slate-900">
							{updateAvailable && latestAvailableVersion ? `Oui — ${latestAvailableVersion} disponible` : "Non (ou thème non suivi)"}
						</div>
					</div>
				</div>
				{updateAvailable && updateUrl ? (
					<a
						href={updateUrl}
						target="_blank"
						rel="noreferrer"
						className="mt-2 inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[rgb(var(--au-primary-rgb)/0.3)]"
					>
						Fiche mise à jour
					</a>
				) : null}
			</div>
		</ReportCard>
	);
}
