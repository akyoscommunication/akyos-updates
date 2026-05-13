import { useState } from "react";

export function defaultCategorySelection(names) {
	const next = {};
	for (const name of names) {
		next[name] = true;
	}
	return next;
}

export function useCategorySelection(initialNames) {
	const [selection, setSelection] = useState(() => defaultCategorySelection(initialNames));
	const toggle = (name, value) => {
		setSelection((prev) => ({ ...prev, [name]: value }));
	};
	return { selection, setSelection, toggle };
}
