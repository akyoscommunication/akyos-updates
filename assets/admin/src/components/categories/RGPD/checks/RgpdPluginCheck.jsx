import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Corriger";

export function RgpdPluginCheck({ result, isFixBusy, onFix }) {
	const matched = Array.isArray(result.payload?.matchedPlugins) ? result.payload.matchedPlugins : [];
	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			{matched.length ? (
				<ul className="mt-2 list-inside list-disc text-sm text-slate-600">
					{matched.map((p) => (
						<li key={p.file || p.slug}>
							<span className="font-medium text-slate-800">{p.label}</span>
							{p.slug ? <span className="text-slate-500"> — {p.slug}</span> : null}
						</li>
					))}
				</ul>
			) : null}
		</ReportCard>
	);
}
