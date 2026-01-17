<template>
  <q-page class="q-pa-md bg-grey-2">
    <div class="row justify-center">
      <div class="col-12 col-md-8 col-lg-6">

        <q-card flat bordered class="q-pa-md">
          <!-- Header -->
          <div class="row items-center q-mb-md">
            <div>
              <div class="text-h6 text-weight-bold">Reservar entradas</div>
              <div class="text-caption text-grey-7">Prueba Stripe Checkout con adultos/niños</div>
            </div>
            <q-space />
            <q-chip color="primary" text-color="white" dense>
              Total: {{ formatEUR(total) }}
            </q-chip>
          </div>

          <!-- EU + Fecha/Hora -->
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <div class="text-subtitle2 text-weight-medium">¿Eres ciudadano de la Unión Europea?</div>
              <q-option-group
                v-model="isEU"
                inline
                :options="[
                  { label: 'Sí', value: true },
                  { label: 'No', value: false }
                ]"
                class="q-mt-xs"
              />
            </div>

            <div class="col-12 col-md-6">
              <q-input
                v-model="date"
                type="date"
                dense
                outlined
                label="Fecha"
              >
                <template #prepend><q-icon name="event" /></template>
              </q-input>
            </div>

            <div class="col-12 col-md-6">
              <q-select
                v-model="time"
                :options="timeOptions"
                dense
                outlined
                label="Hora"
                emit-value
                map-options
                clearable
              >
                <template #prepend><q-icon name="schedule" /></template>
              </q-select>
            </div>
          </div>

          <q-separator class="q-my-md" />

          <!-- Entradas -->
          <div class="text-subtitle2 text-weight-bold q-mb-sm">Entradas</div>

          <!-- Adultos -->
          <q-card flat bordered class="q-pa-md q-mb-sm">
            <div class="row items-center">
              <div class="col">
                <div class="text-subtitle2 text-weight-medium">Adulto</div>
                <div class="text-caption text-grey-7">+17 años</div>
              </div>

              <div class="col-auto text-right q-mr-md">
                <div class="text-subtitle2 text-weight-bold">{{ formatEUR(priceAdult) }}</div>
                <div class="text-caption text-grey-7">c/u</div>
              </div>

              <div class="col-auto">
                <q-btn round dense flat icon="remove" @click="dec('adult')" />
                <q-chip class="q-mx-sm" square>{{ adults }}</q-chip>
                <q-btn round dense flat icon="add" @click="inc('adult')" />
              </div>
            </div>
          </q-card>

          <!-- Niños -->
          <q-card flat bordered class="q-pa-md">
            <div class="row items-center">
              <div class="col">
                <div class="text-subtitle2 text-weight-medium">Niño</div>
                <div class="text-caption text-grey-7">-17 años</div>
              </div>

              <div class="col-auto text-right q-mr-md">
                <div class="text-subtitle2 text-weight-bold">{{ formatEUR(priceKid) }}</div>
                <div class="text-caption text-grey-7">c/u</div>
              </div>

              <div class="col-auto">
                <q-btn round dense flat icon="remove" @click="dec('kid')" />
                <q-chip class="q-mx-sm" square>{{ kids }}</q-chip>
                <q-btn round dense flat icon="add" @click="inc('kid')" />
              </div>
            </div>
          </q-card>

          <q-separator class="q-my-md" />

          <!-- Resumen -->
          <div class="row items-center">
            <div class="col">
              <div class="text-caption text-grey-7">Subtotal</div>
              <div class="text-subtitle1 text-weight-bold">{{ formatEUR(total) }}</div>
            </div>

            <div class="col-auto">
              <q-btn
                unelevated
                color="black"
                no-caps
                icon="payments"
                label="Pagar con Stripe"
                :loading="loading"
                :disable="total <= 0"
                @click="onBuy"
              />
            </div>
          </div>

          <div class="text-caption text-grey-7 q-mt-sm">
            * Esto te redirige a Stripe Checkout (modo prueba).
          </div>
        </q-card>

      </div>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'IndexPage',
  data () {
    return {
      loading: false,

      // form
      isEU: true,
      date: '2026-01-17',
      time: null,

      // cantidades
      adults: 1,
      kids: 0,

      // precios (EUR)
      priceAdult: 33,
      priceKid: 16.50,

      timeOptions: [
        '09:00', '09:30', '10:00', '10:30',
        '11:00', '11:30', '12:00', '12:30',
        '13:00', '13:30', '14:00', '14:30',
        '15:00', '15:30', '16:00', '16:30'
      ].map(v => ({ label: v, value: v }))
    }
  },
  computed: {
    total () {
      return (this.adults * this.priceAdult) + (this.kids * this.priceKid)
    }
  },
  methods: {
    formatEUR (n) {
      return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(n)
    },
    inc (type) {
      if (type === 'adult') this.adults++
      if (type === 'kid') this.kids++
    },
    dec (type) {
      if (type === 'adult') this.adults = Math.max(0, this.adults - 1)
      if (type === 'kid') this.kids = Math.max(0, this.kids - 1)
    },

    async onBuy () {
      try {
        this.loading = true

        const items = [
          { name: 'Entrada Adulto', qty: this.adults, unit_amount: Math.round(this.priceAdult * 100) },
          { name: 'Entrada Niño', qty: this.kids, unit_amount: Math.round(this.priceKid * 100) }
        ].filter(i => i.qty > 0)

        // IMPORTANTE: esto debe apuntar a Laravel (API)
        // Si tienes baseURL bien configurado en axios boot, bastará con '/stripe/checkout'
        const { data } = await this.$axios.post('http://localhost:8000/api/stripe/checkout', {
          items,
          customer_email: null,
          metadata: {
            isEU: this.isEU ? '1' : '0',
            date: this.date,
            time: this.time || '',
            adults: String(this.adults),
            kids: String(this.kids),
            total: String(this.total)
          }
        })

        window.location.href = data.checkout_url
      } catch (e) {
        console.error(e)
        this.$q.notify({ type: 'negative', message: 'Error creando el checkout. Revisa consola/Network.' })
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
