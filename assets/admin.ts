import './styles/admin.css';
import { createMeraShopApp } from '@shared/services/app-factory';

const views = import.meta.glob([
    './modules/**/views/admin/**/*View.vue',
    './modules/Shared/layouts/admin/*.vue',
    './App.vue'
]);

createMeraShopApp(views);
