export function ReportCard({ result, children, actions }) {
	const statusDotClass =
		result.status === "ok"
			? "bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.15)]"
			: result.status === "fail"
				? "bg-red-500 shadow-[0_0_0_4px_rgba(239,68,68,0.15)]"
				: result.status === "info"
					? "bg-slate-400 shadow-[0_0_0_4px_rgba(148,163,184,0.25)]"
					: result.status === "skipped"
						? "bg-slate-400 shadow-[0_0_0_4px_rgba(148,163,184,0.22)]"
						: "bg-amber-500 shadow-[0_0_0_4px_rgba(245,158,11,0.18)]";

	return (
		<article
			className={`rounded-xl border bg-white p-3 shadow-sm ${result.status === "skipped" ? "border-slate-100 opacity-[0.92]" : "border-slate-200"
				}`}
		>
			<div className="flex items-baseline justify-between gap-2">
				<strong>{result.title}</strong>
				{result.id !== "seo.indexability" && result.status !== "info" &&
					(result.status === "skipped" || result.countsTowardCategoryStats !== false) ? (
					<span className={`inline-block h-3 w-3 shrink-0 rounded-full ${statusDotClass}`} />
				) : null}
			</div>
			<p className="mt-2 text-slate-500">{result.message}</p>
			{children}
			{actions}
		</article>
	);
}
