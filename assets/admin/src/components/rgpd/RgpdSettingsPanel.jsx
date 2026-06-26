import { useEffect, useMemo, useState } from "react";
import { apiFetch } from "../../services/api";
import { notifyRestFailure } from "../../utils/restError";
import { ServiceTypePicker, CmpTagsEditor } from "./TarteaucitronTagsEditor";
import { RgpdThemeFields } from "./RgpdThemeFields";
import {
	CollapsibleFieldGroup,
	FieldGrid,
	FieldGroup,
	SectionBody,
	SectionStatusDot,
	SelectField,
	SwitchField,
	TextField,
} from "./ui";

const ACTIVE_RGPD_SECTION_STORAGE_KEY = "akyos_updates_rgpd_section";

const SERVICE_LABELS = {
	tarteaucitron: "TarteAuCitron",
	sirdata: "SirData",
	matomo_no_cookie: "Matomo",
};

const SECTIONS = [
	{
		id: "setup",
		label: "Activation",
		short: "CMP",
		description: "Module, solution de consentement",
	},
	{
		id: "appearance",
		label: "Apparence",
		short: "UI",
		description: "Couleurs, textes, bouton cookies",
	},
	{
		id: "identity",
		label: "Identité",
		short: "Legal",
		description: "Entreprise et coordonnées",
	},
	{
		id: "pages",
		label: "Pages légales",
		short: "Pages",
		description: "Association et génération",
	},
	{
		id: "tracking",
		label: "Mesure",
		short: "Tags",
		description: "Analytics et WooCommerce",
	},
];

const PAGE_FIELDS = [
	{ key: "page_mentions_id", label: "Mentions légales", icon: "§", desc: "Informations légales obligatoires" },
	{ key: "page_privacy_id", label: "Politique de confidentialité", icon: "🔒", desc: "Traitement des données personnelles" },
	{ key: "page_cookies_id", label: "Utilisation des cookies", icon: "🍪", desc: "Lien depuis la bannière cookies" },
];

function bootstrapRgpd() {
	const rgpd = window.AKYOS_UPDATES_BOOTSTRAP?.rgpd;
	return rgpd && typeof rgpd === "object" ? rgpd : {};
}

function sectionProgress(id, form) {
	switch (id) {
		case "setup":
			if (form.enabled && form.service_type) {
				return "done";
			}
			if (form.enabled || form.service_type) {
				return "partial";
			}
			return "todo";
		case "appearance":
			return "done";
		case "identity":
			if (form.legal_company_name && form.site_name) {
				return "done";
			}
			if (form.legal_company_name || form.site_name || form.mail) {
				return "partial";
			}
			return "todo";
		case "pages":
			if (PAGE_FIELDS.every((p) => Number(form[p.key] || 0) > 0)) {
				return "done";
			}
			if (PAGE_FIELDS.some((p) => Number(form[p.key] || 0) > 0)) {
				return "partial";
			}
			return "todo";
		case "tracking":
			if ((form.tac_tags || []).length > 0 || form.woo_tracking?.enabled) {
				return "done";
			}
			return "todo";
		default:
			return "todo";
	}
}

function SetupChecklist({ form }) {
	const items = [
		{ ok: form.enabled, label: "Module activé" },
		{ ok: Boolean(form.service_type), label: "CMP sélectionné" },
		{ ok: !form.hide_banner || !form.hide_cookie_button, label: "Accès cookies garanti" },
	];

	return (
		<ul className="m-0 grid gap-1.5 p-0">
			{items.map(({ ok, label }) => (
				<li key={label} className="flex items-center gap-2 text-xs text-slate-600">
					<span className={`inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] ${ok ? "bg-emerald-100 text-emerald-700" : "bg-slate-100 text-slate-400"}`}>
						{ok ? "✓" : "·"}
					</span>
					{label}
				</li>
			))}
		</ul>
	);
}

function StatusBanner({ form }) {
	const frontActive = Boolean(form.enabled) && Boolean(form.service_type);
	const doneCount = SECTIONS.filter((s) => sectionProgress(s.id, form) === "done").length;

	return (
		<div className="mb-5 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
			<div className="flex flex-wrap items-center gap-2">
				<span
					className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ${
						frontActive ? "bg-emerald-500/15 text-emerald-700" : "bg-amber-500/15 text-amber-700"
					}`}
				>
					<span className={`inline-block h-2 w-2 rounded-full ${frontActive ? "bg-emerald-500" : "bg-amber-500"}`} />
					{frontActive ? "Actif sur le front" : "Module inactif"}
				</span>
				{form.service_type ? (
					<span className="inline-flex items-center rounded-full bg-[rgb(var(--au-primary-rgb)/0.05)] px-3 py-1 text-xs font-medium text-[var(--au-primary)]">
						{SERVICE_LABELS[form.service_type] || form.service_type}
					</span>
				) : null}
			</div>
			<div className="flex items-center gap-3 text-xs text-slate-500">
				<span>
					Configuration : <strong className="font-semibold text-slate-700">{doneCount}/{SECTIONS.length}</strong> sections
				</span>
				<div className="hidden h-4 w-px bg-slate-200 sm:block" />
				<div className="hidden gap-1 sm:flex">
					{SECTIONS.map((s) => (
						<SectionStatusDot key={s.id} status={sectionProgress(s.id, form)} />
					))}
				</div>
			</div>
		</div>
	);
}

function ActivationHero({ enabled, onChange }) {
	return (
		<div
			className={`rounded-2xl border p-5 shadow-[0_1px_3px_rgba(15,23,42,0.04)] sm:p-6 ${
				enabled
					? "border-emerald-200/80 bg-gradient-to-br from-emerald-50/60 via-white to-white"
					: "border-slate-200/90 bg-gradient-to-br from-slate-50/80 via-white to-white"
			}`}
		>
			<div className="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
				<div>
					<p className="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">État du module</p>
					<p className="m-0 mt-2 text-xl font-semibold text-slate-900">{enabled ? "RGPD actif" : "RGPD désactivé"}</p>
					<p className="m-0 mt-1.5 max-w-md text-sm leading-relaxed text-slate-500">
						{enabled
							? "La bannière et les tags sont injectés dès qu'un CMP est configuré."
							: "Active le module pour afficher la bannière cookies et charger les tags après consentement."}
					</p>
				</div>
				<SwitchField
					bare
					label={enabled ? "Activé" : "Désactivé"}
					checked={enabled}
					onChange={onChange}
					hint={enabled ? "Visible sur le site public" : "Aucun script RGPD injecté"}
				/>
			</div>
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

	const pageById = useMemo(() => Object.fromEntries(pages.map((p) => [p.id, p])), [pages]);

	const tacTags = Array.isArray(form.tac_tags) ? form.tac_tags : [];
	const activeIndex = SECTIONS.findIndex((s) => s.id === activeSection);
	const activeMeta = SECTIONS[activeIndex] || SECTIONS[0];
	const isSirdata = form.service_type === "sirdata";
	const isTac = form.service_type === "tarteaucitron" || form.service_type === "";
	const hidden = (id) => (activeSection === id ? "" : "hidden");

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

	const lookupOnEnter = (e) => {
		if (e.key === "Enter") {
			e.preventDefault();
			lookup();
		}
	};

	const goSection = (delta) => {
		const next = SECTIONS[activeIndex + delta];
		if (next) {
			setActiveSection(next.id);
		}
	};

	return (
		<div className="mx-auto max-w-6xl">
			<div className="mb-4 flex flex-wrap items-start justify-between gap-3">
				<div>
					<h2 className="m-0 text-2xl font-semibold text-slate-900">Configuration RGPD</h2>
					<p className="m-0 mt-1 text-sm text-slate-500">Consentement, apparence, identité légale et mesure d'audience.</p>
				</div>
				<button
					type="button"
					onClick={migrate}
					disabled={busy !== ""}
					className="inline-flex min-h-10 items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-800 disabled:opacity-50"
				>
					{busy === "migrate" ? "Migration…" : "Migrer aky-gdpr"}
				</button>
			</div>

			<StatusBanner form={form} />

			{/* Mobile tabs */}
			<div className="mb-4 flex gap-2 overflow-x-auto pb-1 lg:hidden" role="tablist">
				{SECTIONS.map((s) => {
					const status = sectionProgress(s.id, form);
					return (
						<button
							key={s.id}
							type="button"
							role="tab"
							aria-selected={activeSection === s.id}
							onClick={() => setActiveSection(s.id)}
							className={`flex shrink-0 items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition ${
								activeSection === s.id
									? "border-[var(--au-primary)] bg-[rgb(var(--au-primary-rgb)/0.08)] text-[var(--au-primary)]"
									: "border-slate-200 bg-white text-slate-600"
							}`}
						>
							<SectionStatusDot status={status} />
							{s.short}
						</button>
					);
				})}
			</div>

			<section className="relative z-10 grid grid-cols-1 gap-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-lg sm:p-6 lg:grid-cols-[220px_minmax(0,1fr)]">
				<nav className="hidden lg:block lg:pr-4" aria-label="Sections RGPD">
					<p className="mb-3 text-[11px] font-mono uppercase tracking-[0.12em] text-slate-400">Navigation</p>
					{SECTIONS.map((s, index) => {
						const status = sectionProgress(s.id, form);
						const active = activeSection === s.id;
						return (
							<button
								key={s.id}
								type="button"
								onClick={() => setActiveSection(s.id)}
								className={`mb-1.5 flex w-full items-start gap-2.5 rounded-xl border px-3 py-2.5 text-left transition ${
									active
										? "border-[var(--au-primary)] bg-[rgb(var(--au-primary-rgb)/0.04)] shadow-sm"
										: "border-transparent hover:border-slate-200 hover:bg-slate-50"
								}`}
							>
								<span
									className={`mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md font-mono text-[9px] ${
										active ? "bg-[var(--au-primary)] text-white" : "bg-slate-100 text-slate-500"
									}`}
								>
									{String(index + 1).padStart(2, "0")}
								</span>
								<span className="min-w-0 flex-1">
									<span className="flex items-center gap-1.5">
										<span className="text-sm font-semibold text-slate-900">{s.label}</span>
										<SectionStatusDot status={status} />
									</span>
									<span className="mt-0.5 block text-[11px] leading-snug text-slate-500">{s.description}</span>
								</span>
							</button>
						);
					})}
				</nav>

				<div className="min-w-0 border-t border-slate-100 pt-5 lg:border-t-0 lg:pt-0">
					<p className="mb-1 text-[11px] font-mono uppercase tracking-[0.12em] text-slate-400">
						{String(activeIndex + 1).padStart(2, "0")} — {activeMeta.label}
					</p>

					<div className={hidden("setup")}>
						<SectionBody title="Activation & consentement" description="Configure le CMP et les options de visibilité essentielles.">
							<ActivationHero enabled={form.enabled} onChange={(v) => set("enabled", v)} />

							<div className="grid gap-5 lg:grid-cols-[minmax(0,1fr)_200px]">
								<FieldGroup title="Solution de consentement" description="Comment le consentement est collecté et les tags chargés.">
									<ServiceTypePicker value={form.service_type} onChange={(v) => set("service_type", v)} meta={serviceTypeMeta} />
									{isSirdata ? (
										<FieldGrid>
											<TextField label="SirData — utilisateur" value={form.sirdata_user} onChange={(v) => set("sirdata_user", v)} />
											<TextField label="SirData — site" value={form.sirdata_site} onChange={(v) => set("sirdata_site", v)} />
										</FieldGrid>
									) : null}
								</FieldGroup>

								<div className="rounded-xl border border-slate-200 bg-slate-50/80 p-4 lg:sticky lg:top-4 lg:self-start">
									<p className="m-0 mb-2 text-xs font-semibold text-slate-700">Checklist</p>
									<SetupChecklist form={form} />
								</div>
							</div>

							<FieldGroup title="Visibilité" description="Contrôle ce que voient les visiteurs sur le site." compact>
								<div className="grid gap-2 sm:grid-cols-2">
									<SwitchField
										label="Masquer la bannière"
										checked={form.hide_banner}
										onChange={(v) => set("hide_banner", v)}
										hint="Le choix cookies doit rester accessible."
										compact
									/>
									<SwitchField
										label="Masquer le bouton cookies"
										checked={form.hide_cookie_button}
										onChange={(v) => set("hide_cookie_button", v)}
										compact
									/>
								</div>
							</FieldGroup>

							{isTac ? (
								<CollapsibleFieldGroup title="Options avancées tarteaucitron" description="CDN et chargement des scripts." badge="Avancé">
									<SwitchField
										label="Charger depuis jsDelivr (CDN)"
										checked={form.tac_use_cdn !== false}
										onChange={(v) => set("tac_use_cdn", v)}
										hint="Désactivé = copie locale embarquée."
									/>
								</CollapsibleFieldGroup>
							) : null}
						</SectionBody>
					</div>

					<div className={hidden("appearance")}>
						<SectionBody
							title="Apparence de la bannière"
							description="Couleurs, typographie, textes et comportement — aperçu en direct à droite."
						>
							<RgpdThemeFields form={form} set={set} showTacOptions={isTac} />
						</SectionBody>
					</div>

					<div className={hidden("identity")}>
						<SectionBody title="Identité & entreprise" description="Coordonnées injectées dans les pages légales générées.">
							<FieldGroup title="Recherche entreprise" description="Annuaire officiel — préremplit les champs société." accent>
								<div className="flex flex-col gap-2 sm:flex-row sm:items-end">
									<div className="min-w-0 flex-1">
										<TextField
											label="SIRET, SIREN ou raison sociale"
											value={lookupQuery}
											onChange={setLookupQuery}
											placeholder="Ex. 552 100 554 00015"
											onKeyDown={lookupOnEnter}
										/>
									</div>
									<button
										type="button"
										onClick={lookup}
										disabled={busy !== ""}
										className="inline-flex min-h-10 shrink-0 items-center justify-center rounded-xl bg-[var(--au-primary)] px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[var(--au-primary-hover)] disabled:opacity-50"
									>
										{busy === "lookup" ? "Recherche…" : "Récupérer"}
									</button>
								</div>
							</FieldGroup>

							<CollapsibleFieldGroup title="Éditeur du site" description="Nom, contact et adresse." defaultOpen>
								<FieldGrid>
									<TextField label="Nom du site" value={form.site_name} onChange={(v) => set("site_name", v)} />
									<TextField label="Mail DPO / RGPD" type="email" value={form.mail} onChange={(v) => set("mail", v)} />
									<TextField label="Adresse postale" value={form.address} onChange={(v) => set("address", v)} />
									<TextField label="Page contact" value={form.contact_url} onChange={(v) => set("contact_url", v)} placeholder="https://…" />
								</FieldGrid>
							</CollapsibleFieldGroup>

							<CollapsibleFieldGroup title="Société" description="Rempli via la recherche ou saisie manuelle.">
								<FieldGrid>
									<TextField label="Raison sociale" value={form.legal_company_name} onChange={(v) => set("legal_company_name", v)} />
									<TextField label="Forme juridique" value={form.legal_form} onChange={(v) => set("legal_form", v)} />
									<TextField label="SIRET" value={form.legal_siret} onChange={(v) => set("legal_siret", v)} />
									<TextField label="TVA intracommunautaire" value={form.legal_tva} onChange={(v) => set("legal_tva", v)} />
									<TextField label="Capital social" value={form.legal_capital} onChange={(v) => set("legal_capital", v)} hint="Non fourni par l'annuaire." />
									<TextField label="RCS" value={form.legal_rcs} onChange={(v) => set("legal_rcs", v)} hint="Non fourni par l'annuaire." />
								</FieldGrid>
								<TextField label="Directeur de publication" value={form.legal_publication_director} onChange={(v) => set("legal_publication_director", v)} />
							</CollapsibleFieldGroup>

							<CollapsibleFieldGroup
								title="Hébergeur"
								description="OVH, o2switch, IONOS, Gandi…"
								toolbar={
									<button
										type="button"
										onClick={hostLookup}
										disabled={busy !== ""}
										className="inline-flex min-h-9 items-center rounded-lg bg-[var(--au-primary)] px-4 py-2 text-sm font-semibold text-white hover:bg-[var(--au-primary-hover)] disabled:opacity-50"
									>
										{busy === "host-lookup" ? "Détection…" : "Détecter automatiquement"}
									</button>
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
						<SectionBody title="Pages légales" description="Associe des pages existantes ou laisse vide pour en créer de nouvelles.">
							<div className="grid gap-3">
								{PAGE_FIELDS.map((pf) => {
									const pageId = Number(form[pf.key] || 0);
									const linked = pageId > 0 ? pageById[pageId] : null;
									return (
										<div
											key={pf.key}
											className={`rounded-2xl border p-5 shadow-[0_1px_2px_rgba(15,23,42,0.03)] transition ${
												linked ? "border-emerald-200/70 bg-emerald-50/25" : "border-slate-200/90 bg-white"
											}`}
										>
											<div className="mb-3 flex flex-wrap items-start justify-between gap-2">
												<div>
													<p className="m-0 text-sm font-semibold text-slate-900">
														{pf.icon} {pf.label}
													</p>
													<p className="m-0 mt-0.5 text-xs text-slate-500">{pf.desc}</p>
												</div>
												{linked ? (
													<span className="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
														Associée
													</span>
												) : (
													<span className="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">
														Non définie
													</span>
												)}
											</div>
											<SelectField
												label=""
												value={pageId}
												onChange={(v) => set(pf.key, Number(v))}
												options={pageOptions}
											/>
										</div>
									);
								})}
							</div>

							<div className="rounded-xl border border-dashed border-slate-300 bg-slate-50/60 p-4">
								<p className="m-0 text-sm text-slate-600">
									Génère ou met à jour le contenu des pages sélectionnées, puis les passe en{" "}
									<strong className="font-semibold text-slate-800">noindex</strong>.
								</p>
								<button
									type="button"
									onClick={generatePages}
									disabled={busy !== ""}
									className="mt-3 inline-flex min-h-10 items-center rounded-xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-700 disabled:opacity-50"
								>
									{busy === "pages" ? "Génération…" : "Générer / mettre à jour les pages"}
								</button>
							</div>
						</SectionBody>
					</div>

					<div className={hidden("tracking")}>
						<SectionBody title="Mesure & e-commerce" description="Tags après consentement (TarteAuCitron) ou direct (Matomo sans cookie).">
							<FieldGroup title="Analytics & tags" description="Un GTM active automatiquement le Consent Mode v2.">
								<CmpTagsEditor
									tags={tacTags}
									onChange={setTags}
									addToast={addToast}
									serviceType={form.service_type || "tarteaucitron"}
									gcmJobsEnabled={Array.isArray(form.gcm_jobs_enabled) ? form.gcm_jobs_enabled : []}
									onGcmJobsChange={(jobs) => set("gcm_jobs_enabled", jobs)}
								/>
							</FieldGroup>

							<CollapsibleFieldGroup title="WooCommerce (GA4)" description="Events e-commerce : add_to_cart, purchase…" defaultOpen={Boolean(form.woo_tracking?.enabled)}>
								<SwitchField label="Activer le tracking WooCommerce" checked={form.woo_tracking?.enabled} onChange={(v) => setWoo("enabled", v)} />
								{form.woo_tracking?.enabled ? (
									<FieldGrid>
										<TextField
											label="Classe « Ajouter au panier »"
											value={form.woo_tracking?.add_to_cart_btn_class}
											onChange={(v) => setWoo("add_to_cart_btn_class", v)}
											hint="Défaut : a.button.add_to_cart_button"
										/>
										<TextField
											label="Classe « Retirer du panier »"
											value={form.woo_tracking?.remove_from_cart_btn_class}
											onChange={(v) => setWoo("remove_from_cart_btn_class", v)}
											hint="Défaut : .aky-gdpr-tracking-remove-from-cart"
										/>
									</FieldGrid>
								) : null}
							</CollapsibleFieldGroup>
						</SectionBody>
					</div>
				</div>
			</section>

			<div className="sticky bottom-4 z-10 mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white/95 px-4 py-3 shadow-lg backdrop-blur-sm">
				<div className="flex items-center gap-2">
					<button
						type="button"
						onClick={() => goSection(-1)}
						disabled={activeIndex <= 0 || busy !== ""}
						className="inline-flex min-h-9 items-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-40"
					>
						← Précédent
					</button>
					<button
						type="button"
						onClick={() => goSection(1)}
						disabled={activeIndex >= SECTIONS.length - 1 || busy !== ""}
						className="inline-flex min-h-9 items-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-40"
					>
						Suivant →
					</button>
				</div>
				<span className="hidden text-xs text-slate-400 sm:inline">
					{activeMeta.label} · {activeIndex + 1}/{SECTIONS.length}
				</span>
				<button
					type="button"
					onClick={save}
					disabled={busy !== ""}
					className="ml-auto inline-flex min-h-11 items-center rounded-xl bg-[var(--au-primary)] px-6 py-2.5 font-semibold text-white shadow-sm hover:bg-[var(--au-primary-hover)] disabled:opacity-50"
				>
					{busy === "save" ? "Enregistrement…" : "Enregistrer"}
				</button>
			</div>
		</div>
	);
}
