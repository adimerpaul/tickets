<template>
  <q-page class="q-pa-md bg-grey-2">
    <div class="row justify-center">
      <div class="col-12 col-md-8 col-lg-6">

<!--        <q-card flat bordered class="q-pa-md">-->
<!--          &lt;!&ndash; Header &ndash;&gt;-->
<!--          <div class="row items-center q-mb-md">-->
<!--            <div>-->
<!--              <div class="text-h6 text-weight-bold">{{ $t('tickets.title') }}</div>-->
<!--              <div class="text-caption text-grey-7">{{ $t('tickets.subtitle') }}</div>-->
<!--            </div>-->

<!--            <q-space />-->

<!--            <q-chip color="primary" text-color="white" dense class="q-mr-sm">-->
<!--              {{ $t('tickets.total') }}: {{ formatEUR(total) }}-->
<!--            </q-chip>-->

<!--            <q-btn flat dense no-caps icon="language" :label="lang.toUpperCase()">-->
<!--              <q-menu>-->
<!--                <q-list style="min-width: 160px">-->
<!--                  <q-item clickable v-close-popup @click="setLang('es')">-->
<!--                    <q-item-section>Español</q-item-section>-->
<!--                  </q-item>-->
<!--                  <q-item clickable v-close-popup @click="setLang('en')">-->
<!--                    <q-item-section>English</q-item-section>-->
<!--                  </q-item>-->
<!--                </q-list>-->
<!--              </q-menu>-->
<!--            </q-btn>-->
<!--          </div>-->

<!--          &lt;!&ndash; EU + Fecha/Hora &ndash;&gt;-->
<!--          <div class="row q-col-gutter-md">-->
<!--            <div class="col-12">-->
<!--              <div class="text-subtitle2 text-weight-medium">{{ $t('tickets.euQuestion') }}</div>-->
<!--              <q-option-group-->
<!--                v-model="isEU"-->
<!--                inline-->
<!--                :options="[-->
<!--                  { label: $t('tickets.yes'), value: true },-->
<!--                  { label: $t('tickets.no'), value: false }-->
<!--                ]"-->
<!--                class="q-mt-xs"-->
<!--              />-->
<!--            </div>-->

<!--            <div class="col-12 col-md-6">-->
<!--              <q-input-->
<!--                v-model="date"-->
<!--                type="date"-->
<!--                dense-->
<!--                outlined-->
<!--                :label="$t('tickets.date')"-->
<!--              >-->
<!--                <template #prepend><q-icon name="event" /></template>-->
<!--              </q-input>-->
<!--            </div>-->

<!--            <div class="col-12 col-md-6">-->
<!--              <q-select-->
<!--                v-model="time"-->
<!--                :options="timeOptions"-->
<!--                dense-->
<!--                outlined-->
<!--                :label="$t('tickets.time')"-->
<!--                emit-value-->
<!--                map-options-->
<!--                clearable-->
<!--              >-->
<!--                <template #prepend><q-icon name="schedule" /></template>-->
<!--              </q-select>-->
<!--            </div>-->
<!--          </div>-->

<!--          <q-separator class="q-my-md" />-->

<!--          &lt;!&ndash; Entradas &ndash;&gt;-->
<!--          <div class="text-subtitle2 text-weight-bold q-mb-sm">{{ $t('tickets.entries') }}</div>-->

<!--          &lt;!&ndash; Adultos &ndash;&gt;-->
<!--          <q-card flat bordered class="q-pa-md q-mb-sm">-->
<!--            <div class="row items-center">-->
<!--              <div class="col">-->
<!--                <div class="text-subtitle2 text-weight-medium">{{ $t('tickets.adult') }}</div>-->
<!--                <div class="text-caption text-grey-7">{{ $t('tickets.adultHint') }}</div>-->
<!--              </div>-->

<!--              <div class="col-auto text-right q-mr-md">-->
<!--                <div class="text-subtitle2 text-weight-bold">{{ formatEUR(priceAdult) }}</div>-->
<!--                <div class="text-caption text-grey-7">{{ $t('tickets.each') }}</div>-->
<!--              </div>-->

<!--              <div class="col-auto">-->
<!--                <q-btn round dense flat icon="remove" @click="dec('adult')" />-->
<!--                <q-chip class="q-mx-sm" square>{{ adults }}</q-chip>-->
<!--                <q-btn round dense flat icon="add" @click="inc('adult')" />-->
<!--              </div>-->
<!--            </div>-->
<!--          </q-card>-->

<!--          &lt;!&ndash; Niños &ndash;&gt;-->
<!--          <q-card flat bordered class="q-pa-md">-->
<!--            <div class="row items-center">-->
<!--              <div class="col">-->
<!--                <div class="text-subtitle2 text-weight-medium">{{ $t('tickets.kid') }}</div>-->
<!--                <div class="text-caption text-grey-7">{{ $t('tickets.kidHint') }}</div>-->
<!--              </div>-->

<!--              <div class="col-auto text-right q-mr-md">-->
<!--                <div class="text-subtitle2 text-weight-bold">{{ formatEUR(priceKid) }}</div>-->
<!--                <div class="text-caption text-grey-7">{{ $t('tickets.each') }}</div>-->
<!--              </div>-->

<!--              <div class="col-auto">-->
<!--                <q-btn round dense flat icon="remove" @click="dec('kid')" />-->
<!--                <q-chip class="q-mx-sm" square>{{ kids }}</q-chip>-->
<!--                <q-btn round dense flat icon="add" @click="inc('kid')" />-->
<!--              </div>-->
<!--            </div>-->
<!--          </q-card>-->

<!--          <q-separator class="q-my-md" />-->

<!--          &lt;!&ndash; Resumen &ndash;&gt;-->
<!--          <div class="row items-center">-->
<!--            <div class="col">-->
<!--              <div class="text-caption text-grey-7">{{ $t('tickets.subtotal') }}</div>-->
<!--              <div class="text-subtitle1 text-weight-bold">{{ formatEUR(total) }}</div>-->
<!--            </div>-->

<!--            <div class="col-auto">-->
<!--              <q-btn-->
<!--                unelevated-->
<!--                color="black"-->
<!--                no-caps-->
<!--                icon="payments"-->
<!--                :label="$t('tickets.pay')"-->
<!--                :loading="loading"-->
<!--                :disable="total <= 0"-->
<!--                @click="onBuy"-->
<!--              />-->
<!--            </div>-->
<!--          </div>-->

<!--          <div class="text-caption text-grey-7 q-mt-sm">-->
<!--            {{ $t('tickets.note') }}-->
<!--          </div>-->
<!--        </q-card>-->

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
      lang: localStorage.getItem('lang') || 'es',

      isEU: true,
      date: '2026-01-17',
      time: null,

      adults: 1,
      kids: 0,

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
    setLang (l) {
      this.lang = l
      this.$setLang(l) // boot/i18n.js
      this.$q.notify({ type: 'positive', message: `Idioma: ${l.toUpperCase()}` })
    },
    formatEUR (n) {
      // opcional: si quieres formateo según idioma, dímelo y lo ajustamos
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
