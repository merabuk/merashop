import './styles/shop.css';
import { createMeraShopApp } from '@shared/services/app-factory';

const views = import.meta.glob([
    './modules/**/views/shop/*View.vue',
]);

createMeraShopApp(views);
