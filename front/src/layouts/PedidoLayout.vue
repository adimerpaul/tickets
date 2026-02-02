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
          infinite
          height="420px"
          class="rounded-borders"
        >
          <q-carousel-slide :name="1" img-src="home-hero.464470cb.webp" />

          <template v-slot:control>
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

        <!-- CARD FLOTANTE -->
        <div class="q-px-md card-wrap">
          <q-card flat class="ticket-card">
            <div class="row q-col-gutter-xl items-start">

              <!-- IZQUIERDA -->
              <div class="col-12 col-md-7">
                <div class="text-h5 text-weight-bold text-primary2 q-mb-xs">
                  {{ evento?.nombre || 'Cargando evento...' }}
                </div>

                <div class="row items-center text-grey-7 q-mb-lg">
                  <q-icon name="place" size="16px" class="q-mr-xs" />
                  <div class="text-caption">
                    {{ evento?.ciudad ? (evento.ciudad + (evento.pais ? ', ' + evento.pais : '')) : 'Selecciona fecha y hora' }}
                  </div>
                </div>

                <!-- Fecha y hora -->
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
                      :loading="loadingData"
                      readonly
                    >
                      <template #prepend>
                        <q-icon name="event" />
                      </template>

                      <q-popup-proxy transition-show="scale" transition-hide="scale">
                        <q-date
                          v-model="date"
                          mask="YYYY-MM-DD"
                          minimal
                          :options="isDateEnabled"
                          @update:model-value="onDateChange"
                        />
                      </q-popup-proxy>
                    </q-input>
                  </div>

                  <div class="col-12 col-sm-5">
                    <q-select
                      v-model="slotKey"
                      :options="slotOptions"
                      dense
                      outlined
                      bg-color="grey-1"
                      class="pill-input"
                      emit-value
                      map-options
                      :disable="!date || loadingData"
                      :loading="loadingData"
                      label="Hora"
                    >
                      <template #prepend>
                        <q-icon name="schedule" />
                      </template>
                    </q-select>
                  </div>
                </div>

                <!-- Nacionalidad (DINÁMICA DESDE evento_nacionalidades) -->
                <div class="text-subtitle2 text-grey-8 q-mb-sm">Nacionalidad</div>
                <q-option-group
                  v-model="nacionalidadId"
                  inline
                  class="q-mb-md"
                  :options="nacionalidadOptions"
                />

                <q-banner v-if="slotKey && slotResumen" rounded class="bg-grey-1 q-mt-sm">
                  <div class="text-caption text-grey-8">
                    <b>Disponibilidad:</b>
                    Adulto: {{ slotResumen.adulto.disponibles }} disponibles ·
                    Niño: {{ slotResumen.nino.disponibles }} disponibles
                  </div>

                  <div class="text-caption text-grey-8 q-mt-xs">
                    <b>Precios:</b>
                    Adulto: {{ formatCurrency(priceAdult) }} ·
                    Niño: {{ formatCurrency(priceKid) }}
                  </div>
                </q-banner>

                <q-banner v-if="slotKey && slotResumen && priceMissing" rounded class="bg-red-1 q-mt-sm text-red-9">
                  Falta configurar precio para la combinación seleccionada (nacionalidad / tipo / segmento).
                </q-banner>
              </div>

              <!-- DERECHA -->
              <div class="col-12 col-md-5">
                <div class="text-subtitle2 text-grey-8 q-mb-sm">Tipo de Entrada</div>

                <!-- TIPOS (DINÁMICO DESDE evento_tipos_entrada) -->
                <div class="row q-col-gutter-md q-mb-lg">
                  <div
                    v-for="t in tipos"
                    :key="t.id"
                    class="col-6"
                  >
                    <div class="row items-center q-gutter-sm">
                      <q-radio
                        v-model="tipoEntradaId"
                        :val="t.id"
                      />
                      <div class="text-weight-medium">
                        {{ t.nombre }}
                      </div>
                    </div>

                    <q-avatar size="74px" class="q-mt-sm shadow-2">
                      <q-img :src="t.imagen || defaultTipoImg" />
                    </q-avatar>
                  </div>
                </div>

                <div class="text-subtitle2 text-grey-8 q-mb-sm">N° de entradas</div>

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
                      <div class="text-grey-8">{{ kids }} Niños</div>
                      <q-space />
                      <div class="row items-center q-gutter-xs">
                        <q-btn round dense flat icon="remove" class="counter-btn" @click="dec('kid')" />
                        <q-btn round dense flat icon="add" class="counter-btn" @click="inc('kid')" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row items-center">
                  <q-space />
                  <div class="text-grey-7 q-mr-sm">Total</div>
                  <div class="text-h6 text-weight-bold">{{ formatCurrency(total) }}</div>
                </div>

                <div class="row justify-end q-mt-md">
                  <q-btn
                    unelevated
                    no-caps
                    class="buy-btn"
                    icon="shopping_cart"
                    label="Comprar Ahora"
                    :loading="loading"
                    :disable="!canBuy"
                    @click="onBuy"
                  />
                </div>

                <div v-if="!slotKey" class="text-caption text-grey-7 q-mt-sm">
                  Selecciona una fecha y una hora para ver precios y disponibilidad.
                </div>

              </div>
            </div>
          </q-card>
        </div>

      </q-page>
    </q-page-container>

    <!-- DIALOG CHECKOUT -->
    <q-dialog v-model="compraDialog" persistent>
      <q-card class="checkout-card">

        <q-card-section class="row items-center q-pb-sm">
          <div class="text-subtitle1 text-weight-bold">Completa tus datos</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="compraDialog = false" />
        </q-card-section>

        <q-separator />

        <q-form ref="buyForm" @submit.prevent="continueBuy">
          <q-card-section class="q-pa-md">
            <div class="row q-col-gutter-lg">

              <div class="col-12 col-md-7">
                <div class="text-subtitle2 text-grey-8 q-mb-md">Tu información</div>

                <div class="row q-col-gutter-sm">
                  <div class="col-12 col-md-6">
                    <q-input
                      v-model="nombre_completo"
                      outlined dense
                      label="Nombre completo"
                      :rules="[(v)=>!!v || 'Requerido']"
                    />
                  </div>
                  <div class="col-12 col-md-6">
                    <q-input
                      v-model="dni"
                      outlined dense
                      label="Documento (DNI/Pasaporte)"
                      :rules="[(v)=>!!v || 'Requerido']"
                    />
                  </div>
                </div>

                <q-separator spaced />

                <div class="row q-col-gutter-sm">
                  <div class="col-12 col-md-6">
                    <q-input
                      v-model="email"
                      outlined dense
                      label="Correo electrónico"
                      type="email"
                      :rules="[
                        (v)=>!!v || 'El correo es requerido',
                        (v)=>/.+@.+\..+/.test(v) || 'Correo inválido'
                      ]"
                    >
                      <template #prepend><q-icon name="mail" /></template>
                    </q-input>
                  </div>

                  <div class="col-12 col-md-6">
                    <q-input
                      v-model="email_confirm"
                      outlined dense
                      label="Confirmar correo"
                      type="email"
                      :rules="[
                        (v)=>!!v || 'Confirma tu correo',
                        (v)=>v === email || 'Los correos no coinciden'
                      ]"
                    >
                      <template #prepend><q-icon name="mail" /></template>
                    </q-input>
                  </div>
                </div>

                <div class="q-mt-sm">
                  <q-input v-model="phone" outlined dense label="Teléfono (Opcional)">
                    <template #prepend><q-icon name="call" /></template>
                  </q-input>
                </div>

                <div class="q-mt-md">
                  <q-checkbox v-model="accept_terms" dense>
                    Acepto <span class="text-primary cursor-pointer">Términos y privacidad</span>
                  </q-checkbox>
                  <div v-if="termsError" class="text-negative text-caption q-mt-xs">
                    Debes aceptar los términos para continuar.
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-5">
                <q-card flat bordered class="summary-card">
                  <q-card-section>
                    <div class="text-subtitle2 text-weight-bold">Resumen</div>

                    <div class="q-mt-md">
                      <div class="text-caption text-grey-7">Evento</div>
                      <div class="text-body2">{{ evento?.nombre || '—' }}</div>
                    </div>

                    <!-- ✅ En resumen mostramos fecha/hora, pero NO depende de “Nacional/Extranjero hardcode” -->
                    <div class="q-mt-sm">
                      <div class="text-caption text-grey-7">Fecha</div>
                      <div class="text-body2">{{ dateLabel || '—' }}</div>
                    </div>

                    <div class="q-mt-sm">
                      <div class="text-caption text-grey-7">Hora</div>
                      <div class="text-body2">{{ slotLabel || '—' }}</div>
                    </div>

                    <div class="q-mt-sm">
                      <div class="text-caption text-grey-7">Nacionalidad</div>
                      <div class="text-body2">{{ nacionalidadLabel || '—' }}</div>
                    </div>

                    <div class="q-mt-sm">
                      <div class="text-caption text-grey-7">Tipo de Entrada</div>
                      <div class="text-body2">{{ tipoEntradaLabel || '—' }}</div>
                    </div>

                    <q-separator spaced />

                    <div class="row items-center q-mb-sm">
                      <div class="text-body2">Adultos</div>
                      <q-space />
                      <div class="text-body2">{{ adults }}</div>
                    </div>

                    <div class="row items-center q-mb-sm">
                      <div class="text-body2">Niños</div>
                      <q-space />
                      <div class="text-body2">{{ kids }}</div>
                    </div>

                    <q-separator spaced />

                    <div class="row items-center">
                      <div class="text-body1 text-weight-bold">Total:</div>
                      <q-space />
                      <div class="text-body1 text-weight-bold">{{ formatCurrency(total) }}</div>
                    </div>
                  </q-card-section>
                </q-card>
              </div>

            </div>
          </q-card-section>

          <q-separator />

          <q-card-actions align="right" class="q-px-md q-py-sm">
            <q-btn flat no-caps label="Cancelar" color="grey-7" @click="compraDialog = false" :disable="loading" />
            <q-btn unelevated no-caps class="buy-btn" label="Finalizar compra" type="submit" :loading="loading" />
          </q-card-actions>
        </q-form>

      </q-card>
    </q-dialog>

  </q-layout>
</template>

<script>
export default {
  name: 'IndexPage',

  data () {
    return {
      eventoId: 1,

      // respuesta del endpoint nuevo
      evento: null,
      nacionalidades: [],
      tipos: [],
      precios: [],

      // index interno
      horarios: [],
      horariosByDate: {}, // { 'YYYY-MM-DD': { [slotKey]: {adulto:{...}, nino:{...}, label} } }
      preciosMap: new Map(), // key: `${nacId}|${tipoId}|${SEGMENTO}` -> priceNumber

      // UI
      slotOptions: [],
      slotKey: null,
      slotLabel: '',

      date: null,
      dateLabel: 'Selecciona una fecha',

      // selects dinámicos
      nacionalidadId: null,
      tipoEntradaId: null,

      // counters
      adults: 1,
      kids: 0,

      // loading
      loading: false,
      loadingData: false,

      // dialog
      compraDialog: false,
      accept_terms: false,
      termsError: false,

      // customer
      phone: '',
      email_confirm: '',
      dni: '',
      email: '',
      nombre_completo: '',

      // carousel
      slide: 1,
      autoplay: true,
      tab: 'tickets',

      defaultTipoImg: 'https://cdn.quasar.dev/img/parallax2.jpg'
    }
  },

  async mounted () {
    if (this.$route.query.session_id) {
      this.$alert.success('¡Gracias por tu compra! Un equipo de soporte se pondrá en contacto contigo pronto.')
    }
    await this.loadCheckoutData()
  },

  computed: {
    nacionalidadOptions () {
      return (this.nacionalidades || []).map(n => ({
        label: n.nombre,
        value: n.id
      }))
    },

    nacionalidadLabel () {
      const n = (this.nacionalidades || []).find(x => Number(x.id) === Number(this.nacionalidadId))
      return n ? n.nombre : ''
    },

    tipoEntradaLabel () {
      const t = (this.tipos || []).find(x => Number(x.id) === Number(this.tipoEntradaId))
      return t ? t.nombre : ''
    },

    slotResumen () {
      if (!this.date || !this.slotKey) return null
      const map = this.horariosByDate[this.date] || {}
      return map[this.slotKey] || null
    },

    priceAdult () {
      return this.getPrecioVenta('ADULTO')
    },

    priceKid () {
      // si no tienes NINO como segmento, puedes cambiar a 'NINO' o 'NIÑO' según guardes
      return this.getPrecioVenta('NINO')
    },

    total () {
      return (this.adults * this.priceAdult) + (this.kids * this.priceKid)
    },

    priceMissing () {
      // si el usuario quiere comprar y no hay precio configurado, bloqueamos
      const wantsAdult = this.adults > 0
      const wantsKid = this.kids > 0

      const pA = wantsAdult ? this.priceAdult : 1
      const pK = wantsKid ? this.priceKid : 1

      if (wantsAdult && (!pA || pA <= 0)) return true
      if (wantsKid && (!pK || pK <= 0)) return true
      return false
    },

    canBuy () {
      if (!this.date || !this.slotKey) return false
      if (!this.nacionalidadId || !this.tipoEntradaId) return false
      if (this.total <= 0) return false
      if (this.priceMissing) return false

      const r = this.slotResumen
      if (!r) return false

      if (this.adults > 0 && this.adults > r.adulto.disponibles) return false
      if (this.kids > 0 && this.kids > r.nino.disponibles) return false

      return true
    }
  },

  methods: {
    // =========================
    // LOAD NUEVO ENDPOINT
    // =========================
    async loadCheckoutData () {
      this.loadingData = true
      try {
        const { data } = await this.$axios.get(`eventos/${this.eventoId}/checkout-data`)

        this.evento = data.evento
        this.nacionalidades = data.nacionalidades || []
        this.tipos = data.tipos_entrada || []
        this.horarios = data.horarios || []
        this.precios = data.precios || []

        // defaults
        if (!this.nacionalidadId && this.nacionalidades.length) {
          this.nacionalidadId = this.nacionalidades[0].id
        }
        if (!this.tipoEntradaId && this.tipos.length) {
          this.tipoEntradaId = this.tipos[0].id
        }

        // construir indices
        this.buildHorariosIndex()
        this.buildPreciosMap()
      } catch (e) {
        console.error(e)
        this.$alert.error(e.response?.data?.message || 'Error cargando checkout-data')
      } finally {
        this.loadingData = false
      }
    },

    buildPreciosMap () {
      const m = new Map()
      const moneda = (this.evento?.moneda || 'EUR').toUpperCase()

      for (const p of this.precios) {
        const nacId = Number(p.nacionalidad_id)
        const tipoId = Number(p.tipo_entrada_id)
        const seg = String(p.segmento || '').toUpperCase()

        const price = Number(p?.monedas?.[moneda] || 0)
        const key = this.mkPrecioKey(nacId, tipoId, seg)
        m.set(key, price)
      }

      this.preciosMap = m
    },

    mkPrecioKey (nacId, tipoId, segmento) {
      return `${Number(nacId)}|${Number(tipoId)}|${String(segmento).toUpperCase()}`
    },

    getPrecioVenta (segmento) {
      if (!this.nacionalidadId || !this.tipoEntradaId) return 0
      const key = this.mkPrecioKey(this.nacionalidadId, this.tipoEntradaId, segmento)
      return Number(this.preciosMap.get(key) || 0)
    },

    // =========================
    // HORARIOS (solo disponibilidad)
    // =========================
    buildHorariosIndex () {
      const byDate = {}

      const normPlan = (p) => {
        const s = String(p || '').toLowerCase()
        if (s.includes('adult')) return 'adulto'
        if (s.includes('ni')) return 'nino'
        return s
      }

      for (const h of this.horarios) {
        const fecha = (h.fecha || (h.starts_at || '').slice(0, 10))
        if (!fecha) continue

        if (!byDate[fecha]) byDate[fecha] = {}

        const slotKey = h.starts_at
        if (!byDate[fecha][slotKey]) {
          byDate[fecha][slotKey] = {
            adulto: { id: null, disponibles: 0, capacidad: 0, reservados: 0 },
            nino:   { id: null, disponibles: 0, capacidad: 0, reservados: 0 },
            label: this.formatTimeLabel(slotKey)
          }
        }

        const plan = normPlan(h.plan)
        const cap = Number(h.capacidad || 0)
        const res = Number(h.reservados || 0)
        const disp = Math.max(0, cap - res)

        if (plan === 'adulto') {
          byDate[fecha][slotKey].adulto = {
            id: h.id,
            disponibles: disp,
            capacidad: cap,
            reservados: res
          }
        } else if (plan === 'nino') {
          byDate[fecha][slotKey].nino = {
            id: h.id,
            disponibles: disp,
            capacidad: cap,
            reservados: res
          }
        }
      }

      // limpiar vacíos
      for (const fecha of Object.keys(byDate)) {
        const slots = byDate[fecha]
        for (const k of Object.keys(slots)) {
          const s = slots[k]
          const hasAny = (s.adulto.id || s.nino.id)
          if (!hasAny) delete slots[k]
        }
        if (Object.keys(slots).length === 0) delete byDate[fecha]
      }

      this.horariosByDate = byDate

      const fechas = Object.keys(byDate).sort()
      if (!this.date && fechas.length) {
        this.date = fechas[0]
        this.onDateChange(this.date)
      } else if (this.date && !byDate[this.date] && fechas.length) {
        this.date = fechas[0]
        this.onDateChange(this.date)
      }
    },

    // =========================
    // DATE/TIME UI
    // =========================
    isDateEnabled (ymd) {
      const key = String(ymd).replaceAll('/', '-')
      return !!this.horariosByDate[key]
    },

    onDateChange (val) {
      if (!val) return

      const d = new Date(val + 'T00:00:00')
      this.dateLabel = d.toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' })

      const slots = this.horariosByDate[val] || {}
      const options = Object.keys(slots)
        .sort()
        .map(k => ({ label: slots[k].label, value: k }))

      this.slotOptions = options

      this.slotKey = options.length ? options[0].value : null
      this.slotLabel = options.length ? options[0].label : ''
    },

    formatTimeLabel (startsAt) {
      const safe = String(startsAt).replace(' ', 'T')
      const dt = new Date(safe)
      if (isNaN(dt.getTime())) return startsAt
      return dt.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
    },

    formatCurrency (n) {
      const cur = (this.evento?.moneda || 'EUR').toUpperCase()
      return new Intl.NumberFormat('es-ES', { style: 'currency', currency: cur }).format(Number(n || 0))
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
      if (!this.canBuy) {
        this.$alert.error('Revisa fecha/hora, nacionalidad, tipo y disponibilidad.')
        return
      }
      this.compraDialog = true
    },

    async continueBuy () {
      try {
        this.termsError = false
        if (!this.accept_terms) {
          this.termsError = true
          return
        }

        if (!this.slotResumen) {
          this.$alert.error('Selecciona una fecha y hora válida.')
          return
        }

        if (this.priceMissing) {
          this.$alert.error('Falta configurar precios para tu selección.')
          return
        }

        this.loading = true

        const items = []
        const meta = {
          evento_id: String(this.evento?.id || ''),
          evento_slug: this.evento?.slug || '',
          evento_nombre: this.evento?.nombre || '',
          starts_at: this.slotKey,
          fecha: this.date,
          hora_label: this.slotLabel || this.formatTimeLabel(this.slotKey),

          // ✅ ahora mandamos IDs reales + label
          nacionalidad_id: String(this.nacionalidadId || ''),
          nacionalidad: this.nacionalidadLabel || '',
          tipo_entrada_id: String(this.tipoEntradaId || ''),
          entrada_tipo: this.tipoEntradaLabel || '',

          dni: this.dni,
          nombre_completo: this.nombre_completo,
          phone: this.phone || ''
        }

        // ids horarios por plan (para sumar reservados en webhook)
        if (this.adults > 0 && this.slotResumen.adulto?.id) meta.horario_adulto_id = String(this.slotResumen.adulto.id)
        if (this.kids > 0 && this.slotResumen.nino?.id) meta.horario_nino_id = String(this.slotResumen.nino.id)

        // cantidades
        meta.adults = String(this.adults)
        meta.kids = String(this.kids)
        meta.total = String(this.total)

        // items stripe (precio viene de evento_precios)
        if (this.adults > 0) {
          items.push({
            name: `Entrada Adulto - ${this.evento?.nombre || ''} - ${meta.hora_label}`,
            qty: this.adults,
            unit_amount: Math.round((this.priceAdult || 0) * 100)
          })
        }

        if (this.kids > 0) {
          items.push({
            name: `Entrada Niño - ${this.evento?.nombre || ''} - ${meta.hora_label}`,
            qty: this.kids,
            unit_amount: Math.round((this.priceKid || 0) * 100)
          })
        }

        if (!items.length) {
          this.$alert.error('Selecciona al menos una entrada.')
          return
        }

        const { data } = await this.$axios.post('stripe/checkout', {
          items,
          customer_email: this.email,
          metadata: meta
        })

        window.location.href = data.checkout_url
      } catch (e) {
        console.error(e)
        this.$alert.error(e.response?.data?.message || 'Error creando el checkout. Revisa consola/Network.')
      } finally {
        this.loading = false
      }
    }
  },

  watch: {
    slotKey (v) {
      if (!v || !this.date) {
        this.slotLabel = ''
        return
      }
      const slots = this.horariosByDate[this.date] || {}
      this.slotLabel = slots[v]?.label || this.formatTimeLabel(v)
    },

    // si cambia nacionalidad o tipo, recalcular total/estado automáticamente (computed ya lo hace)
    nacionalidadId () {},
    tipoEntradaId () {}
  }
}
</script>

<style scoped>
.card-wrap { margin-top: -120px; }
.ticket-card{
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 14px 40px rgba(0,0,0,.18);
}
.brand-dot{ width: 28px; height: 28px; border-radius: 999px; border: 2px solid rgba(255,255,255,.85); }
.text-primary2{ color: #7a6a2a; }
.pill-input :deep(.q-field__control){ border-radius: 12px; }
.counter-pill{
  display:flex; align-items:center;
  padding: 10px 12px; background: #f4f4f4; border-radius: 12px;
}
.counter-btn{ background: #b79a2b; color: #fff; }
.buy-btn{
  background: #b79a2b; color: #fff;
  border-radius: 12px; padding: 10px 16px;
}
.checkout-card{ width: 980px; max-width: 95vw; border-radius: 14px; }
.summary-card{ border-radius: 12px; background: #fafafa; }
</style>
