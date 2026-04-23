import './styles/admin.css';
import { createMeraShopApp } from '@shared/services/appFactory';
import type { Component } from 'vue';

const views = import.meta.glob<Component>([
    './modules/**/views/admin/**/*View.vue',
    './modules/Shared/layouts/admin/*.vue',
]);

createMeraShopApp(views);
