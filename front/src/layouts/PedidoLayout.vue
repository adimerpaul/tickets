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
                      :loading="loadingHorarios"
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
                      :disable="!date || loadingHorarios"
                      :loading="loadingHorarios"
                      label="Hora"
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

                <q-banner v-if="slotKey && slotResumen" rounded class="bg-grey-1 q-mt-sm">
                  <div class="text-caption text-grey-8">
                    <b>Disponibilidad:</b>
                    Adulto: {{ slotResumen.adulto.disponibles }} disponibles ·
                    Niño: {{ slotResumen.nino.disponibles }} disponibles
                  </div>
                  <div class="text-caption text-grey-8 q-mt-xs">
                    <b>Precios:</b>
                    Adulto: {{ formatCurrency(slotResumen.adulto.precio) }} ·
                    Niño: {{ formatCurrency(slotResumen.nino.precio) }}
                  </div>
                </q-banner>
              </div>

              <!-- DERECHA -->
              <div class="col-12 col-md-5">
                <div class="text-subtitle2 text-grey-8 q-mb-sm">Tipo de Entrada</div>

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
                    <q-input v-model="nombre_completo" outlined dense label="Nombre completo"
                             :rules="[(v)=>!!v || 'Requerido']" />
                  </div>
                  <div class="col-12 col-md-6">
                    <q-input v-model="dni" outlined dense label="Documento (DNI/Pasaporte)"
                             :rules="[(v)=>!!v || 'Requerido']" />
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

                    <div class="q-mt-sm">
                      <div class="text-caption text-grey-7">Fecha</div>
                      <div class="text-body2">{{ dateLabel || '—' }}</div>
                    </div>

                    <div class="q-mt-sm">
                      <div class="text-caption text-grey-7">Hora</div>
                      <div class="text-body2">{{ slotLabel || '—' }}</div>
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

      evento: null,
      horarios: [],
      horariosByDate: {}, // { 'YYYY-MM-DD': { [slotKey]: {adulto:{...}, nino:{...}} } }

      slotOptions: [],
      slotKey: null, // starts_at (YYYY-MM-DD HH:mm:ss)
      slotLabel: '',

      loading: false,
      loadingHorarios: false,

      compraDialog: false,
      accept_terms: false,
      termsError: false,

      phone: '',
      email_confirm: '',
      dni: '',
      email: '',
      nombre_completo: '',

      date: null,
      dateLabel: 'Selecciona una fecha',

      slide: 1,
      autoplay: true,
      tab: 'tickets',

      nationality: 'NACIONAL',
      ticketType: 'simple',

      adults: 1,
      kids: 0,
    }
  },

  async mounted () {
    // éxito stripe
    if (this.$route.query.session_id) {
      this.$alert.success('¡Gracias por tu compra! Un equipo de soporte se pondrá en contacto contigo pronto.')
    }

    await this.loadEventoAndHorarios()
  },

  computed: {
    slotResumen () {
      if (!this.date || !this.slotKey) return null
      const map = this.horariosByDate[this.date] || {}
      return map[this.slotKey] || null
    },

    priceAdult () {
      return this.slotResumen?.adulto?.precio ?? 0
    },

    priceKid () {
      return this.slotResumen?.nino?.precio ?? 0
    },

    total () {
      return (this.adults * this.priceAdult) + (this.kids * this.priceKid)
    },

    canBuy () {
      if (!this.date || !this.slotKey) return false
      if (this.total <= 0) return false
      const r = this.slotResumen
      if (!r) return false

      // disponibilidad
      if (this.adults > 0 && this.adults > r.adulto.disponibles) return false
      if (this.kids > 0 && this.kids > r.nino.disponibles) return false

      return true
    }
  },

  methods: {
    // ========= LOAD =========
    async loadEventoAndHorarios () {
      try {
        this.loadingHorarios = true
        const { data: ev } = await this.$axios.get(`eventos/${this.eventoId}`)
        this.evento = ev

        // traer TODOS los horarios paginando
        const all = []
        let page = 1
        const perPage = 200

        while (true) {
          const { data } = await this.$axios.get(`eventos/${this.eventoId}/horarios`, { params: { page, perPage, activo: true } })
          const rows = data?.data || []
          all.push(...rows)
          if ((data?.current_page || 1) >= (data?.last_page || 1)) break
          page++
          if (page > 50) break // seguridad
        }

        // solo activos y con starts_at
        this.horarios = all.filter(h => h.activo && h.starts_at)

        this.buildHorariosIndex()
      } catch (e) {
        console.error(e)
        this.$alert.error(e.response?.data?.message || 'Error cargando evento/horarios')
      } finally {
        this.loadingHorarios = false
      }
    },

    buildHorariosIndex () {
      const byDate = {}

      const normPlan = (p) => {
        const s = String(p || '').toLowerCase()
        if (s.includes('adult')) return 'adulto'
        if (s.includes('ni')) return 'nino'
        return s // fallback
      }

      for (const h of this.horarios) {
        const fecha = (h.fecha || (h.starts_at || '').slice(0, 10))
        if (!fecha) continue

        if (!byDate[fecha]) byDate[fecha] = {}

        const slotKey = h.starts_at // "YYYY-MM-DD HH:mm:ss"
        if (!byDate[fecha][slotKey]) {
          byDate[fecha][slotKey] = {
            adulto: { id: null, precio: 0, disponibles: 0, capacidad: 0, reservados: 0 },
            nino:   { id: null, precio: 0, disponibles: 0, capacidad: 0, reservados: 0 },
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
            precio: Number(h.precio || 0),
            disponibles: disp,
            capacidad: cap,
            reservados: res
          }
        } else if (plan === 'nino') {
          byDate[fecha][slotKey].nino = {
            id: h.id,
            precio: Number(h.precio || 0),
            disponibles: disp,
            capacidad: cap,
            reservados: res
          }
        }
      }

      // filtrar slots vacíos (sin adulto ni niño)
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

      // si no hay fecha seleccionada, setear la primera disponible
      const fechas = Object.keys(byDate).sort()
      if (!this.date && fechas.length) {
        this.date = fechas[0]
        this.onDateChange(this.date)
      }
    },

    // ========= DATE/TIME =========
    isDateEnabled (ymd) {
      // ymd: "YYYY/MM/DD" en q-date según máscara, pero con mask "YYYY-MM-DD" suele pasar "YYYY/MM/DD" en options.
      // soportamos ambos:
      const key = String(ymd).replaceAll('/', '-')
      return !!this.horariosByDate[key]
    },

    onDateChange (val) {
      if (!val) return

      // val viene "YYYY-MM-DD"
      const d = new Date(val + 'T00:00:00')
      this.dateLabel = d.toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' })

      // cargar opciones de horas de ese día
      const slots = this.horariosByDate[val] || {}
      const options = Object.keys(slots)
        .sort()
        .map(k => ({ label: slots[k].label, value: k }))

      this.slotOptions = options

      // reset slot seleccionado
      this.slotKey = options.length ? options[0].value : null
      this.slotLabel = options.length ? options[0].label : ''
    },

    formatTimeLabel (startsAt) {
      // startsAt: "YYYY-MM-DD HH:mm:ss"
      const safe = String(startsAt).replace(' ', 'T')
      const dt = new Date(safe)
      if (isNaN(dt.getTime())) return startsAt
      return dt.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
    },

    formatCurrency (n) {
      // usa moneda del evento si existe, sino EUR
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
      if (!this.date || !this.slotKey) {
        this.$alert.error('Por favor selecciona una fecha y una hora.')
        return
      }
      if (!this.canBuy) {
        this.$alert.error('No hay disponibilidad suficiente para la cantidad seleccionada.')
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

        this.loading = true

        // items para Stripe desde horarios (Adulto/Niño)
        const items = []
        const meta = {
          evento_id: String(this.evento?.id || ''),
          evento_slug: this.evento?.slug || '',
          evento_nombre: this.evento?.nombre || '',
          starts_at: this.slotKey,
          fecha: this.date,
          hora_label: this.slotLabel || this.formatTimeLabel(this.slotKey),
          nacionalidad: this.nationality,
          entrada_tipo: this.ticketType,
          dni: this.dni,
          nombre_completo: this.nombre_completo,
          phone: this.phone || '',
        }

        // guardar IDs de horarios por plan (CLAVE PARA RESERVADOS)
        if (this.slotResumen.adulto?.id) meta.horario_adulto_id = String(this.slotResumen.adulto.id)
        if (this.slotResumen.nino?.id) meta.horario_nino_id = String(this.slotResumen.nino.id)

        if (this.adults > 0) {
          items.push({
            name: `Entrada Adulto - ${this.evento?.nombre || ''} - ${meta.hora_label}`,
            qty: this.adults,
            unit_amount: Math.round((this.priceAdult || 0) * 100) // centavos
          })
        }

        if (this.kids > 0) {
          items.push({
            name: `Entrada Niño - ${this.evento?.nombre || ''} - ${meta.hora_label}`,
            qty: this.kids,
            unit_amount: Math.round((this.priceKid || 0) * 100) // centavos
          })
        }

        if (!items.length) {
          this.$alert.error('Selecciona al menos una entrada.')
          return
        }

        // también manda cantidades como meta para el webhook
        meta.adults = String(this.adults)
        meta.kids = String(this.kids)
        meta.total = String(this.total)

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
    }
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
