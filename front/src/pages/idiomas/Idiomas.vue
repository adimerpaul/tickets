<template>
  <q-page class="q-pa-md">
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6 text-title">Idiomas</div>
          <div class="text-caption text-grey-7">Administracion de idiomas del sistema</div>
        </div>
        <q-space />
        <q-input v-model="filter" label="Buscar" dense outlined debounce="300" style="width: 280px">
          <template v-slot:append><q-icon name="search" /></template>
        </q-input>
      </q-card-section>
    </q-card>

    <q-table
      :rows="rows"
      :columns="columns"
      row-key="id"
      dense
      flat
      bordered
      wrap-cells
      :filter="filter"
      :rows-per-page-options="[0]"
      loading-label="Cargando..."
      no-data-label="Sin registros"
    >
      <template v-slot:top-right>
        <q-btn
          color="positive"
          label="Nuevo"
          no-caps
          icon="add_circle_outline"
          :loading="loading"
          class="q-mr-sm"
          @click="idiomaNew"
        />
        <q-btn
          color="primary"
          label="Actualizar"
          no-caps
          icon="refresh"
          :loading="loading"
          @click="idiomasGet"
        />
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="text-center">
          <q-btn-dropdown label="Opciones" no-caps size="10px" dense color="primary">
            <q-list>
              <q-item clickable v-close-popup @click="idiomaEdit(props.row)">
                <q-item-section avatar><q-icon name="edit" /></q-item-section>
                <q-item-section><q-item-label>Editar</q-item-label></q-item-section>
              </q-item>

              <q-separator />

              <q-item clickable v-close-popup @click="idiomaDelete(props.row.id)">
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

    <q-dialog v-model="dialog" persistent>
      <q-card style="width: 420px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1 text-weight-bold">
            {{ form.id ? 'Editar idioma' : 'Nuevo idioma' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense @click="dialog = false" />
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <q-form @submit.prevent="form.id ? idiomaPut() : idiomaPost()">
            <q-input v-model="form.codigo" label="Codigo" dense outlined :rules="[req]" class="q-mb-sm" />
            <q-input v-model="form.nombre" label="Nombre" dense outlined :rules="[req]" class="q-mb-sm" />
            <q-input v-model.number="form.orden" type="number" label="Orden" dense outlined class="q-mb-sm" />
            <q-toggle v-model="form.activo" label="Activo" />

            <div class="row justify-end q-gutter-sm q-mt-md">
              <q-btn color="negative" label="Cancelar" no-caps flat @click="dialog = false" :disable="loading" />
              <q-btn color="primary" label="Guardar" no-caps type="submit" :loading="loading" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'IdiomasPage',
  data () {
    return {
      rows: [],
      form: {},
      dialog: false,
      loading: false,
      filter: '',

      columns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'codigo', label: 'Codigo', align: 'left', field: 'codigo' },
        { name: 'nombre', label: 'Nombre', align: 'left', field: 'nombre' },
        { name: 'orden', label: 'Orden', align: 'left', field: 'orden' },
        { name: 'activo', label: 'Estado', align: 'left', field: 'activo' }
      ]
    }
  },

  mounted () {
    this.idiomasGet()
  },

  methods: {
    req (v) {
      return !!v || 'Campo requerido'
    },

    idiomaNew () {
      this.form = { codigo: '', nombre: '', orden: 0, activo: true }
      this.dialog = true
    },

    idiomaEdit (m) {
      this.form = { ...m }
      this.dialog = true
    },

    idiomasGet () {
      this.loading = true
      this.$axios.get('idiomas', { params: { solo_activos: 0 } })
        .then(res => { this.rows = res.data.items || [] })
        .catch(e => this.$alert.error(e.response?.data?.message || 'Error cargando idiomas'))
        .finally(() => { this.loading = false })
    },

    idiomaPost () {
      this.loading = true
      this.$axios.post('idiomas', this.form)
        .then(() => {
          this.dialog = false
          this.$alert.success('Idioma creado')
          this.idiomasGet()
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo crear'))
        .finally(() => { this.loading = false })
    },

    idiomaPut () {
      this.loading = true
      this.$axios.put(`idiomas/${this.form.id}`, this.form)
        .then(() => {
          this.dialog = false
          this.$alert.success('Idioma actualizado')
          this.idiomasGet()
        })
        .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo actualizar'))
        .finally(() => { this.loading = false })
    },

    idiomaDelete (id) {
      this.$alert.dialog('Â¿Desea eliminar el idioma?')
        .onOk(() => {
          this.loading = true
          this.$axios.delete(`idiomas/${id}`)
            .then(() => {
              this.$alert.success('Idioma eliminado')
              this.idiomasGet()
            })
            .catch(e => this.$alert.error(e.response?.data?.message || 'No se pudo eliminar'))
            .finally(() => { this.loading = false })
        })
    }
  }
}
</script>
