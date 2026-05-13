export function ReportMissingPluginCard({ title, description, activateFile, activateButtonLabel, onActivate, activating }) {
	const showButton = typeof activateFile === "string" && activateFile.length > 0;
	return (
		<div className="mb-6 w-full overflow-hidden rounded-2xl border border-amber-300/70 bg-gradient-to-br from-amber-50 via-white to-slate-50 ring-1 ring-amber-100/90">
			<div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:gap-6 sm:p-6 lg:p-8">
				<div className="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-2xl bg-amber-500/[0.12] ring-1 ring-inset ring-amber-500/20">
					<svg className="h-9 w-9 shrink-0 text-amber-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden>
						<path
							fillRule="evenodd"
							d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
							clipRule="evenodd"
						/>
					</svg>
				</div>
				<div className="min-w-0 flex-1">
					<h3 className="text-base font-semibold tracking-tight text-slate-900 sm:text-lg">{title}</h3>
					<p className="mt-2 text-sm leading-relaxed text-slate-600 sm:text-[15px]">{description}</p>
					{showButton ? (
						<button
							type="button"
							disabled={activating}
							onClick={() => onActivate(activateFile)}
							className="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#0052FF] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0047db] disabled:opacity-50"
						>
							{activating ? "Activation…" : activateButtonLabel || "Activer le plugin"}
						</button>
					) : null}
				</div>
			</div>
		</div>
	);
}
