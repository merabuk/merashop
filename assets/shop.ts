import './styles/shop.css';
import { createMeraShopApp } from '@shared/services/appFactory';
import type { Component } from 'vue';

const views = import.meta.glob<Component>([
    './modules/**/views/shop/*View.vue',
]);

createMeraShopApp(views);
