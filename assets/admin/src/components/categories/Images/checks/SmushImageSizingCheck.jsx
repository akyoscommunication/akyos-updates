import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Activer Image sizing";

export function SmushImageSizingCheck({ result, isFixBusy, onFix }) {
	const payload = result.payload || {};
	const rows = [
		["Automatic Resizing", payload.autoResizing],
		["Set image dimensions", payload.imageDimensions],
	];

	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			<div className="mt-2 flex flex-wrap gap-2">
				{rows.map(([label, enabled]) => (
					<span
						key={label}
						className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold ${enabled ? "bg-emerald-500/15 text-emerald-700" : "bg-amber-500/15 text-amber-700"
							}`}
					>
						{label}
					</span>
				))}
			</div>
		</ReportCard>
	);
}
