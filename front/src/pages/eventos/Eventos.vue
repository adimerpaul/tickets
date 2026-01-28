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
            </div>

            <div class="row justify-end q-gutter-sm q-mt-md">
              <q-btn color="negative" no-caps flat label="Cancelar" @click="closeEventoDialog" :disable="loading" />
              <q-btn color="primary" no-caps :label="evento.id ? 'Guardar cambios' : 'Crear evento'" :loading="loading" @click="saveEvento" />
            </div>
          </q-tab-panel>

          <!-- HORARIOS -->
          <q-tab-panel name="horarios">
            <div class="row items-center q-gutter-sm q-mb-sm">
              <div class="text-subtitle1 text-weight-bold">Horarios</div>
              <q-space />
              <q-btn color="primary" no-caps icon="add_circle_outline" label="Crear horarios (lote)" @click="openLoteDialog" />
            </div>

            <q-table
              :rows="horarios"
              :columns="columnsHorarios"
              row-key="id"
              dense flat bordered
              :rows-per-page-options="[0]"
              no-data-label="Sin horarios"
              :loading="horariosLoading"
            >
              <template v-slot:body-cell-actions="props">
                <q-td :props="props" class="text-center">
                  <q-btn-dropdown no-caps dense size="10px" color="primary" label="Opciones">
                    <q-list>
                      <q-item clickable v-close-popup @click="horarioEdit(props.row)">
                        <q-item-section avatar><q-icon name="edit" /></q-item-section>
                        <q-item-section><q-item-label>Editar</q-item-label></q-item-section>
                      </q-item>

                      <q-item clickable v-close-popup @click="horarioToggle(props.row)">
                        <q-item-section avatar><q-icon name="toggle_on" /></q-item-section>
                        <q-item-section><q-item-label>{{ props.row.activo ? 'Desactivar' : 'Activar' }}</q-item-label></q-item-section>
                      </q-item>

                      <q-separator />

                      <q-item clickable v-close-popup @click="horarioDelete(props.row.id)">
                        <q-item-section avatar><q-icon name="delete" /></q-item-section>
                        <q-item-section><q-item-label>Eliminar</q-item-label></q-item-section>
                      </q-item>
                    </q-list>
                  </q-btn-dropdown>
                </q-td>
              </template>

              <template v-slot:body-cell-activo="props">
                <q-td :props="props">
                  <q-badge :color="props.row.activo ? 'positive' : 'grey-6'" text-color="white">
                    {{ props.row.activo ? 'Activo' : 'Inactivo' }}
                  </q-badge>
                </q-td>
              </template>
            </q-table>

            <!-- PAGINACIÓN HORARIOS -->
            <div class="row items-center q-col-gutter-md q-mt-md">
              <div class="col-12 col-sm-auto">
                <q-select
                  v-model="horariosPerPage"
                  dense outlined
                  style="width:140px"
                  label="Por página"
                  :options="[25, 50, 100]"
                  @update:model-value="goHorariosPage(1)"
                />
              </div>

              <div class="col-12 col-sm">
                <q-pagination
                  v-model="horariosPage"
                  :max="horariosLastPage"
                  max-pages="8"
                  boundary-numbers
                  direction-links
                  @update:model-value="goHorariosPage"
                />
              </div>

              <div class="col-12 col-sm-auto text-caption text-grey-7">
                Total: {{ horariosTotal }} | Página {{ horariosPage }} / {{ horariosLastPage }}
              </div>
            </div>

            <!-- Dialog Editar Horario -->
            <q-dialog v-model="horarioDialog" persistent>
              <q-card style="width: 520px; max-width: 95vw;">
                <q-card-section class="row items-center q-pb-none">
                  <div class="text-subtitle1 text-weight-bold">Editar horario</div>
                  <q-space />
                  <q-btn icon="close" flat round dense @click="horarioDialog=false" />
                </q-card-section>

                <q-card-section class="q-pt-sm">
                  <div class="row q-col-gutter-md">
                    <div class="col-12 col-md-6">
                      <q-input v-model="horario.fecha" dense outlined label="Fecha" type="date" />
                    </div>

                    <div class="col-12 col-md-3">
                      <q-input v-model="horario.hora_inicio" dense outlined label="Inicio" type="time" />
                    </div>

                    <div class="col-12 col-md-3">
                      <q-input v-model="horario.hora_fin" dense outlined label="Fin" type="time" />
                    </div>

                    <div class="col-12 col-md-6">
                      <q-input v-model="horario.starts_at" dense outlined label="Starts" type="datetime-local" />
                    </div>

                    <div class="col-12 col-md-6">
                      <q-input v-model="horario.ends_at" dense outlined label="Ends" type="datetime-local" />
                    </div>

                    <div class="col-12 col-md-6">
                      <q-input v-model.number="horario.capacidad" dense outlined label="Capacidad" type="number" />
                    </div>

                    <div class="col-12 col-md-6">
                      <q-input v-model.number="horario.reservados" dense outlined label="Reservados" type="number" />
                    </div>

                    <div class="col-12 col-md-6">
<!--                      <q-input v-model="horario.plan" dense outlined label="Plan (opcional)" />-->
                      <q-select
                        v-model="horario.plan"
                        dense outlined
                        label="Plan"
                        :options="planes"
                        clearable
                      />
                    </div>
<!--                    precio-->
                    <div class="col-12 col-md-6">
                      <q-input v-model.number="horario.precio" dense outlined label="Precio" type="number" />
                    </div>

                    <div class="col-12">
                      <q-input v-model="horario.nota" dense outlined label="Nota" />
                    </div>

                    <div class="col-12">
                      <q-toggle v-model="horario.activo" label="Horario activo" />
                    </div>
                  </div>
                </q-card-section>

                <q-card-actions align="right">
                  <q-btn color="negative" no-caps flat label="Cancelar" @click="horarioDialog=false" :disable="loading" />
                  <q-btn color="primary" no-caps label="Guardar" :loading="loading" @click="horarioSave" />
                </q-card-actions>
              </q-card>
            </q-dialog>

            <!-- Dialog Crear Lote -->
            <q-dialog v-model="loteDialog" persistent>
              <q-card style="width: 640px; max-width: 95vw;">
                <q-card-section class="row items-center q-pb-none">
                  <div class="text-subtitle1 text-weight-bold">Crear horarios en lote</div>
                  <q-space />
                  <q-btn icon="close" flat round dense @click="loteDialog=false" />
                </q-card-section>

                <q-card-section class="q-pt-sm">
                  <div class="row q-col-gutter-md">
                    <div class="col-12 col-md-6">
                      <q-input v-model="lote.fecha_inicio" dense outlined label="Fecha inicio" type="date" />
                    </div>
                    <div class="col-12 col-md-6">
                      <q-input v-model="lote.fecha_fin" dense outlined label="Fecha fin" type="date" />
                    </div>

                    <div class="col-12 col-md-4">
                      <q-input v-model="lote.hora_inicio" dense outlined label="Hora inicio" type="time" />
                    </div>
                    <div class="col-12 col-md-4">
                      <q-input v-model="lote.hora_fin" dense outlined label="Hora fin" type="time" />
                    </div>
                    <div class="col-12 col-md-4">
                      <q-input v-model.number="lote.intervalo_min" dense outlined label="Intervalo (min)" type="number" />
                    </div>

                    <div class="col-12 col-md-4">
                      <q-input v-model.number="lote.capacidad" dense outlined label="Capacidad" type="number" />
                    </div>

                    <div class="col-12 col-md-4">
<!--                      <q-input v-model="lote.plan" dense outlined label="Plan (opcional)" />-->
<!--                      select adulto ni;os-->
                      <q-select
                        v-model="lote.plan"
                        dense outlined
                        label="Plan"
                        :options="planes"
                        clearable
                      />
                    </div>
<!--                    lote precio-->
                    <div class="col-12 col-md-4">
                      <q-input v-model.number="lote.precio" dense outlined label="Precio" type="number" />
                    </div>

                    <div class="col-12 col-md-4">
                      <q-toggle v-model="lote.activo" label="Activo" />
                    </div>

                    <div class="col-12">
                      <q-input v-model="lote.nota" dense outlined label="Nota (opcional)" />
                    </div>
                  </div>

                  <div class="text-caption text-grey-7 q-mt-md">
                    Esto crea automáticamente todos los horarios entre las fechas con el intervalo indicado.
                  </div>
                </q-card-section>

                <q-card-actions align="right">
                  <q-btn flat no-caps color="grey-7" label="Cancelar" @click="loteDialog=false" :disable="loading" />
                  <q-btn color="primary" no-caps label="Crear" :loading="loading" @click="horariosCreateLote" />
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
      planes: [
        'Adulto',
        'Niño',
      ],
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

      // horarios paginado
      horariosLoading: false,
      horarios: [],
      horariosPage: 1,
      horariosPerPage: 50,
      horariosLastPage: 1,
      horariosTotal: 0,

      columnsHorarios: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'id', label: 'ID', align: 'left', field: 'id' },
        { name: 'starts_at', label: 'Starts', align: 'left', field: 'starts_at' },
        { name: 'ends_at', label: 'Ends', align: 'left', field: 'ends_at' },
        { name: 'capacidad', label: 'Capacidad', align: 'left', field: 'capacidad' },
        { name: 'reservados', label: 'Reservados', align: 'left', field: 'reservados' },
        // precios
        { name: 'precio', label: 'Precio', align: 'left', field: 'precio' },
        { name: 'plan', label: 'Plan', align: 'left', field: 'plan' },
        { name: 'activo', label: 'Estado', align: 'left', field: 'activo' }
      ],

      horarioDialog: false,
      horario: {},

      // lote
      loteDialog: false,
      lote: {
        fecha_inicio: '',
        fecha_fin: '',
        hora_inicio: '09:00',
        hora_fin: '17:00',
        intervalo_min: 60,
        capacidad: 100,
        activo: true,
        nota: '',
        plan: '',
        precio: 20
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

  methods: {
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

    autoSlug () {
      this.evento.slug = this.slugify(this.evento.nombre)
    },

    // ========= EVENTOS (PAGINADO) =========
    buildEventosParams () {
      const params = {
        page: this.eventosPage,
        perPage: this.eventosPerPage
      }
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
        moneda: 'EGP'
      }
      this.tab = 'general'
      this.eventoDialog = true

      // reset horarios
      this.horarios = []
      this.horariosPage = 1
      this.horariosLastPage = 1
      this.horariosTotal = 0
    },

    eventoEdit (ev) {
      this.evento = { ...ev }
      this.tab = 'general'
      this.eventoDialog = true

      // reset horarios (se cargan recién al entrar)
      this.horarios = []
      this.horariosPage = 1
      this.horariosLastPage = 1
      this.horariosTotal = 0
    },

    eventoManage (ev) {
      this.eventoEdit(ev)
      this.tab = 'horarios'
      this.horariosGet()
    },

    closeEventoDialog () {
      this.eventoDialog = false
    },

    saveEvento () {
      if (!this.evento.nombre || !this.evento.slug) {
        this.$alert.error('Nombre y slug son requeridos')
        return
      }

      this.loading = true
      const payload = { ...this.evento }

      const req = this.evento.id
        ? this.$axios.put(`eventos/${this.evento.id}`, payload)
        : this.$axios.post('eventos', payload)

      req.then(r => {
        this.evento = r.data
        this.$alert.success(this.evento.id ? 'Evento guardado' : 'Evento creado')
        this.goEventosPage(1)
      })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo guardar'))
        .finally(() => { this.loading = false })
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
              this.goEventosPage(1)
            })
            .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo eliminar'))
            .finally(() => { this.loading = false })
        })
    },

    // ========= HORARIOS (PAGINADO Y LAZY LOAD) =========
    onTabChanged () {
      if (this.tab === 'horarios' && this.evento?.id) {
        this.horariosGet()
      }
    },

    buildHorariosParams () {
      return {
        page: this.horariosPage,
        perPage: this.horariosPerPage
      }
    },

    goHorariosPage (p) {
      this.horariosPage = p
      this.horariosGet()
    },

    horariosGet () {
      if (!this.evento?.id) return
      this.horariosLoading = true

      this.$axios.get(`eventos/${this.evento.id}/horarios`, { params: this.buildHorariosParams() })
        .then(r => {
          const data = r.data || {}
          this.horarios = data.data || []
          this.horariosPage = data.current_page || 1
          this.horariosLastPage = data.last_page || 1
          this.horariosTotal = data.total || 0
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'Error cargando horarios'))
        .finally(() => { this.horariosLoading = false })
    },

    openLoteDialog () {
      // defaults (si ya eligieron fechas, puedes ponerlas)
      if (!this.lote.fecha_inicio) this.lote.fecha_inicio = new Date().toISOString().slice(0,10)
      if (!this.lote.fecha_fin) this.lote.fecha_fin = this.lote.fecha_inicio
      this.loteDialog = true
    },

    horariosCreateLote () {
      if (!this.evento?.id) return
      if (!this.lote.fecha_inicio || !this.lote.fecha_fin) {
        this.$alert.error('Selecciona fecha inicio y fecha fin')
        return
      }
      if (!this.lote.hora_inicio || !this.lote.hora_fin) {
        this.$alert.error('Selecciona hora inicio y hora fin')
        return
      }

      this.loading = true
      this.$axios.post(`eventos/${this.evento.id}/horarios/lote`, { ...this.lote })
        .then(r => {
          this.$alert.success(`Horarios creados: ${r.data?.created || 0}`)
          this.loteDialog = false
          this.goHorariosPage(1)
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo crear horarios'))
        .finally(() => { this.loading = false })
    },

    horarioEdit (h) {
      this.horario = { ...h }
      this.horarioDialog = true
    },

    horarioToggle (h) {
      this.loading = true
      this.$axios.put(`evento-horarios/${h.id}`, { activo: !h.activo })
        .then(r => {
          const idx = this.horarios.findIndex(x => x.id === h.id)
          if (idx !== -1) this.horarios.splice(idx, 1, r.data)
          this.$alert.success('Horario actualizado')
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo actualizar'))
        .finally(() => { this.loading = false })
    },

    horarioSave () {
      if (!this.horario?.id) return
      this.loading = true
      this.$axios.put(`evento-horarios/${this.horario.id}`, { ...this.horario })
        .then(r => {
          const idx = this.horarios.findIndex(x => x.id === this.horario.id)
          if (idx !== -1) this.horarios.splice(idx, 1, r.data)
          this.horarioDialog = false
          this.$alert.success('Horario guardado')
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo guardar'))
        .finally(() => { this.loading = false })
    },

    horarioDelete (id) {
      this.$alert.dialog('¿Eliminar horario?')
        .onOk(() => {
          this.loading = true
          this.$axios.delete(`evento-horarios/${id}`)
            .then(() => {
              this.$alert.success('Horario eliminado')
              // si borraste el último de una página, refresca
              this.horariosGet()
            })
            .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo eliminar'))
            .finally(() => { this.loading = false })
        })
    }
  }
}
</script>
