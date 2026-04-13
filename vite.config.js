import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { VitePWA } from "vite-plugin-pwa";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: "autoUpdate",
            /**
             * Laravel sirve Vite desde `/build/`. Sin esto, el SW queda con scope `/build/`
             * y NO cachea navegaciones a `/ingreso`, etc.
             * @see https://vite-pwa-org.netlify.app/frameworks/laravel
             */
            buildBase: "/build/",
            scope: "/",
            /** Mantenemos `public/manifest.webmanifest` (Blade ya lo enlaza). */
            manifest: false,
            /** Blade no pasa por `index.html` de Vite: el registro va en `app.js`. */
            injectRegister: null,
            strategies: "generateSW",
            workbox: {
                cleanupOutdatedCaches: true,
                /** Laravel no usa `index.html` en la raíz; evita ruta SPA por defecto de Workbox. */
                navigateFallback: null,
                globPatterns: ["**/*.{js,css,ico,png,svg,woff2}"],
                runtimeCaching: [
                    {
                        /** Documentos HTML (Inertia embebido en la respuesta). */
                        urlPattern: ({ request }) => request.mode === "navigate",
                        handler: "NetworkFirst",
                        options: {
                            cacheName: "escaner-html-pages",
                            networkTimeoutSeconds: 5,
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 60 * 60 * 24 * 30,
                            },
                        },
                    },
                    {
                        /** Assets versionados tras `npm run build`. */
                        urlPattern: ({ url, request }) =>
                            url.origin === self.location.origin &&
                            url.pathname.startsWith("/build/") &&
                            request.method === "GET",
                        handler: "StaleWhileRevalidate",
                        options: {
                            cacheName: "escaner-build-assets",
                            expiration: {
                                maxEntries: 80,
                                maxAgeSeconds: 60 * 60 * 24 * 365,
                            },
                        },
                    },
                    {
                        urlPattern: ({ url }) =>
                            url.origin === self.location.origin &&
                            url.pathname === "/manifest.webmanifest",
                        handler: "StaleWhileRevalidate",
                        options: {
                            cacheName: "escaner-manifest",
                            expiration: {
                                maxEntries: 2,
                                maxAgeSeconds: 60 * 60 * 24 * 7,
                            },
                        },
                    },
                ],
            },
            includeAssets: [
                "images/favicon-32x32.png",
                "images/favicon-16x16.png",
                "images/pwa-icon-192.png",
                "images/pwa-icon-512.png",
                "images/apple-touch-icon-180.png",
            ],
            devOptions: {
                enabled: false,
            },
        }),
    ],
    resolve: {
        alias: {
            "@": "/resources/js",
        },
    },
});
