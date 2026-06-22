import { LinkSettingsForm, useLinkSettings } from "../modals/LinkSettingsModal";

export function LinkSettingsPanel({ addToast }) {
	const { form, setForm, busy, save, copyText } = useLinkSettings(addToast);

	return (
		<div className="mx-auto max-w-3xl">
			<div className="mb-5">
				<h2 className="m-0 text-2xl font-semibold text-slate-900">Réglages</h2>
				<p className="m-0 mt-1 text-sm text-slate-500">Connexion à l&apos;outil MAW et paramètres du site.</p>
			</div>

			<section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
				<div className="mb-5">
					<h3
						className="m-0 text-xl font-normal text-slate-900"
						style={{ fontFamily: "Calistoga, serif" }}
					>
						Connexion MAW
					</h3>
					<p className="mt-2 text-sm text-slate-600">
						Lie ce site à l&apos;outil MAW pour remonter les statuts, déclencher les maintenances à distance et ouvrir le
						back-office automatiquement.
					</p>
					{form.linked ? (
						<p className="mt-3 inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">
							<span className="inline-block h-2 w-2 rounded-full bg-emerald-500" />
							Site lié à MAW
						</p>
					) : (
						<p className="mt-3 inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800">
							<span className="inline-block h-2 w-2 rounded-full bg-amber-500" />
							Site non lié
						</p>
					)}
				</div>

				<LinkSettingsForm form={form} setForm={setForm} busy={busy} save={save} copyText={copyText} />
			</section>
		</div>
	);
}
