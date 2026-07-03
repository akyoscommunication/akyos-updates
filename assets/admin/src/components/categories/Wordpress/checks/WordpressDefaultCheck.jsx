import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Corriger";

export function WordpressDefaultCheck({ result, isFixBusy, onFix, fixLabel = PRIMARY_FIX_LABEL }) {
	return <ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, fixLabel)} />;
}
