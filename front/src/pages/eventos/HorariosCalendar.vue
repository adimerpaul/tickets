<template>
  <div class="row q-col-gutter-lg items-start">

    <!-- CALENDAR (izquierda) -->
    <div class="col-12 col-md-8">
      <q-card flat bordered>
        <q-card-section class="row items-center">
          <div>
            <div class="text-h6 text-weight-bold">Enero 2026</div>
            <div class="text-caption text-grey-7">Click en un día para ver horarios</div>
          </div>
          <q-space />
          <q-btn
            color="primary"
            no-caps
            icon="add"
            label="Generar"
            :loading="loadingMonth || gen.loading"
            :disable="loadingMonth || gen.loading"
            @click="openDialogGenerar"
          />
        </q-card-section>

        <q-separator />

        <q-card-section>
          <full-calendar ref="cal" :options="calendarOptions" />
        </q-card-section>
      </q-card>
    </div>

    <!-- PANEL DERECHO (Hora) -->
    <div class="col-12 col-md-4">
      <q-card flat bordered>
        <q-card-section class="row items-center">
          <div>
            <div class="text-h5 text-weight-bold">Hora</div>
            <div class="text-caption text-grey-7">
              {{ selectedDate ? formatFecha(selectedDate) : 'Selecciona un día' }}
            </div>
<!--            <pre>{{dayItems}}</pre>-->
          </div>
          <q-space />
          <q-btn
            flat
            round
            dense
            icon="refresh"
            :loading="loadingDay"
            :disable="loadingDay"
            @click="selectedDate && fetchDay()"
          />
        </q-card-section>

        <q-separator />

        <q-card-section v-if="!selectedDate">
          <div class="text-grey-7">Haz click en una fecha del calendario.</div>
        </q-card-section>
        <q-card-section>
<!--          <pre>{{dayItems}}</pre>-->
<!--          [-->
<!--          {-->
<!--          "id": 20885,-->
<!--          "starts_at": null,-->
<!--          "ends_at": null,-->
<!--          "hora_inicio": "09:00:00",-->
<!--          "hora_fin": "09:30:00",-->
<!--          "plan": null,-->
<!--          "precio": 0,-->
<!--          "capacidad": 100,-->
<!--          "reservados": 0,-->
<!--          "activo": true,-->
<!--          "nota": null-->
<!--          },-->
<!--          {-->
<!--          "id": 20886,-->
<!--          "starts_at": null,-->
<!--          "ends_at": null,-->
<!--          "hora_inicio": "09:30:00",-->
<!--          "hora_fin": "10:00:00",-->
<!--          "plan": null,-->
<!--          "precio": 0,-->
<!--          "capacidad": 100,-->
<!--          "reservados": 0,-->
<!--          "activo": true,-->
<!--          "nota": null-->
<!--          },-->
<!--          {-->
<!--          "id": 20887,-->
<!--          "starts_at": null,-->
<!--          "ends_at": null,-->
<!--          "hora_inicio": "10:00:00",-->
<!--          "hora_fin": "10:30:00",-->
<!--          "plan": null,-->
<!--          "precio": 0,-->
<!--          "capacidad": 100,-->
<!--          "reservados": 0,-->
<!--          "activo": true,-->
<!--          "nota": null-->
<!--          },-->
<!--          {-->
<!--          "id": 20888,-->
<!--          "starts_at": null,-->
<!--          "ends_at": null,-->
<!--          "hora_inicio": "10:30:00",-->
<!--          "hora_fin": "11:00:00",-->
<!--          "plan": null,-->
<!--          "precio": 0,-->
<!--          "capacidad": 100,-->
<!--          "reservados": 0,-->
<!--          "activo": true,-->
<!--          "nota": null-->
<!--          },-->
<!--          {-->
<!--          "id": 20889,-->
<!--          "starts_at": null,-->
<!--          "ends_at": null,-->
<!--          "hora_inicio": "11:00:00",-->
<!--          "hora_fin": "11:30:00",-->
<!--          "plan": null,-->
<!--          "precio": 0,-->
<!--          "capacidad": 100,-->
<!--          "reservados": 0,-->
<!--          "activo": true,-->
<!--          "nota": null-->
<!--          },-->
<!--          {-->
<!--          "id": 20890,-->
<!--          "starts_at": null,-->
<!--          "ends_at": null,-->
<!--          "hora_inicio": "11:30:00",-->
<!--          "hora_fin": "12:00:00",-->
<!--          "plan": null,-->
<!--          "precio": 0,-->
<!--          "capacidad": 100,-->
<!--          "reservados": 0,-->
<!--          "activo": true,-->
<!--          "nota": null-->
<!--          },-->
<!--          {-->
<!--          "id": 20891,-->
<!--          "starts_at": null,-->
<!--          "ends_at": null,-->
<!--          "hora_inicio": "12:00:00",-->
<!--          "hora_fin": "12:30:00",-->
<!--          "plan": null,-->
<!--          "precio": 0,-->
<!--          "capacidad": 100,-->
<!--          "reservados": 0,-->
<!--          "activo": true,-->
<!--          "nota": null-->
<!--          },-->
<!--          {-->
<!--          "id": 20892,-->
<!--          "starts_at": null,-->
<!--          "ends_at": null,-->
<!--          "hora_inicio": "12:30:00",-->
<!--          "hora_fin": "13:00:00",-->
<!--          "plan": null,-->
<!--          "precio": 0,-->
<!--          "capacidad": 100,-->
<!--          "reservados": 0,-->
<!--          "activo": true,-->
<!--          "nota": null-->
<!--          }-->
<!--          ]-->
          <template v-for="it in dayItems" :key="it.id">
<!--            @click="openEdit(it)"-->
            <q-btn
              no-caps
              dense
              class="full-width q-mb-sm"
              :outline="!it.activo"
              :color="isSelectedSlot(it) ? 'amber-3' : (it.activo ? 'grey-3' : 'grey-5')"
              text-color="black"
              style="border-radius: 6px;"
              @click="updateEstado(it)"
            >
              <div class="text-caption text-weight-bold">
                {{ hm(it.hora_inicio) }} - {{ hm(it.hora_fin) }} ({{ it.reservados }})

              </div>
            </q-btn>
<!--            <pre>{{it}}</pre>-->
          </template>

          <div class="q-mt-md">
            <q-btn
              v-if="dayItems && dayItems.length"
              class="full-width"
              no-caps
              color="positive"
              :outline="!hasInactive"
              :loading="loadingDay"
              :disable="loadingDay || !hasInactive"
              label="Habilitar todos"
              @click="toggleAll(true)"
            />
            <q-btn
              v-if="dayItems && dayItems.length"
              class="full-width q-mt-sm"
              no-caps
              color="negative"
              :outline="!hasActive"
              :loading="loadingDay"
              :disable="loadingDay || !hasActive"
              label="Deshabilitar todos"
              @click="toggleAll(false)"
            />
          </div>
        </q-card-section>

<!--        <q-card-section v-else class="q-pt-sm">-->
<!--          <q-tabs-->
<!--            v-model="tab"-->
<!--            dense-->
<!--            align="left"-->
<!--            active-color="brown-8"-->
<!--            indicator-color="brown-8"-->
<!--            class="text-weight-bold"-->
<!--          >-->
<!--            <q-tab name="manana" label="Mañana" />-->
<!--            <q-tab name="tarde" label="Tarde" />-->
<!--          </q-tabs>-->

<!--          <q-separator class="q-mt-sm q-mb-md" />-->

<!--          <div v-if="loadingDay" class="text-grey-7">Cargando...</div>-->

<!--          <template v-else>-->
<!--            <div v-if="slotsForTab.length === 0" class="text-grey-7">-->
<!--              Sin horarios en {{ tabLabel }}.-->
<!--            </div>-->

<!--            &lt;!&ndash; GRID DE HORAS &ndash;&gt;-->
<!--            <div class="row q-col-gutter-sm">-->
<!--              <div-->
<!--                v-for="it in slotsForTab"-->
<!--                :key="it.id"-->
<!--                class="col-3"-->
<!--              >-->
<!--                <q-btn-->
<!--                  no-caps-->
<!--                  dense-->
<!--                  class="full-width"-->
<!--                  :outline="!it.activo"-->
<!--                  :color="isSelectedSlot(it) ? 'amber-3' : (it.activo ? 'grey-3' : 'grey-5')"-->
<!--                  text-color="black"-->
<!--                  @click="openEdit(it)"-->
<!--                  style="border-radius: 6px;"-->
<!--                >-->
<!--                  <div class="text-caption text-weight-bold">-->
<!--                    {{ hhmm(it.starts_at) }}-->
<!--                  </div>-->
<!--                </q-btn>-->

<!--                &lt;!&ndash; borrar pequeño &ndash;&gt;-->
<!--                <div class="text-center q-mt-xs">-->
<!--                  <q-btn-->
<!--                    flat round dense size="sm"-->
<!--                    icon="delete"-->
<!--                    color="negative"-->
<!--                    @click.stop="removeSlot(it)"-->
<!--                  />-->
<!--                </div>-->
<!--              </div>-->
<!--            </div>-->
<!--          </template>-->
<!--        </q-card-section>-->
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

            <div class="col-12 col-md-4">
              <q-input v-model="gen.inicio_hora" dense outlined type="time" label="Inicio" />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="gen.fin_hora" dense outlined type="time" label="Fin" />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model.number="gen.entre" dense outlined type="number" label="Cada (min)" />
            </div>

            <div class="col-12">
              <q-banner rounded class="bg-grey-2">
                Se generarán horarios para todo el rango.
                <b>Importante:</b> se reemplazarán los horarios existentes dentro del rango.
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
              {{ formatFecha(selectedDate) }} · {{ hhmm(edit.form.starts_at) }} - {{ hhmm(edit.form.ends_at) }}
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
import moment from 'moment'

export default {
  name: 'HorariosCalendar',
  components: { FullCalendar },
  props: {
    eventoId: { type: [Number, String], required: true }
  },
  data () {
    return {
      loadingDay: false,
      loadingMonth: false,

      selectedDate: '',
      selectedSlotId: null,

      monthEvents: [],
      dayItems: [],

      tab: 'manana',

      gen: {
        open: false,
        loading: false,
        date_from: '',
        date_to: '',
        inicio_hora: '09:00',
        fin_hora: '13:00',
        entre: 30
      },

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
          left: 'prev,next',
          center: 'title',
          right: ''
        },
        events: this.monthEvents,

        dateClick: (info) => {
          this.selectedDate = info.dateStr
          this.selectedSlotId = null
          this.fetchDay()
        },

        datesSet: (info) => {
          const start = info.startStr.slice(0, 10)
          const end = info.endStr.slice(0, 10)
          this.reloadMonthRange(start, end)
        }
      }
    },

    slotsForTab () {
      const list = (this.dayItems || []).slice()

      // ordenar por hora
      list.sort((a, b) => (a.starts_at || '').localeCompare(b.starts_at || ''))

      // mañana: < 12:00, tarde: >= 12:00
      return list.filter(it => {
        const h = this.hhmm(it.starts_at)
        if (!h) return false
        const hh = parseInt(h.slice(0, 2), 10)
        return this.tab === 'manana' ? (hh < 12) : (hh >= 12)
      })
    },

    tabLabel () {
      return this.tab === 'manana' ? 'Mañana' : 'Tarde'
    },

    hasActive () {
      return (this.dayItems || []).some(it => it.activo)
    },

    hasInactive () {
      return (this.dayItems || []).some(it => !it.activo)
    }
  },

  methods: {
    hm (hms) {
      // "09:00:00" -> "09:00"
      if (!hms) return ''
      return String(hms).slice(0, 5)
    },
    updateEstado(it) {
      const nuevoActivo = !it.activo
      this.loadingDay = true
      this.$axios.put(`evento-horarios/${it.id}`, {
        activo: nuevoActivo
      })
        .then(() => {
          this.$alert.success(`Horario ${nuevoActivo ? 'activado' : 'desactivado'}`)
          this.fetchDay()

          // refresca calendario
          const api = this.$refs.cal?.getApi?.()
          if (api) {
            const start = api.view.activeStart.toISOString().slice(0, 10)
            const end = api.view.activeEnd.toISOString().slice(0, 10)
            this.reloadMonthRange(start, end)
          }
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo actualizar'))
        .finally(() => { this.loadingDay = false })
    },
    async toggleAll (activate) {
      if (!this.selectedDate) return
      const items = (this.dayItems || []).filter(it => it.activo !== activate)
      if (!items.length) return

      this.loadingDay = true
      try {
        await Promise.all(items.map(it =>
          this.$axios.put(`evento-horarios/${it.id}`, { activo: activate })
        ))

        this.$alert.success(activate ? 'Todos habilitados' : 'Todos deshabilitados')
        await this.fetchDay()

        const api = this.$refs.cal?.getApi?.()
        if (api) {
          const start = api.view.activeStart.toISOString().slice(0, 10)
          const end = api.view.activeEnd.toISOString().slice(0, 10)
          this.reloadMonthRange(start, end)
        }
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo actualizar')
      } finally {
        this.loadingDay = false
      }
    },
    formatFecha (ymd) {
      if (!ymd) return ''
      // return moment(ymd).format('dddd D [de] MMMM YYYY') languar espa;ol
      const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
      const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
      const d = moment(ymd)
      return `${dias[d.day()]} ${d.date()} de ${meses[d.month()]} ${d.year()}`
      // return moment(ymd).locale('es').format('dddd D [de] MMMM YYYY')
    },

    hhmm (dt) {
      if (!dt) return ''
      const parts = String(dt).split(' ')
      if (parts.length < 2) return ''
      return parts[1].slice(0, 5)
    },

    isSelectedSlot (it) {
      return this.selectedSlotId && it && it.id === this.selectedSlotId
    },

    openDialogGenerar () {
      const api = this.$refs.cal?.getApi?.()
      if (api) {
        const start = moment(api.view.currentStart).startOf('month').format('YYYY-MM-DD')
        const end = moment(api.view.currentStart).endOf('month').format('YYYY-MM-DD')
        this.gen.date_from = start
        this.gen.date_to = end
      } else {
        this.gen.date_from = moment().startOf('month').format('YYYY-MM-DD')
        this.gen.date_to = moment().endOf('month').format('YYYY-MM-DD')
      }

      this.gen.inicio_hora = '09:00'
      this.gen.fin_hora = '13:00'
      this.gen.entre = 30
      this.gen.open = true
    },

    reloadMonthRange (start, end) {
      this.loadingMonth = true
      this.$axios.get(`eventos/${this.eventoId}/horarios/month`, {
        params: { start, end } // ya sin plan
      })
        .then(r => { this.monthEvents = r.data?.items || [] })
        .catch(e => this.$alert.error(e.response?.data?.message || 'Error cargando calendario'))
        .finally(() => { this.loadingMonth = false })
    },

    fetchDay () {
      if (!this.selectedDate) return
      this.loadingDay = true
      this.$axios.get(`eventos/${this.eventoId}/horarios/day`, {
        params: { date: this.selectedDate } // ya sin plan
      })
        .then(r => {
          this.dayItems = r.data?.items || []
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'Error cargando horarios del día'))
        .finally(() => { this.loadingDay = false })
    },

    doGenerate () {
      if (!this.gen.date_from || !this.gen.date_to) {
        this.$alert.error('Selecciona rango')
        return
      }
      if (!this.gen.inicio_hora || !this.gen.fin_hora) {
        this.$alert.error('Selecciona hora inicio y fin')
        return
      }

      this.gen.loading = true
      this.$axios.post(`eventos/${this.eventoId}/horarios/generate`, {
        date_from: this.gen.date_from,
        date_to: this.gen.date_to,
        inicio_hora: this.gen.inicio_hora,
        fin_hora: this.gen.fin_hora,
        entre: this.gen.entre
      })
        .then(r => {
          const created = r.data?.created ?? 0
          this.$alert.success(`Generado. Slots creados: ${created}`)
          this.gen.open = false

          // recargar mes visible
          const api = this.$refs.cal?.getApi?.()
          if (api) {
            const start = api.view.activeStart.toISOString().slice(0, 10)
            const end = api.view.activeEnd.toISOString().slice(0, 10)
            this.reloadMonthRange(start, end)
          }

          if (this.selectedDate) this.fetchDay()
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo generar'))
        .finally(() => { this.gen.loading = false })
    },

    openEdit (it) {
      this.selectedSlotId = it.id
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

          // refresca calendario
          const api = this.$refs.cal?.getApi?.()
          if (api) {
            const start = api.view.activeStart.toISOString().slice(0, 10)
            const end = api.view.activeEnd.toISOString().slice(0, 10)
            this.reloadMonthRange(start, end)
          }
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

              const api = this.$refs.cal?.getApi?.()
              if (api) {
                const start = api.view.activeStart.toISOString().slice(0, 10)
                const end = api.view.activeEnd.toISOString().slice(0, 10)
                this.reloadMonthRange(start, end)
              }
            })
            .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo eliminar'))
        })
    }
  }
}
</script>

<style scoped>
/* Si quieres que el calendario se parezca más al screenshot (más limpio) */
:deep(.fc .fc-toolbar-title){
  font-size: 26px;
  font-weight: 800;
}
:deep(.fc .fc-col-header-cell-cushion){
  font-size: 12px;
  color: #666;
}
:deep(.fc .fc-daygrid-day-number){
  font-weight: 700;
  color: #666;
}
</style>
