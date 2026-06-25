import { useEffect, useMemo, useState } from "react";
import { apiFetch } from "../../services/api";
import { notifyRestFailure } from "../../utils/restError";

const GTM_TAG_IDS = new Set(["googletagmanager", "multiplegoogletagmanager"]);

function allGcmJobIds(catalog) {
	return catalog?.googleConsentMode?.jobs?.length
		? catalog.googleConsentMode.jobs
		: ["gcmadsuserdata", "gcmadstorage", "gcmanalyticsstorage", "gcmpersonalization", "gcmfunctionality", "gcmsecurity"];
}

function isGtmTag(id) {
	return GTM_TAG_IDS.has(id);
}

function CodeBlock({ label, code, onCopy }) {
	if (!code) return null;
	return (
		<div className="grid gap-1">
			<div className="flex items-center justify-between gap-2">
				<span className="text-xs font-medium text-slate-600">{label}</span>
				<button type="button" onClick={() => onCopy(code)} className="text-xs font-semibold text-[var(--au-primary)] hover:underline">
					Copier
				</button>
			</div>
			<pre className="m-0 max-h-40 overflow-auto rounded-lg border border-slate-200 bg-slate-900 p-3 text-[11px] leading-relaxed text-slate-100">{code}</pre>
		</div>
	);
}

function TagCard({ tag, def, onChange, onRemove, onCopy, tagMode }) {
	const [open, setOpen] = useState(false);
	const fields = Array.isArray(def?.fields) ? def.fields : [];
	const mode = def?.renderMode || tagMode || "tarteaucitron_job";

	const setParam = (key, value) => {
		onChange({ ...tag, params: { ...(tag.params || {}), [key]: value } });
	};

	const modeLabel =
		mode === "sirdata_head"
			? "injection head"
			: mode === "matomo_direct"
				? "script direct"
				: "tarteaucitron.job";

	return (
		<div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
			<div className="flex flex-wrap items-start justify-between gap-2">
				<div>
					<p className="m-0 text-sm font-semibold text-slate-900">{def?.name || tag.id}</p>
					<p className="m-0 mt-0.5 text-xs text-slate-500">
						{def?.category || "—"} · {modeLabel}
						{Array.isArray(def?.jobs) && def.jobs.length ? ` · ${def.jobs.join(", ")}` : ""}
					</p>
				</div>
				<div className="flex gap-2">
					<button type="button" onClick={() => setOpen((v) => !v)} className="text-xs font-semibold text-slate-600 hover:text-[var(--au-primary)]">
						{open ? "Masquer le code" : "Voir le code"}
					</button>
					<button type="button" onClick={onRemove} className="text-xs font-semibold text-red-600 hover:underline">
						Retirer
					</button>
				</div>
			</div>

			<div className="mt-3 grid gap-3">
				{fields.length === 0 ? (
					<p className="m-0 text-xs text-slate-500">Aucun paramètre requis.</p>
				) : (
					fields.map((field) => (
						<label key={field.key} className="grid gap-1">
							<span className="text-xs font-medium text-slate-700">{field.label || field.key}</span>
							<input
								type={field.type === "url" ? "url" : "text"}
								className="min-h-9 rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
								value={tag.params?.[field.key] ?? ""}
								onChange={(e) => setParam(field.key, e.target.value)}
								placeholder={field.key === "multiplegoogletagmanagerId" ? "GTM-XXXX|GTM-YYYY" : ""}
							/>
							{field.key === "multiplegoogletagmanagerId" ? (
								<span className="text-xs text-slate-400">Plusieurs conteneurs GTM séparés par |</span>
							) : null}
						</label>
					))
				)}
			</div>

			{open ? (
				<div className="mt-4 grid gap-3 border-t border-slate-100 pt-4">
					<CodeBlock label="➕ Code à intégrer" code={def?.addCode} onCopy={onCopy} />
					{def?.addCodePlacement ? <CodeBlock label="➕ Code à placer dans le contenu" code={def.addCodePlacement} onCopy={onCopy} /> : null}
					{def?.removeCode ? <CodeBlock label="🗑️ Code à supprimer du site" code={def.removeCode} onCopy={onCopy} /> : null}
				</div>
			) : null}
		</div>
	);
}

function GcmSignalCard({ id, def, onRemove, onCopy }) {
	return (
		<div className="ml-3 rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 shadow-sm sm:ml-6">
			<div className="flex flex-wrap items-start justify-between gap-2">
				<div>
					<p className="m-0 text-sm font-semibold text-slate-900">{def?.name || id}</p>
					<p className="m-0 mt-0.5 text-xs text-slate-500">
						Google Consent Mode v2 · tarteaucitron.job · {id}
					</p>
					<p className="m-0 mt-1 text-xs font-medium text-emerald-700">Ajouté automatiquement avec le GTM</p>
				</div>
				<div className="flex gap-2">
					{def?.addCode ? (
						<button type="button" onClick={() => onCopy(def.addCode)} className="text-xs font-semibold text-slate-600 hover:text-[var(--au-primary)]">
							Copier le code
						</button>
					) : null}
					<button type="button" onClick={onRemove} className="text-xs font-semibold text-red-600 hover:underline">
						Retirer
					</button>
				</div>
			</div>
		</div>
	);
}

export function CmpTagsEditor({ tags, onChange, addToast, serviceType = "tarteaucitron", gcmJobsEnabled, onGcmJobsChange }) {
	const [catalog, setCatalog] = useState(null);
	const [loading, setLoading] = useState(true);
	const [syncing, setSyncing] = useState(false);
	const [query, setQuery] = useState("");
	const [category, setCategory] = useState("");
	const [pickerOpen, setPickerOpen] = useState(false);

	const effectiveType = serviceType || "tarteaucitron";
	const isTac = effectiveType === "tarteaucitron";

	const loadCatalog = () => {
		setLoading(true);
		return apiFetch({ path: `/akyos-updates/v1/rgpd/cmp-catalog?service_type=${encodeURIComponent(effectiveType)}`, method: "GET" })
			.then((data) => setCatalog(data))
			.catch((error) => notifyRestFailure("RGPD / catalogue tags", error, addToast))
			.finally(() => setLoading(false));
	};

	useEffect(() => {
		setPickerOpen(false);
		setQuery("");
		setCategory("");
		loadCatalog();
		// eslint-disable-next-line react-hooks/exhaustive-deps -- recharge quand le CMP change
	}, [effectiveType]);

	const allGcmJobs = useMemo(() => allGcmJobIds(catalog), [catalog]);
	const enabledGcmJobs = useMemo(
		() => (Array.isArray(gcmJobsEnabled) ? gcmJobsEnabled.filter((id) => allGcmJobs.includes(id)) : []),
		[gcmJobsEnabled, allGcmJobs]
	);
	const hasGtm = useMemo(() => (tags || []).some((t) => isGtmTag(t.id)), [tags]);

	useEffect(() => {
		if (!hasGtm || !onGcmJobsChange || allGcmJobs.length === 0) {
			return;
		}
		if (enabledGcmJobs.length === 0) {
			onGcmJobsChange(allGcmJobs);
		}
	}, [hasGtm, enabledGcmJobs.length, allGcmJobs, onGcmJobsChange]);

	const syncCatalog = () => {
		if (!isTac) {
			loadCatalog();
			return;
		}
		setSyncing(true);
		apiFetch({ path: "/akyos-updates/v1/rgpd/cmp-catalog", method: "POST" })
			.then((data) => {
				setCatalog(data);
				addToast(data?.message || "Catalogue synchronisé.", data?.success ? "success" : "error");
			})
			.catch((error) => notifyRestFailure("RGPD / sync catalogue", error, addToast))
			.finally(() => setSyncing(false));
	};

	const services = useMemo(() => (Array.isArray(catalog?.services) ? catalog.services : []), [catalog]);
	const categories = useMemo(() => (Array.isArray(catalog?.categories) ? catalog.categories : []), [catalog]);

	const byId = useMemo(() => {
		const map = new Map();
		for (const s of services) map.set(s.id, s);
		return map;
	}, [services]);

	const configuredIds = useMemo(() => new Set((tags || []).map((t) => t.id)), [tags]);
	const gcmJobs = useMemo(() => new Set(catalog?.googleConsentMode?.jobs || []), [catalog]);

	const filtered = useMemo(() => {
		const q = query.trim().toLowerCase();
		return services.filter((s) => {
			if (gcmJobs.has(s.id)) {
				return false;
			}
			if (category && s.category !== category) return false;
			if (configuredIds.has(s.id)) return false;
			if (!q) return true;
			return s.name.toLowerCase().includes(q) || s.id.toLowerCase().includes(q) || s.category.toLowerCase().includes(q);
		});
	}, [services, query, category, configuredIds, gcmJobs]);

	const copy = async (text) => {
		try {
			await navigator.clipboard.writeText(text);
			addToast("Code copié.", "success");
		} catch {
			addToast("Copie impossible.", "error");
		}
	};

	const addTag = (service) => {
		onChange([...(tags || []), { id: service.id, params: {} }]);
		if (isGtmTag(service.id)) {
			onGcmJobsChange?.(allGcmJobIds(catalog));
		}
		setPickerOpen(false);
		setQuery("");
		const gcmHint = isGtmTag(service.id) ? " Consent Mode v2 activé (modifiable ci-dessus)." : "";
		addToast(`Service « ${service.name} » ajouté.${gcmHint}`, "success");
	};

	const updateTag = (index, next) => {
		const list = [...(tags || [])];
		list[index] = next;
		onChange(list);
	};

	const removeGcmJob = (id) => {
		onGcmJobsChange?.(enabledGcmJobs.filter((jobId) => jobId !== id));
	};

	const addAllGcmJobs = () => {
		onGcmJobsChange?.(allGcmJobs);
	};

	const removeTag = (index) => {
		onChange((tags || []).filter((_, i) => i !== index));
	};

	if (loading) {
		return <p className="text-sm text-slate-500">Chargement du catalogue…</p>;
	}

	const tagMode = catalog?.tagMode || "tarteaucitron_job";

	return (
		<div className="grid gap-4">
			{catalog?.hint ? (
				<p className="m-0 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">{catalog.hint}</p>
			) : null}

			<div className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-[rgb(var(--au-primary-rgb)/0.2)] bg-[rgb(var(--au-primary-rgb)/0.031)] px-4 py-3">
				<div className="text-sm text-slate-600">
					<p className="m-0">
						Catalogue <strong className="text-slate-800">{services.length} services</strong>
						{catalog?.source ? (
							<>
								{" "}
								· <code className="text-xs">{catalog.source}</code>
							</>
						) : null}
					</p>
					{catalog?.syncedAt ? (
						<p className="m-0 mt-1 text-xs text-slate-500">Dernière sync CDN : {new Date(catalog.syncedAt).toLocaleString("fr-FR")}</p>
					) : null}
					{isTac && catalog?.cdnBase ? (
						<p className="m-0 mt-1 text-xs text-slate-500">Librairie front : {catalog.cdnBase}/tarteaucitron.js</p>
					) : null}
				</div>
				{isTac ? (
					<button
						type="button"
						onClick={syncCatalog}
						disabled={syncing}
						className="inline-flex min-h-9 shrink-0 items-center rounded-lg border border-[var(--au-primary)] bg-white px-3 py-1.5 text-xs font-semibold text-[var(--au-primary)] hover:bg-[rgb(var(--au-primary-rgb)/0.031)] disabled:opacity-50"
					>
						{syncing ? "Sync…" : "Synchroniser depuis CDN"}
					</button>
				) : (
					<button
						type="button"
						onClick={syncCatalog}
						className="inline-flex min-h-9 shrink-0 items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"
					>
						Actualiser
					</button>
				)}
			</div>

			{(tags || []).length === 0 ? (
				<p className="m-0 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500">
					Aucun tag configuré. Ajoute GTM, Matomo, Pixel Facebook, YouTube, Clarity…
				</p>
			) : (
				<div className="grid gap-3">
					{(tags || []).map((tag, index) => (
						<div key={`${tag.id}-${index}`} className="grid gap-2">
							<TagCard
								tag={tag}
								def={byId.get(tag.id)}
								tagMode={tagMode}
								onChange={(next) => updateTag(index, next)}
								onRemove={() => removeTag(index)}
								onCopy={copy}
							/>
							{isGtmTag(tag.id)
								? enabledGcmJobs.map((gcmId) => (
										<GcmSignalCard
											key={`gcm-${gcmId}`}
											id={gcmId}
											def={byId.get(gcmId)}
											onRemove={() => removeGcmJob(gcmId)}
											onCopy={copy}
										/>
									))
								: null}
							{isGtmTag(tag.id) && enabledGcmJobs.length < allGcmJobs.length ? (
								<button
									type="button"
									onClick={addAllGcmJobs}
									className="ml-3 text-left text-xs font-semibold text-[var(--au-primary)] hover:underline sm:ml-6"
								>
									Réactiver tous les signaux Consent Mode v2 ({allGcmJobs.length - enabledGcmJobs.length} manquant
									{allGcmJobs.length - enabledGcmJobs.length > 1 ? "s" : ""})
								</button>
							) : null}
						</div>
					))}
				</div>
			)}

			<div className="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
				<button
					type="button"
					onClick={() => setPickerOpen((v) => !v)}
					className="inline-flex min-h-10 items-center rounded-xl border border-[var(--au-primary)] bg-white px-4 py-2 text-sm font-semibold text-[var(--au-primary)] shadow-sm transition hover:bg-[rgb(var(--au-primary-rgb)/0.031)]"
				>
					{pickerOpen ? "Fermer le catalogue" : "+ Ajouter un service"}
				</button>

				{pickerOpen ? (
					<div className="mt-4 grid gap-3">
						<div className="flex flex-col gap-2 sm:flex-row">
							<input
								type="search"
								className="min-h-10 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"
								placeholder="Rechercher un service (GTM, Matomo, Clarity…)"
								value={query}
								onChange={(e) => setQuery(e.target.value)}
							/>
							<select
								className="min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm sm:w-56"
								value={category}
								onChange={(e) => setCategory(e.target.value)}
							>
								<option value="">Toutes catégories</option>
								{categories.map((c) => (
									<option key={c} value={c}>
										{c}
									</option>
								))}
							</select>
						</div>
						<ul className="m-0 max-h-64 list-none overflow-auto rounded-lg border border-slate-200 bg-white p-1">
							{filtered.slice(0, 40).map((s) => (
								<li key={s.id}>
									<button
										type="button"
										onClick={() => addTag(s)}
										className="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50"
									>
										<span className="font-medium text-slate-900">{s.name}</span>
										<span className="shrink-0 text-xs text-slate-400">{s.category}</span>
									</button>
								</li>
							))}
							{filtered.length === 0 ? <li className="px-3 py-2 text-sm text-slate-500">Aucun service trouvé.</li> : null}
							{filtered.length > 40 ? (
								<li className="px-3 py-2 text-xs text-slate-400">Affine la recherche ({filtered.length} résultats).</li>
							) : null}
						</ul>
					</div>
				) : null}
			</div>
		</div>
	);
}

/** @deprecated alias */
export const TarteaucitronTagsEditor = CmpTagsEditor;

export function ServiceTypePicker({ value, onChange, meta = {} }) {
	const types = Object.keys(meta).length
		? Object.entries(meta)
		: [
				["tarteaucitron", { label: "TarteAuCitron", description: "CMP open source intégré.", docUrl: "https://tarteaucitron.io/installation-gratuite-open-source/", tagMode: "tarteaucitron" }],
				["sirdata", { label: "SirData", description: "CMP externalisé.", docUrl: "https://www.sirdata.com/", tagMode: "sirdata" }],
				["matomo_no_cookie", { label: "Matomo sans cookie", description: "MTM direct.", docUrl: "https://matomo.org/guide/tag-manager/", tagMode: "matomo_direct" }],
			];

	return (
		<div className="grid gap-2 sm:grid-cols-1">
			{types.map(([id, info]) => {
				const active = value === id;
				return (
					<button
						key={id}
						type="button"
						onClick={() => onChange(id)}
						className={`rounded-xl border p-4 text-left transition ${
							active ? "border-[var(--au-primary)] bg-[rgb(var(--au-primary-rgb)/0.031)] shadow-sm" : "border-slate-200 hover:border-[rgb(var(--au-primary-rgb)/0.2)]"
						}`}
					>
						<p className="m-0 text-sm font-semibold text-slate-900">{info.label || id}</p>
						<p className="m-0 mt-1 text-xs text-slate-500">{info.description}</p>
						{info.docUrl ? (
							<a
								href={info.docUrl}
								target="_blank"
								rel="noopener noreferrer"
								onClick={(e) => e.stopPropagation()}
								className="mt-2 inline-block text-xs font-semibold text-[var(--au-primary)] hover:underline"
							>
								Documentation →
							</a>
						) : null}
					</button>
				);
			})}
		</div>
	);
}
