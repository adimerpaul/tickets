<template>
  <q-page class="q-pa-md">
    <orders-page
      :site="site"
      :key="refreshKey"
    />
  </q-page>
</template>

<script>
import OrdersPage from 'pages/ordenes/Orders.vue'

export default {
  name: 'SiteDetailsPage',

  components: {
    OrdersPage
  },

  data () {
    return {
      site: null,
      refreshKey: 0,
      unwatchRoute: null
    }
  },

  mounted () {
    // valor inicial desde la ruta
    this.site = this.$route.params.site || null

    // watcher de cambio de /evento/:site
    this.unwatchRoute = this.$watch(
      () => this.$route.params.site,
      (newSite, oldSite) => {
        if (newSite === oldSite) return

        // actualizar site
        this.site = newSite || null

        // 🔥 fuerza recreación completa del componente hijo
        this.refreshKey++
      }
    )
  },

  beforeUnmount () {
    if (this.unwatchRoute) {
      this.unwatchRoute()
      this.unwatchRoute = null
    }
  }
}
</script>

<style scoped>
/* sin estilos extra */
</style>
