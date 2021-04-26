import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Employee.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'employee',
    component: Home
  },
  {
    path: '/job',
    name: 'job',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "about" */ '../views/Job.vue')
  },
  {
    path: '/competency',
    name: 'competency',
    component: () => import(/* webpackChunkName: "about" */ '../views/Competency.vue')
    
  }
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})

export default router
