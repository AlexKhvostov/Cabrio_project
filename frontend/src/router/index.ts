import { createRouter, createWebHashHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';

const routes = [
  { path: '/', name: 'home', component: HomeView },
  { path: '/users', name: 'users', component: () => import('../views/UsersView.vue') },
  { path: '/cars', name: 'cars', component: () => import('../views/CarsView.vue') },
  { path: '/events', name: 'events', component: () => import('../views/EventsView.vue') },
  { path: '/guide', name: 'guide', component: () => import('../views/GuideView.vue') },
  { path: '/settings', name: 'settings', component: () => import('../views/SettingsView.vue') },
  { path: '/me', name: 'me', component: () => import('../views/MeView.vue') },
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
});

export default router;
