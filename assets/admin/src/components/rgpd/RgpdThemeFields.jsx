import { useState } from "react";
import { ColorField, FieldGrid, FormSectionLabel, RangeField, SelectField, SubTabs, SwitchField, TextField } from "./ui";

const FONT_PRESETS = [
	{ value: "", label: "Système (par défaut)" },
	{ value: 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif', label: "System UI" },
	{ value: 'Georgia, "Times New Roman", serif', label: "Serif (Georgia)" },
	{ value: '"Helvetica Neue", Helvetica, Arial, sans-serif', label: "Helvetica / Arial" },
	{ value: "Inter, system-ui, sans-serif", label: "Inter" },
];

const APPEARANCE_TABS = [
	{ id: "colors", label: "Couleurs" },
	{ id: "content", label: "Textes & bouton" },
	{ id: "behavior", label: "Comportement" },
];

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
	const radius = Math.max(8, Math.min(32, Number(form.theme_radius) || 16));

	return {
		primary,
		primaryLight,
		primaryHover: darkenHex(primary, 0.08),
		primarySoft: `rgba(${r}, ${g}, ${b}, 0.08)`,
		text: normalizeThemeHex(form.theme_text) || "#0F172A",
		textMuted: normalizeThemeHex(form.theme_text_muted) || "#64748B",
		border: normalizeThemeHex(form.theme_border) || "#E2E8F0",
		surface: normalizeThemeHex(form.theme_surface) || "#FFFFFF",
		danger: normalizeThemeHex(form.theme_danger) || "#DC2626",
		radius,
		font: form.theme_font || 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
		bannerTitle: String(form.banner_title || "").trim() || "Vos choix concernant les cookies",
	};
}

function BannerPreview({ theme }) {
	return (
		<div className="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-[0_4px_24px_rgba(15,23,42,0.06)]">
			<p className="m-0 mb-4 text-[11px] font-bold uppercase tracking-[0.1em] text-slate-400">Aperçu live</p>
			<div
				className="rounded-2xl border p-4 shadow-md"
				style={{
					borderColor: theme.border,
					background: theme.surface,
					fontFamily: theme.font,
					borderRadius: `${theme.radius}px`,
				}}
			>
				<p className="m-0 text-sm font-bold sm:text-base" style={{ color: theme.text }}>
					{theme.bannerTitle}
				</p>
				<p className="mt-2 text-xs sm:text-sm" style={{ color: theme.textMuted }}>
					Ce site utilise des cookies pour améliorer votre expérience.
				</p>
				<a href="#preview" className="mt-2 inline-block text-xs font-semibold underline sm:text-sm" style={{ color: theme.primary }}>
					Politique de confidentialité
				</a>
				<div className="mt-3 flex flex-wrap gap-2">
					<span
						className="rounded-lg px-3 py-1.5 text-xs font-semibold text-white"
						style={{
							background: theme.primary,
							borderRadius: `${Math.max(6, Math.round(theme.radius * 0.625))}px`,
						}}
					>
						Tout accepter
					</span>
					<span
						className="rounded-lg border px-3 py-1.5 text-xs font-semibold"
						style={{
							color: theme.text,
							borderColor: theme.border,
							borderRadius: `${Math.max(6, Math.round(theme.radius * 0.625))}px`,
						}}
					>
						Personnaliser
					</span>
				</div>
			</div>
			<div className="mt-4 flex flex-wrap gap-2">
				{[
					{ c: theme.primary, l: "Principale" },
					{ c: theme.text, l: "Texte" },
					{ c: theme.surface, l: "Fond" },
					{ c: theme.border, l: "Bordure" },
				].map(({ c, l }) => (
					<span
						key={l}
						className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-600"
					>
						<span className="inline-block h-3 w-3 rounded-full border border-black/5 shadow-sm" style={{ background: c }} />
						{l}
					</span>
				))}
			</div>
		</div>
	);
}

export function RgpdThemeFields({ form, set, showTacOptions = false }) {
	const [tab, setTab] = useState("colors");
	const theme = resolveRgpdTheme(form);
	const radius = Math.max(8, Math.min(32, Number(form.theme_radius) || 16));

	const tabs = showTacOptions ? APPEARANCE_TABS : APPEARANCE_TABS.filter((t) => t.id !== "behavior");

	return (
		<div className="grid gap-5 lg:grid-cols-[minmax(0,1fr)_272px] lg:items-start">
			<div className="grid min-w-0 gap-5">
				<SubTabs tabs={tabs} active={tab} onChange={setTab} />

				{tab === "colors" ? (
					<div className="grid gap-5">
						<div>
							<FormSectionLabel>Accents</FormSectionLabel>
							<div className="grid gap-4 sm:grid-cols-2">
								<ColorField
									label="Couleur principale"
									hint="Bouton accepter, liens et accents."
									value={form.theme_primary || "#0052FF"}
									onChange={(v) => set("theme_primary", v)}
								/>
								<ColorField
									label="Couleur secondaire"
									hint="Dégradés et éléments clairs."
									value={form.theme_primary_light || "#4D7CFF"}
									onChange={(v) => set("theme_primary_light", v)}
								/>
							</div>
						</div>

						<div>
							<FormSectionLabel>Neutres</FormSectionLabel>
							<div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
								<ColorField label="Texte principal" value={form.theme_text || "#0F172A"} onChange={(v) => set("theme_text", v)} />
								<ColorField label="Texte atténué" value={form.theme_text_muted || "#64748B"} onChange={(v) => set("theme_text_muted", v)} />
								<ColorField label="Fond" value={form.theme_surface || "#FFFFFF"} onChange={(v) => set("theme_surface", v)} />
								<ColorField label="Bordures" value={form.theme_border || "#E2E8F0"} onChange={(v) => set("theme_border", v)} />
								<ColorField label="Refus / danger" hint="Survol du bouton refuser." value={form.theme_danger || "#DC2626"} onChange={(v) => set("theme_danger", v)} />
							</div>
						</div>

						<div className="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
							<p className="m-0 mb-4 text-[15px] font-semibold text-slate-900">Typographie & forme</p>
							<div className="grid gap-4 sm:grid-cols-2">
								<SelectField
									label="Police"
									value={form.theme_font || ""}
									onChange={(v) => set("theme_font", v)}
									options={FONT_PRESETS}
									hint="Appliquée à toute la bannière cookies."
								/>
								<RangeField
									label="Arrondi des coins"
									value={radius}
									min={8}
									max={32}
									step={1}
									onChange={(v) => set("theme_radius", v)}
								/>
							</div>
						</div>
					</div>
				) : null}

				{tab === "content" ? (
					<div className="grid gap-4">
						<TextField
							label="Titre de la bannière"
							value={form.banner_title || ""}
							onChange={(v) => set("banner_title", v)}
							placeholder="Vos choix concernant les cookies"
							hint="Laisser vide pour le libellé par défaut en français."
						/>
						<TextField
							label="Contenu du bouton cookies"
							value={form.cookie_button_content || ""}
							onChange={(v) => set("cookie_button_content", v)}
							placeholder="Gérer mes cookies"
							hint="Vide = icône cookie. Texte ou HTML accepté."
						/>
						<FieldGrid>
							<SelectField
								label="Position du bouton"
								value={form.cookie_button_position || "bottom-left"}
								onChange={(v) => set("cookie_button_position", v)}
								options={[
									{ value: "bottom-left", label: "Bas gauche" },
									{ value: "bottom-right", label: "Bas droite" },
								]}
							/>
							<SwitchField
								label="Bouton toujours visible"
								checked={form.cookie_button_always_visible}
								onChange={(v) => set("cookie_button_always_visible", v)}
								hint="Sinon, visible en bas de page au scroll."
							/>
						</FieldGrid>
					</div>
				) : null}

				{tab === "behavior" && showTacOptions ? (
					<div className="grid gap-4">
						<SelectField
							label="Position de la bannière"
							value={form.tac_orientation || "bottom"}
							onChange={(v) => set("tac_orientation", v)}
							options={[
								{ value: "bottom", label: "Bas de l'écran" },
								{ value: "top", label: "Haut de l'écran" },
								{ value: "middle", label: "Centre" },
							]}
						/>
						<div className="grid gap-3 sm:grid-cols-2">
							{[
								{ key: "tac_show_accept_all", label: "Bouton « Tout accepter »" },
								{ key: "tac_show_deny_all", label: "Bouton « Tout refuser »" },
								{ key: "tac_high_privacy", label: "Haute confidentialité", hint: "Opt-in explicite par service." },
								{ key: "tac_google_consent_mode", label: "Google Consent Mode v2" },
								{ key: "tac_group_services", label: "Regrouper par catégorie" },
							].map(({ key, label, hint }) => (
								<SwitchField
									key={key}
									label={label}
									hint={hint}
									checked={form[key] !== false}
									onChange={(v) => set(key, v)}
									compact
								/>
							))}
						</div>
					</div>
				) : null}
			</div>

			<div className="lg:sticky lg:top-4">
				<BannerPreview theme={theme} />
			</div>
		</div>
	);
}
