const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      { path: 'pago-exitoso', component: () => import('pages/PagoExitoso.vue') },
      { path: 'pago-cancelado', component: () => import('pages/PagoCancelado.vue') },
      {
        path: '/usuarios',
        component: () => import('pages/usuarios/Usuarios.vue'),
        meta: {requiresAuth: true, perm: 'Usuarios'}
      },
      // http://localhost:9000/reservas?
      {
        path: '/reservas',
        component: () => import('pages/ordenes/Orders.vue'),
        meta: {requiresAuth: true, perm: 'Reservas'}
      }
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
