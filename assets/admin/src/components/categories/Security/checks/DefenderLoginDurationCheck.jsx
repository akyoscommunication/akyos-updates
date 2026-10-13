import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Fixer à 7 jours";

export function DefenderLoginDurationCheck({ result, isFixBusy, onFix }) {
	const payload = result.payload || {};
	const targetDays = payload.targetDays ?? 7;
	const duration = payload.duration;
	const compliant = Boolean(payload.active);

	const durationLabel = duration != null
		? `${duration} jours${compliant ? "" : ` (cible : ${targetDays} j)`}`
		: `Non configurée (cible : ${targetDays} j)`;

	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			<div className="mt-3">
				<div className="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
					<span className="font-semibold text-slate-800">Durée de session</span>
					<span className={`rounded-full px-2 py-1 font-semibold ${compliant ? "bg-emerald-500/15 text-emerald-700" : "bg-amber-500/15 text-amber-700"}`}>
						{durationLabel}
					</span>
				</div>
			</div>
		</ReportCard>
	);
}
