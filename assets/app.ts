import './styles/app.css';
import { Component, createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import { useSessionStore } from '@shared/store/useSessionStore';
import { APP_NAME, DOM_DATA_ATTRIBUTES } from '@shared/constants';
import { i18n } from '@shared/i18n';

const views = import.meta.glob<Component>([
    './modules/**/views/*View.vue',
    './modules/**/layouts/*Layout.vue',
    './App.vue'
]);

const components: Record<string, Component> = {};

for (const path in views) {
    const name = path.split('/').pop()?.replace('.vue', '') as string;
    components[name] = defineAsyncComponent(views[path] as () => Promise<Component>);
}

const rootElement = document.getElementById('app');

if (rootElement) {
    const componentName = rootElement.dataset.component || 'App';
    const RootComponent = components[componentName];

    if (!RootComponent) {
        console.error(`❌[${APP_NAME}] Component ${componentName} not found. Available:`, Object.keys(components));
    } else {
        const app = createApp(RootComponent);
        const pinia = createPinia();

        app.use(pinia);
        app.use(i18n);

        const session = useSessionStore();
        session.initialize({
            userJson: rootElement.dataset[DOM_DATA_ATTRIBUTES.USER] ?? 'guest',
            currentTraceId: rootElement.dataset[DOM_DATA_ATTRIBUTES.TRACE_ID] ?? 'unknown'
        });

        app.mount('#app');
        console.log(`🚀[${APP_NAME}] Vue initialized with: ${componentName}`);
    }
}
