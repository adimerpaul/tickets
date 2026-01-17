const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      { path: 'pago-exitoso', component: () => import('pages/PagoExitoso.vue') },
      { path: 'pago-cancelado', component: () => import('pages/PagoCancelado.vue') }
    ]
  },

  {
    path: '/comprar',
    component: () => import('layouts/PedidoLayout.vue'),
    children: []
  },

  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
