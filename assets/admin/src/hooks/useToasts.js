import { useState } from "react";

export function useToasts() {
	const [toasts, setToasts] = useState([]);

	const addToast = (message, kind = "success") => {
		const item = { id: `${Date.now()}-${Math.random()}`, message, kind };
		const durationMs = kind === "error" ? 10000 : 3000;
		setToasts((prev) => prev.concat(item).slice(-5));
		setTimeout(() => {
			setToasts((prev) => prev.filter((current) => current.id !== item.id));
		}, durationMs);
	};

	return { toasts, addToast };
}
