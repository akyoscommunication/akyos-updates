import { WordpressVersionCheck } from "./checks/WordpressVersionCheck";
import { WordpressActiveThemeCheck } from "./checks/WordpressActiveThemeCheck";
import { WordpressFaviconCheck } from "./checks/WordpressFaviconCheck";
import { WordpressDefaultCheck } from "./checks/WordpressDefaultCheck";

export function WordpressChecks({ visibleResults, isFixBusy, onFix }) {
	return (
		<div className="grid gap-3">
			{visibleResults.map((result) => {
				if (result.id === "wordpress.version") {
					return <WordpressVersionCheck key={result.id} result={result} />;
				}
				if (result.id === "wordpress.active_theme") {
					return <WordpressActiveThemeCheck key={result.id} result={result} />;
				}
				if (result.id === "wordpress.favicon") {
					return <WordpressFaviconCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
				}
				return <WordpressDefaultCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
			})}
		</div>
	);
}
