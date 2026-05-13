export function formatRestErrorMessage(error) {
	if (error == null) {
		return "Erreur inconnue.";
	}
	if (typeof error !== "object") {
		return String(error);
	}
	const msg =
		typeof error.message === "string" && error.message.trim() !== ""
			? error.message.trim()
			: "Erreur de requête.";
	const parts = [msg];
	if (error.code && String(error.code).trim() !== "") {
		parts.push(`code ${error.code}`);
	}
	const http = error.data?.status ?? error.status;
	if (typeof http === "number") {
		parts.push(`HTTP ${http}`);
	}
	const b = typeof window !== "undefined" ? window.AKYOS_UPDATES_BOOTSTRAP : null;
	if (b?.restDebug && error.data && typeof error.data === "object" && error.data.file) {
		parts.push(`${error.data.file}:${error.data.line ?? "?"}`);
	}
	return parts.join(" · ");
}

export function notifyRestFailure(context, error, addToast) {
	console.error(`[Akyos Updates] ${context}`, error);
	addToast(formatRestErrorMessage(error), "error");
}
