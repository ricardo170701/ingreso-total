/**
 * Envío unificado para formularios con uploads en Inertia.
 *
 * Patrón recomendado (por bugs comunes de PHP con PUT multipart):
 * - Usar POST + _method para PUT/PATCH
 * - Forzar FormData en Inertia: forceFormData: true
 */
export function submitUploadForm(form, url, method = "post", options = {}) {
    const m = String(method || "post").toLowerCase();
    const inertiaOptions = { forceFormData: true, ...options };

    if (m === "post") {
        return form.post(url, inertiaOptions);
    }

    // PUT/PATCH (u otros) vía method override
    return form
        .transform((data) => ({ ...data, _method: m }))
        .post(url, inertiaOptions);
}


