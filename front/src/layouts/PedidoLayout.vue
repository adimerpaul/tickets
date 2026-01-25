<template>
  <q-layout view="hHh lpR fFf">
    <q-page-container>
      <q-page class="bg-grey-2">

        <!-- HERO / CAROUSEL -->
        <q-carousel
          swipeable
          animated
          v-model="slide"
          :autoplay="autoplay"
          ref="carousel"
          infinite
          height="420px"
          class="rounded-borders"
        >
          <q-carousel-slide :name="1" img-src="home-hero.464470cb.webp" />

          <template v-slot:control>
            <!-- Top pill nav dentro del carrusel -->
            <q-carousel-control position="top" :offset="[18, 18]" class="full-width">
              <div class="q-px-md">
                <div
                  class="row items-center no-wrap q-px-md q-py-sm"
                  style="border-radius:999px;background:rgba(20,20,20,.86);backdrop-filter:blur(10px);"
                >
                  <div class="row items-center no-wrap">
                    <div class="brand-dot q-mr-sm"></div>
                    <div class="text-white text-weight-bold">VisitaEgipto</div>
                  </div>

                  <q-space />

                  <q-tabs
                    v-model="tab"
                    dense
                    no-caps
                    inline-label
                    outside-arrows
                    mobile-arrows
                    class="text-white"
                    active-color="white"
                    indicator-color="transparent"
                  >
                    <q-tab name="tickets" label="Tiquetes" />
                    <q-tab name="gallery" label="Galería" />
                    <q-tab name="faq" label="Preguntas frecuentes" />
                    <q-tab name="contact" label="Contacto" />
                  </q-tabs>
                </div>
              </div>
            </q-carousel-control>
          </template>
        </q-carousel>

        <!-- CARD FLOTANTE (como tu diseño) -->
        <div class="q-px-md card-wrap">
          <q-card flat class="ticket-card">

            <div class="row q-col-gutter-xl items-start">

              <!-- IZQUIERDA -->
              <div class="col-12 col-md-7">
                <div class="text-h5 text-weight-bold text-primary2 q-mb-xs">
                  Gran Museo Egipcio
                </div>

                <div class="row items-center text-grey-7 q-mb-lg">
                  <q-icon name="place" size="16px" class="q-mr-xs" />
                  <div class="text-caption">
                    O haz click aquí para buscar otro destino
                  </div>
                </div>

                <!-- Fecha y hora (pill) -->
                <div class="text-subtitle2 text-grey-8 q-mb-sm">
                  Selecciona la fecha y la hora
                </div>

                <div class="row q-col-gutter-sm q-mb-lg">
                  <div class="col-12 col-sm-7">
                    <q-input
                      v-model="dateLabel"
                      dense
                      outlined
                      bg-color="grey-1"
                      class="pill-input"
                    >
                      <template #prepend>
                        <q-icon name="event" />
                      </template>

                      <q-popup-proxy transition-show="scale" transition-hide="scale">
                        <q-date
                          v-model="date"
                          mask="YYYY-MM-DD"
                          minimal
                          @update:model-value="onDateChange"
                        />
                      </q-popup-proxy>
                    </q-input>

                  </div>

                  <div class="col-12 col-sm-5">
                    <q-select
                      v-model="time"
                      :options="timeOptions"
                      dense
                      outlined
                      bg-color="grey-1"
                      class="pill-input"
                      emit-value
                      map-options
                    >
                      <template #prepend>
                        <q-icon name="schedule" />
                      </template>
                    </q-select>
                  </div>
                </div>

                <!-- Nacionalidad -->
                <div class="text-subtitle2 text-grey-8 q-mb-sm">Nacionalidad</div>
                <q-option-group
                  v-model="nationality"
                  inline
                  class="q-mb-md"
                  :options="[
                    { label: 'Nacional', value: 'NACIONAL' },
                    { label: 'Extranjero', value: 'EXTRANJERO' }
                  ]"
                />
              </div>

              <!-- DERECHA -->
              <div class="col-12 col-md-5">
                <div class="text-subtitle2 text-grey-8 q-mb-sm">Tipo de Entrada</div>

                <!-- radios con imágenes -->
                <div class="row q-col-gutter-md q-mb-lg">
                  <div class="col-6">
                    <div class="row items-center q-gutter-sm">
                      <q-radio v-model="ticketType" val="simple" />
                      <div class="text-weight-medium">Simple</div>
                    </div>

                    <q-avatar size="74px" class="q-mt-sm shadow-2">
                      <q-img src="https://cdn.quasar.dev/img/parallax2.jpg" />
                    </q-avatar>
                  </div>

                  <div class="col-6">
                    <div class="row items-center q-gutter-sm">
                      <q-radio v-model="ticketType" val="composed" />
                      <div class="text-weight-medium">Compuesta</div>
                    </div>

                    <q-avatar size="74px" class="q-mt-sm shadow-2">
                      <q-img src="https://cdn.quasar.dev/img/parallax1.jpg" />
                    </q-avatar>
                  </div>
                </div>

                <div class="text-subtitle2 text-grey-8 q-mb-sm">N° de entradas</div>

                <!-- Contadores estilo diseño -->
                <div class="row q-col-gutter-sm q-mb-md">
                  <div class="col-12">
                    <div class="counter-pill">
                      <div class="text-grey-8">{{ adults }} Adulto</div>
                      <q-space />
                      <div class="row items-center q-gutter-xs">
                        <q-btn round dense flat icon="remove" class="counter-btn" @click="dec('adult')" />
                        <q-btn round dense flat icon="add" class="counter-btn" @click="inc('adult')" />
                      </div>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="counter-pill">
                      <div class="text-grey-8">
                        {{ kids }}
                        Niños
                      </div>
                      <q-space />
                      <div class="row items-center q-gutter-xs">
                        <q-btn round dense flat icon="remove" class="counter-btn" @click="dec('kid')" />
                        <q-btn round dense flat icon="add" class="counter-btn" @click="inc('kid')" />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Total + botón -->
                <div class="row items-center">
                  <q-space />
                  <div class="text-grey-7 q-mr-sm">Total</div>
                  <div class="text-h6 text-weight-bold">{{ formatEUR(total) }}</div>
                </div>

                <div class="row justify-end q-mt-md">
                  <q-btn
                    unelevated
                    no-caps
                    class="buy-btn"
                    icon="shopping_cart"
                    label="Comprar Ahora"
                    :loading="loading"
                    :disable="total <= 0"
                    @click="onBuy"
                  />
                </div>
              </div>

            </div>

          </q-card>
        </div>

      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script>
export default {
  name: 'IndexPage',
  data () {
    return {
      date: null,
      dateLabel: 'Selecciona una fecha',
      slide: 1,
      autoplay: true,
      tab: 'tickets',

      loading: false,
      time: '10:00 A.M.',

      nationality: 'NACIONAL',
      ticketType: 'simple',

      adults: 1,
      kids: 0,

      priceAdult: 10.5,
      priceKid: 5.5,

      timeOptions: [
        '09:00 A.M.', '10:00 A.M.', '11:00 A.M.',
        '12:00 P.M.', '01:00 P.M.', '02:00 P.M.'
      ].map(v => ({ label: v, value: v }))
    }
  },
  computed: {
    total () {
      return (this.adults * this.priceAdult) + (this.kids * this.priceKid)
    }
  },
  methods: {
    onDateChange (val) {
      if (!val) return

      const d = new Date(val + 'T00:00:00')

      this.dateLabel = d.toLocaleDateString('es-ES', {
        weekday: 'short',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      })
    },
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
      // deve selecionar un fecha
      if (!this.date) {
        this.$alert.error('Por favor selecciona una fecha para tu visita.')
        return
      }
      try {
        this.loading = true

        const items = [
          { name: 'Entrada Adulto', qty: this.adults, unit_amount: Math.round(this.priceAdult * 100) },
          { name: 'Entrada Niño', qty: this.kids, unit_amount: Math.round(this.priceKid * 100) }
        ].filter(i => i.qty > 0)

        const { data } = await this.$axios.post('stripe/checkout', {
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

<style scoped>
/* mínimo CSS: solo para lograr el look del mockup */

.card-wrap{
  margin-top: -120px; /* flota sobre la imagen */
}

.ticket-card{
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 14px 40px rgba(0,0,0,.18);
}

.brand-dot{
  width: 28px;
  height: 28px;
  border-radius: 999px;
  border: 2px solid rgba(255,255,255,.85);
}

.text-primary2{
  color: #7a6a2a; /* dorado suave (puedes poner tu primary si quieres) */
}

.pill-input :deep(.q-field__control){
  border-radius: 12px;
}

.counter-pill{
  display:flex;
  align-items:center;
  padding: 10px 12px;
  background: #f4f4f4;
  border-radius: 12px;
}

.counter-btn{
  background: #b79a2b;
  color: #fff;
}

.buy-btn{
  background: #b79a2b;
  color: #fff;
  border-radius: 12px;
  padding: 10px 16px;
}
</style>
