import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { useAuthStore } from './stores/auth';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Dipakai oleh interceptor axios untuk force-logout saat 401 (lihat lib/axios.js)
window.__piniaAuthStore = useAuthStore(pinia);

app.mount('#app');
