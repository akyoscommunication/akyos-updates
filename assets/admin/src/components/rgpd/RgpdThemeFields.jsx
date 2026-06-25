function normalizeThemeHex(value) {
	if (typeof value !== "string") {
		return null;
	}
	let hex = value.trim();
	if (!hex) {
		return null;
	}
	if (!hex.startsWith("#")) {
		hex = `#${hex}`;
	}
	if (!/^#[0-9A-Fa-f]{6}$/.test(hex)) {
		return null;
	}
	return hex.toUpperCase();
}

function hexToRgb(hex) {
	const raw = hex.replace("#", "");
	return [parseInt(raw.slice(0, 2), 16), parseInt(raw.slice(2, 4), 16), parseInt(raw.slice(4, 6), 16)];
}

function darkenHex(hex, amount) {
	const [r, g, b] = hexToRgb(hex);
	const factor = 1 - Math.max(0, Math.min(1, amount));
	const clamp = (n) => Math.max(0, Math.min(255, Math.round(n)));
	return `#${[r * factor, g * factor, b * factor].map((n) => clamp(n).toString(16).padStart(2, "0")).join("").toUpperCase()}`;
}

export function resolveRgpdTheme(form) {
	const primary = normalizeThemeHex(form.theme_primary) || "#0052FF";
	const primaryLight = normalizeThemeHex(form.theme_primary_light) || "#4D7CFF";
	const [r, g, b] = hexToRgb(primary);

	return {
		primary,
		primaryLight,
		primaryHover: darkenHex(primary, 0.08),
		primarySoft: `rgba(${r}, ${g}, ${b}, 0.08)`,
	};
}

function ThemeColorField({ label, hint, value, onChange }) {
	return (
		<label className="grid gap-1.5">
			<span className="text-sm font-semibold text-slate-900">{label}</span>
			<div className="flex flex-wrap items-center gap-3">
				<input
					type="color"
					value={value}
					onChange={(e) => onChange(e.target.value.toUpperCase())}
					className="h-11 w-14 cursor-pointer rounded-lg border border-slate-200 bg-white p-1"
				/>
				<input
					type="text"
					value={value}
					onChange={(e) => onChange(e.target.value.toUpperCase())}
					className="min-h-10 w-32 rounded-xl border border-slate-200 bg-white px-3 py-2 font-mono text-sm text-slate-900 shadow-sm"
					spellCheck={false}
				/>
			</div>
			{hint ? <span className="text-xs text-slate-500">{hint}</span> : null}
		</label>
	);
}

export function RgpdThemeFields({ form, set }) {
	const theme = resolveRgpdTheme(form);

	return (
		<FieldGroup
			title="Couleurs de la bannière cookies"
			description="Bannière de consentement, panneau de préférences et bouton flottant sur le site public."
		>
			<ThemeColorField
				label="Couleur principale"
				hint="Bouton « Tout accepter », liens et accents."
				value={form.theme_primary || "#0052FF"}
				onChange={(v) => set("theme_primary", v)}
			/>
			<ThemeColorField
				label="Couleur secondaire"
				hint="Dégradés et éléments plus clairs du panneau."
				value={form.theme_primary_light || "#4D7CFF"}
				onChange={(v) => set("theme_primary_light", v)}
			/>

			<div className="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
				<p className="m-0 mb-3 text-xs font-mono uppercase tracking-[0.1em] text-slate-500">Aperçu bannière</p>
				<div
					className="max-w-md rounded-2xl border p-4 shadow-lg"
					style={{
						borderColor: theme.primarySoft,
						background: "#fff",
						fontFamily: "system-ui, sans-serif",
					}}
				>
					<p className="m-0 text-base font-bold text-slate-900">Vos choix concernant les cookies</p>
					<p className="mt-2 text-sm text-slate-500">Ce site utilise des cookies pour améliorer votre expérience.</p>
					<a href="#preview" className="mt-2 inline-block text-sm font-semibold underline" style={{ color: theme.primary }}>
						Politique de confidentialité
					</a>
					<div className="mt-4 flex flex-wrap gap-2">
						<button
							type="button"
							className="rounded-lg px-4 py-2 text-sm font-semibold text-white"
							style={{ background: theme.primary, border: `1px solid ${theme.primary}` }}
						>
							Tout accepter
						</button>
						<button type="button" className="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900">
							Personnaliser
						</button>
					</div>
				</div>
			</div>
		</FieldGroup>
	);
}

function FieldGroup({ title, description, children }) {
	return (
		<div className="rounded-xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
			<div className="mb-4">
				<h3 className="m-0 text-sm font-semibold text-slate-900">{title}</h3>
				{description ? <p className="m-0 mt-1 text-xs text-slate-500">{description}</p> : null}
			</div>
			<div className="grid gap-4">{children}</div>
		</div>
	);
}
