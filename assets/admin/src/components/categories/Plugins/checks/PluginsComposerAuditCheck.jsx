import { ReportCard } from "../../../report/ReportCard";

export function PluginsComposerAuditCheck({ result, isFixBusy, onFix, composerAuditBadgeDismissed, setComposerAuditBadgeDismissed }) {
	const missingPlugins = Array.isArray(result.payload?.missingPlugins) ? result.payload.missingPlugins : [];
	const hasSlugPayload = Array.isArray(result.payload?.missingWpackagistSlugs) && Array.isArray(result.payload?.missingGitignoreSlugs);
	return (
		<ReportCard result={result} actions={null}>
			{missingPlugins.length ? (
				<div className="mt-2 space-y-1">
					<div className="flex flex-wrap items-center gap-1 text-xs text-slate-500">
						<span>Plugins hors composer :</span>
						{missingPlugins.map((plugin) => (
							<span key={plugin} className="inline-block rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{plugin}</span>
						))}
					</div>
					{result.actionable && result.actionId === "plugins.generate_composer_guidance" && hasSlugPayload ? (
						<div className="mt-5 pt-5 flex flex-wrap gap-2">
							{(result.payload.missingWpackagistSlugs || []).length > 0 ? (
								<span className="relative inline-flex">
									<button type="button" disabled={isFixBusy(result)} onClick={() => { setComposerAuditBadgeDismissed((d) => ({ ...d, composer: true })); onFix(result); }} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d] disabled:cursor-not-allowed disabled:opacity-60">
										{isFixBusy(result) ? <span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
										Mettre à jour composer.json
									</button>
									{!composerAuditBadgeDismissed.composer && (result.payload.missingWpackagistSlugs || []).length > 0 ? <span className="absolute -right-1.5 -top-1.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-[#0052FF] px-1 text-[10px] font-bold leading-none text-white shadow-sm">{(result.payload.missingWpackagistSlugs || []).length}</span> : null}
								</span>
							) : null}
							{(result.payload.missingGitignoreSlugs || []).length > 0 ? (
								<span className="relative inline-flex">
									<button type="button" disabled={isFixBusy({ ...result, actionId: "plugins.generate_gitignore_exceptions" })} onClick={() => { setComposerAuditBadgeDismissed((d) => ({ ...d, gitignore: true })); onFix({ ...result, actionId: "plugins.generate_gitignore_exceptions" }); }} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d] disabled:cursor-not-allowed disabled:opacity-60">
										{isFixBusy({ ...result, actionId: "plugins.generate_gitignore_exceptions" }) ? <span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
										Mettre à jour .gitignore
									</button>
									{!composerAuditBadgeDismissed.gitignore && (result.payload.missingGitignoreSlugs || []).length > 0 ? <span className="absolute -right-1.5 -top-1.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold leading-none text-white shadow-sm">{(result.payload.missingGitignoreSlugs || []).length}</span> : null}
								</span>
							) : null}
						</div>
					) : null}
				</div>
			) : null}
		</ReportCard>
	);
}
