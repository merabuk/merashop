import './styles/app.css';
import { createApp } from 'vue';
import App from './App.vue';

const rootElement = document.getElementById('app');

if (!rootElement) {
    console.error('❌ [MeraShop] The #app element was not found in the DOM!');
} else {
    const app = createApp(App, {
        traceId: rootElement.dataset.traceId || 'no-trace-id'
    });

    app.mount('#app');
    console.log('🚀 [MeraShop] Vue has been successfully installed!');
}
