<template>
  <!-- HEADER -->
  <q-card flat bordered class="q-mb-md">
    <q-card-section class="row items-center">
      <div>
        <div class="text-h6 text-weight-bold">Eventos</div>
        <div class="text-caption text-grey-7">Gestión de eventos y horarios</div>
      </div>
      <q-space />
      <q-input v-model="filter" dense outlined debounce="300" label="Buscar..." style="width: 320px">
        <template v-slot:append><q-icon name="search" /></template>
      </q-input>
    </q-card-section>

    <q-separator />

    <q-card-section class="row items-center q-col-gutter-sm">
      <div class="col-12 col-md-3">
        <q-select
          v-model="filters.activo"
          dense outlined
          label="Estado"
          :options="activoOptions"
          emit-value
          map-options
          clearable
        />
      </div>

      <div class="col-12 col-md-5">
        <q-input v-model="filters.search" dense outlined label="Buscar (nombre/slug/ciudad)" debounce="300" />
      </div>

      <div class="col-12 col-md-4 row justify-end q-gutter-sm">
        <q-btn color="positive" no-caps icon="add_circle_outline" label="Nuevo evento" :loading="loading" @click="eventoNew" />
        <q-btn color="primary" no-caps icon="refresh" label="Actualizar" :loading="loading" @click="goEventosPage(1)" />
      </div>
    </q-card-section>
  </q-card>

  <!-- TABLA EVENTOS (PAGINADO) -->
  <q-card flat bordered>
    <q-card-section class="row items-center">
      <div class="text-subtitle1 text-weight-bold">Listado</div>
      <q-space />
      <div class="text-caption text-grey-7">
        Total: {{ eventosTotal }} | Página {{ eventosPage }} / {{ eventosLastPage }}
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section class="q-pa-none">
      <q-table
        :rows="eventos"
        :columns="columns"
        row-key="id"
        dense
        flat
        bordered
        wrap-cells
        :filter="filter"
        :rows-per-page-options="[0]"
        loading-label="Cargando..."
        no-data-label="Sin eventos"
        :loading="loading"
      >
        <template v-slot:body-cell-actions="props">
          <q-td :props="props" class="text-center">
            <q-btn-dropdown label="Opciones" no-caps dense color="primary" size="10px">
              <q-list>
                <q-item clickable v-close-popup @click="eventoManage(props.row)">
                  <q-item-section avatar><q-icon name="schedule" /></q-item-section>
                  <q-item-section><q-item-label>Administrar horarios</q-item-label></q-item-section>
                </q-item>

                <q-item clickable v-close-popup @click="eventoEdit(props.row)">
                  <q-item-section avatar><q-icon name="edit" /></q-item-section>
                  <q-item-section><q-item-label>Editar</q-item-label></q-item-section>
                </q-item>

                <q-item clickable v-close-popup @click="toggleActivo(props.row)">
                  <q-item-section avatar>
                    <q-icon :name="props.row.activo ? 'toggle_off' : 'toggle_on'" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ props.row.activo ? 'Desactivar' : 'Activar' }}</q-item-label>
                  </q-item-section>
                </q-item>

                <q-separator />

                <q-item clickable v-close-popup @click="eventoDelete(props.row.id)">
                  <q-item-section avatar><q-icon name="delete" /></q-item-section>
                  <q-item-section><q-item-label>Eliminar</q-item-label></q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </q-td>
        </template>

        <template v-slot:body-cell-activo="props">
          <q-td :props="props">
            <q-badge :color="props.row.activo ? 'positive' : 'grey-6'" text-color="white" class="text-weight-bold">
              {{ props.row.activo ? 'Activo' : 'Inactivo' }}
            </q-badge>
          </q-td>
        </template>

        <template v-slot:body-cell_regla="props">
          <q-td :props="props">
            <q-chip dense :color="colorRegla(props.row.regla_nacionalidad)" text-color="white" size="12px">
              {{ labelRegla(props.row.regla_nacionalidad) }}
            </q-chip>
          </q-td>
        </template>
      </q-table>
    </q-card-section>

    <q-separator />

    <q-card-section class="row items-center q-col-gutter-md">
      <div class="col-12 col-sm-auto">
        <q-select
          v-model="eventosPerPage"
          dense outlined
          style="width:140px"
          label="Por página"
          :options="[25, 50, 100]"
          @update:model-value="goEventosPage(1)"
        />
      </div>

      <div class="col-12 col-sm">
        <q-pagination
          v-model="eventosPage"
          :max="eventosLastPage"
          max-pages="8"
          boundary-numbers
          direction-links
          @update:model-value="goEventosPage"
        />
      </div>
    </q-card-section>
  </q-card>

  <!-- DIALOG: EVENTO (CREAR/EDITAR + HORARIOS) -->
  <q-dialog v-model="eventoDialog" persistent maximized>
    <q-card class="column">

      <q-card-section class="row items-center">
        <div>
          <div class="text-h6 text-weight-bold">
            {{ evento.id ? 'Evento: ' + evento.nombre : 'Nuevo evento' }}
          </div>
          <div class="text-caption text-grey-7">Configura información general y horarios.</div>
        </div>
        <q-space />
        <q-btn icon="close" flat round dense @click="closeEventoDialog" />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-none">
        <q-tabs v-model="tab" dense active-color="primary" indicator-color="primary" align="left" class="bg-grey-1">
          <q-tab name="general" icon="info" label="General" />
          <q-tab name="horarios" icon="schedule" label="Horarios" :disable="!evento.id" />
        </q-tabs>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-md col">
        <q-tab-panels v-model="tab" animated @transition="onTabChanged">

          <!-- GENERAL -->
          <q-tab-panel name="general">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-input v-model="evento.nombre" dense outlined label="Nombre" :rules="[req]" />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="evento.slug"
                  dense outlined
                  label="Slug (para /evento/:site)"
                  hint="Ej: giza-plateau"
                  :rules="[req]"
                >
                  <template v-slot:append>
                    <q-btn flat dense icon="auto_fix_high" @click="autoSlug" :disable="!evento.nombre" />
                  </template>
                </q-input>
              </div>

              <div class="col-12">
                <q-input v-model="evento.descripcion" type="textarea" autogrow dense outlined label="Descripción" />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model="evento.pais" dense outlined label="País" />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model="evento.ciudad" dense outlined label="Ciudad" />
              </div>

              <div class="col-12 col-md-6">
                <q-input v-model="evento.ubicacion" dense outlined label="Ubicación / Dirección" />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model.number="evento.lat" dense outlined label="Latitud" type="number" />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model.number="evento.lng" dense outlined label="Longitud" type="number" />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model="evento.categoria" dense outlined label="Categoría" />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model.number="evento.orden" dense outlined label="Orden" type="number" />
              </div>

              <div class="col-12 col-md-3">
                <q-select
                  v-model="evento.regla_nacionalidad"
                  dense outlined
                  label="Regla de nacionalidad"
                  :options="reglaOptions"
                  emit-value
                  map-options
                />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model="evento.moneda" dense outlined label="Moneda" />
              </div>

              <div class="col-12 col-md-6">
                <q-input v-model="evento.imagen" dense outlined label="Imagen (URL / path)" />
              </div>

              <div class="col-12 col-md-3">
                <q-toggle v-model="evento.activo" label="Evento activo" />
              </div>

              <!-- ✅ NUEVO: CONFIG HORARIOS -->
              <div class="col-12">
                <q-separator spaced />
                <div class="text-subtitle2 text-weight-bold q-mb-sm">Configuración de horarios</div>
              </div>

              <div class="col-12 col-md-3">
                <q-input
                  v-model.number="evento.slot_interval_min"
                  dense outlined
                  label="Intervalo (min)"
                  type="number"
                  :min="5"
                  :max="240"
                />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model="evento.semana_hora_inicio" dense outlined label="Hora inicio (semana)" type="time" />
              </div>

              <div class="col-12 col-md-3">
                <q-input v-model="evento.semana_hora_fin" dense outlined label="Hora fin (semana)" type="time" />
              </div>

              <div class="col-12 col-md-3">
                <q-input
                  v-model.number="evento.generar_semanas"
                  dense outlined
                  label="Generar semanas"
                  type="number"
                  :min="1"
                  :max="520"
                  hint="Cuántas semanas hacia adelante se generan slots reales."
                />
              </div>
            </div>

            <div class="row justify-end q-gutter-sm q-mt-md">
              <q-btn color="negative" no-caps flat label="Cancelar" @click="closeEventoDialog" :disable="loading" />
              <q-btn color="primary" no-caps :label="evento.id ? 'Guardar cambios' : 'Crear evento'" :loading="loading" @click="saveEvento" />
            </div>
          </q-tab-panel>

          <!-- HORARIOS (PLANTILLA SEMANAL) -->
          <q-tab-panel name="horarios">
            <q-card flat bordered class="q-mb-md">
              <q-card-section class="row items-center q-col-gutter-sm">
                <div class="col-12 col-md-3">
                  <q-select
                    v-model="semana.plan"
                    dense outlined
                    label="Plan"
                    :options="planes"
                    @update:model-value="semanaFetch"
                  />
                </div>

                <div class="col-12 col-md-2">
                  <q-input v-model.number="semana.slot_interval_min" dense outlined type="number" label="Intervalo (min)" />
                </div>

                <div class="col-12 col-md-2">
                  <q-input v-model="semana.hora_inicio" dense outlined type="time" label="Inicio" />
                </div>

                <div class="col-12 col-md-2">
                  <q-input v-model="semana.hora_fin" dense outlined type="time" label="Fin" />
                </div>

                <div class="col-12 col-md-3">
                  <q-input v-model.number="semana.generar_semanas" dense outlined type="number" label="Generar semanas" />
                </div>

                <div class="col-12">
                  <div class="row items-center q-gutter-sm">
                    <q-btn
                      color="primary"
                      no-caps
                      icon="save"
                      label="Guardar plantilla"
                      :loading="semana.loading"
                      @click="semanaSave"
                    />
                    <q-btn
                      color="secondary"
                      no-caps
                      icon="sync"
                      label="Regenerar slots"
                      :loading="semana.loading"
                      @click="semanaRegenerarSlots"
                    />
                    <q-space />
                    <div class="text-caption text-grey-7">
                      Click: activar/desactivar · Doble click: editar precio/capacidad
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <q-card flat bordered>
              <q-card-section class="row items-center">
                <div>
                  <div class="text-subtitle1 text-weight-bold">Plantilla semanal</div>
                  <div class="text-caption text-grey-7">
                    Lunes a Domingo — slots de {{ semana.slot_interval_min }} min
                  </div>
                </div>
                <q-space />
                <q-chip dense icon="check_circle" color="positive" text-color="white">Activo</q-chip>
                <q-chip dense icon="block" color="grey-6" text-color="white">Inactivo</q-chip>
              </q-card-section>

              <q-separator />

              <q-card-section class="q-pa-none">
                <div class="q-pa-sm">
                  <q-markup-table dense flat bordered>
                    <thead>
                    <tr>
                      <th class="text-left" style="width:110px">Hora</th>
                      <th v-for="d in semana.dias" :key="d.dow" class="text-center">
                        <div class="text-weight-bold">{{ d.label }}</div>
                        <div class="text-caption text-grey-7">{{ d.short }}</div>
                      </th>
                    </tr>
                    </thead>

                    <tbody>
                    <!-- mañana/tarde: solo etiqueta visual -->
                    <tr v-if="timesManiana.length">
                      <td class="text-left">
                        <q-chip dense color="grey-3" text-color="dark" icon="wb_sunny">Mañana</q-chip>
                      </td>
                      <td :colspan="7"></td>
                    </tr>

                    <tr v-for="t in timesManiana" :key="'m-'+t">
                      <td class="text-left">
                        <div class="text-weight-bold">{{ t }}</div>
                        <div class="text-caption text-grey-7">{{ addMinutes(t, semana.slot_interval_min) }}</div>
                      </td>

                      <td v-for="d in semana.dias" :key="d.dow + '|' + t" class="text-center">
                        <q-btn
                          dense
                          no-caps
                          size="11px"
                          :color="cell(d.dow, t).activo ? 'positive' : 'grey-5'"
                          :text-color="cell(d.dow, t).activo ? 'white' : 'dark'"
                          :icon="cell(d.dow, t).activo ? 'check' : 'remove'"
                          :label="cellLabel(d.dow, t)"
                          @click="toggleCell(d.dow, t)"
                          @dblclick="openCellDialog(d.dow, t)"
                        >
                          <q-tooltip>
                            <div><b>{{ d.label }}</b> · {{ t }} - {{ addMinutes(t, semana.slot_interval_min) }}</div>
                            <div>Activo: {{ cell(d.dow, t).activo ? 'Sí' : 'No' }}</div>
                            <div>Capacidad: {{ cell(d.dow, t).capacidad }}</div>
                            <div>Precio: {{ cell(d.dow, t).precio }}</div>
                          </q-tooltip>
                        </q-btn>
                      </td>
                    </tr>

                    <tr v-if="timesTarde.length">
                      <td class="text-left">
                        <q-chip dense color="grey-3" text-color="dark" icon="schedule">Tarde</q-chip>
                      </td>
                      <td :colspan="7"></td>
                    </tr>

                    <tr v-for="t in timesTarde" :key="'t-'+t">
                      <td class="text-left">
                        <div class="text-weight-bold">{{ t }}</div>
                        <div class="text-caption text-grey-7">{{ addMinutes(t, semana.slot_interval_min) }}</div>
                      </td>

                      <td v-for="d in semana.dias" :key="d.dow + '|' + t" class="text-center">
                        <q-btn
                          dense
                          no-caps
                          size="11px"
                          :color="cell(d.dow, t).activo ? 'positive' : 'grey-5'"
                          :text-color="cell(d.dow, t).activo ? 'white' : 'dark'"
                          :icon="cell(d.dow, t).activo ? 'check' : 'remove'"
                          :label="cellLabel(d.dow, t)"
                          @click="toggleCell(d.dow, t)"
                          @dblclick="openCellDialog(d.dow, t)"
                        >
                          <q-tooltip>
                            <div><b>{{ d.label }}</b> · {{ t }} - {{ addMinutes(t, semana.slot_interval_min) }}</div>
                            <div>Activo: {{ cell(d.dow, t).activo ? 'Sí' : 'No' }}</div>
                            <div>Capacidad: {{ cell(d.dow, t).capacidad }}</div>
                            <div>Precio: {{ cell(d.dow, t).precio }}</div>
                          </q-tooltip>
                        </q-btn>
                      </td>
                    </tr>

                    </tbody>
                  </q-markup-table>
                </div>
              </q-card-section>
            </q-card>

            <!-- Dialog editar celda -->
            <q-dialog v-model="cellDialog.open" persistent>
              <q-card style="width: 520px; max-width: 95vw;">
                <q-card-section class="row items-center q-pb-none">
                  <div class="text-subtitle1 text-weight-bold">Editar horario</div>
                  <q-space />
                  <q-btn icon="close" flat round dense @click="cellDialog.open=false" />
                </q-card-section>

                <q-card-section class="q-pt-sm">
                  <q-banner rounded class="bg-grey-2">
                    <div class="text-weight-bold">
                      {{ cellDialog.dayLabel }} · {{ cellDialog.time }}
                      <span class="text-grey-7">({{ cellDialog.time }} - {{ addMinutes(cellDialog.time, semana.slot_interval_min) }})</span>
                    </div>
                    <div class="text-caption text-grey-7">
                      Plan: {{ semana.plan }}
                    </div>
                  </q-banner>

                  <div class="row q-col-gutter-md q-mt-sm">
                    <div class="col-12 col-md-4">
                      <q-toggle v-model="cellDialog.value.activo" label="Activo" />
                    </div>
                    <div class="col-12 col-md-4">
                      <q-input v-model.number="cellDialog.value.capacidad" dense outlined type="number" label="Capacidad" />
                    </div>
                    <div class="col-12 col-md-4">
                      <q-input v-model.number="cellDialog.value.precio" dense outlined type="number" label="Precio" />
                    </div>
                  </div>
                </q-card-section>

                <q-card-actions align="right">
                  <q-btn flat no-caps color="grey-7" label="Cancelar" @click="cellDialog.open=false" />
                  <q-btn color="primary" no-caps label="Aplicar" @click="applyCellDialog" />
                </q-card-actions>
              </q-card>
            </q-dialog>
          </q-tab-panel>

        </q-tab-panels>
      </q-card-section>

    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: 'EventosPage',
  data () {
    return {
      planes: ['Adulto', 'Niño'],

      loading: false,
      filter: '',
      filters: { activo: null, search: '' },

      // eventos paginado
      eventos: [],
      eventosPage: 1,
      eventosPerPage: 50,
      eventosLastPage: 1,
      eventosTotal: 0,

      columns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'id', label: 'ID', align: 'left', field: 'id', sortable: true },
        { name: 'nombre', label: 'Nombre', align: 'left', field: 'nombre', sortable: true },
        { name: 'slug', label: 'Slug', align: 'left', field: 'slug', sortable: true },
        { name: 'categoria', label: 'Categoría', align: 'left', field: 'categoria' },
        { name: 'ciudad', label: 'Ciudad', align: 'left', field: 'ciudad' },
        { name: 'moneda', label: 'Moneda', align: 'left', field: 'moneda' },
        { name: 'regla', label: 'Nacionalidad', align: 'left', field: 'regla_nacionalidad' },
        { name: 'activo', label: 'Estado', align: 'left', field: 'activo' },
        { name: 'orden', label: 'Orden', align: 'left', field: 'orden', sortable: true }
      ],

      // dialog evento
      eventoDialog: false,
      evento: {},
      tab: 'general',

      // ✅ plantilla semanal (front)
      semana: {
        loading: false,
        plan: 'Adulto',
        slot_interval_min: 30,
        hora_inicio: '09:00',
        hora_fin: '17:00',
        generar_semanas: 52,
        dias: [
          { dow: 1, label: 'Lunes', short: 'Lun' },
          { dow: 2, label: 'Martes', short: 'Mar' },
          { dow: 3, label: 'Miércoles', short: 'Mié' },
          { dow: 4, label: 'Jueves', short: 'Jue' },
          { dow: 5, label: 'Viernes', short: 'Vie' },
          { dow: 6, label: 'Sábado', short: 'Sáb' },
          { dow: 7, label: 'Domingo', short: 'Dom' }
        ],
        grid: {} // key: "dow|HH:mm" => { activo, capacidad, precio }
      },

      cellDialog: {
        open: false,
        dow: null,
        time: '',
        dayLabel: '',
        value: { activo: true, capacidad: 100, precio: 0 }
      },

      // options
      activoOptions: [
        { label: 'Activos', value: true },
        { label: 'Inactivos', value: false }
      ],
      reglaOptions: [
        { label: 'Todos', value: 'ALL' },
        { label: 'Solo Egipcios', value: 'EGYPTIAN_ONLY' },
        { label: 'Solo Extranjeros', value: 'FOREIGNERS_ONLY' }
      ]
    }
  },

  mounted () {
    this.goEventosPage(1)
  },

  watch: {
    'filters.activo' () { this.goEventosPage(1) },
    'filters.search' () { this.goEventosPage(1) }
  },

  computed: {
    timesAll () {
      const start = this.semana.hora_inicio || '09:00'
      const end = this.semana.hora_fin || '17:00'
      const step = Number(this.semana.slot_interval_min || 30)

      const times = []
      let t = start
      // evita loops raros
      for (let i = 0; i < 2000; i++) {
        const next = this.addMinutes(t, step)
        if (this.timeToMin(next) > this.timeToMin(end)) break
        times.push(t)
        t = next
        if (this.timeToMin(t) >= this.timeToMin(end)) break
      }
      return times
    },
    timesManiana () {
      return this.timesAll.filter(t => this.timeToMin(t) < 12 * 60)
    },
    timesTarde () {
      return this.timesAll.filter(t => this.timeToMin(t) >= 12 * 60)
    }
  },

  methods: {
    // ===== helpers =====
    req (v) { return !!v || 'Campo requerido' },

    labelRegla (v) {
      if (v === 'EGYPTIAN_ONLY') return 'Solo Egipcios'
      if (v === 'FOREIGNERS_ONLY') return 'Solo Extranjeros'
      return 'Todos'
    },
    colorRegla (v) {
      if (v === 'EGYPTIAN_ONLY') return 'deep-orange'
      if (v === 'FOREIGNERS_ONLY') return 'indigo'
      return 'primary'
    },

    slugify (text) {
      return (text || '')
        .toString()
        .trim()
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)+/g, '')
    },
    autoSlug () { this.evento.slug = this.slugify(this.evento.nombre) },

    timeToMin (hhmm) {
      const [h, m] = (hhmm || '00:00').split(':').map(Number)
      return (h * 60) + (m || 0)
    },
    minToTime (mins) {
      const h = Math.floor(mins / 60)
      const m = mins % 60
      return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0')
    },
    addMinutes (hhmm, add) {
      const mins = this.timeToMin(hhmm) + Number(add || 0)
      return this.minToTime(mins)
    },

    // ===== EVENTOS (PAGINADO) =====
    buildEventosParams () {
      const params = { page: this.eventosPage, perPage: this.eventosPerPage }
      if (this.filters.activo !== null && this.filters.activo !== undefined) params.activo = this.filters.activo
      if (this.filters.search) params.search = this.filters.search
      return params
    },
    goEventosPage (p) {
      this.eventosPage = p
      this.eventosGet()
    },
    eventosGet () {
      this.loading = true
      this.$axios.get('eventos', { params: this.buildEventosParams() })
        .then(r => {
          const data = r.data || {}
          this.eventos = data.data || []
          this.eventosPage = data.current_page || 1
          this.eventosLastPage = data.last_page || 1
          this.eventosTotal = data.total || 0
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'Error cargando eventos'))
        .finally(() => { this.loading = false })
    },

    // ===== dialog evento =====
    eventoNew () {
      this.evento = {
        nombre: '',
        slug: '',
        descripcion: '',
        pais: 'Egypt',
        ciudad: '',
        ubicacion: '',
        lat: null,
        lng: null,
        activo: true,
        imagen: '',
        categoria: '',
        orden: 0,
        regla_nacionalidad: 'ALL',
        moneda: 'EGP',

        // ✅ defaults horarios
        slot_interval_min: 30,
        semana_hora_inicio: '09:00',
        semana_hora_fin: '17:00',
        generar_semanas: 52
      }
      this.tab = 'general'
      this.eventoDialog = true

      // reset semana
      this.semanaReset()
    },

    eventoEdit (ev) {
      this.evento = { ...ev }
      // asegurar defaults si vienen null
      if (!this.evento.slot_interval_min) this.evento.slot_interval_min = 30
      if (!this.evento.semana_hora_inicio) this.evento.semana_hora_inicio = '09:00'
      if (!this.evento.semana_hora_fin) this.evento.semana_hora_fin = '17:00'
      if (!this.evento.generar_semanas) this.evento.generar_semanas = 52

      this.tab = 'general'
      this.eventoDialog = true

      this.semanaReset()
    },

    eventoManage (ev) {
      this.eventoEdit(ev)
      this.tab = 'horarios'
      this.semanaFetch()
    },

    closeEventoDialog () {
      this.eventoDialog = false
    },

    saveEvento () {
      if (!this.evento.nombre || !this.evento.slug) {
        this.$alert.error('Nombre y slug son requeridos')
        return
      }

      // normaliza times (por si vienen HH:mm:ss)
      const payload = { ...this.evento }
      if (payload.semana_hora_inicio && payload.semana_hora_inicio.length > 5) payload.semana_hora_inicio = payload.semana_hora_inicio.slice(0, 5)
      if (payload.semana_hora_fin && payload.semana_hora_fin.length > 5) payload.semana_hora_fin = payload.semana_hora_fin.slice(0, 5)

      this.loading = true

      const req = this.evento.id
        ? this.$axios.put(`eventos/${this.evento.id}`, payload)
        : this.$axios.post('eventos', payload)

      req.then(r => {
        this.evento = r.data
        this.fetchMenuEventos()
        this.$alert.success(this.evento.id ? 'Evento guardado' : 'Evento creado')
        this.goEventosPage(1)

        // si está en tab horarios, recarga plantilla
        if (this.tab === 'horarios') {
          this.semanaFetch()
        }
      })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo guardar'))
        .finally(() => { this.loading = false })
    },

    fetchMenuEventos () {
      this.$axios.get('/eventosMenu')
        .then(res => { this.$store.menuEventosByPais = res.data.items || [] })
        .catch(() => { this.$store.menuEventosByPais = [] })
    },

    toggleActivo (ev) {
      this.loading = true
      this.$axios.put(`eventos/${ev.id}`, { activo: !ev.activo })
        .then(() => {
          this.$alert.success('Estado actualizado')
          this.goEventosPage(1)
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo actualizar'))
        .finally(() => { this.loading = false })
    },

    eventoDelete (id) {
      this.$alert.dialog('¿Desea eliminar el evento?')
        .onOk(() => {
          this.loading = true
          this.$axios.delete(`eventos/${id}`)
            .then(() => {
              this.$alert.success('Evento eliminado')
              this.fetchMenuEventos()
              this.goEventosPage(1)
            })
            .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo eliminar'))
            .finally(() => { this.loading = false })
        })
    },

    // ===== HORARIOS: plantilla semanal =====
    onTabChanged () {
      if (this.tab === 'horarios' && this.evento?.id) {
        this.semanaFetch()
      }
    },

    semanaReset () {
      this.semana.plan = 'Adulto'
      this.semana.slot_interval_min = Number(this.evento?.slot_interval_min || 30)
      this.semana.hora_inicio = (this.evento?.semana_hora_inicio || '09:00').slice(0, 5)
      this.semana.hora_fin = (this.evento?.semana_hora_fin || '17:00').slice(0, 5)
      this.semana.generar_semanas = Number(this.evento?.generar_semanas || 52)
      this.semana.grid = {}
      this.buildEmptyGrid()
    },

    buildEmptyGrid () {
      // prepara grid con defaults: activo=false (o true si quieres)
      const times = this.timesAll
      const g = {}
      for (const d of this.semana.dias) {
        for (const t of times) {
          g[`${d.dow}|${t}`] = { activo: false, capacidad: 100, precio: 0 }
        }
      }
      this.semana.grid = g
    },

    semanaFetch () {
      if (!this.evento?.id) return
      this.semana.loading = true

      this.$axios.get(`eventos/${this.evento.id}/semana`, { params: { plan: this.semana.plan } })
        .then(r => {
          const data = r.data || {}

          // sincroniza config desde backend si viene
          const evCfg = data.evento || {}
          if (evCfg.slot_interval_min) this.semana.slot_interval_min = Number(evCfg.slot_interval_min)
          if (evCfg.semana_hora_inicio) this.semana.hora_inicio = String(evCfg.semana_hora_inicio).slice(0, 5)
          if (evCfg.semana_hora_fin) this.semana.hora_fin = String(evCfg.semana_hora_fin).slice(0, 5)
          if (evCfg.generar_semanas) this.semana.generar_semanas = Number(evCfg.generar_semanas)

          // reconstruye grilla según intervalo/hora
          this.buildEmptyGrid()

          // aplica datos
          const items = data.items || []
          for (const it of items) {
            const t = String(it.hora_inicio || '').slice(0, 5)
            const key = `${it.dow}|${t}`
            if (!this.semana.grid[key]) continue
            this.semana.grid[key] = {
              activo: !!it.activo,
              capacidad: Number(it.capacidad || 0),
              precio: Number(it.precio || 0)
            }
          }
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'Error cargando plantilla semanal'))
        .finally(() => { this.semana.loading = false })
    },

    cell (dow, time) {
      const key = `${dow}|${time}`
      if (!this.semana.grid[key]) {
        this.semana.grid[key] = { activo: false, capacidad: 100, precio: 0 }
      }
      return this.semana.grid[key]
    },

    cellLabel (dow, time) {
      const c = this.cell(dow, time)
      if (!c.activo) return ''
      // muestra precio si existe
      if (Number(c.precio) > 0) return String(c.precio)
      return 'OK'
    },

    toggleCell (dow, time) {
      const key = `${dow}|${time}`
      const cur = this.cell(dow, time)
      this.$set
        ? this.$set(this.semana.grid, key, { ...cur, activo: !cur.activo })
        : (this.semana.grid[key] = { ...cur, activo: !cur.activo })
    },

    openCellDialog (dow, time) {
      const d = this.semana.dias.find(x => x.dow === dow)
      this.cellDialog.dow = dow
      this.cellDialog.time = time
      this.cellDialog.dayLabel = d ? d.label : ''
      this.cellDialog.value = { ...this.cell(dow, time) }
      this.cellDialog.open = true
    },

    applyCellDialog () {
      const key = `${this.cellDialog.dow}|${this.cellDialog.time}`
      const v = {
        activo: !!this.cellDialog.value.activo,
        capacidad: Number(this.cellDialog.value.capacidad || 0),
        precio: Number(this.cellDialog.value.precio || 0)
      }
      this.$set
        ? this.$set(this.semana.grid, key, v)
        : (this.semana.grid[key] = v)
      this.cellDialog.open = false
    },

    semanaSave () {
      if (!this.evento?.id) return

      // validaciones suaves
      if (!this.semana.hora_inicio || !this.semana.hora_fin) {
        this.$alert.error('Configura hora inicio y fin')
        return
      }
      if (this.timeToMin(this.semana.hora_fin) <= this.timeToMin(this.semana.hora_inicio)) {
        this.$alert.error('Hora fin debe ser mayor a hora inicio')
        return
      }

      this.semana.loading = true

      const cells = []
      const times = this.timesAll
      for (const d of this.semana.dias) {
        for (const t of times) {
          const c = this.cell(d.dow, t)
          cells.push({
            dow: d.dow,
            hora_inicio: t,
            activo: !!c.activo,
            capacidad: Number(c.capacidad || 0),
            precio: Number(c.precio || 0)
          })
        }
      }

      const payload = {
        plan: this.semana.plan,
        slot_interval_min: Number(this.semana.slot_interval_min || 30),
        semana_hora_inicio: this.semana.hora_inicio,
        semana_hora_fin: this.semana.hora_fin,
        generar_semanas: Number(this.semana.generar_semanas || 52),
        cells
      }

      this.$axios.put(`eventos/${this.evento.id}/semana`, payload)
        .then(() => {
          this.$alert.success('Plantilla guardada')
          // sincroniza también al evento actual (para que se vea en General)
          this.evento.slot_interval_min = payload.slot_interval_min
          this.evento.semana_hora_inicio = payload.semana_hora_inicio
          this.evento.semana_hora_fin = payload.semana_hora_fin
          this.evento.generar_semanas = payload.generar_semanas
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo guardar la plantilla'))
        .finally(() => { this.semana.loading = false })
    },

    semanaRegenerarSlots () {
      if (!this.evento?.id) return
      this.semana.loading = true
      this.$axios.post(`eventos/${this.evento.id}/generar-slots`, { weeks: Number(this.semana.generar_semanas || 52) })
        .then(r => {
          const created = r.data?.created ?? 0
          this.$alert.success(`Slots regenerados. Creados: ${created}`)
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo regenerar slots'))
        .finally(() => { this.semana.loading = false })
    }
  }
}
</script>
