export function CategorySwitches({ names, value, onToggle, disabled }) {
	if (!names?.length) {
		return null;
	}

	return (
		<ul className="mt-4 grid gap-2 sm:grid-cols-2">
			{names.map((name) => {
				const on = Boolean(value[name]);
				return (
					<li
						key={name}
						className="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5"
					>
						<span className="text-sm font-medium text-slate-800">{name}</span>
						<button
							type="button"
							role="switch"
							aria-checked={on}
							disabled={disabled}
							onClick={() => onToggle(name, !on)}
							className={`relative h-7 w-12 shrink-0 rounded-full transition ${
								on ? "bg-[var(--au-primary)]" : "bg-slate-300"
							} ${disabled ? "cursor-not-allowed opacity-60" : "cursor-pointer"}`}
						>
							<span
								className={`absolute top-0.5 left-0.5 h-6 w-6 rounded-full bg-white shadow transition ${
									on ? "translate-x-5" : "translate-x-0"
								}`}
							/>
						</button>
					</li>
				);
			})}
		</ul>
	);
}
