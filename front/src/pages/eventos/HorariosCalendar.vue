<template>
  <div class="row q-col-gutter-md">
    <!-- CALENDAR -->
    <div class="col-12 col-md-8">
      <q-card flat bordered>
        <q-card-section class="row items-center">
          <div>
            <div class="text-subtitle1 text-weight-bold">Calendario</div>
            <div class="text-caption text-grey-7">
              Click en un día para ver/editar horarios
            </div>
          </div>
          <q-space />

<!--          <q-select-->
<!--            v-model="plan"-->
<!--            dense outlined-->
<!--            style="width: 160px"-->
<!--            label="Plan"-->
<!--            :options="planes"-->
<!--            @update:model-value="reloadMonth"-->
<!--          />-->

<!--          <q-btn-->
<!--            class="q-ml-sm"-->
<!--            color="primary"-->
<!--            no-caps-->
<!--            icon="add"-->
<!--            label="Generar"-->
<!--            @click="gen.open=true"-->
<!--          />-->
        </q-card-section>

        <q-separator />

        <q-card-section>
          <full-calendar
            ref="cal"
            :options="calendarOptions"
          />
        </q-card-section>
      </q-card>
    </div>

    <!-- SIDE DAY PANEL -->
    <div class="col-12 col-md-4">
      <q-card flat bordered>
        <q-card-section class="row items-center">
          <div>
            <div class="text-subtitle1 text-weight-bold">Horarios del día</div>
            <div class="text-caption text-grey-7">
              {{ selectedDate || 'Selecciona un día' }}
            </div>
          </div>
          <q-space />
          <q-btn flat round dense icon="refresh" @click="selectedDate && fetchDay()" />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-none">
          <q-list separator>
            <q-item v-if="!selectedDate">
              <q-item-section>
                <q-item-label class="text-grey-7">Haz click en una fecha del calendario.</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-else-if="loadingDay">
              <q-item-section>
                <q-item-label>Cargando...</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-else-if="dayItems.length === 0">
              <q-item-section>
                <q-item-label class="text-grey-7">Sin horarios. Puedes generar.</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-for="it in dayItems" :key="it.id" clickable @click="openEdit(it)">
              <q-item-section>
                <q-item-label class="text-weight-bold">
                  {{ hhmm(it.starts_at) }} - {{ hhmm(it.ends_at) }}
                  <q-badge v-if="!it.activo" class="q-ml-sm" color="grey-6" text-color="white">Inactivo</q-badge>
                </q-item-label>
                <q-item-label caption>
                  Plan: {{ it.plan || '-' }} · Cap: {{ it.capacidad }} · Precio: {{ it.precio }}
                </q-item-label>
              </q-item-section>

              <q-item-section side>
                <q-btn flat round dense icon="delete" color="negative" @click.stop="removeSlot(it)" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </div>

    <!-- DIALOG GENERAR -->
    <q-dialog v-model="gen.open" persistent>
      <q-card style="width: 520px; max-width: 95vw;">
        <q-card-section class="row items-center">
          <div class="text-subtitle1 text-weight-bold">Generar horarios</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="gen.open=false" />
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <q-input v-model="gen.date_from" dense outlined type="date" label="Desde" />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="gen.date_to" dense outlined type="date" label="Hasta" />
            </div>

            <div class="col-12 col-md-6">
              <q-select
                v-model="gen.mode"
                dense outlined
                label="Modo"
                :options="modeOptions"
                emit-value
                map-options
              />
            </div>

            <div class="col-12 col-md-6">
              <q-select
                v-model="gen.plan"
                dense outlined
                label="Plan"
                :options="planes"
              />
            </div>

            <div class="col-12">
              <q-banner rounded class="bg-grey-2">
                <div class="text-weight-bold">KEEP</div>
                <div class="text-caption text-grey-8">
                  Solo crea los que faltan, no toca lo existente.
                </div>
                <div class="text-weight-bold q-mt-sm">REPLACE</div>
                <div class="text-caption text-grey-8">
                  Borra los del rango y los genera de nuevo.
                </div>
              </q-banner>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat no-caps color="grey-7" label="Cancelar" @click="gen.open=false" />
          <q-btn color="primary" no-caps label="Generar" :loading="gen.loading" @click="doGenerate" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- DIALOG EDITAR SLOT -->
    <q-dialog v-model="edit.open" persistent>
      <q-card style="width: 520px; max-width: 95vw;">
        <q-card-section class="row items-center">
          <div class="text-subtitle1 text-weight-bold">Editar horario</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="edit.open=false" />
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-banner rounded class="bg-grey-2">
            <div class="text-weight-bold">
              {{ selectedDate }} · {{ hhmm(edit.form.starts_at) }} - {{ hhmm(edit.form.ends_at) }}
            </div>
            <div class="text-caption text-grey-7">
              Plan: {{ edit.form.plan || '-' }}
            </div>
          </q-banner>

          <div class="row q-col-gutter-md q-mt-sm">
            <div class="col-12 col-md-4">
              <q-toggle v-model="edit.form.activo" label="Activo" />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model.number="edit.form.capacidad" dense outlined type="number" label="Capacidad" />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model.number="edit.form.precio" dense outlined type="number" label="Precio" />
            </div>
            <div class="col-12">
              <q-input v-model="edit.form.nota" dense outlined label="Nota" />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat no-caps color="grey-7" label="Cancelar" @click="edit.open=false" />
          <q-btn color="primary" no-caps label="Guardar" :loading="edit.loading" @click="saveEdit" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

export default {
  name: 'HorariosCalendar',
  components: { FullCalendar },
  props: {
    eventoId: { type: [Number, String], required: true }
  },
  data () {
    return {
      planes: ['Adulto', 'Niño'],
      plan: 'Adulto',

      loadingMonth: false,
      loadingDay: false,

      selectedDate: '',

      monthEvents: [],
      dayItems: [],

      gen: {
        open: false,
        loading: false,
        date_from: '',
        date_to: '',
        plan: 'Adulto',
        mode: 'keep'
      },

      modeOptions: [
        { label: 'KEEP (mantener existentes)', value: 'keep' },
        { label: 'REPLACE (borrar y regenerar)', value: 'replace' }
      ],

      edit: {
        open: false,
        loading: false,
        form: {}
      }
    }
  },

  computed: {
    calendarOptions () {
      return {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: 'es',
        height: 'auto',
        selectable: true,
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: ''
        },
        events: this.monthEvents,
        dateClick: (info) => {
          this.selectedDate = info.dateStr
          this.fetchDay()
        },
        datesSet: (info) => {
          // info.startStr / info.endStr vienen en ISO (incluye hora)
          const start = info.startStr.slice(0, 10)
          const end = info.endStr.slice(0, 10)
          this.reloadMonthRange(start, end)
        }
      }
    }
  },

  mounted () {
    // set defaults para generar: mes actual
    const today = new Date()
    const y = today.getFullYear()
    const m = String(today.getMonth() + 1).padStart(2, '0')
    this.gen.date_from = `${y}-${m}-01`
    this.gen.date_to = `${y}-${m}-28`
    this.gen.plan = this.plan
  },

  methods: {
    hhmm (dt) {
      if (!dt) return ''
      // "YYYY-MM-DD HH:mm:ss"
      const parts = String(dt).split(' ')
      if (parts.length < 2) return ''
      return parts[1].slice(0, 5)
    },

    reloadMonth () {
      this.gen.plan = this.plan
      const api = this.$refs.cal && this.$refs.cal.getApi ? this.$refs.cal.getApi() : null
      if (api) {
        const v = api.view
        this.reloadMonthRange(v.activeStart.toISOString().slice(0, 10), v.activeEnd.toISOString().slice(0, 10))
      }
    },

    reloadMonthRange (start, end) {
      this.loadingMonth = true
      this.$axios.get(`eventos/${this.eventoId}/horarios/month`, {
        params: { start, end, plan: this.plan }
      })
        .then(r => { this.monthEvents = r.data?.items || [] })
        .catch(e => this.$alert.error(e.response?.data?.message || 'Error cargando calendario'))
        .finally(() => { this.loadingMonth = false })
    },

    fetchDay () {
      if (!this.selectedDate) return
      this.loadingDay = true
      this.$axios.get(`eventos/${this.eventoId}/horarios/day`, {
        params: { date: this.selectedDate, plan: this.plan }
      })
        .then(r => { this.dayItems = r.data?.items || [] })
        .catch(e => this.$alert.error(e.response?.data?.message || 'Error cargando horarios del día'))
        .finally(() => { this.loadingDay = false })
    },

    doGenerate () {
      if (!this.gen.date_from || !this.gen.date_to) {
        this.$alert.error('Selecciona rango')
        return
      }

      this.gen.loading = true
      this.$axios.post(`eventos/${this.eventoId}/horarios/generate`, {
        date_from: this.gen.date_from,
        date_to: this.gen.date_to,
        plan: this.gen.plan,
        mode: this.gen.mode
      })
        .then(r => {
          const created = r.data?.created ?? 0
          const updated = r.data?.updated ?? 0
          this.$alert.success(`Generado. Creados: ${created} · Actualizados: ${updated}`)
          this.gen.open = false
          this.reloadMonth()
          if (this.selectedDate) this.fetchDay()
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo generar'))
        .finally(() => { this.gen.loading = false })
    },

    openEdit (it) {
      this.edit.form = { ...it }
      this.edit.open = true
    },

    saveEdit () {
      this.edit.loading = true
      this.$axios.put(`evento-horarios/${this.edit.form.id}`, {
        activo: this.edit.form.activo,
        capacidad: this.edit.form.capacidad,
        precio: this.edit.form.precio,
        nota: this.edit.form.nota
      })
        .then(() => {
          this.$alert.success('Guardado')
          this.edit.open = false
          this.fetchDay()
          this.reloadMonth()
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo guardar'))
        .finally(() => { this.edit.loading = false })
    },

    removeSlot (it) {
      this.$alert.dialog('¿Eliminar este horario?')
        .onOk(() => {
          this.$axios.delete(`evento-horarios/${it.id}`)
            .then(() => {
              this.$alert.success('Eliminado')
              this.fetchDay()
              this.reloadMonth()
            })
            .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo eliminar'))
        })
    }
  }
}
</script>

<style scoped>
/* opcional: hace que el calendario se vea más compacto */
</style>
