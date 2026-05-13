import { useEffect, useState } from "react";
import { WordpressVersionCheck } from "./checks/WordpressVersionCheck";
import { WordpressActiveThemeCheck } from "./checks/WordpressActiveThemeCheck";
import { WordpressFaviconCheck } from "./checks/WordpressFaviconCheck";
import { WordpressDefaultCheck } from "./checks/WordpressDefaultCheck";

export function WordpressChecks({ categoryResults, visibleResults, isFixBusy, onFix }) {
	const wordpressVersionResult = categoryResults.find((result) => result.id === "wordpress.version") || null;
	const [selectedWordPressVersion, setSelectedWordPressVersion] = useState("");

	useEffect(() => {
		if (!wordpressVersionResult) {
			return;
		}
		const versions = Array.isArray(wordpressVersionResult.payload?.availableVersions)
			? wordpressVersionResult.payload.availableVersions.filter((version) => typeof version === "string" && version.trim() !== "")
			: [];
		const currentVersion = wordpressVersionResult.payload?.currentVersion || "";
		setSelectedWordPressVersion((previous) => {
			if (previous && versions.includes(previous)) {
				return previous;
			}
			if (versions.includes(currentVersion)) {
				return currentVersion;
			}
			return versions[0] || currentVersion;
		});
	}, [
		wordpressVersionResult?.payload?.currentVersion,
		JSON.stringify(wordpressVersionResult?.payload?.availableVersions || []),
	]);

	return (
		<div className="grid gap-3">
			{visibleResults.map((result) => {
				if (result.id === "wordpress.version") {
					return (
						<WordpressVersionCheck
							key={result.id}
							result={result}
							selectedWordPressVersion={selectedWordPressVersion}
							setSelectedWordPressVersion={setSelectedWordPressVersion}
							isFixBusy={isFixBusy}
							onFix={onFix}
						/>
					);
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
