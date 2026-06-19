import { RgpdSettingsPanel } from "./components/rgpd/RgpdSettingsPanel";
import { Toasts } from "./components/ui/Toasts";
import { useToasts } from "./hooks/useToasts";

export function RgpdApp() {
	const { toasts, addToast } = useToasts();

	return (
		<main className="akyos-updates-shell relative min-h-screen overflow-hidden bg-slate-50 px-7 pb-16 pt-7 text-slate-900">
			<RgpdSettingsPanel addToast={addToast} />
			<Toasts items={toasts} />
		</main>
	);
}
