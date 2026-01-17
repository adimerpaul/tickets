<template>
  <q-page class="q-pa-md bg-grey-2">
    <div class="row justify-center">
      <div class="col-12 col-md-7 col-lg-5">

        <q-card flat bordered class="q-pa-lg">
          <div class="row items-center q-col-gutter-md">
            <div class="col-auto">
              <q-icon name="check_circle" color="positive" size="56px" />
            </div>
            <div class="col">
              <div class="text-h6 text-weight-bold">¡Pago realizado con éxito!</div>
              <div class="text-caption text-grey-7">
                Tu pago fue procesado correctamente. En breve podrás ver o recibir tu entrada.
              </div>
            </div>
          </div>

          <q-separator class="q-my-md" />

          <q-banner rounded class="bg-grey-1 text-grey-9">
            <div class="text-subtitle2 text-weight-medium">Detalle</div>
            <div class="text-caption text-grey-8">
              Session ID:
              <span class="text-weight-medium">{{ sessionId || '—' }}</span>
            </div>
          </q-banner>

          <div class="row q-col-gutter-sm q-mt-md">
            <div class="col-12 col-sm-6">
              <q-btn
                unelevated
                color="primary"
                class="full-width"
                icon="confirmation_number"
                label="Ver mi entrada"
                @click="goHome"
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

          <div class="text-caption text-grey-7 q-mt-sm">
            * Si necesitas validar el pago en el backend, usa el <b>session_id</b>.
          </div>
        </q-card>

      </div>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'PagoExitoso',
  computed: {
    sessionId () {
      return this.$route.query.session_id || null
    }
  },
  methods: {
    goHome () {
      this.$router.push('/')
    }
  }
}
</script>
