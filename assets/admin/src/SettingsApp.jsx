import { LinkSettingsPanel } from "./components/settings/LinkSettingsPanel";
import { Toasts } from "./components/ui/Toasts";
import { useToasts } from "./hooks/useToasts";

export function SettingsApp() {
	const { toasts, addToast } = useToasts();

	return (
		<main className="akyos-updates-shell relative min-h-screen overflow-hidden bg-slate-50 px-7 pb-16 pt-7 text-slate-900">
			<LinkSettingsPanel addToast={addToast} />
			<Toasts items={toasts} />
		</main>
	);
}
