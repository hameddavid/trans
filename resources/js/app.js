import { createApp } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import router from './router';
import App from './App.vue';
import Toast, { POSITION } from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import VueApexCharts from 'vue3-apexcharts';

const app = createApp(App);

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

app.use(pinia);
app.use(router);
app.use(Toast, {
    position: POSITION.TOP_RIGHT,
    timeout: 4000,
    closeOnClick: true,
    pauseOnHover: true,
});
app.use(VueApexCharts);

app.mount('#app');
