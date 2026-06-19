import { useEffect, useState } from "react";
import { apiFetch } from "../services/api";
import { notifyRestFailure } from "../utils/restError";
import { useClipboardCopy } from "../hooks/useClipboardCopy";

function bootstrapLink() {
	const link = window.AKYOS_UPDATES_BOOTSTRAP?.link;
	return link && typeof link === "object" ? link : {};
}

function TextField({ label, value, onChange, hint, type = "text", readOnly = false }) {
	return (
		<label className="grid gap-1.5">
			<span className="text-sm font-semibold text-slate-900">{label}</span>
			<input
				type={type}
				value={value}
				readOnly={readOnly}
				onChange={(e) => onChange(e.target.value)}
				className="min-h-10 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm read-only:bg-slate-50"
			/>
			{hint ? <span className="text-xs text-slate-500">{hint}</span> : null}
		</label>
	);
}

export function LinkSettingsPanel({ addToast }) {
	const [form, setForm] = useState(bootstrapLink);
	const [busy, setBusy] = useState("");
	const { copyText } = useClipboardCopy(
		(msg) => addToast?.({ type: "success", message: msg }),
		(msg) => addToast?.({ type: "error", message: msg })
	);

	useEffect(() => {
		let cancelled = false;
		apiFetch({ path: "/akyos-updates/v1/link", method: "GET" })
			.then((data) => {
				if (!cancelled && data?.link) {
					setForm(data.link);
				}
			})
			.catch(() => {});
		return () => {
			cancelled = true;
		};
	}, []);

	const save = async ({ register = false, regenerateApiKey = false } = {}) => {
		setBusy(register ? "link" : regenerateApiKey ? "regen" : "save");
		try {
			const data = await apiFetch({
				path: "/akyos-updates/v1/link",
				method: "POST",
				data: {
					pairing_key: form.pairing_key || "",
					register,
					regenerate_api_key: regenerateApiKey,
				},
			});
			if (data?.link) {
				setForm(data.link);
			}
			if (data?.registration?.message) {
				addToast?.({
					type: data.registration.success ? "success" : "error",
					message: data.registration.message,
				});
			} else if (!regenerateApiKey) {
				addToast?.({ type: "success", message: "Réglages enregistrés." });
			} else {
				addToast?.({ type: "success", message: "Clé API régénérée." });
			}
		} catch (error) {
			notifyRestFailure(error, addToast);
		} finally {
			setBusy("");
		}
	};

	const statusLabel = form.linked
		? `Lié${form.linked_at ? ` depuis ${new Date(form.linked_at).toLocaleString("fr-FR")}` : ""}`
		: "Non lié";

	return (
		<section className="relative z-10 my-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
			<h2 className="mb-1 text-3xl font-normal text-slate-900" style={{ fontFamily: "Calistoga, serif" }}>
				Connexion MAW
			</h2>
			<p className="mb-5 text-sm text-slate-600">
				Lie ce site à l&apos;outil MAW pour remonter les statuts, déclencher les maintenances à distance et ouvrir le back-office automatiquement.
			</p>

			<div className="mb-5 inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">
				<span
					className={`inline-block h-2.5 w-2.5 rounded-full ${form.linked ? "bg-emerald-500" : "bg-amber-500"}`}
					aria-hidden
				/>
				{statusLabel}
			</div>

			<div className="grid gap-4">
				<TextField
					label="Clé de liaison MAW"
					value={form.pairing_key || ""}
					onChange={(v) => setForm((prev) => ({ ...prev, pairing_key: v }))}
					hint="Clé fournie par MAW lors de l'ajout du site dans votre parc."
				/>

				<div className="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
					<div className="mb-3 flex flex-wrap items-end justify-between gap-3">
						<div className="min-w-0 flex-1">
							<TextField
								label="Clé API du site"
								value={form.api_key || ""}
								readOnly
								onChange={() => {}}
								hint="À renseigner côté MAW si la liaison automatique échoue. Authentifie les appels distants."
							/>
						</div>
						<button
							type="button"
							className="inline-flex min-h-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:border-[#0052FF4d] disabled:opacity-60"
							disabled={!form.api_key || busy !== ""}
							onClick={() => copyText(form.api_key || "", "Clé API copiée.")}
						>
							Copier
						</button>
					</div>
					{form.api_key_preview ? (
						<p className="m-0 text-xs text-slate-500">Aperçu : {form.api_key_preview}</p>
					) : null}
				</div>

				{form.link_error ? (
					<p className="m-0 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{form.link_error}</p>
				) : null}

				<div className="flex flex-wrap gap-2">
					<button
						type="button"
						disabled={busy !== ""}
						onClick={() => save({ register: true })}
						className="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#0052FF] px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0047db] disabled:opacity-60"
					>
						{busy === "link" ? "Liaison…" : "Enregistrer et lier à MAW"}
					</button>
					<button
						type="button"
						disabled={busy !== ""}
						onClick={() => save({ regenerateApiKey: true })}
						className="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:border-[#0052FF4d] disabled:opacity-60"
					>
						{busy === "regen" ? "Régénération…" : "Régénérer la clé API"}
					</button>
				</div>
			</div>
		</section>
	);
}
