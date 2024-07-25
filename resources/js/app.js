import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import VueGoogleMaps from "vue-google-maps-community-fork";

const appName =
    window.document.getElementsByTagName("title")[0]?.innerText || "Maps";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(VueGoogleMaps, {
                load: {
                    key: "AIzaSyD2dASx5Zo68GSyZuPjUs-4SBLYGsn4OPQ",
                    libraries: "places",
                },
            })
            .mount(el);
    },
    progress: {
        color: "#3b82f6",
    },
});
