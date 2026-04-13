/** @type {string} */
export const OFFLINE_QR_STORAGE_KEY = "escaner_total_last_qr_v1";

/**
 * @typedef {{ userId: number, token: string, svg: string, userName: string, expiresAtFormatted: string, fechaGeneracion: string, savedAt: string }} OfflineQrPayload
 */

/**
 * @param {OfflineQrPayload} payload
 */
export function saveOfflineQr(payload) {
    try {
        if (typeof localStorage === "undefined") return;
        localStorage.setItem(OFFLINE_QR_STORAGE_KEY, JSON.stringify(payload));
    } catch {
        // ignore quota / private mode
    }
}

/** @returns {OfflineQrPayload | null} */
export function loadOfflineQr() {
    try {
        if (typeof localStorage === "undefined") return null;
        const raw = localStorage.getItem(OFFLINE_QR_STORAGE_KEY);
        if (!raw) return null;
        const data = JSON.parse(raw);
        if (!data || typeof data.userId !== "number" || !data.token || !data.svg) return null;
        return data;
    } catch {
        return null;
    }
}

export function clearOfflineQr() {
    try {
        if (typeof localStorage !== "undefined") {
            localStorage.removeItem(OFFLINE_QR_STORAGE_KEY);
        }
    } catch {
        // ignore
    }
}
