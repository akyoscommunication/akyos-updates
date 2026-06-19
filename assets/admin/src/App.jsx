import { useEffect, useMemo, useState } from "react";
import { apiFetch } from "./services/api";
import { DashboardInfos } from "./components/DashboardInfos";
import { AnalysisModal } from "./components/modals/AnalysisModal";
import { ReportSection } from "./components/report/ReportSection";
import { ComposerJsonModal } from "./components/categories/Plugins/ComposerJsonModal";
import { ComposerProposalsModal } from "./components/categories/Plugins/ComposerProposalsModal";
import { GitignoreProposalsModal } from "./components/categories/Plugins/GitignoreProposalsModal";
import { RelaunchAnalysisModal } from "./components/modals/RelaunchAnalysisModal";
import { Toasts } from "./components/ui/Toasts";
import { RgpdSettingsPanel } from "./components/rgpd/RgpdSettingsPanel";
import { useToasts } from "./hooks/useToasts";
import { getVisibleReportCategoryNames } from "./utils/reportCategories";
import { notifyRestFailure } from "./utils/restError";
import { useClipboardCopy } from "./hooks/useClipboardCopy";
import { useReportSummaryTotals } from "./hooks/useReportSummaryTotals";
import { defaultCategorySelection, useCategorySelection } from "./hooks/useCategorySelection";

const ACTIVE_CATEGORY_STORAGE_KEY = "akyos_updates_active_category";
const ACTIVE_TAB_STORAGE_KEY = "akyos_updates_active_tab";

const CATEGORY_FALLBACK = ["WordPress", "Images", "Plugins", "Performance", "Sécurité", "SEO", "Back-office", "RGPD"];
const STEP_PARALLEL_LIMIT = 5;
const STEP_PARALLEL_DELAY_MS = 450;

function wait(ms) {
	return new Promise((resolve) => {
		window.setTimeout(resolve, ms);
	});
}

function pickStepResponse(fulfilled) {
	const withReport = fulfilled.filter((item) => item?.done && item?.report);
	if (withReport.length > 0) {
		return withReport[0];
	}

	const doneItems = fulfilled.filter((item) => item?.done);
	if (doneItems.length > 0) {
		const withoutError = doneItems.find((item) => !item?.error);
		return withoutError || doneItems[0];
	}

	return fulfilled.reduce((best, current) =>
		Number(current?.cursor || 0) > Number(best?.cursor || 0) ? current : best
	);
}

async function loadPersistedReport() {
	const data = await apiFetch({ path: "/akyos-updates/v1/report", method: "GET" });
	if (data?.report?.results?.length) {
		return data.report;
	}
	if (data?.results?.length) {
		return data;
	}
	return null;
}

async function runWithConcurrencyLimit(taskFactories, concurrency) {
	const limit = Math.max(1, Math.min(concurrency, taskFactories.length || 1));
	const results = new Array(taskFactories.length);
	let cursor = 0;

	const workers = Array.from({ length: limit }, async () => {
		while (cursor < taskFactories.length) {
			const i = cursor;
			cursor += 1;
			try {
				const value = await taskFactories[i]();
				results[i] = { status: "fulfilled", value };
			} catch (reason) {
				results[i] = { status: "rejected", reason };
			}
		}
	});

	await Promise.allSettled(workers);
	return results;
}

export function App() {
	const [overview, setOverview] = useState(window.AKYOS_UPDATES_BOOTSTRAP?.overview || {});
	const [running, setRunning] = useState(false);
	const [cursor, setCursor] = useState(0);
	const [total, setTotal] = useState(0);
	const [currentEvent, setCurrentEvent] = useState(null);
	const [report, setReport] = useState(null);
	const [currentStep, setCurrentStep] = useState("");
	const [composerTemplateModal, setComposerTemplateModal] = useState({
		open: false,
		content: "",
		targetPath: "",
	});
	const [composerProposalsModal, setComposerProposalsModal] = useState({
		open: false,
		content: "",
		unavailableSlugs: [],
	});
	const [gitignoreProposalsModal, setGitignoreProposalsModal] = useState({
		open: false,
		content: "",
		installationType: "vanilla",
	});
	const [fixingKey, setFixingKey] = useState(null);
	const [activeTab, setActiveTab] = useState(() => {
		if (typeof window === "undefined") {
			return "maintenance";
		}
		return window.localStorage.getItem(ACTIVE_TAB_STORAGE_KEY) || "maintenance";
	});
	const { toasts, addToast } = useToasts();
	const appNode = typeof document !== "undefined" ? document.getElementById("akyos-updates-admin-app") : null;
	const logoUrl =
		window.AKYOS_UPDATES_BOOTSTRAP?.logoUrl ||
		appNode?.dataset?.logoUrl ||
		"";
	const restDebugBanner = Boolean(
		typeof window !== "undefined" && window.AKYOS_UPDATES_BOOTSTRAP?.restDebug
	);

	const categoryNames = useMemo(() => {
		const b = window.AKYOS_UPDATES_BOOTSTRAP?.reportCategories;
		return Array.isArray(b) && b.length ? b : CATEGORY_FALLBACK;
	}, []);

	const {
		selection: dashCategorySelection,
		toggle: toggleDashCategory,
	} = useCategorySelection(
		Array.isArray(window.AKYOS_UPDATES_BOOTSTRAP?.reportCategories) &&
			window.AKYOS_UPDATES_BOOTSTRAP.reportCategories.length
			? window.AKYOS_UPDATES_BOOTSTRAP.reportCategories
			: CATEGORY_FALLBACK
	);

	const [relaunchModalOpen, setRelaunchModalOpen] = useState(false);
	const {
		selection: relaunchSelection,
		setSelection: setRelaunchSelection,
		toggle: toggleRelaunchCategory,
	} = useCategorySelection(CATEGORY_FALLBACK);

	const hydrateFaviconReport = async (sourceReport) => {
		const reportToHydrate = sourceReport || null;
		if (!reportToHydrate?.results?.length) {
			return reportToHydrate;
		}

		const faviconIndex = reportToHydrate.results.findIndex((item) => item.id === "wordpress.favicon");
		if (faviconIndex === -1) {
			return reportToHydrate;
		}

		const faviconResult = reportToHydrate.results[faviconIndex];
		if (faviconResult?.payload?.faviconUrl) {
			return reportToHydrate;
		}

		let siteIconId = Number(faviconResult?.payload?.siteIconId || 0);
		if (!siteIconId) {
			try {
				const settings = await apiFetch({ path: "/wp/v2/settings", method: "GET" });
				siteIconId = Number(settings?.site_icon || 0);
			} catch {
				siteIconId = 0;
			}
		}

		if (!siteIconId) {
			return reportToHydrate;
		}

		let faviconUrl = "";
		try {
			const media = await apiFetch({ path: `/wp/v2/media/${siteIconId}`, method: "GET" });
			faviconUrl = media?.source_url || "";
		} catch {
			faviconUrl = "";
		}

		if (!faviconUrl) {
			return reportToHydrate;
		}

		const hydratedResults = reportToHydrate.results.map((item) =>
			item.id === "wordpress.favicon"
				? {
					...item,
					payload: {
						...(item.payload || {}),
						siteIconId,
						faviconUrl,
					},
				}
				: item
		);

		return {
			...reportToHydrate,
			results: hydratedResults,
		};
	};

	const applyReport = (nextReport) => {
		setReport(nextReport || null);
		const categoryNames = getVisibleReportCategoryNames(nextReport);
		const storedCategory =
			typeof window !== "undefined" ? window.localStorage.getItem(ACTIVE_CATEGORY_STORAGE_KEY) : "";
		const nextStep = storedCategory && categoryNames.includes(storedCategory) ? storedCategory : categoryNames[0] || "";
		setCurrentStep(nextStep);

		hydrateFaviconReport(nextReport).then((hydrated) => {
			if (!hydrated || hydrated === nextReport) {
				return;
			}
			setReport(hydrated);
		});
	};

	const pollStep = (sessionId, pollOptions = {}) =>
		Promise.resolve().then(async () => {
			const requestCount = STEP_PARALLEL_LIMIT;
			const settled = await runWithConcurrencyLimit(
				Array.from({ length: requestCount }, () => () =>
					apiFetch({ path: "/akyos-updates/v1/analyze/step", method: "POST", data: { sessionId } })
				),
				requestCount
			);
			const fulfilled = settled.filter((item) => item?.status === "fulfilled").map((item) => item.value);
			if (!fulfilled.length) {
				const firstRejected = settled.find((item) => item?.status === "rejected");
				throw firstRejected?.reason || new Error("Aucune réponse step exploitable.");
			}

			const response = pickStepResponse(fulfilled);
			const shouldRetry = fulfilled.some((item) => item?.retry === true);

			const successToastMessage = pollOptions.successToast ?? "Analyse terminée.";
			if (response.done) {
				setRunning(false);
				setCurrentEvent(null);
				if (response.report) {
					applyReport(response.report);
					addToast(successToastMessage, "success");
					return;
				}

				const persistedReport = await loadPersistedReport();
				if (persistedReport) {
					applyReport(persistedReport);
					addToast(successToastMessage, "success");
					return;
				}

				if (response.error) {
					addToast(response.error, "error");
				}
				return;
			}

			if (shouldRetry) {
				await wait(STEP_PARALLEL_DELAY_MS);
				return pollStep(sessionId, pollOptions);
			}

			if (response.event) {
				setCurrentEvent(response.event);
			}
			setCursor(response.cursor || 0);
			await wait(STEP_PARALLEL_DELAY_MS);
			return pollStep(sessionId, pollOptions);
		})
			.catch((error) => {
				setRunning(false);
				setCurrentEvent(null);
				notifyRestFailure("analyse / étape", error, addToast);
			});

	const startAnalysis = (mode = "full", opts = null) => {
		const isQuickMode = mode === "quick";
		const explicitCategories = opts && typeof opts === "object" && Array.isArray(opts.categories) ? opts.categories : null;

		let categoriesForFull = null;
		if (mode === "full") {
			if (explicitCategories) {
				categoriesForFull = explicitCategories.filter((c) => categoryNames.includes(c));
			} else {
				categoriesForFull = categoryNames.filter((c) => dashCategorySelection[c]);
			}
			if (!categoriesForFull.length) {
				addToast("Sélectionne au moins une catégorie.", "error");
				return;
			}
		}

		setRunning(true);
		setCurrentEvent(null);
		setReport(null);
		setCurrentStep("");
		setCursor(0);

		const startPayload = { mode };
		if (mode === "full" && categoriesForFull) {
			startPayload.categories = categoriesForFull;
		}

		apiFetch({ path: "/akyos-updates/v1/analyze/start", method: "POST", data: startPayload })
			.then((response) => {
				setTotal(response.totalChecks || 0);
				setOverview(response.overview || {});
				const meta = Array.isArray(response.checkMeta) && response.checkMeta.length > 0 ? response.checkMeta[0] : null;
				if (meta && typeof meta.title === "string" && meta.title.trim() !== "") {
					setCurrentEvent({
						checkId: meta.id,
						category: meta.category,
						title: meta.title,
						message: "Exécution du contrôle…",
						nextCheck: null,
					});
				}
				return pollStep(response.sessionId);
			})
			.catch((error) => {
				setRunning(false);
				notifyRestFailure("analyse / démarrage", error, addToast);
			});
	};

	const openRelaunchModal = () => {
		const included = report?.includedCategories;
		const next = {};
		for (const c of categoryNames) {
			if (Array.isArray(included) && included.length > 0) {
				next[c] = included.includes(c);
			} else {
				next[c] = true;
			}
		}
		setRelaunchSelection(next);
		setRelaunchModalOpen(true);
	};

	const confirmRelaunchAnalysis = () => {
		const cats = categoryNames.filter((c) => relaunchSelection[c]);
		if (!cats.length) {
			addToast("Sélectionne au moins une catégorie.", "error");
			return;
		}
		setRelaunchModalOpen(false);
		startAnalysis("full", { categories: cats });
	};

	const runCategoryRecheck = (categoryName) => {
		const name = typeof categoryName === "string" ? categoryName.trim() : "";
		if (!name || running) {
			return;
		}

		setRunning(true);
		setCurrentEvent(null);
		setCursor(0);

		apiFetch({
			path: "/akyos-updates/v1/analyze/start",
			method: "POST",
			data: { mode: "category", category: name },
		})
			.then((response) => {
				setTotal(response.totalChecks || 0);
				setOverview(response.overview || {});
				const meta = Array.isArray(response.checkMeta) && response.checkMeta.length > 0 ? response.checkMeta[0] : null;
				if (meta && typeof meta.title === "string" && meta.title.trim() !== "") {
					setCurrentEvent({
						checkId: meta.id,
						category: meta.category,
						title: meta.title,
						message: "Exécution du contrôle…",
						nextCheck: null,
					});
				}
				return pollStep(response.sessionId, {
					successToast: "Contrôles de la catégorie mis à jour.",
				});
			})
			.catch((error) => {
				setRunning(false);
				notifyRestFailure("analyse / catégorie", error, addToast);
			});
	};

	const runFix = (result, payloadOverride = null) => {
		if (!result.actionId) {
			return Promise.resolve();
		}

		if (result.actionId === "wordpress.set_favicon") {
			const frame = window.wp.media({
				title: "Choisir favicon",
				button: { text: "Utiliser ce média" },
				multiple: false,
			});

			frame.on("select", () => {
				const attachment = frame.state().get("selection").first().toJSON();
				apiFetch({
					path: "/akyos-updates/v1/fix",
					method: "POST",
					data: { actionId: result.actionId, checkId: result.id, payload: { attachmentId: attachment.id } },
				}).then((res) => {
					addToast(res.message || "Action exécutée.", res.success ? "success" : "error");
					if (res.updatedReport) {
						applyReport(res.updatedReport);
					}
				});
			});
			frame.open();
			return Promise.resolve();
		}

		const fixKey =
			result.id && String(result.id).length > 0
				? `${String(result.id)}::${String(result.actionId)}`
				: `::${String(result.actionId)}`;

		const finishFixing = () => {
			setFixingKey((current) => (current === fixKey ? null : current));
		};

		setFixingKey(fixKey);

		if (result.actionId === "plugins.generate_composer_json") {
			return apiFetch({
				path: "/akyos-updates/v1/fix",
				method: "POST",
				data: {
					actionId: result.actionId,
					payload: payloadOverride && typeof payloadOverride === "object" ? payloadOverride : result.payload || {},
				},
			})
				.then((res) => {
					addToast(res.message || "Action exécutée.", res.success ? "success" : "error");
					if (!res.success) {
						return;
					}
					setComposerTemplateModal({
						open: true,
						content: res.composerJson || "",
						targetPath: res.targetPath || "",
					});
				})
				.catch((error) => {
					notifyRestFailure("action / fix (composer)", error, addToast);
				})
				.finally(finishFixing);
		}

		if (result.actionId === "plugins.generate_composer_guidance") {
			return apiFetch({
				path: "/akyos-updates/v1/fix",
				method: "POST",
				data: {
					actionId: result.actionId,
					checkId: result.id,
					payload: payloadOverride && typeof payloadOverride === "object" ? payloadOverride : result.payload || {},
				},
			})
				.then((res) => {
					addToast(res.message || "Action exécutée.", res.success ? "success" : "error");
					if (res.success) {
						setComposerProposalsModal({
							open: true,
							content: typeof res.composerProposalsText === "string" ? res.composerProposalsText : "",
							unavailableSlugs: Array.isArray(res.unavailableSlugs) ? res.unavailableSlugs : [],
						});
					}
					if (res.updatedReport) {
						applyReport(res.updatedReport);
					}
				})
				.catch((error) => {
					notifyRestFailure("action / fix (propositions composer)", error, addToast);
				})
				.finally(finishFixing);
		}

		if (result.actionId === "plugins.generate_gitignore_exceptions") {
			return apiFetch({
				path: "/akyos-updates/v1/fix",
				method: "POST",
				data: {
					actionId: result.actionId,
					checkId: result.id,
					payload: payloadOverride && typeof payloadOverride === "object" ? payloadOverride : result.payload || {},
				},
			})
				.then((res) => {
					addToast(res.message || "Action exécutée.", res.success ? "success" : "error");
					if (res.success) {
						setGitignoreProposalsModal({
							open: true,
							content: typeof res.gitignoreProposalsText === "string" ? res.gitignoreProposalsText : "",
							installationType:
								typeof res.installationType === "string" && res.installationType === "bedrock" ? "bedrock" : "vanilla",
						});
					}
					if (res.updatedReport) {
						applyReport(res.updatedReport);
					}
				})
				.catch((error) => {
					notifyRestFailure("action / fix (.gitignore)", error, addToast);
				})
				.finally(finishFixing);
		}

		return apiFetch({
			path: "/akyos-updates/v1/fix",
			method: "POST",
			data: {
				actionId: result.actionId,
				checkId: result.actionId === "seo.toggle_indexing" ? undefined : result.id,
				payload: payloadOverride && typeof payloadOverride === "object" ? payloadOverride : result.payload || {},
			},
		})
			.then((res) => {
				addToast(res.message || "Action exécutée.", res.success ? "success" : "error");
				if (res.success && result.actionId === "seo.toggle_indexing" && payloadOverride) {
					setReport((previousReport) => {
						if (!previousReport?.results) {
							return previousReport;
						}
						const nextResults = previousReport.results.map((item) => {
							if (item.id === "seo.legal_pages_noindex" && payloadOverride.kind === "post") {
								const payload = item.payload || {};
								const pages = Array.isArray(payload.pages) ? payload.pages : [];
								const nextPages = pages.map((page) =>
									Number(page.id) === Number(payloadOverride.postId) ? { ...page, noindex: !payloadOverride.indexable } : page
								);
								const indexedPagesCount = nextPages.filter((page) => !page.noindex).length;
								return {
									...item,
									status: indexedPagesCount > 0 ? "warn" : "ok",
									severity: indexedPagesCount > 0 ? "warning" : "success",
									message:
										indexedPagesCount > 0
											? `${indexedPagesCount} page(s) légale(s) encore indexable(s).`
											: "Toutes les pages légales détectées sont en noindex.",
									payload: {
										...payload,
										pages: nextPages,
										indexedPagesCount,
									},
								};
							}
							if (item.id !== "seo.indexability") {
								return item;
							}
							const payload = item.payload || {};
							const rows = Array.isArray(payload.rows) ? payload.rows : [];
							const nextRows = rows.map((row) =>
								row.kind === payloadOverride.kind && row.name === payloadOverride.name
									? { ...row, indexable: payloadOverride.indexable }
									: row
							);
							const indexableCount = nextRows.filter((row) => row.indexable).length;
							const noindexCount = nextRows.length - indexableCount;
							return {
								...item,
								status: "info",
								severity: "neutral",
								countsTowardCategoryStats: false,
								message: `Aperçu des réglages d’indexation des archives (plugin SEO). Les choix dépendent du projet ; il n’existe pas de référence unique. ${indexableCount} indexable(s), ${noindexCount} noindex.`,
								payload: {
									...payload,
									rows: nextRows,
									indexableCount,
								},
							};
						});
						return {
							...previousReport,
							results: nextResults,
						};
					});
				}
				if (res.updatedReport) {
					applyReport(res.updatedReport);
				}
			})
			.catch((error) => {
				notifyRestFailure("action / fix", error, addToast);
			})
			.finally(finishFixing);
	};

	useEffect(() => {
		apiFetch({ path: "/akyos-updates/v1/overview", method: "GET" }).then((data) => {
			setOverview(data || {});
		});
		apiFetch({ path: "/akyos-updates/v1/report", method: "GET" }).then((data) => {
			const maybeReport = data?.report && data.report.results ? data.report : data;
			if (maybeReport?.results?.length) {
				applyReport(maybeReport);
			}
		});
	}, []);

	useEffect(() => {
		if (!currentStep || typeof window === "undefined") {
			return;
		}
		window.localStorage.setItem(ACTIVE_CATEGORY_STORAGE_KEY, currentStep);
	}, [currentStep]);

	useEffect(() => {
		if (typeof window === "undefined") {
			return;
		}
		window.localStorage.setItem(ACTIVE_TAB_STORAGE_KEY, activeTab);
	}, [activeTab]);

	const shellClass = useMemo(() => "akyos-updates-shell", []);

	const reportSummaryTotals = useReportSummaryTotals(report);

	const closeComposerModal = () => {
		setComposerTemplateModal({
			open: false,
			content: "",
			targetPath: "",
		});
	};

	const closeComposerProposalsModal = () => {
		setComposerProposalsModal({
			open: false,
			content: "",
			unavailableSlugs: [],
		});
	};

	const closeGitignoreProposalsModal = () => {
		setGitignoreProposalsModal({
			open: false,
			content: "",
			installationType: "vanilla",
		});
	};

	const { copyText } = useClipboardCopy(
		(message) => addToast(message, "success"),
		(message) => addToast(message, "error")
	);

	const copyGitignoreProposalsText = () => {
		copyText(gitignoreProposalsModal.content || "");
	};

	const copyComposerProposalsText = () => {
		copyText(composerProposalsModal.content || "");
	};

	const copyComposerTemplate = () => {
		copyText(composerTemplateModal.content || "", "composer.json copié.");
	};

	return (
		<main className={`${shellClass} relative min-h-screen overflow-hidden bg-slate-50 px-7 pb-16 pt-7 text-slate-900`}>
			<div className="relative z-10 mb-5 flex flex-wrap items-center gap-2">
				{[
					{ id: "maintenance", label: "Maintenance" },
					{ id: "rgpd", label: "RGPD" },
				].map((tab) => (
					<button
						key={tab.id}
						type="button"
						onClick={() => setActiveTab(tab.id)}
						className={`inline-flex min-h-9 items-center rounded-full px-4 py-1.5 text-sm font-semibold transition ${
							activeTab === tab.id
								? "bg-[#0052FF] text-white shadow-sm"
								: "border border-slate-200 bg-white text-slate-600 hover:border-[#0052FF4d]"
						}`}
					>
						{tab.label}
					</button>
				))}
			</div>
			{activeTab === "rgpd" ? (
				<RgpdSettingsPanel addToast={addToast} />
			) : (
				<>
			{report ? <DashboardInfos overview={overview} compact /> : null}

			<div className="relative z-10 mb-3 flex flex-col items-start justify-between gap-3 md:flex-row md:items-center">
				<div className="flex flex-wrap items-center gap-3">
					<h1 className="m-0 text-[44px] font-normal leading-[1.05] tracking-[-0.02em] text-slate-900" style={{ fontFamily: "Calistoga, serif" }}>
						{logoUrl ? <img src={logoUrl} alt="Akyos" className="mr-2 inline-block h-12 w-auto align-middle" /> : "Akyos"}
					</h1>
					{report ? (
						<div className="flex flex-col gap-2">
							<span className="inline-flex w-fit items-center rounded-full border border-[#0052FF3d] bg-[#0052FF0d] px-3 py-1 font-mono text-[12px] uppercase tracking-[0.15em] text-[#0052FF]">
								Rapport
							</span>
							<div className="flex flex-wrap items-center gap-2">
								<span
									className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-2 py-1 text-xs font-bold text-emerald-700"
									title="Succès"
								>
									<span className="inline-block h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,0.15)]" />
									{reportSummaryTotals.ok}
								</span>
								<span
									className="inline-flex items-center gap-1.5 rounded-full bg-amber-500/15 px-2 py-1 text-xs font-bold text-amber-700"
									title="Alertes"
								>
									<span className="inline-block h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500 shadow-[0_0_0_3px_rgba(245,158,11,0.18)]" />
									{reportSummaryTotals.warn}
								</span>
								<span
									className="inline-flex items-center gap-1.5 rounded-full bg-red-500/15 px-2 py-1 text-xs font-bold text-red-700"
									title="Échecs"
								>
									<span className="inline-block h-2.5 w-2.5 shrink-0 rounded-full bg-red-500 shadow-[0_0_0_3px_rgba(239,68,68,0.15)]" />
									{reportSummaryTotals.fail}
								</span>
							</div>
						</div>
					) : null}
				</div>
				{report ? (
					<div className="flex flex-wrap items-center gap-2">
						<button
							className="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-[#0052FF] bg-[#0052FF] px-5 py-2.5 font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[#0047db]"
							onClick={() => startAnalysis("quick")}
							disabled={running}
						>
							<svg viewBox="0 0 24 24" aria-hidden="true" className="h-4 w-4 fill-current">
								<path d="M13 2L4 14h6l-1 8 9-12h-6l1-8z" />
							</svg>
							Analyse rapide
						</button>
						<button
							className="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0052FF4d]"
							onClick={openRelaunchModal}
							disabled={running}
						>
							Relancer l'analyse
						</button>
					</div>
				) : null}
			</div>
			{restDebugBanner ? (
				<p className="mb-4 max-w-3xl text-xs leading-relaxed text-slate-500">
					Debug REST actif : chaque erreur affiche code et statut HTTP dans le toast ; les détails complets (objet
					erreur, fichier/ligne côté PHP si disponible) sont dans la console du navigateur (F12 → Console).
				</p>
			) : null}
			{!report ? (
				<DashboardInfos
					overview={overview}
					running={running}
					onStart={() => startAnalysis("full")}
					categoryNames={categoryNames}
					categorySelection={dashCategorySelection}
					onCategoryToggle={toggleDashCategory}
				/>
			) : null}
			<RelaunchAnalysisModal
				open={relaunchModalOpen}
				onClose={() => setRelaunchModalOpen(false)}
				categoryNames={categoryNames}
				selection={relaunchSelection}
				onToggle={toggleRelaunchCategory}
				onConfirm={confirmRelaunchAnalysis}
				running={running}
			/>
			<AnalysisModal open={running} event={currentEvent} cursor={cursor} total={total} />
			{report ? (
				<ReportSection
					report={report}
					onFix={runFix}
					fixingKey={fixingKey}
					currentStep={currentStep}
					onStepChange={setCurrentStep}
					onCategoryRecheck={runCategoryRecheck}
					onPluginPresenceChange={(nextPresence) => {
						setReport((previousReport) =>
							previousReport ? { ...previousReport, pluginPresence: nextPresence } : previousReport
						);
					}}
					onNotify={addToast}
				/>
			) : null}
			<ComposerJsonModal
				open={composerTemplateModal.open}
				content={composerTemplateModal.content}
				targetPath={composerTemplateModal.targetPath}
				onClose={closeComposerModal}
				onCopy={copyComposerTemplate}
			/>
			<ComposerProposalsModal
				open={composerProposalsModal.open}
				content={composerProposalsModal.content}
				unavailableSlugs={composerProposalsModal.unavailableSlugs}
				onClose={closeComposerProposalsModal}
				onCopy={copyComposerProposalsText}
			/>
			<GitignoreProposalsModal
				open={gitignoreProposalsModal.open}
				content={gitignoreProposalsModal.content}
				installationType={gitignoreProposalsModal.installationType}
				onClose={closeGitignoreProposalsModal}
				onCopy={copyGitignoreProposalsText}
			/>
				</>
			)}
			<Toasts items={toasts} />
		</main>
	);
}
