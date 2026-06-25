function TabWarningGlyph() {
	return (
		<svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden>
			<path
				fillRule="evenodd"
				d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
				clipRule="evenodd"
			/>
		</svg>
	);
}

export function ReportCategoryTabs({ orderedCategories, categories, safeStep, onStepChange, presence }) {
	return (
		<div className="border-b border-slate-200 pb-3 lg:border-b-0 lg:border-r lg:pb-0 lg:pr-4">
			{orderedCategories.map((name, index) => {
				const row = categories[name] || { ok: 0, warn: 0, fail: 0 };
				const categoryAllClear = (row.total || 0) > 0 && (row.warn || 0) === 0 && (row.fail || 0) === 0;
				const hasPluginWarnings =
					(name === "Sécurité" && presence?.defenderActive === false) ||
					(name === "Back-office" && presence?.brandaActive === false) ||
					(name === "Performance" && presence?.hummingbirdActive === false) ||
					(name === "SEO" && presence?.seoPluginActive === false) ||
					(name === "Images" && presence?.smushActive === false) ||
					(name === "RGPD" && presence && presence.rgpdPluginActive === false);
				const tabRightPadding = categoryAllClear || hasPluginWarnings ? "pr-14" : "";
				return (
					<button
						className={`relative mb-2 w-full rounded-xl border p-3 text-left ${tabRightPadding} ${safeStep === name ? "border-[var(--au-primary)]" : "border-slate-200 transition hover:-translate-y-0.5 hover:shadow-md"
							}`}
						key={name}
						onClick={() => onStepChange(name)}
						type="button"
					>
						{hasPluginWarnings ? (
							<div className="absolute right-3 top-1/2 flex -translate-y-1/2 items-center gap-0.5 text-amber-500">
								{name === "Sécurité" && presence?.defenderActive === false ? <span title="Defender absent ou inactif"><TabWarningGlyph /></span> : null}
								{name === "Back-office" && presence?.brandaActive === false ? <span title="Branda absent ou inactif"><TabWarningGlyph /></span> : null}
								{name === "Performance" && presence?.hummingbirdActive === false ? <span title="Hummingbird absent ou inactif"><TabWarningGlyph /></span> : null}
								{name === "SEO" && presence?.seoPluginActive === false ? <span title="Yoast ou SmartCrawl absent ou inactif"><TabWarningGlyph /></span> : null}
								{name === "Images" && presence?.smushActive === false ? <span title="Smush absent ou inactif"><TabWarningGlyph /></span> : null}
								{name === "RGPD" && presence && presence.rgpdPluginActive === false ? <span title="Aucun plugin RGPD / cookies détecté"><TabWarningGlyph /></span> : null}
							</div>
						) : null}
						{categoryAllClear && !hasPluginWarnings ? (
							<span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-emerald-600" title="Aucun avertissement ni échec" aria-hidden>
								<svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
									<path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" />
								</svg>
							</span>
						) : null}
						<div className="mb-2 flex items-center gap-2">
							<span className="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[rgb(var(--au-primary-rgb)/0.08)] font-mono text-[11px] text-[var(--au-primary)]">{String(index + 1).padStart(2, "0")}</span>
							<span className="text-[15px] font-semibold text-slate-900">{name}</span>
						</div>
						<div className="flex flex-wrap gap-1.5">
							<span className="rounded-full bg-emerald-500/15 px-2 py-1 text-xs font-bold text-emerald-700" title={`OK ${row.ok}`}>{row.ok}</span>
							<span className="rounded-full bg-amber-500/15 px-2 py-1 text-xs font-bold text-amber-700" title={`Warn ${row.warn}`}>{row.warn}</span>
							<span className="rounded-full bg-red-500/15 px-2 py-1 text-xs font-bold text-red-700" title={`Fail ${row.fail}`}>{row.fail}</span>
						</div>
					</button>
				);
			})}
		</div>
	);
}
