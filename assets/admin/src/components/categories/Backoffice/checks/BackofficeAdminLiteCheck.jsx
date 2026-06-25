import { ReportCard } from "../../../report/ReportCard";

export function BackofficeAdminLiteCheck({ result, setAdminLiteEmail, setAdminLiteName, setAdminLiteModalOpen }) {
	const payload = result.payload || {};
	const preview = Array.isArray(payload.usersPreview) ? payload.usersPreview : [];
	return (
		<ReportCard result={result} actions={null}>
			<div className="mt-3 space-y-3">
				{preview.length > 0 ? (
					<ul className="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">
						{preview.map((u) => (
							<li key={u.id}>
								{u.name || "—"} · {u.email}
							</li>
						))}
					</ul>
				) : null}
				{result.actionable ? (
					<button
						type="button"
						className="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-[rgb(var(--au-primary-rgb)/0.3)]"
						onClick={() => {
							setAdminLiteEmail("");
							setAdminLiteName("");
							setAdminLiteModalOpen(true);
						}}
					>
						Créer l&apos;utilisateur
					</button>
				) : null}
			</div>
		</ReportCard>
	);
}
