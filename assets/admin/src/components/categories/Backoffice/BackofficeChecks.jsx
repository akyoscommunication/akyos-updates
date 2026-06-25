import { useEffect, useMemo, useState } from "react";
import { ReportMissingPluginCard } from "../../report/ReportMissingPluginCard";
import { ReportCard } from "../../report/ReportCard";
import { buildDefaultAction } from "../../report/ReportCheckAction";

const DEFAULT_BACKOFFICE_FIX_LABEL = "Corriger";
import { BackofficeAdminLiteCheck } from "./checks/BackofficeAdminLiteCheck";
import { BackofficeBrandaSmtpCheck } from "./checks/BackofficeBrandaSmtpCheck";
import { BackofficeDashboardWidgetsCheck } from "./checks/BackofficeDashboardWidgetsCheck";

export function BackofficeChecks({
	categoryResults,
	visibleResults,
	pluginPresence,
	onActivatePlugin,
	activatingPluginKey,
	isFixBusy,
	onFix,
}) {
	const smtpResult = categoryResults.find((r) => r.id === "backoffice.branda_smtp") || null;
	const [isAdminLiteModalOpen, setAdminLiteModalOpen] = useState(false);
	const [adminLiteEmail, setAdminLiteEmail] = useState("");
	const [adminLiteName, setAdminLiteName] = useState("");
	const [smtpFromEmail, setSmtpFromEmail] = useState("");
	const [smtpFromName, setSmtpFromName] = useState("");
	const [smtpHost, setSmtpHost] = useState("");
	const [smtpPort, setSmtpPort] = useState("");
	const [smtpUser, setSmtpUser] = useState("");
	const [smtpPassword, setSmtpPassword] = useState("");
	const [smtpEncryption, setSmtpEncryption] = useState("tls");
	const [smtpTestTo, setSmtpTestTo] = useState("");

	useEffect(() => {
		if (!smtpResult) {
			return;
		}
		const p = smtpResult.payload || {};
		setSmtpFromEmail(p.fromEmail || "");
		setSmtpFromName(p.fromName || "");
		setSmtpHost(p.smtpHost || "");
		setSmtpPort(String(p.smtpPort || "587"));
		setSmtpUser(p.smtpUsername || "");
		setSmtpPassword("");
		setSmtpEncryption(["none", "ssl", "tls"].includes(p.smtpEncryption) ? p.smtpEncryption : "tls");
	}, [smtpResult?.id, smtpResult?.payload?.fromEmail, smtpResult?.payload?.smtpHost]);

	const backofficeRenderContext = useMemo(
		() => ({
			smtpFromEmail,
			setSmtpFromEmail,
			smtpFromName,
			setSmtpFromName,
			smtpHost,
			setSmtpHost,
			smtpPort,
			setSmtpPort,
			smtpUser,
			setSmtpUser,
			smtpPassword,
			setSmtpPassword,
			smtpEncryption,
			setSmtpEncryption,
			smtpTestTo,
			setSmtpTestTo,
			isFixBusy,
			onFix,
		}),
		[
			smtpFromEmail,
			smtpFromName,
			smtpHost,
			smtpPort,
			smtpUser,
			smtpPassword,
			smtpEncryption,
			smtpTestTo,
			isFixBusy,
			onFix,
		]
	);

	return (
		<>
			{pluginPresence?.brandaActive === false ? (
				<ReportMissingPluginCard
					title="Branda"
					description={
						pluginPresence.brandaActivateFile
							? "Branda (Ultimate Branding) est installé mais désactivé. Active-le pour les contrôles SMTP, widgets du tableau de bord et Admin Lite."
							: "Branda n'est pas installé sur ce site. Ajoute Ultimate Branding, puis relance une analyse complète."
					}
					activateFile={pluginPresence.brandaActivateFile || null}
					activateButtonLabel="Activer Branda"
					onActivate={(file) => onActivatePlugin("branda", file)}
					activating={activatingPluginKey === "branda"}
				/>
			) : null}
			{isAdminLiteModalOpen ? (
				<div
					className="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 p-4"
					role="presentation"
					onClick={() => setAdminLiteModalOpen(false)}
				>
					<div
						className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl"
						role="dialog"
						aria-modal="true"
						onClick={(e) => e.stopPropagation()}
					>
						<div className="mb-3 text-lg font-semibold text-slate-900">Nouvel utilisateur admin lite</div>
						<label className="mb-1 block text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Email</label>
						<input
							type="email"
							className="mb-3 h-10 w-full rounded-lg border border-slate-200 px-3 text-sm"
							value={adminLiteEmail}
							onChange={(e) => setAdminLiteEmail(e.target.value)}
						/>
						<label className="mb-1 block text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Nom affiché</label>
						<input
							type="text"
							className="mb-4 h-10 w-full rounded-lg border border-slate-200 px-3 text-sm"
							value={adminLiteName}
							onChange={(e) => setAdminLiteName(e.target.value)}
						/>
						<div className="flex flex-wrap justify-end gap-2">
							<button
								type="button"
								className="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-800"
								onClick={() => setAdminLiteModalOpen(false)}
							>
								Annuler
							</button>
							<button
								type="button"
								className="inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--au-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
								disabled={
									!adminLiteEmail.trim() ||
									!adminLiteName.trim() ||
									isFixBusy({ id: "backoffice.admin_lite_user", actionId: "backoffice.create_admin_lite_user" })
								}
								onClick={() => {
									const row = categoryResults.find((r) => r.id === "backoffice.admin_lite_user");
									if (!row?.actionId) {
										return;
									}
									onFix(row, { email: adminLiteEmail.trim(), displayName: adminLiteName.trim() });
									setAdminLiteModalOpen(false);
								}}
							>
								{isFixBusy({ id: "backoffice.admin_lite_user", actionId: "backoffice.create_admin_lite_user" }) ? (
									<span
										className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-white/40 border-t-white"
										aria-hidden
									/>
								) : null}
								Créer
							</button>
						</div>
					</div>
				</div>
			) : null}
			<div className="grid gap-3">
				{visibleResults.map((result) => {
					if (result.id === "backoffice.admin_lite_user") {
						return (
							<BackofficeAdminLiteCheck
								key={result.id}
								result={result}
								setAdminLiteEmail={setAdminLiteEmail}
								setAdminLiteName={setAdminLiteName}
								setAdminLiteModalOpen={setAdminLiteModalOpen}
							/>
						);
					}
					if (result.id === "backoffice.branda_smtp") {
						return <BackofficeBrandaSmtpCheck key={result.id} result={result} ctx={backofficeRenderContext} />;
					}
					if (result.id === "backoffice.branda_dashboard_widgets") {
						return (
							<BackofficeDashboardWidgetsCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />
						);
					}
					return (
						<ReportCard key={result.id} result={result} actions={buildDefaultAction(result, isFixBusy, onFix, DEFAULT_BACKOFFICE_FIX_LABEL)} />
					);
				})}
			</div>
		</>
	);
}
