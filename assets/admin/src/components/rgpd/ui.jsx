import { useState } from "react";

/** Classe principale — styles dans styles.css (priorité sur WP admin). */
export const fieldInputClass = "au-field-input";
export const fieldSelectClass = "au-field-input au-field-select";

const fieldLabelClass = "au-field-label";
const fieldHintClass = "au-field-hint";
const fieldShellClass = "au-field-shell";

function FieldShell({ label, hint, htmlFor, children, className = "" }) {
	return (
		<div className={`${fieldShellClass} ${className}`.trim()}>
			{label ? (
				htmlFor ? (
					<label htmlFor={htmlFor} className={fieldLabelClass}>
						{label}
					</label>
				) : (
					<span className={fieldLabelClass}>{label}</span>
				)
			) : null}
			{children}
			{hint ? <p className={fieldHintClass}>{hint}</p> : null}
		</div>
	);
}

function SelectChevron() {
	return (
		<svg className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden>
			<path fillRule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clipRule="evenodd" />
		</svg>
	);
}

export function FieldGroup({ title, description, children, accent = false, compact = false }) {
	return (
		<div
			className={`rounded-2xl border shadow-[0_1px_3px_rgba(15,23,42,0.04)] ${
				accent
					? "border-[rgb(var(--au-primary-rgb)/0.18)] bg-[rgb(var(--au-primary-rgb)/0.025)]"
					: "border-slate-200/90 bg-white"
			} ${compact ? "p-4" : "p-5 sm:p-6"}`}
		>
			{title ? (
				<div className={compact ? "mb-4" : "mb-5"}>
					<h3 className="m-0 text-[15px] font-semibold text-slate-900">{title}</h3>
					{description ? <p className="m-0 mt-1.5 text-sm leading-relaxed text-slate-500">{description}</p> : null}
				</div>
			) : null}
			<div className="grid gap-4">{children}</div>
		</div>
	);
}

export function CollapsibleFieldGroup({ title, description, accent = false, defaultOpen = false, toolbar, badge, children }) {
	const [open, setOpen] = useState(defaultOpen);

	return (
		<div
			className={`overflow-hidden rounded-2xl border shadow-[0_1px_3px_rgba(15,23,42,0.04)] ${
				accent
					? "border-[rgb(var(--au-primary-rgb)/0.18)] bg-[rgb(var(--au-primary-rgb)/0.025)]"
					: "border-slate-200/90 bg-white"
			}`}
		>
			<button
				type="button"
				onClick={() => setOpen((v) => !v)}
				aria-expanded={open}
				className="flex w-full items-start justify-between gap-3 px-5 py-4 text-left transition hover:bg-slate-50/80 sm:px-6 sm:py-5"
			>
				<div className="min-w-0 flex-1">
					<div className="flex flex-wrap items-center gap-2">
						{title ? <h3 className="m-0 text-[15px] font-semibold text-slate-900">{title}</h3> : null}
						{badge ? (
							<span className="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
								{badge}
							</span>
						) : null}
					</div>
					{description ? <p className="m-0 mt-1.5 text-sm text-slate-500">{description}</p> : null}
				</div>
				<ChevronIcon open={open} />
			</button>
			{toolbar && !open ? <div className="border-t border-slate-100 px-5 pb-4 pt-3 sm:px-6">{toolbar}</div> : null}
			{open ? (
				<div className="grid gap-4 border-t border-slate-100 px-5 pb-5 pt-4 sm:px-6 sm:pb-6">
					{toolbar ? <div>{toolbar}</div> : null}
					{children}
				</div>
			) : null}
		</div>
	);
}

function ChevronIcon({ open }) {
	return (
		<svg
			className={`mt-1 h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 ${open ? "rotate-180" : ""}`}
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
	);
}

export function SectionBody({ title, description, children }) {
	return (
		<div>
			<div className="mb-1 text-[22px] font-normal text-slate-900 sm:text-[24px]" style={{ fontFamily: "Calistoga, serif" }}>
				{title}
			</div>
			{description ? <p className="mb-5 mt-0 text-sm leading-relaxed text-slate-500">{description}</p> : null}
			<div className="grid gap-5">{children}</div>
		</div>
	);
}

export function FieldGrid({ cols = 2, children }) {
	return <div className={`grid gap-4 ${cols === 2 ? "sm:grid-cols-2" : ""}`}>{children}</div>;
}

export function FormSectionLabel({ children }) {
	return (
		<p className="m-0 mb-3 flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">
			<span className="h-px flex-1 bg-slate-200" />
			<span className="shrink-0">{children}</span>
			<span className="h-px flex-1 bg-slate-200" />
		</p>
	);
}

export function TextField({ label, value, onChange, placeholder, hint, type = "text", onKeyDown, id }) {
	const inputId = id || (label ? `au-field-${label.replace(/\s+/g, "-").toLowerCase()}` : undefined);

	return (
		<FieldShell label={label} hint={hint} htmlFor={inputId}>
			<input
				id={inputId}
				type={type}
				className={fieldInputClass}
				value={value ?? ""}
				placeholder={placeholder || ""}
				onChange={(e) => onChange(e.target.value)}
				onKeyDown={onKeyDown}
			/>
		</FieldShell>
	);
}

export function SelectField({ label, value, onChange, options, hint, id }) {
	const inputId = id || (label ? `au-select-${label.replace(/\s+/g, "-").toLowerCase()}` : undefined);

	return (
		<FieldShell label={label || null} hint={hint} htmlFor={inputId}>
			<div className="relative">
				<select id={inputId} className={fieldSelectClass} value={value ?? ""} onChange={(e) => onChange(e.target.value)}>
					{options.map((opt) => (
						<option key={String(opt.value)} value={opt.value}>
							{opt.label}
						</option>
					))}
				</select>
				<SelectChevron />
			</div>
		</FieldShell>
	);
}

export function RangeField({ label, value, onChange, min = 0, max = 100, step = 1, hint, unit = "px" }) {
	const pct = max > min ? ((value - min) / (max - min)) * 100 : 0;

	return (
		<FieldShell label={label} hint={hint}>
			<div className="rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3.5">
				<div className="mb-3 flex items-center justify-between gap-2">
					<span className="text-xs text-slate-500">Valeur</span>
					<span className="rounded-lg bg-white px-2.5 py-1 font-mono text-sm font-semibold text-[var(--au-primary)] shadow-sm ring-1 ring-slate-200/80">
						{value}
						{unit}
					</span>
				</div>
				<input
					type="range"
					min={min}
					max={max}
					step={step}
					value={value}
					onChange={(e) => onChange(Number(e.target.value))}
					className="au-range-input h-2 w-full cursor-pointer appearance-none rounded-full bg-slate-200 accent-[var(--au-primary)]"
					style={{
						background: `linear-gradient(to right, var(--au-primary) 0%, var(--au-primary) ${pct}%, #e2e8f0 ${pct}%, #e2e8f0 100%)`,
					}}
				/>
			</div>
		</FieldShell>
	);
}

export function ColorField({ label, hint, value, onChange }) {
	const normalized = normalizeHex(value) || "#000000";
	const display = value || normalized;

	return (
		<FieldShell label={label} hint={hint}>
			<div className="au-field-color">
				<label className="au-field-color-swatch">
					<span className="au-field-color-preview" style={{ background: normalized }} aria-hidden />
					<input
						type="color"
						value={normalized.startsWith("#") ? normalized : "#000000"}
						onChange={(e) => onChange(e.target.value.toUpperCase())}
						className="au-field-color-picker"
						aria-label={`Couleur ${label}`}
					/>
				</label>
				<input
					type="text"
					value={display}
					onChange={(e) => onChange(e.target.value.toUpperCase())}
					className="au-field-color-hex"
					placeholder="#000000"
					spellCheck={false}
				/>
			</div>
		</FieldShell>
	);
}

function normalizeHex(value) {
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

export function SwitchField({ label, checked, onChange, hint, compact = false, bare = false }) {
	const toggle = (
		<button
			type="button"
			role="switch"
			aria-checked={Boolean(checked)}
			onClick={() => onChange(!checked)}
			className={`relative h-[26px] w-[46px] shrink-0 rounded-full transition-colors duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--au-primary)] ${
				checked ? "bg-[var(--au-primary)]" : "bg-slate-200"
			}`}
		>
			<span
				className={`absolute top-[3px] left-[3px] h-5 w-5 rounded-full bg-white shadow-[0_1px_3px_rgba(15,23,42,0.2)] transition-transform duration-200 ${
					checked ? "translate-x-5" : "translate-x-0"
				}`}
			/>
		</button>
	);

	if (bare) {
		return (
			<div className="flex items-center gap-4">
				{toggle}
				<span className="grid gap-0.5">
					<span className="text-sm font-semibold text-slate-900">{label}</span>
					{hint ? <span className="text-xs text-slate-500">{hint}</span> : null}
				</span>
			</div>
		);
	}

	return (
		<div
			className={`group flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.03)] transition hover:border-slate-300 ${
				compact ? "px-3.5 py-3" : "px-4 py-3.5"
			} ${checked ? "ring-1 ring-[rgb(var(--au-primary-rgb)/0.08)]" : ""}`}
		>
			<span className="grid min-w-0 gap-0.5">
				<span className="text-[13px] font-semibold text-slate-800">{label}</span>
				{hint ? <span className="text-xs leading-snug text-slate-500">{hint}</span> : null}
			</span>
			{toggle}
		</div>
	);
}

export function SubTabs({ tabs, active, onChange }) {
	return (
		<div
			className="flex gap-1 overflow-x-auto rounded-xl border border-slate-200/90 bg-slate-100/70 p-1 shadow-inner"
			role="tablist"
		>
			{tabs.map((tab) => (
				<button
					key={tab.id}
					type="button"
					role="tab"
					aria-selected={active === tab.id}
					onClick={() => onChange(tab.id)}
					className={`shrink-0 rounded-lg px-3.5 py-2 text-xs font-semibold transition-all sm:px-4 sm:text-sm ${
						active === tab.id
							? "bg-white text-slate-900 shadow-[0_1px_3px_rgba(15,23,42,0.08)]"
							: "text-slate-500 hover:text-slate-800"
					}`}
				>
					{tab.label}
				</button>
			))}
		</div>
	);
}

export function SectionStatusDot({ status }) {
	const styles = {
		done: "bg-emerald-500 ring-emerald-500/20",
		partial: "bg-amber-400 ring-amber-400/25",
		todo: "bg-slate-300 ring-slate-300/30",
	};
	return (
		<span
			className={`inline-block h-2 w-2 shrink-0 rounded-full ring-2 ${styles[status] || styles.todo}`}
			title={status === "done" ? "Complet" : status === "partial" ? "En cours" : "À faire"}
		/>
	);
}
