export function useClipboardCopy(onSuccess, onError) {
	const copyText = (content, successMessage = "Texte copié.") => {
		const text = typeof content === "string" ? content : "";
		if (!text.trim()) {
			if (typeof onError === "function") {
				onError("Aucun texte à copier.");
			}
			return;
		}
		const copyPromise =
			typeof navigator !== "undefined" && navigator.clipboard && typeof navigator.clipboard.writeText === "function"
				? navigator.clipboard.writeText(text)
				: new Promise((resolve, reject) => {
					if (typeof document === "undefined") {
						reject(new Error("Clipboard indisponible"));
						return;
					}
					const textarea = document.createElement("textarea");
					textarea.value = text;
					textarea.setAttribute("readonly", "readonly");
					textarea.style.position = "fixed";
					textarea.style.opacity = "0";
					document.body.appendChild(textarea);
					textarea.select();
					const copied = document.execCommand("copy");
					document.body.removeChild(textarea);
					if (copied) {
						resolve();
						return;
					}
					reject(new Error("Clipboard indisponible"));
				});
		copyPromise
			.then(() => {
				if (typeof onSuccess === "function") {
					onSuccess(successMessage);
				}
			})
			.catch(() => {
				if (typeof onError === "function") {
					onError("Impossible de copier automatiquement.");
				}
			});
	};
	return { copyText };
}
