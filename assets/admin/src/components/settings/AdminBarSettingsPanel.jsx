import { useEffect, useState } from "react";
import { apiFetch } from "../../services/api";
import { notifyRestFailure } from "../../utils/restError";
import { SwitchField } from "../rgpd/ui";

function bootstrapDisplay() {
	const display = window.AKYOS_UPDATES_BOOTSTRAP?.display;
	return Boolean(display?.show_maintenance_admin_bar);
}

export function AdminBarSettingsPanel({ addToast }) {
	const [visible, setVisible] = useState(bootstrapDisplay);
	const [busy, setBusy] = useState(false);

	useEffect(() => {
		let cancelled = false;
		apiFetch({ path: "/akyos-updates/v1/settings", method: "GET" })
			.then((data) => {
				if (!cancelled && typeof data?.show_maintenance_admin_bar === "boolean") {
					setVisible(data.show_maintenance_admin_bar);
				}
			})
			.catch(() => {});
		return () => {
			cancelled = true;
		};
	}, []);

	const save = async (next) => {
		setVisible(next);
		setBusy(true);
		try {
			const data = await apiFetch({
				path: "/akyos-updates/v1/settings",
				method: "POST",
				data: { show_maintenance_admin_bar: next },
			});
			if (typeof data?.show_maintenance_admin_bar === "boolean") {
				setVisible(data.show_maintenance_admin_bar);
			}
			addToast?.("Réglages enregistrés.", "success");
		} catch (error) {
			setVisible(!next);
			notifyRestFailure("Barre d’admin", error, addToast);
		} finally {
			setBusy(false);
		}
	};

	return (
		<section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
			<div className="mb-5">
				<h3
					className="m-0 text-xl font-normal text-slate-900"
					style={{ fontFamily: "Calistoga, serif" }}
				>
					Barre d’administration
				</h3>
				<p className="mt-2 text-sm text-slate-600">
					Contrôle l’affichage du statut de maintenance dans la barre d’admin WordPress.
				</p>
			</div>

			<SwitchField
				label="Afficher le statut Maintenance"
				hint="Masqué par défaut. Active l’indicateur de maintenance dans la barre d’admin WordPress."
				checked={visible}
				onChange={(next) => {
					if (!busy) {
						save(next);
					}
				}}
			/>
		</section>
	);
}
