import { useEffect, useMemo, useState } from "react";
import { apiFetch } from "../../services/api";
import { notifyRestFailure } from "../../utils/restError";
import { ServiceTypePicker, CmpTagsEditor } from "./TarteaucitronTagsEditor";
import { RgpdThemeFields } from "./RgpdThemeFields";

const ACTIVE_RGPD_SECTION_STORAGE_KEY = "akyos_updates_rgpd_section";

const SERVICE_LABELS = {
	tarteaucitron: "TarteAuCitron (bannière cookies)",
	sirdata: "SirData (CMP)",
	matomo_no_cookie: "Matomo (sans consentement)",
};

const SECTIONS = [
	{
		id: "setup",
		label: "Consentement",
		description: "Activation, CMP et affichage de la bannière",
	},
	{
		id: "identity",
		label: "Identité & entreprise",
		description: "Coordonnées du site et mentions légales",
	},
	{
		id: "pages",
		label: "Pages légales",
		description: "Association et génération du contenu",
	},
	{
		id: "tracking",
		label: "Mesure & e-commerce",
		description: "Tags analytics et tracking WooCommerce",
	},
];

const PAGE_FIELDS = [
	{ key: "page_mentions_id", label: "Mentions légales", icon: "§" },
	{ key: "page_privacy_id", label: "Politique de confidentialité", icon: "🔒" },
	{ key: "page_cookies_id", label: "Utilisation des cookies", icon: "🍪" },
];

function bootstrapRgpd() {
	const rgpd = window.AKYOS_UPDATES_BOOTSTRAP?.rgpd;
	return rgpd && typeof rgpd === "object" ? rgpd : {};
}

function FieldGroup({ title, description, children, accent = false }) {
	return (
		<div
			className={`rounded-xl border p-4 sm:p-5 ${
				accent ? "border-[rgb(var(--au-primary-rgb)/0.2)] bg-[rgb(var(--au-primary-rgb)/0.031)]" : "border-slate-200 bg-slate-50/60"
			}`}
		>
			{title ? (
				<div className="mb-4">
					<h3 className="m-0 text-sm font-semibold text-slate-900">{title}</h3>
					{description ? <p className="m-0 mt-1 text-xs text-slate-500">{description}</p> : null}
				</div>
			) : null}
			<div className="grid gap-4">{children}</div>
		</div>
	);
}

function CollapsibleFieldGroup({ title, description, accent = false, defaultOpen = false, toolbar, children }) {
	const [open, setOpen] = useState(defaultOpen);

	return (
		<div
			className={`rounded-xl border ${
				accent ? "border-[rgb(var(--au-primary-rgb)/0.2)] bg-[rgb(var(--au-primary-rgb)/0.031)]" : "border-slate-200 bg-slate-50/60"
			}`}
		>
			<button
				type="button"
				onClick={() => setOpen((v) => !v)}
				aria-expanded={open}
				className="flex w-full items-start justify-between gap-3 p-4 text-left sm:p-5"
			>
				<div className="min-w-0 flex-1">
					{title ? <h3 className="m-0 text-sm font-semibold text-slate-900">{title}</h3> : null}
					{description ? <p className="m-0 mt-1 text-xs text-slate-500">{description}</p> : null}
				</div>
				<svg
					className={`mt-0.5 h-4 w-4 shrink-0 text-slate-400 transition-transform ${open ? "rotate-180" : ""}`}
					viewBox="0 0 20 20"
					fill="currentColor"
					aria-hidden
				>
					<path
						fillRule="evenodd"
						d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
						clipRule="evenodd"
					/>
				</svg>
			</button>
			{toolbar ? <div className={`px-4 sm:px-5 ${open ? "pb-4" : "pb-4 sm:pb-5"}`}>{toolbar}</div> : null}
			{open ? (
				<div
					className={`grid gap-4 border-t border-slate-200/80 px-4 pb-4 pt-4 sm:px-5 sm:pb-5 ${
						toolbar ? "mt-0" : "-mt-px"
					}`}
				>
					{children}
				</div>
			) : null}
		</div>
	);
}

function SectionBody({ title, description, children }) {
	return (
		<div>
			<div className="mb-1 text-[24px] font-normal text-slate-900" style={{ fontFamily: "Calistoga, serif" }}>
				{title}
			</div>
			{description ? <p className="mb-5 mt-0 text-sm text-slate-500">{description}</p> : null}
			<div className="grid gap-5">{children}</div>
		</div>
	);
}

function FieldGrid({ cols = 2, children }) {
	return <div className={`grid gap-4 ${cols === 2 ? "sm:grid-cols-2" : ""}`}>{children}</div>;
}

function TextField({ label, value, onChange, placeholder, hint, type = "text", onKeyDown }) {
	return (
		<label className="grid gap-1.5">
			<span className="text-sm font-medium text-slate-700">{label}</span>
			<input
				type={type}
				className="min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[var(--au-primary)] focus:outline-none focus:ring-1 focus:ring-[var(--au-primary)]"
				value={value ?? ""}
				placeholder={placeholder || ""}
				onChange={(e) => onChange(e.target.value)}
				onKeyDown={onKeyDown}
			/>
			{hint ? <span className="text-xs text-slate-400">{hint}</span> : null}
		</label>
	);
}

function SwitchField({ label, checked, onChange, hint }) {
	return (
		<label className="flex items-start justify-between gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3">
			<span className="grid gap-0.5">
				<span className="text-sm font-medium text-slate-800">{label}</span>
				{hint ? <span className="text-xs text-slate-400">{hint}</span> : null}
			</span>
			<button
				type="button"
				role="switch"
				aria-checked={Boolean(checked)}
				onClick={() => onChange(!checked)}
				className={`relative mt-0.5 h-7 w-12 shrink-0 rounded-full transition ${checked ? "bg-[var(--au-primary)]" : "bg-slate-300"}`}
			>
				<span
					className={`absolute top-0.5 left-0.5 h-6 w-6 rounded-full bg-white shadow transition ${
						checked ? "translate-x-5" : "translate-x-0"
					}`}
				/>
			</button>
		</label>
	);
}

function SelectField({ label, value, onChange, options, hint }) {
	return (
		<label className="grid gap-1.5">
			<span className="text-sm font-medium text-slate-700">{label}</span>
			<select
				className="min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[var(--au-primary)] focus:outline-none focus:ring-1 focus:ring-[var(--au-primary)]"
				value={value ?? ""}
				onChange={(e) => onChange(e.target.value)}
			>
				{options.map((opt) => (
					<option key={String(opt.value)} value={opt.value}>
						{opt.label}
					</option>
				))}
			</select>
			{hint ? <span className="text-xs text-slate-400">{hint}</span> : null}
		</label>
	);
}

function StatusBanner({ form }) {
	const frontActive = Boolean(form.enabled) && Boolean(form.service_type);
	const configured =
		frontActive && (String(form.site_name || "").trim() !== "" || Boolean(form.legal_company_name));
	const companyFilled = Boolean(form.legal_company_name || form.legal_siret);

	return (
		<div className="mb-5 flex flex-wrap items-center gap-2">
			<span
				className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ${
					frontActive ? "bg-emerald-500/15 text-emerald-700" : "bg-amber-500/15 text-amber-700"
				}`}
			>
				<span className={`inline-block h-2 w-2 rounded-full ${frontActive ? "bg-emerald-500" : "bg-amber-500"}`} />
				{frontActive ? "Actif sur le front" : "Module inactif"}
			</span>
			{configured ? (
				<span className="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
					Pages légales prêtes
				</span>
			) : frontActive ? (
				<span className="inline-flex items-center rounded-full bg-amber-500/15 px-3 py-1 text-xs font-medium text-amber-800">
					Renseigne le nom du site pour les pages légales
				</span>
			) : null}
			{companyFilled ? (
				<span className="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
					Entreprise renseignée
				</span>
			) : null}
			{form.service_type ? (
				<span className="inline-flex items-center rounded-full bg-[rgb(var(--au-primary-rgb)/0.05)] px-3 py-1 text-xs font-medium text-[var(--au-primary)]">
					{SERVICE_LABELS[form.service_type] || form.service_type}
				</span>
			) : null}
		</div>
	);
}

export function RgpdSettingsPanel({ addToast }) {
	const initial = useMemo(() => bootstrapRgpd(), []);
	const [form, setForm] = useState(() => initial.settings || {});
	const [pages, setPages] = useState([]);
	const [lookupQuery, setLookupQuery] = useState("");
	const [busy, setBusy] = useState("");
	const [serviceTypeMeta, setServiceTypeMeta] = useState(() => initial.serviceTypeMeta || {});
	const [activeSection, setActiveSection] = useState(() => {
		if (typeof window === "undefined") {
			return "setup";
		}
		const stored = window.localStorage.getItem(ACTIVE_RGPD_SECTION_STORAGE_KEY);
		if (stored && SECTIONS.some((s) => s.id === stored)) {
			return stored;
		}
		return "setup";
	});

	const set = (key, value) => setForm((prev) => ({ ...prev, [key]: value }));
	const setTags = (tags) => setForm((prev) => ({ ...prev, tac_tags: typeof tags === "function" ? tags(prev.tac_tags || []) : tags }));
	const setWoo = (key, value) =>
		setForm((prev) => ({ ...prev, woo_tracking: { ...(prev.woo_tracking || {}), [key]: value } }));

	useEffect(() => {
		apiFetch({ path: "/akyos-updates/v1/rgpd/settings", method: "GET" })
			.then((data) => {
				if (data?.settings) {
					setForm(data.settings);
				}
				if (Array.isArray(data?.meta?.pages)) {
					setPages(data.meta.pages);
				}
				if (data?.meta?.serviceTypeMeta) {
					setServiceTypeMeta(data.meta.serviceTypeMeta);
				}
			})
			.catch((error) => notifyRestFailure("RGPD / chargement", error, addToast));
		// eslint-disable-next-line react-hooks/exhaustive-deps -- montage unique
	}, []);

	useEffect(() => {
		if (typeof window !== "undefined") {
			window.localStorage.setItem(ACTIVE_RGPD_SECTION_STORAGE_KEY, activeSection);
		}
	}, [activeSection]);

	const pageOptions = useMemo(
		() => [{ value: 0, label: "— Créer une nouvelle page —" }, ...pages.map((p) => ({ value: p.id, label: `${p.title} (#${p.id})` }))],
		[pages]
	);

	const tacTags = Array.isArray(form.tac_tags) ? form.tac_tags : [];

	const activeMeta = SECTIONS.find((s) => s.id === activeSection) || SECTIONS[0];

	const save = () => {
		setBusy("save");
		apiFetch({ path: "/akyos-updates/v1/rgpd/settings", method: "POST", data: { settings: form } })
			.then((res) => {
				if (res?.settings) {
					setForm(res.settings);
				}
				addToast(res?.message || "Réglages enregistrés.", "success");
			})
			.catch((error) => notifyRestFailure("RGPD / enregistrement", error, addToast))
			.finally(() => setBusy(""));
	};

	const migrate = () => {
		setBusy("migrate");
		apiFetch({ path: "/akyos-updates/v1/rgpd/migrate", method: "POST" })
			.then((res) => {
				if (res?.settings) {
					setForm(res.settings);
				}
				addToast(res?.message || "Migration effectuée.", res?.migrated ? "success" : "info");
			})
			.catch((error) => notifyRestFailure("RGPD / migration", error, addToast))
			.finally(() => setBusy(""));
	};

	const lookup = () => {
		if (!lookupQuery.trim()) {
			addToast("Renseigne un SIRET, SIREN ou nom d'entreprise.", "error");
			return;
		}
		setBusy("lookup");
		apiFetch({ path: "/akyos-updates/v1/rgpd/company-lookup", method: "POST", data: { query: lookupQuery } })
			.then((res) => {
				if (res?.found && res.company) {
					setForm((prev) => ({
						...prev,
						legal_company_name: res.company.legal_company_name || prev.legal_company_name,
						legal_form: res.company.legal_form || prev.legal_form,
						legal_siret: res.company.legal_siret || prev.legal_siret,
						legal_tva: res.company.legal_tva || prev.legal_tva,
						legal_publication_director: res.company.legal_publication_director || prev.legal_publication_director,
						address: res.company.address || prev.address,
					}));
					addToast(res.message || "Infos entreprise récupérées.", "success");
				} else {
					addToast(res?.message || "Aucune entreprise trouvée.", "error");
				}
			})
			.catch((error) => notifyRestFailure("RGPD / lookup entreprise", error, addToast))
			.finally(() => setBusy(""));
	};

	const hostLookup = () => {
		setBusy("host-lookup");
		apiFetch({ path: "/akyos-updates/v1/rgpd/host-lookup", method: "POST" })
			.then((res) => {
				if (res?.found && res.host) {
					setForm((prev) => ({
						...prev,
						legal_host_name: res.host.legal_host_name || prev.legal_host_name,
						legal_host_address: res.host.legal_host_address || prev.legal_host_address,
						legal_host_phone: res.host.legal_host_phone || prev.legal_host_phone,
					}));
					addToast(res.message || "Infos hébergeur récupérées.", "success");
				} else {
					addToast(res?.message || "Hébergeur non reconnu.", "error");
				}
			})
			.catch((error) => notifyRestFailure("RGPD / lookup hébergeur", error, addToast))
			.finally(() => setBusy(""));
	};

	const generatePages = () => {
		setBusy("pages");
		apiFetch({ path: "/akyos-updates/v1/rgpd/generate-pages", method: "POST", data: { kinds: [] } })
			.then((res) => {
				if (res?.settings) {
					setForm(res.settings);
				}
				addToast(res?.message || "Pages générées.", res?.success ? "success" : "error");
			})
			.catch((error) => notifyRestFailure("RGPD / génération pages", error, addToast))
			.finally(() => setBusy(""));
	};

	const isSirdata = form.service_type === "sirdata";
	const hidden = (id) => (activeSection === id ? "" : "hidden");

	const lookupOnEnter = (e) => {
		if (e.key === "Enter") {
			e.preventDefault();
			lookup();
		}
	};

	return (
		<div className="mx-auto max-w-5xl">
			<div className="mb-2 flex flex-wrap items-start justify-between gap-3">
				<div>
					<h2 className="m-0 text-2xl font-semibold text-slate-900">Configuration RGPD</h2>
					<p className="m-0 mt-1 text-sm text-slate-500">
						Consentement cookies, identité légale, pages et mesure d'audience.
					</p>
				</div>
				<button
					type="button"
					onClick={migrate}
					disabled={busy !== ""}
					className="inline-flex min-h-10 items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-[rgb(var(--au-primary-rgb)/0.3)] disabled:opacity-50"
				>
					{busy === "migrate" ? "Migration…" : "Migrer depuis aky-gdpr"}
				</button>
			</div>

			<StatusBanner form={form} />

			<section className="relative z-10 grid grid-cols-1 gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-lg sm:p-6 lg:grid-cols-[260px_minmax(0,1fr)]">
				<nav className="border-b border-slate-200 pb-4 lg:border-b-0 lg:border-r lg:pb-0 lg:pr-5" aria-label="Sections RGPD">
					<p className="mb-3 hidden text-[11px] font-mono uppercase tracking-[0.12em] text-slate-400 lg:block">Étapes</p>
					{SECTIONS.map((s, index) => (
						<button
							key={s.id}
							type="button"
							onClick={() => setActiveSection(s.id)}
							className={`relative mb-2 flex w-full flex-col gap-0.5 rounded-xl border p-3 text-left transition ${
								activeSection === s.id
									? "border-[var(--au-primary)] bg-[rgb(var(--au-primary-rgb)/0.031)] shadow-sm"
									: "border-slate-200 hover:-translate-y-0.5 hover:border-[rgb(var(--au-primary-rgb)/0.2)] hover:shadow-md"
							}`}
						>
							<span className="flex items-center gap-2">
								<span
									className={`inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full font-mono text-[10px] ${
										activeSection === s.id ? "bg-[var(--au-primary)] text-white" : "bg-[rgb(var(--au-primary-rgb)/0.08)] text-[var(--au-primary)]"
									}`}
								>
									{String(index + 1).padStart(2, "0")}
								</span>
								<span className="text-[14px] font-semibold text-slate-900">{s.label}</span>
							</span>
							<span className="pl-8 text-xs text-slate-500">{s.description}</span>
						</button>
					))}
				</nav>

				<div className="min-w-0">
					<p className="mb-4 text-[11px] font-mono uppercase tracking-[0.12em] text-slate-400">{activeMeta.label}</p>

					<div className={hidden("setup")}>
						<SectionBody
							title="Consentement & affichage"
							description="Active le module, choisis le CMP et règle ce que voient les visiteurs."
						>
							<SwitchField
								label="Activer le module RGPD"
								checked={form.enabled}
								onChange={(v) => set("enabled", v)}
								hint="Injecte la bannière et les tags dès que le module est activé et qu'un CMP est choisi."
							/>

							<FieldGroup title="Solution de consentement" description="Choisis comment le consentement est collecté et comment les tags sont chargés.">
								<ServiceTypePicker value={form.service_type} onChange={(v) => set("service_type", v)} meta={serviceTypeMeta} />
								{isSirdata ? (
									<FieldGrid>
										<TextField label="Identifiant SirData (utilisateur)" value={form.sirdata_user} onChange={(v) => set("sirdata_user", v)} />
										<TextField label="Identifiant SirData (site)" value={form.sirdata_site} onChange={(v) => set("sirdata_site", v)} />
									</FieldGrid>
								) : null}
							</FieldGroup>

							<FieldGroup title="Affichage sur le site" description="Bouton flottant et visibilité de la bannière.">
								<TextField
									label="Contenu du bouton cookies"
									value={form.cookie_button_content}
									onChange={(v) => set("cookie_button_content", v)}
									hint="Vide = icône cookie par défaut. Texte ou HTML accepté."
								/>
								<SwitchField
									label="Masquer le bouton de gestion des cookies"
									checked={form.hide_cookie_button}
									onChange={(v) => set("hide_cookie_button", v)}
								/>
								<SwitchField
									label="Masquer la bannière de consentement"
									checked={form.hide_banner}
									onChange={(v) => set("hide_banner", v)}
									hint="À utiliser avec précaution : le choix cookies doit rester accessible."
								/>
							</FieldGroup>

							<RgpdThemeFields form={form} set={set} />

							{form.service_type === "tarteaucitron" || form.service_type === "" ? (
								<FieldGroup title="Mise à jour tarteaucitron" description="jsDelivr sert la dernière 1.x ; le catalogue admin se sync depuis le CDN.">
									<SwitchField
										label="Charger tarteaucitron depuis le CDN jsDelivr"
										checked={form.tac_use_cdn !== false}
										onChange={(v) => set("tac_use_cdn", v)}
										hint="Désactivé = fallback sur la copie locale embarquée dans le plugin."
									/>
								</FieldGroup>
							) : null}
						</SectionBody>
					</div>

					<div className={hidden("identity")}>
						<SectionBody
							title="Identité & entreprise"
							description="Coordonnées affichées dans les pages légales et la bannière."
						>
							<FieldGroup
								title="Recherche entreprise"
								description="Annuaire officiel des entreprises — remplit automatiquement les champs ci-dessous."
								accent
							>
								<div className="flex flex-col gap-2 sm:flex-row sm:items-end">
									<div className="min-w-0 flex-1">
										<TextField
											label="SIRET, SIREN ou raison sociale"
											value={lookupQuery}
											onChange={setLookupQuery}
											placeholder="Ex. 552 100 554 00015 ou Akyos"
											onKeyDown={lookupOnEnter}
										/>
									</div>
									<button
										type="button"
										onClick={lookup}
										disabled={busy !== ""}
										className="inline-flex min-h-10 shrink-0 items-center justify-center rounded-xl border border-[var(--au-primary)] bg-[var(--au-primary)] px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--au-primary-hover)] disabled:opacity-50"
									>
										{busy === "lookup" ? "Recherche…" : "Récupérer"}
									</button>
								</div>
							</FieldGroup>

							<CollapsibleFieldGroup
								title="Éditeur du site"
								description="Coordonnées affichées sur le site et dans les mentions légales."
							>
								<FieldGrid>
									<TextField label="Nom du site" value={form.site_name} onChange={(v) => set("site_name", v)} />
									<TextField label="Mail DPO / contact RGPD" type="email" value={form.mail} onChange={(v) => set("mail", v)} />
									<TextField label="Adresse postale" value={form.address} onChange={(v) => set("address", v)} />
									<TextField
										label="Page contact"
										value={form.contact_url}
										onChange={(v) => set("contact_url", v)}
										placeholder="https://…/contact"
									/>
								</FieldGrid>
							</CollapsibleFieldGroup>

							<CollapsibleFieldGroup
								title="Société"
								description="Rempli automatiquement via la recherche entreprise — ouvrir pour corriger ou compléter."
							>
								<FieldGrid>
									<TextField label="Raison sociale" value={form.legal_company_name} onChange={(v) => set("legal_company_name", v)} />
									<TextField label="Forme juridique" value={form.legal_form} onChange={(v) => set("legal_form", v)} />
									<TextField label="SIRET" value={form.legal_siret} onChange={(v) => set("legal_siret", v)} />
									<TextField label="TVA intracommunautaire" value={form.legal_tva} onChange={(v) => set("legal_tva", v)} />
									<TextField
										label="Capital social"
										value={form.legal_capital}
										onChange={(v) => set("legal_capital", v)}
										hint="Non fourni par l'annuaire."
									/>
									<TextField
										label="RCS"
										value={form.legal_rcs}
										onChange={(v) => set("legal_rcs", v)}
										hint="Non fourni par l'annuaire."
									/>
								</FieldGrid>
								<TextField
									label="Directeur de la publication"
									value={form.legal_publication_director}
									onChange={(v) => set("legal_publication_director", v)}
								/>
							</CollapsibleFieldGroup>

							<CollapsibleFieldGroup
								title="Hébergeur"
								description="Détection automatique depuis ce site WordPress — ouvrir pour corriger les champs."
								toolbar={
									<div className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-[rgb(var(--au-primary-rgb)/0.2)] bg-[rgb(var(--au-primary-rgb)/0.031)] px-4 py-3">
										<p className="m-0 text-sm text-slate-600">
											Identifie OVH, o2switch, IONOS, Gandi, Scaleway et autres hébergeurs courants.
										</p>
										<button
											type="button"
											onClick={hostLookup}
											disabled={busy !== ""}
											className="inline-flex min-h-10 shrink-0 items-center justify-center rounded-xl border border-[var(--au-primary)] bg-[var(--au-primary)] px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--au-primary-hover)] disabled:opacity-50"
										>
											{busy === "host-lookup" ? "Détection…" : "Détecter l'hébergeur"}
										</button>
									</div>
								}
							>
								<FieldGrid>
									<TextField label="Nom" value={form.legal_host_name} onChange={(v) => set("legal_host_name", v)} />
									<TextField label="Téléphone" value={form.legal_host_phone} onChange={(v) => set("legal_host_phone", v)} />
								</FieldGrid>
								<TextField label="Adresse" value={form.legal_host_address} onChange={(v) => set("legal_host_address", v)} />
							</CollapsibleFieldGroup>
						</SectionBody>
					</div>

					<div className={hidden("pages")}>
						<SectionBody
							title="Pages légales"
							description="Associe des pages WordPress existantes ou laisse vide pour en créer de nouvelles."
						>
							<FieldGroup title="Association des pages">
								{PAGE_FIELDS.map((pf) => (
									<div key={pf.key} className="rounded-lg border border-slate-200 bg-white p-3">
										<SelectField
											label={`${pf.icon} ${pf.label}`}
											value={Number(form[pf.key] || 0)}
											onChange={(v) => set(pf.key, Number(v))}
											options={pageOptions}
										/>
									</div>
								))}
							</FieldGroup>

							<div className="rounded-xl border border-dashed border-slate-300 bg-slate-50/80 p-4">
								<p className="m-0 text-sm text-slate-600">
									Le bouton ci-dessous génère ou met à jour le contenu des pages sélectionnées, puis les passe en{" "}
									<strong className="font-semibold text-slate-800">noindex</strong>.
								</p>
								<button
									type="button"
									onClick={generatePages}
									disabled={busy !== ""}
									className="mt-3 inline-flex min-h-10 items-center rounded-xl border border-slate-900 bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 disabled:opacity-50"
								>
									{busy === "pages" ? "Génération…" : "Générer / mettre à jour les pages"}
								</button>
							</div>
						</SectionBody>
					</div>

					<div className={hidden("tracking")}>
						<SectionBody
							title="Mesure & e-commerce"
							description="Tags injectés après consentement (TarteAuCitron) ou directement (Matomo sans cookie)."
						>
							<FieldGroup
								title="Analytics & tags"
								description="Services chargés après consentement. Ajouter un GTM coche automatiquement le Consent Mode v2 (décochable)."
							>
								<CmpTagsEditor
									tags={tacTags}
									onChange={setTags}
									addToast={addToast}
									serviceType={form.service_type || "tarteaucitron"}
									gcmJobsEnabled={Array.isArray(form.gcm_jobs_enabled) ? form.gcm_jobs_enabled : []}
									onGcmJobsChange={(jobs) => set("gcm_jobs_enabled", jobs)}
								/>
							</FieldGroup>

							<FieldGroup title="WooCommerce (GA4)" description="Events e-commerce dans le dataLayer : add_to_cart, purchase…">
								<SwitchField
									label="Activer le tracking WooCommerce"
									checked={form.woo_tracking?.enabled}
									onChange={(v) => setWoo("enabled", v)}
								/>
								{form.woo_tracking?.enabled ? (
									<FieldGrid>
										<TextField
											label="Classe bouton « Ajouter au panier »"
											value={form.woo_tracking?.add_to_cart_btn_class}
											onChange={(v) => setWoo("add_to_cart_btn_class", v)}
											hint="Défaut : a.button.add_to_cart_button"
										/>
										<TextField
											label="Classe bouton « Retirer du panier »"
											value={form.woo_tracking?.remove_from_cart_btn_class}
											onChange={(v) => setWoo("remove_from_cart_btn_class", v)}
											hint="Défaut : .aky-gdpr-tracking-remove-from-cart"
										/>
									</FieldGrid>
								) : null}
							</FieldGroup>
						</SectionBody>
					</div>
				</div>
			</section>

			<div className="sticky bottom-4 z-10 mt-5 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white/95 px-4 py-3 shadow-lg backdrop-blur-sm">
				<span className="hidden text-xs text-slate-500 sm:inline">
					Section : <strong className="font-medium text-slate-700">{activeMeta.label}</strong>
				</span>
				<button
					type="button"
					onClick={save}
					disabled={busy !== ""}
					className="ml-auto inline-flex min-h-11 items-center rounded-xl border border-[var(--au-primary)] bg-[var(--au-primary)] px-6 py-2.5 font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[var(--au-primary-hover)] disabled:opacity-50"
				>
					{busy === "save" ? "Enregistrement…" : "Enregistrer les réglages"}
				</button>
			</div>
		</div>
	);
}
