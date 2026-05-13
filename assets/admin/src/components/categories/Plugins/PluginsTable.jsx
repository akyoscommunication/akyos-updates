function ComposerPresenceBadge({ plugin, composerJsonReadable }) {
	if (!composerJsonReadable) {
		return (
			<span className="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">—</span>
		);
	}
	if (plugin.inComposer === true) {
		return (
			<span className="inline-flex rounded-full bg-emerald-500/15 px-2 py-0.5 text-xs font-semibold text-emerald-800">
				Oui
			</span>
		);
	}
	if (plugin.inComposer === false) {
		return (
			<span className="inline-flex rounded-full bg-amber-500/15 px-2 py-0.5 text-xs font-semibold text-amber-900">
				Non
			</span>
		);
	}
	return (
		<span className="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">—</span>
	);
}

export function PluginsTable({ plugins, composerJsonReadable = false }) {
	return (
		<div className="mt-3 overflow-x-auto">
			<table className="w-full border-collapse text-left text-[13px]">
				<thead>
					<tr>
						<th className="border-b border-slate-200 px-2 py-2">Nom</th>
						<th className="border-b border-slate-200 px-2 py-2">Slug</th>
						<th className="border-b border-slate-200 px-2 py-2">Version</th>
						<th className="border-b border-slate-200 px-2 py-2">Actif</th>
						<th className="border-b border-slate-200 px-2 py-2">Présence composer</th>
					</tr>
				</thead>
				<tbody>
					{plugins.map((plugin, index) => (
						<tr key={`${plugin.slug}-${index}`}>
							<td className="border-b border-slate-100 px-2 py-2">{plugin.name}</td>
							<td className="border-b border-slate-100 px-2 py-2">{plugin.slug}</td>
							<td className="border-b border-slate-100 px-2 py-2">{plugin.version}</td>
							<td className="border-b border-slate-100 px-2 py-2">{plugin.active ? "Oui" : "Non"}</td>
							<td className="border-b border-slate-100 px-2 py-2 align-middle">
								<ComposerPresenceBadge plugin={plugin} composerJsonReadable={composerJsonReadable} />
							</td>
						</tr>
					))}
				</tbody>
			</table>
		</div>
	);
}
