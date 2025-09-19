import { createApp } from 'vue';
import App from './App.vue';
import Dashboard from './components/Dashboard.vue';
import OrdersTable from './components/OrdersTable.vue';
import AuthModal from './components/AuthModal.vue';

// Главное приложение для главной страницы
const app = createApp(App);
app.component('auth-modal', AuthModal);
app.mount('#app');

// Приложение для dashboard
const dashboardApp = createApp(Dashboard);
dashboardApp.component('orders-table', OrdersTable);
dashboardApp.mount('#dashboard-app');