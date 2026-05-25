export function SeoSitemapSection({ sitemapUrl }) {
	if (!sitemapUrl) {
		return null;
	}

	return (
		<article className="rounded-xl border border-slate-200 bg-white p-3 shadow-sm mb-3">
			<strong>Sitemap</strong>
			<a
				href={sitemapUrl}
				target="_blank"
				rel="noreferrer"
				className="ml-3 inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d]"
			>
				Ouvrir le sitemap
			</a>
		</article>
	);
}
