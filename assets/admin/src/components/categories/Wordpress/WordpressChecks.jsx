import { WordpressVersionCheck } from "./checks/WordpressVersionCheck";
import { WordpressActiveThemeCheck } from "./checks/WordpressActiveThemeCheck";
import { WordpressFaviconCheck } from "./checks/WordpressFaviconCheck";
import { WordpressDefaultCheck } from "./checks/WordpressDefaultCheck";

const WORDPRESS_ACTION_LABELS = {
	"wordpress.update_translations": "Mettre à jour les traductions",
};

function wordpressFixLabel(actionId) {
	return WORDPRESS_ACTION_LABELS[actionId] ?? "Corriger";
}

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
				return (
					<WordpressDefaultCheck
						key={result.id}
						result={result}
						isFixBusy={isFixBusy}
						onFix={onFix}
						fixLabel={wordpressFixLabel(result.actionId)}
					/>
				);
			})}
		</div>
	);
}
