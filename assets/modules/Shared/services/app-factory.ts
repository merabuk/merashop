import { Component, createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import { i18n } from '@shared/i18n';
import { useSessionStore } from '@shared/store/useSessionStore';
import { APP_NAME, DOM_DATA_ATTRIBUTES } from '@shared/constants';
import { useToastStore } from '@shared/store/useToastStore';

export function createMeraShopApp(views: Record<string, () => Promise<Component>>) {
    const components: Record<string, Component> = {};

    for (const path in views) {
        const match = path.match(/\/([^/]+)\.vue$/);
        const name = match ? match[1] : null;
        if (name) {
            components[name] = defineAsyncComponent(views[path]);
        }
    }

    const rootElement = document.getElementById('app');

    if (rootElement) {
        const componentName = rootElement.dataset.component || 'App';
        const RootComponent = components[componentName];

        if (!RootComponent) {
            console.error(`❌[${APP_NAME}] Component ${componentName} not found.`);
            return;
        }

        const app = createApp(RootComponent);
        const pinia = createPinia();

        app.use(pinia);
        app.use(i18n);

        const toastStore = useToastStore(pinia);
        toastStore.init();

        const session = useSessionStore();
        session.initialize({
            userJson: rootElement.dataset[DOM_DATA_ATTRIBUTES.USER] ?? 'guest',
            currentTraceId: rootElement.dataset[DOM_DATA_ATTRIBUTES.TRACE_ID] ?? 'unknown'
        });

        app.mount('#app');
    }
}
