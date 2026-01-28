<template>
  <q-page class="q-pa-md">
    <orders-page
      v-if="site"
      :site="site"
      :key="refreshKey"
    />
  </q-page>
</template>

<script>
import OrdersPage from 'pages/ordenes/Orders.vue'

export default {
  name: 'SiteDetailsPage',
  components: { OrdersPage },

  data () {
    return {
      site: null,
      refreshKey: 0,
      unwatchRoute: null
    }
  },

  mounted () {
    // inicial (ya viene en la ruta)
    this.site = this.$route.params.site || null

    this.unwatchRoute = this.$watch(
      () => this.$route.params.site,
      (newSite, oldSite) => {
        if (newSite === oldSite) return
        this.site = newSite || null
        this.refreshKey++
      }
    )
  },

  beforeUnmount () {
    if (this.unwatchRoute) this.unwatchRoute()
  }
}
</script>
