import { createRoot } from "react-dom/client";
import { App } from "./App";
import { RgpdApp } from "./RgpdApp";
import { SettingsApp } from "./SettingsApp";
import "./styles.css";

const container = document.getElementById("akyos-updates-admin-app");
const page = window.AKYOS_UPDATES_BOOTSTRAP?.page || container?.dataset?.page || "maintenance";

const APPS = {
	rgpd: RgpdApp,
	settings: SettingsApp,
};

if (container) {
	const RootApp = APPS[page] || App;
	createRoot(container).render(<RootApp />);
}
