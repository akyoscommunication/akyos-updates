import { createRoot } from "react-dom/client";
import { App } from "./App";
import "./styles.css";

const container = document.getElementById("akyos-updates-admin-app");

if (container) {
	createRoot(container).render(<App />);
}
