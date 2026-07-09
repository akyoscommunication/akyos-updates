import { ReportCard } from "../../../report/ReportCard";

function EnvAlert({ wpEnv, envKind, envRisk, indexingForcedOff, indexed, isProduction, switchHint }) {
	if (indexingForcedOff) {
		return (
			<div className="mt-3 rounded-xl border-2 border-amber-400 bg-amber-50 px-3 py-2.5 text-sm text-amber-950">
				<strong className="font-semibold">Switch verrouillé</strong>
				<p className="mt-1 text-amber-900/90">
					{switchHint || (
						isProduction ? (
							<>
								<code className="rounded bg-amber-100 px-1 py-0.5 text-xs">DISALLOW_INDEXING</code> est actif alors que{" "}
								<code className="rounded bg-amber-100 px-1 py-0.5 text-xs">WP_ENV=production</code>. Vérifie{" "}
								<code className="rounded bg-amber-100 px-1 py-0.5 text-xs">config/application.php</code> ou un mu-plugin qui
								forcerait ce flag.
							</>
						) : (
							<>
								<code className="rounded bg-amber-100 px-1 py-0.5 text-xs">DISALLOW_INDEXING</code> est actif (Bedrock) pour{" "}
								<code className="rounded bg-amber-100 px-1 py-0.5 text-xs">WP_ENV={wpEnv}</code>. Passe en{" "}
								<code className="rounded bg-amber-100 px-1 py-0.5 text-xs">production</code> dans{" "}
								<code className="rounded bg-amber-100 px-1 py-0.5 text-xs">.env</code> ou désactive le flag dans{" "}
								<code className="rounded bg-amber-100 px-1 py-0.5 text-xs">config/environments/{wpEnv}.php</code>.
							</>
						)
					)}
				</p>
			</div>
		);
	}

	if (isProduction && !indexed) {
		return (
			<div className="mt-3 rounded-xl border-2 border-red-500 bg-red-50 px-3 py-3 text-sm text-red-950 shadow-[0_0_0_4px_rgba(239,68,68,0.1)]">
				<strong className="text-base font-bold text-red-900">Site en ligne — indexation désactivée</strong>
				<p className="mt-1.5 text-red-900/90">
					WP_ENV = <code className="rounded bg-red-100 px-1 py-0.5 text-xs">production</code>. Le site est public mais les
					moteurs de recherche sont dissuadés d’indexer. Active l’indexation avant la mise en ligne SEO.
				</p>
			</div>
		);
	}

	if (isProduction && indexed) {
		return (
			<div className="mt-3 rounded-xl border border-emerald-300 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-950">
				<strong className="font-semibold">Site en ligne (production)</strong>
				<p className="mt-1 text-emerald-900/90">Environnement de production — indexation active.</p>
			</div>
		);
	}

	if (envRisk === "dev_indexed") {
		return (
			<div className="mt-3 rounded-xl border-2 border-red-500 bg-red-50 px-3 py-3 text-sm text-red-950 shadow-[0_0_0_4px_rgba(239,68,68,0.1)]">
				<strong className="text-base font-bold text-red-900">Critique — indexation active hors production</strong>
				<p className="mt-1.5 text-red-900/90">
					WP_ENV = <code className="rounded bg-red-100 px-1 py-0.5 text-xs">{wpEnv}</code>. Ce n’est pas le site en ligne :
					désactive l’indexation immédiatement.
				</p>
			</div>
		);
	}

	return (
		<div className="mt-3 rounded-xl border-2 border-violet-500 bg-violet-50 px-3 py-3 text-sm text-violet-950 shadow-[0_0_0_4px_rgba(139,92,246,0.12)]">
			<strong className="text-base font-bold text-violet-900">
				{envKind === "staging" ? "Staging — pas le site en ligne" : "Développement — pas le site en ligne"}
			</strong>
			<p className="mt-1.5 text-violet-900/90">
				WP_ENV = <code className="rounded bg-violet-100 px-1 py-0.5 text-xs">{wpEnv}</code>. Tu travailles sur un environnement
				de {envKind === "staging" ? "pré-production" : "dev"}. L’indexation doit rester désactivée — ne confonds pas avec la
				production.
			</p>
		</div>
	);
}

export function SeoSiteIndexingCheck({ result, isFixBusy, onFix }) {
	const indexed = Boolean(result.payload?.indexed);
	const wpEnv = result.payload?.wpEnv || "unknown";
	const envKind = result.payload?.envKind || "other";
	const envRisk = result.payload?.envRisk || "";
	const isProduction = Boolean(result.payload?.isProduction);
	const indexingForcedOff = Boolean(result.payload?.indexingForcedOff);
	const switchDisabled = Boolean(result.payload?.switchDisabled);
	const switchHint = result.payload?.switchHint || "";
	const busy = isFixBusy({ id: result.id, actionId: "seo.toggle_site_indexing" });
	const isCritical = result.status === "fail";
	const actionId = result.actionId || "seo.toggle_site_indexing";
	// Production : switch toujours actif. Hors prod : actif seulement pour couper l’indexation.
	const canToggle = !switchDisabled && !busy && (isProduction || indexed);

	const switchTitle = switchDisabled
		? switchHint || "Indexation verrouillée par DISALLOW_INDEXING (Bedrock)"
		: !isProduction && !indexed
			? "Activer l’indexation hors production est interdit"
			: undefined;

	const shellClass = isCritical
		? "mt-3 rounded-xl border-2 border-red-400 bg-red-50 px-3 py-3 shadow-[0_0_0_4px_rgba(248,113,113,0.12)]"
		: indexed
			? "mt-3 rounded-xl border border-emerald-200 bg-emerald-50/60 px-3 py-3"
			: "mt-3 rounded-xl border-2 border-red-400 bg-red-50 px-3 py-3 shadow-[0_0_0_4px_rgba(248,113,113,0.12)]";

	return (
		<ReportCard result={result} actions={null}>
			<div className={shellClass}>
				<div className="flex flex-wrap items-center justify-between gap-3">
					<div className="grid gap-1">
						<span
							className={`text-sm font-semibold ${
								isCritical ? "text-red-900" : indexed ? "text-emerald-900" : "text-red-900"
							}`}
						>
							{isProduction
								? indexed
									? "Site en ligne · indexé"
									: "Site en ligne · non indexé"
								: indexed
									? "Hors production · indexé"
									: "Hors production · non indexé"}
						</span>
						<span
							className={`text-xs ${isCritical ? "text-red-800/90" : indexed ? "text-emerald-800/80" : "text-red-800/90"}`}
						>
							Réglage WordPress « Visibilité moteurs de recherche » · WP_ENV = {wpEnv}
						</span>
					</div>
					<button
						type="button"
						role="switch"
						aria-checked={indexed}
						disabled={!canToggle}
						title={switchTitle}
						className={`relative h-8 w-14 shrink-0 rounded-full transition ${
							indexed ? "bg-emerald-600" : "bg-red-500"
						} ${!canToggle ? "cursor-not-allowed opacity-60" : "cursor-pointer"}`}
						onClick={() => {
							if (!canToggle) {
								return;
							}
							onFix({ ...result, actionId }, { indexed: !indexed });
						}}
					>
						<span
							className={`absolute top-1 left-1 h-6 w-6 rounded-full bg-white shadow transition ${
								indexed ? "translate-x-6" : "translate-x-0"
							}`}
						/>
					</button>
				</div>
			</div>
			<EnvAlert
				wpEnv={wpEnv}
				envKind={envKind}
				envRisk={envRisk}
				indexingForcedOff={indexingForcedOff}
				indexed={indexed}
				isProduction={isProduction}
				switchHint={switchHint}
			/>
		</ReportCard>
	);
}
