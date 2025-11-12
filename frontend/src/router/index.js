import { createRouter, createWebHistory } from 'vue-router'
import NovaProposta from '../views/NovaProposta.vue'
import ListarPropostas from "../views/ListarPropostas.vue"

const routes = [
  { path: '/', name: 'NovaProposta', component: NovaProposta },
  { path: "/propostas", component: ListarPropostas }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default createRouter({
  history: createWebHistory(),
  routes
})
