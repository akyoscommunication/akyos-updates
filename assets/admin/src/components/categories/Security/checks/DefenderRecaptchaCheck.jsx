import { ReportCard } from "../../../report/ReportCard";

export function DefenderRecaptchaCheck({ result, recaptchaSiteKey, setRecaptchaSiteKey, recaptchaSecretKey, setRecaptchaSecretKey, isFixBusy, onFix }) {
	const payload = result.payload || {};
	const hasKeys = Boolean(payload.hasKeys);
	const keysValid = Boolean(payload.keysValid);
	return (
		<ReportCard result={result} actions={null}>
			<div className="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
				<label className="mb-1 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Clé site reCAPTCHA</label>
				<input type="text" className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900" value={recaptchaSiteKey} onChange={(event) => setRecaptchaSiteKey(event.target.value)} placeholder="Site key" />
				<label className="mb-1 mt-2 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Clé secrète reCAPTCHA</label>
				<input type="text" className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900" value={recaptchaSecretKey} onChange={(event) => setRecaptchaSecretKey(event.target.value)} placeholder="Secret key" />
				<button
					type="button"
					className="mt-2 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d] disabled:cursor-not-allowed disabled:opacity-60"
					onClick={() => onFix(result, { ...(result.payload || {}), siteKey: recaptchaSiteKey, secretKey: recaptchaSecretKey })}
					disabled={!recaptchaSiteKey.trim() || !recaptchaSecretKey.trim() || isFixBusy(result)}
				>
					{isFixBusy(result) ? <span className="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-slate-800" aria-hidden /> : null}
					Enregistrer
				</button>
				{hasKeys && keysValid ? <div className="mt-2 text-xs text-slate-600">Clé secrète valide. Vérifie la preview pour valider la clé site.</div> : null}
			</div>
		</ReportCard>
	);
}
