<template>
  <q-page class="q-pa-md">

    <!-- HEADER -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6 text-weight-bold">Eventos</div>
          <div class="text-caption text-grey-7">
            Gestión de eventos, horarios y tipos de entrada (tickets)
          </div>
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
          <q-btn color="primary" no-caps icon="refresh" label="Actualizar" :loading="loading" @click="eventosGet" />
        </div>
      </q-card-section>
    </q-card>

    <!-- TABLA EVENTOS -->
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
<!--      template acciones-->
      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="text-center">
          <q-btn-dropdown label="Opciones" no-caps dense color="primary" size="10px">
            <q-list>
              <q-item clickable v-close-popup @click="eventoManage(props.row)">
                <q-item-section avatar><q-icon name="tune" /></q-item-section>
                <q-item-section><q-item-label>Administrar (horarios/tickets)</q-item-label></q-item-section>
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
          <q-badge
            :color="props.row.activo ? 'positive' : 'grey-6'"
            text-color="white"
            class="text-weight-bold"
          >
            {{ props.row.activo ? 'Activo' : 'Inactivo' }}
          </q-badge>
        </q-td>
      </template>

      <template v-slot:body-cell_regla="props">
        <q-td :props="props">
          <q-chip
            dense
            :color="colorRegla(props.row.regla_nacionalidad)"
            text-color="white"
            size="12px"
          >
            {{ labelRegla(props.row.regla_nacionalidad) }}
          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell_actions="props">
        <q-td :props="props" class="text-center">
          <q-btn-dropdown label="Opciones" no-caps dense color="primary" size="10px">
            <q-list>
              <q-item clickable v-close-popup @click="eventoManage(props.row)">
                <q-item-section avatar><q-icon name="tune" /></q-item-section>
                <q-item-section><q-item-label>Administrar (horarios/tickets)</q-item-label></q-item-section>
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
    </q-table>

    <!-- DIALOG: EVENTO (CREAR/EDITAR + ADMIN) -->
    <q-dialog v-model="eventoDialog" persistent maximized>
      <q-card class="column">

        <!-- Header dialog -->
        <q-card-section class="row items-center">
          <div>
            <div class="text-h6 text-weight-bold">
              {{ evento.id ? 'Evento: ' + evento.nombre : 'Nuevo evento' }}
            </div>
            <div class="text-caption text-grey-7">
              Configura información general, horarios y tickets del evento.
            </div>
          </div>
          <q-space />
          <q-btn icon="close" flat round dense @click="closeEventoDialog" />
        </q-card-section>

        <q-separator />

        <!-- Tabs -->
        <q-card-section class="q-pa-none">
          <q-tabs v-model="tab" dense active-color="primary" indicator-color="primary" align="left" class="bg-grey-1">
            <q-tab name="general" icon="info" label="General" />
            <q-tab name="horarios" icon="schedule" label="Horarios" :disable="!evento.id" />
            <q-tab name="tickets" icon="confirmation_number" label="Tickets" :disable="!evento.id" />
          </q-tabs>
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-md col">

          <q-tab-panels v-model="tab" animated>
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
                  <q-input v-model="evento.categoria" dense outlined label="Categoría" hint="museo / templo / site..." />
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
                <q-btn color="positive" no-caps icon="add_circle_outline" label="Nuevo horario" @click="horarioNew" />
              </div>

              <q-table
                :rows="horarios"
                :columns="columnsHorarios"
                row-key="id"
                dense flat bordered
                :rows-per-page-options="[0]"
                no-data-label="Sin horarios"
              >
                <template v-slot:body-cell_activo="props">
                  <q-td :props="props">
                    <q-badge :color="props.row.activo ? 'positive' : 'grey-6'" text-color="white">
                      {{ props.row.activo ? 'Activo' : 'Inactivo' }}
                    </q-badge>
                  </q-td>
                </template>

                <template v-slot:body-cell_actions="props">
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
              </q-table>

              <!-- Dialog Horario -->
              <q-dialog v-model="horarioDialog" persistent>
                <q-card style="width: 520px; max-width: 95vw;">
                  <q-card-section class="row items-center q-pb-none">
                    <div class="text-subtitle1 text-weight-bold">
                      {{ horario.id ? 'Editar horario' : 'Nuevo horario' }}
                    </div>
                    <q-space />
                    <q-btn icon="close" flat round dense @click="horarioDialog=false" />
                  </q-card-section>

                  <q-card-section class="q-pt-sm">
                    <div class="row q-col-gutter-md">

                      <div class="col-12 col-md-6">
                        <q-input v-model="horario.fecha" dense outlined label="Fecha (opcional)" type="date" />
                      </div>

                      <div class="col-12 col-md-3">
                        <q-input v-model="horario.hora_inicio" dense outlined label="Hora inicio" type="time" />
                      </div>

                      <div class="col-12 col-md-3">
                        <q-input v-model="horario.hora_fin" dense outlined label="Hora fin" type="time" />
                      </div>

                      <div class="col-12 col-md-6">
                        <q-input v-model="horario.starts_at" dense outlined label="Inicio (datetime opcional)" type="datetime-local" />
                      </div>

                      <div class="col-12 col-md-6">
                        <q-input v-model="horario.ends_at" dense outlined label="Fin (datetime opcional)" type="datetime-local" />
                      </div>

                      <div class="col-12 col-md-6">
                        <q-input v-model.number="horario.capacidad" dense outlined label="Capacidad" type="number" />
                      </div>

                      <div class="col-12 col-md-6">
                        <q-input v-model.number="horario.reservados" dense outlined label="Reservados" type="number" />
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
                    <q-btn color="primary" no-caps :label="horario.id ? 'Guardar' : 'Crear'" :loading="loading" @click="horarioSave" />
                  </q-card-actions>
                </q-card>
              </q-dialog>
            </q-tab-panel>

            <!-- TICKETS -->
            <q-tab-panel name="tickets">
              <div class="row items-center q-gutter-sm q-mb-sm">
                <div class="text-subtitle1 text-weight-bold">Tickets</div>
                <q-space />
<!--                <q-btn color="positive" no-caps icon="add_circle_outline" label="Nuevo ticket" @click="ticketNew" />-->
              </div>

              <q-table
                :rows="tickets"
                :columns="columnsTickets"
                row-key="id"
                dense flat bordered
                :rows-per-page-options="[0]"
                no-data-label="Sin tickets"
              >
                <template v-slot:body-cell_activo="props">
                  <q-td :props="props">
                    <q-badge :color="props.row.activo ? 'positive' : 'grey-6'" text-color="white">
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

                <template v-slot:body-cell_actions="props">
                  <q-td :props="props" class="text-center">
                    <q-btn-dropdown no-caps dense size="10px" color="primary" label="Opciones">
                      <q-list>
                        <q-item clickable v-close-popup @click="ticketEdit(props.row)">
                          <q-item-section avatar><q-icon name="edit" /></q-item-section>
                          <q-item-section><q-item-label>Editar</q-item-label></q-item-section>
                        </q-item>
                        <q-item clickable v-close-popup @click="ticketToggle(props.row)">
                          <q-item-section avatar><q-icon name="toggle_on" /></q-item-section>
                          <q-item-section><q-item-label>{{ props.row.activo ? 'Desactivar' : 'Activar' }}</q-item-label></q-item-section>
                        </q-item>
                        <q-separator />
                        <q-item clickable v-close-popup @click="ticketDelete(props.row.id)">
                          <q-item-section avatar><q-icon name="delete" /></q-item-section>
                          <q-item-section><q-item-label>Eliminar</q-item-label></q-item-section>
                        </q-item>
                      </q-list>
                    </q-btn-dropdown>
                  </q-td>
                </template>
              </q-table>

              <!-- Dialog Ticket -->
              <q-dialog v-model="ticketDialog" persistent>
                <q-card style="width: 560px; max-width: 95vw;">
                  <q-card-section class="row items-center q-pb-none">
                    <div class="text-subtitle1 text-weight-bold">
                      {{ ticket.id ? 'Editar ticket' : 'Nuevo ticket' }}
                    </div>
                    <q-space />
                    <q-btn icon="close" flat round dense @click="ticketDialog=false" />
                  </q-card-section>

                  <q-card-section class="q-pt-sm">
                    <div class="row q-col-gutter-md">
                      <div class="col-12 col-md-6">
                        <q-input v-model="ticket.nombre" dense outlined label="Nombre" :rules="[req]" />
                      </div>

                      <div class="col-12 col-md-6">
                        <q-input v-model="ticket.codigo" dense outlined label="Código" hint="GENERAL / VIP / STUDENT" />
                      </div>

                      <div class="col-12">
                        <q-input v-model="ticket.descripcion" dense outlined type="textarea" autogrow label="Descripción" />
                      </div>

                      <div class="col-12 col-md-4">
                        <q-input v-model.number="ticket.precio" dense outlined label="Precio" type="number" :rules="[req]" />
                      </div>

                      <div class="col-12 col-md-4">
                        <q-input v-model="ticket.moneda" dense outlined label="Moneda" />
                      </div>

                      <div class="col-12 col-md-4">
                        <q-input v-model.number="ticket.stock" dense outlined label="Stock" type="number" />
                      </div>

                      <div class="col-12 col-md-4">
                        <q-input v-model.number="ticket.vendidos" dense outlined label="Vendidos" type="number" />
                      </div>

                      <div class="col-12 col-md-5">
                        <q-select
                          v-model="ticket.regla_nacionalidad"
                          dense outlined
                          label="Regla nacionalidad"
                          :options="reglaOptions"
                          emit-value
                          map-options
                        />
                      </div>

                      <div class="col-12 col-md-3">
                        <q-input v-model.number="ticket.orden" dense outlined label="Orden" type="number" />
                      </div>

                      <div class="col-12">
                        <q-toggle v-model="ticket.activo" label="Ticket activo" />
                      </div>
                    </div>
                  </q-card-section>

                  <q-card-actions align="right">
                    <q-btn color="negative" no-caps flat label="Cancelar" @click="ticketDialog=false" :disable="loading" />
                    <q-btn color="primary" no-caps :label="ticket.id ? 'Guardar' : 'Crear'" :loading="loading" @click="ticketSave" />
                  </q-card-actions>
                </q-card>
              </q-dialog>

            </q-tab-panel>
          </q-tab-panels>
        </q-card-section>

      </q-card>
    </q-dialog>

  </q-page>
</template>

<script>
export default {
  name: 'EventosPage',

  data () {
    return {
      loading: false,
      filter: '',
      filters: {
        activo: null,
        search: ''
      },

      eventos: [],

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

      // Horarios
      horarios: [],
      columnsHorarios: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'id', label: 'ID', align: 'left', field: 'id' },
        { name: 'fecha', label: 'Fecha', align: 'left', field: 'fecha' },
        { name: 'hora_inicio', label: 'Inicio', align: 'left', field: 'hora_inicio' },
        { name: 'hora_fin', label: 'Fin', align: 'left', field: 'hora_fin' },
        { name: 'starts_at', label: 'Starts', align: 'left', field: 'starts_at' },
        { name: 'ends_at', label: 'Ends', align: 'left', field: 'ends_at' },
        { name: 'capacidad', label: 'Capacidad', align: 'left', field: 'capacidad' },
        { name: 'reservados', label: 'Reservados', align: 'left', field: 'reservados' },
        { name: 'activo', label: 'Estado', align: 'left', field: 'activo' }
      ],
      horarioDialog: false,
      horario: {},

      // Tickets
      tickets: [],
      columnsTickets: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'id', label: 'ID', align: 'left', field: 'id' },
        { name: 'nombre', label: 'Nombre', align: 'left', field: 'nombre' },
        { name: 'codigo', label: 'Código', align: 'left', field: 'codigo' },
        { name: 'precio', label: 'Precio', align: 'left', field: 'precio' },
        { name: 'moneda', label: 'Moneda', align: 'left', field: 'moneda' },
        { name: 'stock', label: 'Stock', align: 'left', field: 'stock' },
        { name: 'vendidos', label: 'Vendidos', align: 'left', field: 'vendidos' },
        { name: 'regla', label: 'Nacionalidad', align: 'left', field: 'regla_nacionalidad' },
        { name: 'activo', label: 'Estado', align: 'left', field: 'activo' },
        { name: 'orden', label: 'Orden', align: 'left', field: 'orden' }
      ],
      ticketDialog: false,
      ticket: {},

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
    this.eventosGet()
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

    // ===== Eventos =====
    eventosGet () {
      this.loading = true
      const params = {}

      if (this.filters.activo !== null && this.filters.activo !== undefined) params.activo = this.filters.activo
      if (this.filters.search) params.search = this.filters.search

      this.$axios.get('eventos', { params })
        .then(r => { this.eventos = r.data })
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
      this.horarios = []
      this.tickets = []
      this.tab = 'general'
      this.eventoDialog = true
    },

    eventoEdit (ev) {
      this.evento = { ...ev }
      this.horarios = (ev.horarios || []).map(x => ({ ...x }))
      this.tickets = (ev.tickets || []).map(x => ({ ...x }))
      this.tab = 'general'
      this.eventoDialog = true
    },

    eventoManage (ev) {
      this.eventoEdit(ev)
      this.tab = 'horarios'
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
        this.horarios = (r.data.horarios || []).map(x => ({ ...x }))
        this.tickets = (r.data.tickets || []).map(x => ({ ...x }))
        this.$alert.success(this.evento.id ? 'Evento guardado' : 'Evento creado')
        this.eventosGet()
        if (!this.evento.id) this.tab = 'general'
      })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo guardar'))
        .finally(() => { this.loading = false })
    },

    toggleActivo (ev) {
      this.loading = true
      this.$axios.put(`eventos/${ev.id}`, { activo: !ev.activo })
        .then(() => {
          this.$alert.success('Estado actualizado')
          this.eventosGet()
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
              this.eventosGet()
            })
            .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo eliminar'))
            .finally(() => { this.loading = false })
        })
    },

    // ===== Horarios =====
    horarioNew () {
      this.horario = {
        id: null,
        fecha: '',
        hora_inicio: '',
        hora_fin: '',
        starts_at: '',
        ends_at: '',
        capacidad: 0,
        reservados: 0,
        activo: true,
        nota: ''
      }
      this.horarioDialog = true
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
      if (!this.evento.id) {
        this.$alert.error('Primero guarda el evento')
        return
      }

      this.loading = true
      const payload = { ...this.horario }

      // Para datetime-local: viene "YYYY-MM-DDTHH:mm"
      // Laravel lo acepta como string y lo castea, si tu DB es datetime
      const req = payload.id
        ? this.$axios.put(`evento-horarios/${payload.id}`, payload)
        : this.$axios.post(`eventos/${this.evento.id}/horarios`, payload)

      req.then(r => {
        if (payload.id) {
          const idx = this.horarios.findIndex(x => x.id === payload.id)
          if (idx !== -1) this.horarios.splice(idx, 1, r.data)
        } else {
          this.horarios.unshift(r.data)
        }
        this.horarioDialog = false
        this.$alert.success(payload.id ? 'Horario guardado' : 'Horario creado')
        this.eventosGet()
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
              this.horarios = this.horarios.filter(x => x.id !== id)
              this.$alert.success('Horario eliminado')
              this.eventosGet()
            })
            .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo eliminar'))
            .finally(() => { this.loading = false })
        })
    },

    // ===== Tickets =====
    ticketNew () {
      this.ticket = {
        id: null,
        nombre: '',
        codigo: '',
        descripcion: '',
        precio: 0,
        moneda: this.evento.moneda || 'EGP',
        stock: 0,
        vendidos: 0,
        regla_nacionalidad: 'ALL',
        activo: true,
        orden: 0
      }
      this.ticketDialog = true
    },

    ticketEdit (t) {
      this.ticket = { ...t }
      this.ticketDialog = true
    },

    ticketToggle (t) {
      this.loading = true
      this.$axios.put(`evento-tickets/${t.id}`, { activo: !t.activo })
        .then(r => {
          const idx = this.tickets.findIndex(x => x.id === t.id)
          if (idx !== -1) this.tickets.splice(idx, 1, r.data)
          this.$alert.success('Ticket actualizado')
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo actualizar'))
        .finally(() => { this.loading = false })
    },

    ticketSave () {
      if (!this.evento.id) {
        this.$alert.error('Primero guarda el evento')
        return
      }
      if (!this.ticket.nombre) {
        this.$alert.error('Nombre del ticket es requerido')
        return
      }

      this.loading = true
      const payload = { ...this.ticket }

      const req = payload.id
        ? this.$axios.put(`evento-tickets/${payload.id}`, payload)
        : this.$axios.post(`eventos/${this.evento.id}/tickets`, payload)

      req.then(r => {
        if (payload.id) {
          const idx = this.tickets.findIndex(x => x.id === payload.id)
          if (idx !== -1) this.tickets.splice(idx, 1, r.data)
        } else {
          this.tickets.unshift(r.data)
        }
        this.ticketDialog = false
        this.$alert.success(payload.id ? 'Ticket guardado' : 'Ticket creado')
        this.eventosGet()
      })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo guardar'))
        .finally(() => { this.loading = false })
    },

    ticketDelete (id) {
      this.$alert.dialog('¿Eliminar ticket?')
        .onOk(() => {
          this.loading = true
          this.$axios.delete(`evento-tickets/${id}`)
            .then(() => {
              this.tickets = this.tickets.filter(x => x.id !== id)
              this.$alert.success('Ticket eliminado')
              this.eventosGet()
            })
            .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo eliminar'))
            .finally(() => { this.loading = false })
        })
    }
  },

  watch: {
    'filters.activo' () { this.eventosGet() },
    'filters.search' () { this.eventosGet() }
  }
}
</script>
