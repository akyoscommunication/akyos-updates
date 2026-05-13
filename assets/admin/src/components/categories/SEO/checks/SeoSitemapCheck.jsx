import { ReportCard } from "../../../report/ReportCard";
import { buildDefaultAction } from "../../../report/ReportCheckAction";

const PRIMARY_FIX_LABEL = "Corriger";

export function SeoSitemapCheck({ result, isFixBusy, onFix }) {
	const sitemapUrl = result.payload?.sitemapUrl;
	return (
		<ReportCard result={result} actions={buildDefaultAction(result, isFixBusy, onFix, PRIMARY_FIX_LABEL)}>
			{sitemapUrl ? (
				<a
					href={sitemapUrl}
					target="_blank"
					rel="noreferrer"
					className="mt-2 inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d]"
				>
					Ouvrir le sitemap
				</a>
			) : null}
		</ReportCard>
	);
}
