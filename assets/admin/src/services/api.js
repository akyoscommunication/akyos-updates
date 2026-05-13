const apiFetch = window.wp?.apiFetch;

if (!apiFetch) {
    throw new Error("wp.apiFetch indisponible dans la page admin.");
}

apiFetch.use(apiFetch.createNonceMiddleware(window.AKYOS_UPDATES_BOOTSTRAP?.nonce || ""));

export { apiFetch };
