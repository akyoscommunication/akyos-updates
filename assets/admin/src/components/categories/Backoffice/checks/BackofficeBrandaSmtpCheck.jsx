import { ReportCard } from "../../../report/ReportCard";

export function BackofficeBrandaSmtpCheck({ result, ctx }) {
	const {
		smtpFromEmail, setSmtpFromEmail, smtpFromName, setSmtpFromName, smtpHost, setSmtpHost, smtpPort, setSmtpPort,
		smtpUser, setSmtpUser, smtpPassword, setSmtpPassword, smtpEncryption, setSmtpEncryption, smtpTestTo, setSmtpTestTo,
		isFixBusy, onFix,
	} = ctx;
	const payload = result.payload || {};
	const brandaOk = Boolean(payload.brandaActive);
	return (
		<ReportCard result={result} actions={null}>
			<div className="mt-3 space-y-4">
				{!brandaOk ? (
					<div className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">Active Branda pour configurer le SMTP ici.</div>
				) : (
					<>
						<div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
							<div className="mb-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Configuration SMTP</div>
							<div className="space-y-3">
								<div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
									<div className="min-w-0"><label className="mb-1 block text-xs font-semibold text-slate-600">Adresse expéditeur</label><input type="email" className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900" value={smtpFromEmail} onChange={(e) => setSmtpFromEmail(e.target.value)} autoComplete="off" /></div>
									<div className="min-w-0"><label className="mb-1 block text-xs font-semibold text-slate-600">Nom expéditeur (optionnel)</label><input type="text" className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900" value={smtpFromName} onChange={(e) => setSmtpFromName(e.target.value)} autoComplete="off" /></div>
								</div>
								<div className="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_5.5rem_7rem] sm:items-end">
									<div className="min-w-0"><label className="mb-1 block text-xs font-semibold text-slate-600">SMTP Host</label><input type="text" className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900" value={smtpHost} onChange={(e) => setSmtpHost(e.target.value)} autoComplete="off" /></div>
									<div className="min-w-0"><label className="mb-1 block text-xs font-semibold text-slate-600">SMTP Port</label><input type="text" className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900" value={smtpPort} onChange={(e) => setSmtpPort(e.target.value)} autoComplete="off" /></div>
									<div className="min-w-0"><label className="mb-1 block text-xs font-semibold text-slate-600">Chiffrement</label><select className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900" value={smtpEncryption} onChange={(e) => setSmtpEncryption(e.target.value)}><option value="tls">TLS</option><option value="ssl">SSL</option><option value="none">Aucun</option></select></div>
								</div>
								<div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
									<div className="min-w-0"><label className="mb-1 block text-xs font-semibold text-slate-600">SMTP Username</label><input type="text" className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900" value={smtpUser} onChange={(e) => setSmtpUser(e.target.value)} autoComplete="off" /></div>
									<div className="min-w-0"><label className="mb-1 block text-xs font-semibold text-slate-600">SMTP Password</label><input type="password" className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900" value={smtpPassword} onChange={(e) => setSmtpPassword(e.target.value)} placeholder={payload.passwordSet ? "Laisser vide pour conserver" : ""} autoComplete="new-password" /></div>
								</div>
							</div>
							<button type="button" className="mt-3 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[rgb(var(--au-primary-rgb)/0.3)] disabled:cursor-not-allowed disabled:opacity-60" disabled={!smtpFromEmail.trim() || !smtpHost.trim() || isFixBusy(result)} onClick={() => onFix(result, { fromEmail: smtpFromEmail.trim(), fromName: smtpFromName.trim(), smtpHost: smtpHost.trim(), smtpPort: smtpPort.trim(), smtpUsername: smtpUser.trim(), smtpPassword, smtpEncryption })}>
								{isFixBusy(result) ? <span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
								Enregistrer SMTP
							</button>
						</div>
						<div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
							<div className="mb-2 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Email de test</div>
							<label className="mb-1 block text-xs font-semibold text-slate-600">Destinataire</label>
							<input type="email" className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900" value={smtpTestTo} onChange={(e) => setSmtpTestTo(e.target.value)} placeholder="email@exemple.com" />
							<button type="button" className="mt-2 inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[rgb(var(--au-primary-rgb)/0.3)] disabled:cursor-not-allowed disabled:opacity-60" disabled={!smtpTestTo.trim()} onClick={() => onFix({ id: "backoffice.branda_smtp", actionId: "backoffice.branda_send_test_email" }, { to: smtpTestTo.trim() })}>
								Envoyer un email de test
							</button>
						</div>
					</>
				)}
			</div>
		</ReportCard>
	);
}
