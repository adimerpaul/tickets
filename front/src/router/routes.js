const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue'), meta: {requiresAuth: true} },
      { path: 'pago-exitoso', component: () => import('pages/PagoExitoso.vue'), meta: {requiresAuth: true} },
      { path: 'pago-cancelado', component: () => import('pages/PagoCancelado.vue'), meta: {requiresAuth: true} },
      {
        path: '/usuarios',
        component: () => import('pages/usuarios/Usuarios.vue'),
        meta: {requiresAuth: true, perm: 'Usuarios'}
      },
      {
        path: '/monedas',
        component: () => import('pages/monedas/Monedas.vue'),
        meta: {requiresAuth: true, perm: 'Monedas'}
      },
      {
        path: '/idiomas',
        component: () => import('pages/idiomas/Idiomas.vue'),
        meta: {requiresAuth: true, perm: 'Idiomas'}
      },
      // eventos
      {
        path: '/eventos',
        component: () => import('pages/eventos/Eventos.vue'),
        meta: {requiresAuth: true, perm: 'Egipto'}
      },
      {
        path: '/evento/:site',
        component: () => import('pages/eventos/SiteDetails.vue'),
        meta: {requiresAuth: true, perm: 'Egipto'}
      }
      // {
      //   path: '/reservas',
      //   component: () => import('pages/ordenes/Orders.vue'),
      //   meta: {requiresAuth: true, perm: 'Reservas'}
      // }
    ]
  },

  {
    path: '/pedido',
    component: () => import('layouts/PedidoLayout.vue'),
    children: []
  },
  {
    path: '/login',
    component: () => import('layouts/Login.vue'),
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
