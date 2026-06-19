#!/usr/bin/env node
/**
 * Génère assets/rgpd/tarteaucitron-catalog.json depuis la doc tarteaucitron.io.
 * Usage: node scripts/build-tac-catalog.mjs [chemin-vers.md]
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const defaultSource = path.join(__dirname, "../assets/rgpd/tarteaucitron-catalog.source.md");
const outFile = path.join(__dirname, "../assets/rgpd/tarteaucitron-catalog.json");
const source = process.argv[2] || defaultSource;

if (!fs.existsSync(source)) {
	console.error("Source introuvable:", source);
	process.exit(1);
}

const md = fs.readFileSync(source, "utf8");
const start = md.indexOf("#### Étape 3");
const body = start >= 0 ? md.slice(start) : md;
const chunks = body.split(/^##### /m).slice(1);

/** @type {Map<string, object>} */
const byId = new Map();

for (const chunk of chunks) {
	const nl = chunk.indexOf("\n");
	const category = chunk.slice(0, nl).trim();
	if (!category || category.includes("Étape")) continue;

	const text = chunk.slice(nl);
	const re = /\n([^\n#][^\n]*)\n\n###### ➕/g;
	let m;
	while ((m = re.exec(text))) {
		const name = m[1].trim();
		const from = m.index + m[0].length - 14;
		const tail = text.slice(from);
		const next = tail.search(/\n[^\n#][^\n]*\n\n###### ➕/);
		const block = next === -1 ? tail : tail.slice(0, next);

		const codes = [...block.matchAll(/`([^`]+)`/g)].map((x) => x[1].trim());
		const addCode = codes[0] || "";
		const addCodePlacement = block.includes("à l'endroit où") && codes[1] ? codes[1] : "";
		const removeCode = block.includes("🗑️") ? codes[codes.length - 1] || "" : "";

		const jobs = [...addCode.matchAll(/push\('([^']+)'\)/g)].map((x) => x[1]);
		const fieldKeys = [...addCode.matchAll(/tarteaucitron\.user\.(\w+)\s*=/g)].map((x) => x[1]);
		if (addCodePlacement) {
			for (const k of addCodePlacement.matchAll(/tarteaucitron\.user\.(\w+)\s*=/g)) {
				if (!fieldKeys.includes(k[1])) fieldKeys.push(k[1]);
			}
		}

		const fields = fieldKeys
			.filter((k) => !k.endsWith("More"))
			.map((key) => {
				const ph = addCode.match(new RegExp(`tarteaucitron\\.user\\.${key}\\s*=\\s*['\`"]?\\*\\*([^*]+)\\*\\*`));
				return {
					key,
					label: ph ? ph[1].replace(/_/g, " ") : key,
					type: key.toLowerCase().includes("url") ? "url" : "text",
				};
			});

		const id = jobs[0] || name.toLowerCase().replace(/[^a-z0-9]+/g, "");
		if (!id) continue;

		byId.set(id, {
			id,
			name,
			category,
			jobs: jobs.length ? jobs : [id],
			fields,
			addCode,
			addCodePlacement,
			removeCode,
		});
	}
}

const services = [...byId.values()].sort((a, b) => a.name.localeCompare(b.name, "fr"));
const categories = [...new Set(services.map((s) => s.category))];

const catalog = {
	generatedAt: new Date().toISOString().slice(0, 10),
	source: "https://tarteaucitron.io/installation-gratuite-open-source/",
	categories,
	services,
};

fs.writeFileSync(outFile, JSON.stringify(catalog, null, "\t") + "\n");
console.log(`Catalogue: ${services.length} services → ${outFile}`);
