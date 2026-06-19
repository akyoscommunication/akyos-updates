import { createRoot } from "react-dom/client";
import { App } from "./App";
import { RgpdApp } from "./RgpdApp";
import "./styles.css";

const container = document.getElementById("akyos-updates-admin-app");
const page = window.AKYOS_UPDATES_BOOTSTRAP?.page || container?.dataset?.page || "maintenance";

if (container) {
	createRoot(container).render(page === "rgpd" ? <RgpdApp /> : <App />);
}
