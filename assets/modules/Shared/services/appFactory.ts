import { Component, createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import { i18n } from '@shared/i18n';
import { useSessionStore } from '@shared/stores/admin/useSessionStore';
import { useToastStore } from '@shared/stores/admin/useToastStore';
import { APP_NAME } from '@shared/config/app';
import { APP_ID, getRootElement, getTargetComponent, getTargetTraceId } from '@shared/services/domDataProvider';

export function createMeraShopApp(views: Record<string, () => Promise<Component>>) {
    const components: Record<string, Component> = {};

    for (const path in views) {
        const match = path.match(/\/([^/]+)\.vue$/);
        const name = match ? match[1] : null;
        if (name) {
            components[name] = defineAsyncComponent(views[path]);
        }
    }

    const rootElement = getRootElement();

    if (!rootElement) {
        console.error(`❌[${APP_NAME}] Target element not found with id '${APP_ID}'.`);

        return;
    }

    const componentName = getTargetComponent();

    if (!componentName) {
        console.error(`❌[${APP_NAME}] Missing component name in dataset.`);

        return;
    }

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
        currentTraceId: getTargetTraceId()
    });

    app.mount(rootElement);
}
