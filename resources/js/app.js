import '../css/app.css'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import IndicadorConexion from './Components/IndicadorConexion.vue'
import DialogoCentrado from './Components/DialogoCentrado.vue'
import FondoNocturno from './Components/FondoNocturno.vue'

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => [h(FondoNocturno), h(IndicadorConexion), h(App, props), h(DialogoCentrado)] })
            .use(plugin)
            .mount(el)
    },
})
