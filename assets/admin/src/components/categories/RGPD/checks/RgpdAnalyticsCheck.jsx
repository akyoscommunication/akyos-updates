import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Corriger";

export function RgpdAnalyticsCheck({ result, isFixBusy, onFix }) {
	const signals = Array.isArray(result.payload?.signals) ? result.payload.signals : [];
	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			{signals.length ? (
				<ul className="mt-2 space-y-1 text-sm text-slate-600">
					{signals.map((s, i) => (
						<li key={`${s.type}-${i}`}>
							<span className="font-semibold text-slate-800">{s.label}</span>
							{s.detail ? <span className="text-slate-500"> — {s.detail}</span> : null}
						</li>
					))}
				</ul>
			) : null}
		</ReportCard>
	);
}
