import { useMemo } from "react";

export function useReportSummaryTotals(report) {
	return useMemo(() => {
		const categories = report?.categories;
		if (!categories || typeof categories !== "object") {
			return { ok: 0, warn: 0, fail: 0 };
		}
		let ok = 0;
		let warn = 0;
		let fail = 0;
		for (const row of Object.values(categories)) {
			if (!row || typeof row !== "object") {
				continue;
			}
			ok += Number(row.ok) || 0;
			warn += Number(row.warn) || 0;
			fail += Number(row.fail) || 0;
		}
		return { ok, warn, fail };
	}, [report]);
}
