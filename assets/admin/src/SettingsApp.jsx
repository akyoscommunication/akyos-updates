import { AdminBarSettingsPanel } from "./components/settings/AdminBarSettingsPanel";
import { LinkSettingsPanel } from "./components/settings/LinkSettingsPanel";
import { Toasts } from "./components/ui/Toasts";
import { useToasts } from "./hooks/useToasts";

export function SettingsApp() {
	const { toasts, addToast } = useToasts();

	return (
		<main className="akyos-updates-shell relative min-h-screen overflow-hidden bg-slate-50 px-7 pb-16 pt-7 text-slate-900">
			<div className="mx-auto flex max-w-3xl flex-col gap-6">
				<div>
					<h2 className="m-0 text-2xl font-semibold text-slate-900">Réglages</h2>
					<p className="m-0 mt-1 text-sm text-slate-500">Affichage et connexion à l&apos;outil MAW.</p>
				</div>
				<AdminBarSettingsPanel addToast={addToast} />
				<LinkSettingsPanel addToast={addToast} />
			</div>
			<Toasts items={toasts} />
		</main>
	);
}
