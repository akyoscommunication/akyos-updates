import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Corriger";

export function WordpressFaviconCheck({ result, isFixBusy, onFix }) {
	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			{result.status === "ok" && result.payload?.faviconUrl ? (
				<div className="mt-2 inline-flex rounded-lg border border-slate-200 bg-white p-2">
					<img className="h-6 w-6 rounded-md" src={result.payload.faviconUrl} alt="Favicon du site" />
				</div>
			) : null}
		</ReportCard>
	);
}
