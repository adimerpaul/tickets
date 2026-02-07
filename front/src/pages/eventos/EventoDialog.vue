<template>
    <q-card class="column">

      <q-card-section class="row items-center">
        <div>
          <div class="text-h6 text-weight-bold">
            {{ local.id ? 'Evento: ' + local.nombre : 'Nuevo evento' }}
          </div>
          <div class="text-caption text-grey-7">General + gestión de horarios</div>
        </div>
        <q-space />
        <q-btn icon="close" flat round dense @click="close" />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-none">
        <q-tabs v-model="tab" dense active-color="primary" indicator-color="primary" class="bg-grey-1">
          <q-tab name="general" icon="info" label="General" />
          <q-tab name="horarios" icon="schedule" label="Horarios" :disable="!local.id" />
          <q-tab name="precios" icon="payments" label="Precios" :disable="!local.id" />
        </q-tabs>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-md col">
        <q-tab-panels v-model="tab" animated>

          <q-tab-panel name="general">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-input v-model="local.nombre" dense outlined label="Nombre" :rules="[req]" />
              </div>

              <div class="col-12 col-md-6">
                <q-input v-model="local.slug" dense outlined label="Slug" :rules="[req]">
                  <template v-slot:append>
                    <q-btn flat dense icon="auto_fix_high" @click="autoSlug" :disable="!local.nombre" />
                  </template>
                </q-input>
              </div>

              <div class="col-12">
                <q-input v-model="local.descripcion" type="textarea" autogrow dense outlined label="Descripción" />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model="local.pais" dense outlined label="País" />
              </div>
              <div class="col-12 col-md-3">
                <q-input v-model="local.ciudad" dense outlined label="Ciudad" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="local.ubicacion" dense outlined label="Ubicación / Dirección" />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model.number="local.lat" dense outlined type="number" label="Latitud" />
              </div>
              <div class="col-12 col-md-3">
                <q-input v-model.number="local.lng" dense outlined type="number" label="Longitud" />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model="local.categoria" dense outlined label="Categoría" />
              </div>
              <div class="col-12 col-md-3">
                <q-input v-model.number="local.orden" dense outlined type="number" label="Orden" />
              </div>

              <div class="col-12 col-md-3">
                <q-select
                  v-model="local.regla_nacionalidad"
                  dense outlined
                  label="Regla de nacionalidad"
                  :options="reglaOptions"
                  emit-value
                  map-options
                />
              </div>
              <div class="col-12 col-md-3">
                <q-select
                  v-model="local.moneda_id"
                  dense outlined
                  label="Moneda"
                  :options="monedas.map(m => ({ label: `${m.codigo} - ${m.nombre}`, value: m.id }))"
                  emit-value
                  map-options
                />
              </div>
              <div class="col-12 col-md-3">
                <q-select
                  v-model="local.idioma_id"
                  dense outlined
                  label="Idioma"
                  :options="idiomas.map(i => ({ label: `${i.codigo} - ${i.nombre}`, value: i.id }))"
                  emit-value
                  map-options
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input v-model="local.imagen" dense outlined label="Imagen (URL / path)" />
              </div>

              <div class="col-12 col-md-3">
                <q-toggle v-model="local.activo" label="Evento activo" />
              </div>

<!--              <div class="col-12">-->
<!--                <q-separator spaced />-->
<!--                <div class="text-subtitle2 text-weight-bold q-mb-sm">Configuración default de horarios</div>-->
<!--              </div>-->

<!--              <div class="col-12 col-md-3">-->
<!--                <q-input v-model.number="local.slot_interval_min" dense outlined type="number" label="Intervalo (min)" />-->
<!--              </div>-->
<!--              <div class="col-12 col-md-3">-->
<!--                <q-input v-model="local.semana_hora_inicio" dense outlined type="time" label="Hora inicio (default)" />-->
<!--              </div>-->
<!--              <div class="col-12 col-md-3">-->
<!--                <q-input v-model="local.semana_hora_fin" dense outlined type="time" label="Hora fin (default)" />-->
<!--              </div>-->
<!--              <div class="col-12 col-md-3">-->
<!--                <q-input v-model.number="local.generar_semanas" dense outlined type="number" label="Generar semanas" />-->
<!--              </div>-->
            </div>

            <div class="row justify-end q-gutter-sm q-mt-md">
              <q-btn flat no-caps color="grey-7" label="Cancelar" @click="close" :disable="loading" />
              <q-btn color="primary" no-caps :label="local.id ? 'Guardar' : 'Crear'" :loading="loading" @click="save" />
            </div>
          </q-tab-panel>

          <q-tab-panel name="horarios">
            <horarios-calendar :evento-id="local.id" />
          </q-tab-panel>
          <q-tab-panel name="precios">
            <evento-precios :evento-id="local.id" />
          </q-tab-panel>

        </q-tab-panels>
      </q-card-section>

    </q-card>
</template>

<script>
import HorariosCalendar from './HorariosCalendar.vue'
import EventoPrecios from './EventoPrecios.vue'

export default {
  name: 'EventoDialog',
  components: { HorariosCalendar, EventoPrecios },
  props: {
    value: { type: Boolean, default: false },
    evento: { type: Object, default: () => ({}) }
  },
  data () {
    return {
      open: false,
      tab: 'general',
      loading: false,
      local: {},
      monedas: [],
      idiomas: [],

      reglaOptions: [
        { label: 'Todos', value: 'ALL' },
        { label: 'Solo Egipcios', value: 'EGYPTIAN_ONLY' },
        { label: 'Solo Extranjeros', value: 'FOREIGNERS_ONLY' }
      ]
    }
  },
  mounted() {
    this.initLocal()
    this.loadOptions()
  },
  watch: {
    evento: {
      deep: true,
      handler () {
        this.initLocal()
      }
    }
  },
  // watch: {
  //   value: {
  //     immediate: true,
  //     handler (v) { this.open = v }
  //   },
  //   open (v) {
  //     this.$emit('input', v)
  //     if (v) this.initLocal()
  //   },
  //   evento: {
  //     deep: true,
  //     handler () {
  //       if (this.open) this.initLocal()
  //     }
  //   }
  // },
  methods: {
    req (v) { return !!v || 'Campo requerido' },

    initLocal () {
      const ev = this.evento || {}
      this.local = {
        id: ev.id || null,
        nombre: ev.nombre || '',
        slug: ev.slug || '',
        descripcion: ev.descripcion || '',
        pais: ev.pais || 'Egypt',
        ciudad: ev.ciudad || '',
        ubicacion: ev.ubicacion || '',
        lat: ev.lat ?? null,
        lng: ev.lng ?? null,
        activo: ev.activo !== undefined ? !!ev.activo : true,
        imagen: ev.imagen || '',
        categoria: ev.categoria || '',
        orden: ev.orden ?? 0,
        regla_nacionalidad: ev.regla_nacionalidad || 'ALL',
        moneda_id: ev.moneda_id ?? ev.moneda?.id ?? null,
        idioma_id: ev.idioma_id ?? ev.idioma?.id ?? null,

        slot_interval_min: ev.slot_interval_min || 30,
        semana_hora_inicio: (ev.semana_hora_inicio || '09:00').slice(0, 5),
        semana_hora_fin: (ev.semana_hora_fin || '17:00').slice(0, 5),
        generar_semanas: ev.generar_semanas || 52
      }
      if (!this.local.moneda_id && this.monedas.length) {
        this.local.moneda_id = this.monedas[0].id
      }
      if (!this.local.idioma_id && this.idiomas.length) {
        this.local.idioma_id = this.idiomas[0].id
      }
      this.tab = this.local.id ? 'general' : 'general'
    },
    async loadOptions () {
      try {
        const [rm, ri] = await Promise.all([
          this.$axios.get('monedas', { params: { solo_activos: 1 } }),
          this.$axios.get('idiomas', { params: { solo_activos: 1 } })
        ])
        this.monedas = rm.data.items || []
        this.idiomas = ri.data.items || []

        if (!this.local.moneda_id && this.monedas.length) {
          this.local.moneda_id = this.monedas[0].id
        }
        if (!this.local.idioma_id && this.idiomas.length) {
          this.local.idioma_id = this.idiomas[0].id
        }
      } catch (e) {
        this.$alert.error('No se pudieron cargar monedas/idiomas')
      }
    },

    slugify (text) {
      return (text || '')
        .toString().trim().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)+/g, '')
    },
    autoSlug () { this.local.slug = this.slugify(this.local.nombre) },

    close () {
      this.open = false
      console.log('emit closed');
      this.$emit('closed')
    },

    save () {
      if (!this.local.nombre || !this.local.slug) {
        this.$alert.error('Nombre y slug son requeridos')
        return
      }
      if (!this.local.moneda_id || !this.local.idioma_id) {
        this.$alert.error('Moneda e idioma son requeridos')
        return
      }

      const payload = { ...this.local }
      // normalizar time
      if (payload.semana_hora_inicio && payload.semana_hora_inicio.length > 5) payload.semana_hora_inicio = payload.semana_hora_inicio.slice(0, 5)
      if (payload.semana_hora_fin && payload.semana_hora_fin.length > 5) payload.semana_hora_fin = payload.semana_hora_fin.slice(0, 5)

      this.loading = true
      const req = payload.id
        ? this.$axios.put(`eventos/${payload.id}`, payload)
        : this.$axios.post('eventos', payload)

      req.then(r => {
        this.$alert.success(payload.id ? 'Evento guardado' : 'Evento creado')
        this.local = r.data
        // si recién se creó, habilita tab horarios
        this.tab = 'horarios'
        this.$emit('saved', this.local)
      })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo guardar'))
        .finally(() => { this.loading = false })
    }
  }
}
</script>
