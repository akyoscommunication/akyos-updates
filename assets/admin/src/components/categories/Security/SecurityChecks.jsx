import { Fragment, useEffect, useState } from "react";
import { ReportMissingPluginCard } from "../../report/ReportMissingPluginCard";
import { ReportCard } from "../../report/ReportCard";
import { buildDefaultAction } from "../../report/ReportCheckAction";

const DEFAULT_SECURITY_FIX_LABEL = "Corriger";
const SECURITY_ACTION_LABELS = {
	"security.defender_enable_security_headers": "Activer les headers",
	"security.defender_enable_pwned_passwords": "Activer Pwned Passwords",
	"security.defender_enable_global_ip_blocker": "Activer Global IP Blocker",
	"security.defender_apply_firewall_translations": "Appliquer les traductions firewall",
};

function securityFixLabel(actionId) {
	return SECURITY_ACTION_LABELS[actionId] ?? DEFAULT_SECURITY_FIX_LABEL;
}
import { SecurityHeadersCheck } from "./checks/SecurityHeadersCheck";
import { DefenderMaskLoginCheck } from "./checks/DefenderMaskLoginCheck";
import { DefenderRecaptchaCheck } from "./checks/DefenderRecaptchaCheck";

export function SecurityChecks({
	categoryResults,
	visibleResults,
	pluginPresence,
	onActivatePlugin,
	activatingPluginKey,
	isFixBusy,
	onFix,
}) {
	const defenderMaskLoginResult =
		categoryResults.find((result) => result.id === "security.defender_mask_login") || null;
	const defenderRecaptchaResult =
		categoryResults.find((result) => result.id === "security.defender_recaptcha_keys") || null;
	const [maskLoginValue, setMaskLoginValue] = useState("");
	const [recaptchaSiteKey, setRecaptchaSiteKey] = useState("");
	const [recaptchaSecretKey, setRecaptchaSecretKey] = useState("");

	useEffect(() => {
		if (!defenderMaskLoginResult) {
			return;
		}
		setMaskLoginValue(defenderMaskLoginResult?.payload?.maskUrl || "");
	}, [defenderMaskLoginResult?.id, defenderMaskLoginResult?.payload?.maskUrl]);

	useEffect(() => {
		if (!defenderRecaptchaResult) {
			return;
		}
		setRecaptchaSiteKey(defenderRecaptchaResult?.payload?.siteKey || "");
		setRecaptchaSecretKey(defenderRecaptchaResult?.payload?.secretKey || "");
	}, [
		defenderRecaptchaResult?.id,
		defenderRecaptchaResult?.payload?.siteKey,
		defenderRecaptchaResult?.payload?.secretKey,
	]);

	return (
		<Fragment>
			{pluginPresence?.defenderActive === false ? (
				<ReportMissingPluginCard
					title="Defender"
					description={
						pluginPresence.defenderActivateFile
							? "Le plugin Defender est installé mais désactivé. Active-le pour afficher les contrôles de sécurité."
							: "Defender n'est pas installé sur ce site. Ajoute le plugin (WPMU DEV), puis relance une analyse complète."
					}
					activateFile={pluginPresence.defenderActivateFile || null}
					activateButtonLabel="Activer Defender"
					onActivate={(file) => onActivatePlugin("defender", file)}
					activating={activatingPluginKey === "defender"}
				/>
			) : null}
			<div className="grid gap-3">
			{visibleResults.map((result) => {
				if (result.id === "security.defender_security_headers") {
					return <SecurityHeadersCheck key={result.id} result={result} isFixBusy={isFixBusy} onFix={onFix} />;
				}
				if (result.id === "security.defender_mask_login" && result.actionable) {
					return <DefenderMaskLoginCheck key={result.id} result={result} maskLoginValue={maskLoginValue} setMaskLoginValue={setMaskLoginValue} isFixBusy={isFixBusy} onFix={onFix} />;
				}
				if (result.id === "security.defender_recaptcha_keys" && result.actionable) {
					return (
						<DefenderRecaptchaCheck
							key={result.id}
							result={result}
							recaptchaSiteKey={recaptchaSiteKey}
							setRecaptchaSiteKey={setRecaptchaSiteKey}
							recaptchaSecretKey={recaptchaSecretKey}
							setRecaptchaSecretKey={setRecaptchaSecretKey}
							isFixBusy={isFixBusy}
							onFix={onFix}
						/>
					);
				}
				return (
					<ReportCard
						key={result.id}
						result={result}
						actions={buildDefaultAction(result, isFixBusy, onFix, securityFixLabel(result.actionId))}
					/>
				);
			})}
			</div>
		</Fragment>
	);
}
