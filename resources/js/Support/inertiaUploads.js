/**
 * Envío unificado para formularios con uploads en Inertia.
 *
 * Patrón recomendado (por bugs comunes de PHP con PUT multipart):
 * - Usar POST + _method para PUT/PATCH
 * - Forzar FormData en Inertia: forceFormData: true
 *
 * Opción transform: si se pasa, se aplica sobre los datos antes de añadir _method.
 * Útil para no enviar arrays de archivos vacíos (documentos/fotos) y así preservar
 * los existentes en el servidor al editar.
 */
export function submitUploadForm(form, url, method = "post", options = {}) {
    const m = String(method || "post").toLowerCase();
    const { transform: userTransform, ...inertiaOptions } = options;
    const forceFormData = { forceFormData: true, ...inertiaOptions };

    const transform = (data) => {
        const d = userTransform ? userTransform(data) : data;
        const out = { ...d };
        if (m !== "post") {
            out._method = m;
        }
        return out;
    };

    if (m === "post") {
        if (userTransform) {
            return form.transform(transform).post(url, forceFormData);
        }
        return form.post(url, forceFormData);
    }

    return form.transform(transform).post(url, forceFormData);
}


