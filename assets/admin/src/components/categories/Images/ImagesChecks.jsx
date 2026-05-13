import { Fragment, useEffect, useState } from "react";
import { ReportMissingPluginCard } from "../../report/ReportMissingPluginCard";
import { SmushConfigCheck } from "./checks/SmushConfigCheck";
import { SmushImageSizingCheck } from "./checks/SmushImageSizingCheck";
import { SmushStatsCheck } from "./checks/SmushStatsCheck";
import { SmushResizeLargeCheck } from "./checks/SmushResizeLargeCheck";
import { SmushNextGenCheck } from "./checks/SmushNextGenCheck";
import { ImagesDefaultCheck } from "./checks/ImagesDefaultCheck";

export function ImagesChecks({
	categoryResults,
	visibleResults,
	pluginPresence,
	onActivatePlugin,
	activatingPluginKey,
	isFixBusy,
	onFix,
}) {
	const nextGenResult = categoryResults.find((result) => result.id === "images.smush_nextgen_config") || null;
	const resizeLargeResult = categoryResults.find((result) => result.id === "images.smush_resize_large") || null;
	const [nextGenEnabled, setNextGenEnabled] = useState(false);
	const [nextGenFormat, setNextGenFormat] = useState("webp");
	const [resizeLargeEnabled, setResizeLargeEnabled] = useState(false);
	const [resizeLargeWidth, setResizeLargeWidth] = useState(1920);
	const [resizeLargeHeight, setResizeLargeHeight] = useState(1920);

	useEffect(() => {
		if (!nextGenResult) {
			return;
		}
		const payload = nextGenResult.payload || {};
		setNextGenEnabled(Boolean(payload.nextGenEnabled));
		setNextGenFormat(payload.activeFormat === "avif" ? "avif" : "webp");
	}, [nextGenResult?.id, nextGenResult?.payload?.nextGenEnabled, nextGenResult?.payload?.activeFormat]);

	useEffect(() => {
		if (!resizeLargeResult) {
			return;
		}
		const p = resizeLargeResult.payload || {};
		setResizeLargeEnabled(Boolean(p.resizeActive));
		const w = Number(p.resizeWidth);
		const h = Number(p.resizeHeight);
		setResizeLargeWidth(Number.isFinite(w) && w > 0 ? w : 1920);
		setResizeLargeHeight(Number.isFinite(h) && h > 0 ? h : 1920);
	}, [
		resizeLargeResult?.id,
		resizeLargeResult?.payload?.resizeActive,
		resizeLargeResult?.payload?.resizeWidth,
		resizeLargeResult?.payload?.resizeHeight,
	]);

	return (
		<Fragment>
			{pluginPresence?.smushActive === false ? (
				<ReportMissingPluginCard
					title="Smush"
					description={
						pluginPresence.smushActivateFile
							? "Smush est installé mais désactivé. Active-le pour les contrôles de compression, lazy load et formats next-gen."
							: "Smush n'est pas installé sur ce site. Ajoute Smush ou Smush Pro, puis relance une analyse complète."
					}
					activateFile={pluginPresence.smushActivateFile || null}
					activateButtonLabel={
						pluginPresence.smushActivateFile && pluginPresence.smushActivateFile.includes("smush-pro")
							? "Activer Smush Pro"
							: "Activer Smush"
					}
					onActivate={(file) => onActivatePlugin("smush", file)}
					activating={activatingPluginKey === "smush"}
				/>
			) : null}
			<div className="grid gap-3">
			{visibleResults.map((result) => {
				if (result.id === "images.smush_config") {
					return <SmushConfigCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
				}
				if (result.id === "images.smush_image_sizing") {
					return <SmushImageSizingCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
				}
				if (result.id === "images.smush_bulk_pending") {
					return <SmushStatsCheck key={result.id} result={result} />;
				}
				if (result.id === "images.smush_resize_large") {
					return (
						<SmushResizeLargeCheck
							key={result.id}
							result={result}
							resizeLargeEnabled={resizeLargeEnabled}
							setResizeLargeEnabled={setResizeLargeEnabled}
							resizeLargeWidth={resizeLargeWidth}
							setResizeLargeWidth={setResizeLargeWidth}
							resizeLargeHeight={resizeLargeHeight}
							setResizeLargeHeight={setResizeLargeHeight}
							isFixBusy={isFixBusy}
							onFix={onFix}
						/>
					);
				}
				if (result.id === "images.smush_nextgen_config") {
					return (
						<SmushNextGenCheck
							key={result.id}
							result={result}
							nextGenEnabled={nextGenEnabled}
							setNextGenEnabled={setNextGenEnabled}
							nextGenFormat={nextGenFormat}
							setNextGenFormat={setNextGenFormat}
							isFixBusy={isFixBusy}
							onFix={onFix}
						/>
					);
				}
				return <ImagesDefaultCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
			})}
			</div>
		</Fragment>
	);
}
