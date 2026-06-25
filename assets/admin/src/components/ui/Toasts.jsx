export function Toasts({ items }) {
	return (
		<div className="fixed bottom-6 right-6 z-[99] grid gap-2">
			{items.map((item) => (
				<div
					key={item.id}
					className={`min-w-[280px] rounded-xl px-4 py-3 text-white shadow-lg ${
						item.kind === "error" ? "bg-gradient-to-br from-red-500 to-rose-400" : "bg-gradient-to-br from-[var(--au-primary)] to-[var(--au-primary-light)]"
					}`}
				>
					{item.message}
				</div>
			))}
		</div>
	);
}
