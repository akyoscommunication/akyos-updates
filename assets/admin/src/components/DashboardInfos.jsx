import { CategorySwitches } from "./ui/CategorySwitches";

export function buildOverviewItems(overview = {}) {
	return [
		["WordPress", overview.wordpressVersion || "n/a"],
		["PHP", overview.phpVersion || "n/a"],
		["Theme", overview.activeTheme || "n/a"],
		["Installation", overview.installationType || "n/a"],
	];
}

export function DashboardInfos({
	overview = {},
	running,
	onStart,
	categoryNames,
	categorySelection,
	onCategoryToggle,
	compact = false,
	mawLinked,
	onOpenMawModal,
}) {
	const items = buildOverviewItems(overview);

	if (compact) {
		const cliRows = [
			["WP-CLI", overview.wpCliAvailable],
			["Composer", overview.composerAvailable],
		];

		return (
			<section className="relative z-10 mb-3 mt-0 border-b border-slate-200/90 pb-2.5">
				<div className="flex w-full flex-col gap-2 text-[11px] leading-tight sm:flex-row sm:items-center sm:justify-between sm:gap-4">
					<div className="flex min-w-0 flex-1 flex-wrap items-center gap-x-0 gap-y-1">
						{items.map((row, i) => (
							<span
								key={row[0]}
								className={`inline-flex min-w-0 max-w-full items-center gap-1 ${i > 0 ? "border-l border-slate-200 pl-2.5 ml-2 sm:pl-3 sm:ml-3" : ""}`}
							>
								<span className="shrink-0 font-mono uppercase tracking-[0.06em] text-slate-500">{row[0]}</span>
								<span className="min-w-0 truncate font-semibold text-slate-900">{row[1]}</span>
							</span>
						))}
					</div>
					<div className="flex shrink-0 flex-wrap items-center justify-start gap-x-0 gap-y-1 border-t border-slate-200 pt-2 sm:justify-end sm:border-l sm:border-t-0 sm:pl-3 sm:pt-0">
						{cliRows.map(([label, val], i) => (
							<span
								key={label}
								className={`inline-flex items-center gap-1 ${i > 0 ? "border-l border-slate-200 pl-2.5 ml-2 sm:pl-3 sm:ml-3" : ""}`}
							>
								<span className="shrink-0 font-mono uppercase tracking-[0.06em] text-slate-500">{label}</span>
								<span className={`shrink-0 font-semibold ${val === true ? "text-emerald-600" : "text-red-600"}`}>
									{val === true ? "OK" : "NOK"}
								</span>
							</span>
						))}
						<span className="inline-flex items-center gap-1.5 border-l border-slate-200 pl-2.5 ml-2 sm:pl-3 sm:ml-3">
							<button
								type="button"
								onClick={onOpenMawModal}
								className="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-[#0052FF]/30 bg-[#0052FF]/10 text-[#0052FF] shadow-sm transition hover:-translate-y-px hover:border-[#0052FF]/50 hover:bg-[#0052FF] hover:text-white hover:shadow-md"
								title="Connexion MAW"
								aria-label="Connexion MAW"
							>
								<svg viewBox="0 0 24 24" aria-hidden="true" className="h-3.5 w-3.5 fill-current">
									<path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z" />
								</svg>
							</button>
							<span className="shrink-0 font-mono uppercase tracking-[0.06em] text-slate-500">MAW</span>
							<span className={`shrink-0 font-semibold ${mawLinked ? "text-emerald-600" : "text-red-600"}`}>
								{mawLinked ? "OK" : "NOK"}
							</span>
						</span>
					</div>
				</div>
			</section>
		);
	}

	return (
		<section className="relative z-10 my-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
			<h2 className="mb-4 text-3xl font-normal text-slate-900" style={{ fontFamily: "Calistoga, serif" }}>
				Informations du site
			</h2>
			<div className="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
				{items.map((row) => (
					<div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm" key={row[0]}>
						<div className="font-mono text-xs uppercase tracking-[0.12em] text-slate-500">{row[0]}</div>
						<div className="mt-1.5 text-2xl font-bold text-slate-900">{row[1]}</div>
					</div>
				))}
			</div>
			<div className="mt-6 border-t border-slate-100 pt-5">
				<h3 className="text-lg font-semibold text-slate-900">Portée de l’analyse</h3>
				<p className="mt-1 text-sm text-slate-600">Active les catégories à inclure avant de lancer.</p>
				<CategorySwitches
					names={categoryNames}
					value={categorySelection}
					onToggle={onCategoryToggle}
					disabled={running}
				/>
			</div>
			<button
				className="mt-5 inline-flex min-h-12 items-center justify-center rounded-xl bg-gradient-to-br from-[#0052FF] to-[#4D7CFF] px-6 py-3 font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_8px_24px_rgba(0,82,255,0.35)] disabled:cursor-wait disabled:opacity-80"
				onClick={onStart}
				disabled={running}
			>
				{running ? "Analyse en cours..." : "Lancer l'analyse"}
			</button>
		</section>
	);
}
