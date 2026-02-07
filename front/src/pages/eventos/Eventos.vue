<template>
  <q-page class="q-pa-md">
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6 text-weight-bold">Eventos</div>
          <div class="text-caption text-grey-7">Gestión de eventos y horarios</div>
        </div>
        <q-space />
        <q-input v-model="filters.search" dense outlined debounce="300" label="Buscar (nombre/slug/ciudad)" style="width: 320px">
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
        <div class="col-12 col-md-9 row justify-end q-gutter-sm">
          <q-btn color="positive" no-caps icon="add_circle_outline" label="Nuevo evento" :loading="loading" @click="openNew" />
          <q-btn color="primary" no-caps icon="refresh" label="Actualizar" :loading="loading" @click="fetch(1)" />
        </div>
      </q-card-section>
    </q-card>

    <q-card flat bordered>
      <q-card-section class="row items-center">
        <div class="text-subtitle1 text-weight-bold">Listado</div>
        <q-space />
        <div class="text-caption text-grey-7">
          Total: {{ total }} | Página {{ page }} / {{ lastPage }}
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-none">
        <q-table
          :rows="rows"
          :columns="columns"
          row-key="id"
          dense flat bordered wrap-cells
          :rows-per-page-options="[0]"
          :loading="loading"
          no-data-label="Sin eventos"
        >
          <template v-slot:body-cell-actions="props">
            <q-td :props="props" class="text-center">
              <q-btn-dropdown label="Opciones" no-caps dense color="primary" size="10px">
                <q-list>
                  <q-item clickable v-close-popup @click="openEdit(props.row)">
                    <q-item-section avatar><q-icon name="edit" /></q-item-section>
                    <q-item-section><q-item-label>Editar / Horarios</q-item-label></q-item-section>
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

                  <q-item clickable v-close-popup @click="remove(props.row.id)">
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
        </q-table>
      </q-card-section>

      <q-separator />

      <q-card-section class="row items-center q-col-gutter-md">
        <div class="col-12 col-sm-auto">
          <q-select
            v-model="perPage"
            dense outlined
            style="width:140px"
            label="Por página"
            :options="[25, 50, 100]"
            @update:model-value="fetch(1)"
          />
        </div>

        <div class="col-12 col-sm">
          <q-pagination
            v-model="page"
            :max="lastPage"
            max-pages="8"
            boundary-numbers
            direction-links
            @update:model-value="fetch"
          />
        </div>
      </q-card-section>
    </q-card>
    <q-dialog v-model="open" persistent maximized >
      <evento-dialog
        v-model="dlg"
        :evento="current"
        @saved="onSaved"
        @closed="open = false"
      />
    </q-dialog>
  </q-page>
</template>

<script>
import EventoDialog from './EventoDialog.vue'

export default {
  name: 'EventosPage',
  components: { EventoDialog },
  data () {
    return {
      open: false,
      loading: false,
      dlg: false,
      current: {},

      filters: { activo: null, search: '' },

      rows: [],
      page: 1,
      perPage: 50,
      lastPage: 1,
      total: 0,

      activoOptions: [
        { label: 'Activos', value: true },
        { label: 'Inactivos', value: false }
      ],

      columns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'id', label: 'ID', align: 'left', field: 'id', sortable: true },
        { name: 'nombre', label: 'Nombre', align: 'left', field: 'nombre', sortable: true },
        { name: 'slug', label: 'Slug', align: 'left', field: 'slug', sortable: true },
        { name: 'ciudad', label: 'Ciudad', align: 'left', field: 'ciudad' },
        { name: 'moneda', label: 'Moneda', align: 'left', field: row => row.moneda?.codigo || row.moneda_id || '' },
        { name: 'idioma', label: 'Idioma', align: 'left', field: row => row.idioma?.codigo || row.idioma_id || '' },
        { name: 'activo', label: 'Estado', align: 'left', field: 'activo' }
      ]
    }
  },

  mounted () { this.fetch(1) },

  watch: {
    'filters.activo' () { this.fetch(1) },
    'filters.search' () { this.fetch(1) }
  },

  methods: {
    buildParams () {
      const p = { page: this.page, perPage: this.perPage }
      if (this.filters.activo !== null && this.filters.activo !== undefined) p.activo = this.filters.activo
      if (this.filters.search) p.search = this.filters.search
      return p
    },

    fetch (p) {
      this.page = p
      this.loading = true
      this.$axios.get('eventos', { params: this.buildParams() })
        .then(r => {
          const data = r.data || {}
          this.rows = data.data || []
          this.page = data.current_page || 1
          this.lastPage = data.last_page || 1
          this.total = data.total || 0
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'Error cargando eventos'))
        .finally(() => { this.loading = false })
    },

    openNew () {
      this.current = {}
      this.dlg = true
      this.open = true
    },

    openEdit (row) {
      this.current = { ...row }
      this.dlg = true
      this.open = true
    },

    onSaved () {
      this.fetch(1)
    },

    toggleActivo (row) {
      this.loading = true
      this.$axios.put(`eventos/${row.id}`, { activo: !row.activo })
        .then(() => {
          this.$alert.success('Estado actualizado')
          this.fetch(1)
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo actualizar'))
        .finally(() => { this.loading = false })
    },

    remove (id) {
      this.$alert.dialog('¿Eliminar el evento?')
        .onOk(() => {
          this.loading = true
          this.$axios.delete(`eventos/${id}`)
            .then(() => {
              this.$alert.success('Evento eliminado')
              this.fetch(1)
            })
            .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo eliminar'))
            .finally(() => { this.loading = false })
        })
    }
  }
}
</script>
