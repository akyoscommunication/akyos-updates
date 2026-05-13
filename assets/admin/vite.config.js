import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
	plugins: [react()],
	build: {
		outDir: "build",
		emptyOutDir: false,
		cssCodeSplit: false,
		rollupOptions: {
			input: "src/main.jsx",
			output: {
				entryFileNames: "index.js",
				assetFileNames: (assetInfo) => {
					if (assetInfo.name && assetInfo.name.endsWith(".css")) {
						return "index.css";
					}
					return "assets/[name]-[hash][extname]";
				},
			},
		},
	},
	server: {
		host: "127.0.0.1",
		port: 5173,
		strictPort: true,
	},
});
