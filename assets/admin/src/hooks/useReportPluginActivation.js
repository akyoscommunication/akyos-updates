import { useState } from "react";
import { apiFetch } from "../services/api";
import { notifyRestFailure } from "../utils/restError";

const SECTION_PLUGIN_NOTICE_CATEGORY = {
	defender: "Sécurité",
	branda: "Back-office",
	hummingbird: "Performance",
	seo: "SEO",
	smush: "Images",
};

export function useReportPluginActivation({ onPluginPresenceChange, onNotify, onCategoryRecheck }) {
	const [activatingSection, setActivatingSection] = useState(null);

	const handleActivatePlugin = async (sectionKey, pluginFile) => {
		if (!onPluginPresenceChange || !pluginFile) {
			return;
		}
		setActivatingSection(sectionKey);
		try {
			const res = await apiFetch({
				path: "/akyos-updates/v1/plugins/activate",
				method: "POST",
				data: { plugin: pluginFile },
			});
			if (res.pluginPresence) {
				onPluginPresenceChange(res.pluginPresence);
			}
			if (onNotify) {
				onNotify(res.alreadyActive ? "Extension déjà active." : "Extension activée.", "success");
			}
			const categoryForRecheck = SECTION_PLUGIN_NOTICE_CATEGORY[sectionKey];
			if (categoryForRecheck && typeof onCategoryRecheck === "function") {
				onCategoryRecheck(categoryForRecheck);
			}
		} catch (err) {
			if (onNotify) {
				notifyRestFailure("activation d’extension", err, onNotify);
			}
		} finally {
			setActivatingSection(null);
		}
	};

	return { activatingSection, handleActivatePlugin };
}
