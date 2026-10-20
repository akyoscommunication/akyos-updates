import { createContext, useContext } from "react";

export const ReportActionsContext = createContext({ onFix: null, isFixBusy: () => false });

function ManualValidation({ result }) {
	const { onFix, isFixBusy } = useContext(ReportActionsContext);
	const validation = result.validation;
	const target = { id: result.id, actionId: validation.actionId };
	const busy = isFixBusy(target);
	const date = validation.validatedAt ? new Date(validation.validatedAt).toLocaleDateString("fr-FR") : "";

	return (
		<div
			className={`mt-3 flex flex-wrap items-center justify-between gap-2 rounded-xl border px-3 py-2 text-sm ${validation.validated ? "border-emerald-200 bg-emerald-50 text-emerald-900" : "border-amber-200 bg-amber-50 text-amber-900"
				}`}
		>
			<span>
				{validation.validated
					? `Configuration validée${validation.validatedBy ? ` par ${validation.validatedBy}` : ""}${date ? ` le ${date}` : ""}.`
					: validation.stale
						? "La configuration a changé depuis la dernière validation."
						: "Ce point dépend du projet : vérifie la configuration puis valide-la."}
			</span>
			{onFix ? (
				<button
					type="button"
					disabled={busy}
					className={`inline-flex min-h-9 items-center justify-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold shadow-sm transition disabled:cursor-not-allowed disabled:opacity-60 ${validation.validated
						? "border border-slate-200 bg-white text-slate-900 hover:border-[rgb(var(--au-primary-rgb)/0.3)]"
						: "border border-[var(--au-primary)] bg-[var(--au-primary)] text-white hover:bg-[var(--au-primary-hover)]"
						}`}
					onClick={() => onFix(target, { validated: !validation.validated })}
				>
					{busy ? <span className="inline-block h-3.5 w-3.5 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
					{validation.validated ? "Annuler la validation" : "Valider"}
				</button>
			) : null}
		</div>
	);
}

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
				{result.status !== "info" &&
					(result.status === "skipped" || result.countsTowardCategoryStats !== false) ? (
					<span className={`inline-block h-3 w-3 shrink-0 rounded-full ${statusDotClass}`} />
				) : null}
			</div>
			<p className="mt-2 text-slate-500">{result.validation?.autoMessage || result.message}</p>
			{children}
			{result.validation?.required ? <ManualValidation result={result} /> : null}
			{actions}
		</article>
	);
}
