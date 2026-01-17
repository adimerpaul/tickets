<template>
  <q-page class="q-pa-md bg-grey-2">
    <div class="row justify-center">
      <div class="col-12 col-md-7 col-lg-5">

        <q-card flat bordered class="q-pa-lg">
          <div class="row items-center q-col-gutter-md">
            <div class="col-auto">
              <q-icon name="cancel" color="negative" size="56px" />
            </div>
            <div class="col">
              <div class="text-h6 text-weight-bold">Pago cancelado</div>
              <div class="text-caption text-grey-7">
                No se realizó ningún cobro. Puedes intentarlo nuevamente cuando quieras.
              </div>
            </div>
          </div>

          <q-separator class="q-my-md" />

          <div class="row q-col-gutter-sm">
            <div class="col-12 col-sm-6">
              <q-btn
                unelevated
                color="primary"
                class="full-width"
                icon="restart_alt"
                label="Intentar de nuevo"
                @click="goBack"
              />
            </div>

            <div class="col-12 col-sm-6">
              <q-btn
                flat
                class="full-width"
                icon="home"
                label="Volver al inicio"
                @click="goHome"
              />
            </div>
          </div>
        </q-card>

      </div>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'PagoCancelado',
  methods: {
    goBack () {
      // vuelve a la página anterior (tu form)
      if (window.history.length > 1) this.$router.back()
      else this.$router.push('/')
    },
    goHome () {
      this.$router.push('/')
    }
  }
}
</script>
