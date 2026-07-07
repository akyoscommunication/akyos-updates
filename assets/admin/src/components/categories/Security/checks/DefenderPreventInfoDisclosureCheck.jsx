import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Protéger les fichiers";

export function DefenderPreventInfoDisclosureCheck({ result, isFixBusy, onFix }) {
	const payload = result.payload || {};
	const server = payload.server || "unknown";
	const manualRequired = Boolean(payload.manualRequired);

	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			<div className="mt-3 space-y-2">
				<div className="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
					<span className="font-semibold text-slate-800">Serveur détecté</span>
					<span className="rounded-full bg-slate-200/80 px-2 py-1 font-semibold text-slate-700">{server}</span>
				</div>
				{manualRequired ? (
					<p className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
						Configuration automatique indisponible pour ce serveur. Applique les règles manuellement dans Defender &gt; Hardening.
					</p>
				) : null}
			</div>
		</ReportCard>
	);
}
