<template>
  <div class="q-pa-sm">

    <!-- HEADER -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6 text-weight-bold">Precios</div>
          <div class="text-caption text-grey-7">
            Nacionalidades + Tipos de entrada + matriz de precios
          </div>
        </div>
        <q-space />
        <q-btn
          class="q-ml-sm"
          color="primary"
          no-caps
          icon="refresh"
          label="Actualizar"
          :loading="loading"
          @click="loadAll"
        />
      </q-card-section>
    </q-card>

    <div class="row q-col-gutter-md">

      <!-- ================= NACIONALIDADES ================= -->
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section class="row items-center">
            <div class="text-subtitle1 text-weight-bold">Nacionalidades</div>
            <q-space />
            <q-btn color="positive" no-caps dense icon="add" label="Nuevo" @click="openNacNew" />
          </q-card-section>
          <q-separator />

          <q-table
            :rows="nacionalidades"
            :columns="colsNac"
            row-key="id"
            dense flat bordered
            :rows-per-page-options="[0]"
          >
            <template v-slot:body-cell-actions="props">
              <q-td class="text-center">
                <q-btn flat round dense icon="edit" @click="openNacEdit(props.row)" />
                <q-btn flat round dense icon="delete" color="negative" @click="removeNac(props.row)" />
              </q-td>
            </template>

            <template v-slot:body-cell-activo="props">
              <q-td>
                <q-badge :color="props.row.activo ? 'positive' : 'grey-6'" text-color="white">
                  {{ props.row.activo ? 'Activo' : 'Inactivo' }}
                </q-badge>
              </q-td>
            </template>
          </q-table>
        </q-card>
      </div>

      <!-- ================= TIPOS ================= -->
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section class="row items-center">
            <div class="text-subtitle1 text-weight-bold">Tipos de entrada</div>
            <q-space />
            <q-btn color="positive" no-caps dense icon="add" label="Nuevo" @click="openTipoNew" />
          </q-card-section>
          <q-separator />

          <q-table
            :rows="tipos"
            :columns="colsTipo"
            row-key="id"
            dense flat bordered
            :rows-per-page-options="[0]"
          >
            <template v-slot:body-cell-actions="props">
              <q-td class="text-center">
                <q-btn flat round dense icon="edit" @click="openTipoEdit(props.row)" />
                <q-btn flat round dense icon="delete" color="negative" @click="removeTipo(props.row)" />
              </q-td>
            </template>

            <template v-slot:body-cell-activo="props">
              <q-td>
                <q-badge :color="props.row.activo ? 'positive' : 'grey-6'" text-color="white">
                  {{ props.row.activo ? 'Activo' : 'Inactivo' }}
                </q-badge>
              </q-td>
            </template>
          </q-table>
        </q-card>
      </div>

      <!-- ================= MATRIZ ================= -->
      <div class="col-12">
        <q-card flat bordered>
          <q-card-section class="row items-center">
            <div class="text-subtitle1 text-weight-bold">Matriz de precios</div>
            <q-space />
            <q-btn
              color="primary"
              no-caps
              icon="save"
              label="Guardar precios"
              :loading="saving"
              :disable="matrixRows.length === 0"
              @click="savePrices"
            />
          </q-card-section>

          <q-separator />

          <q-card-section v-if="matrixRows.length === 0">
            <q-banner rounded class="bg-grey-2">
              Crea al menos una nacionalidad y un tipo de entrada.
            </q-banner>
          </q-card-section>

          <q-table
            v-else
            :rows="matrixRows"
            :columns="colsPrices"
            row-key="key"
            dense flat bordered
            :rows-per-page-options="[0]"
            wrap-cells
          >

            <!-- MONEDA TEMPLATE -->
            <template v-for="m in monedas" v-slot:[`body-cell-${m}`]="props">
              <q-td :key="m">
                <div class="row q-col-gutter-xs">
                  <div class="col-6">
                    <q-input dense outlined type="number" v-model.number="props.row[`${m}_compra`]" label="Compra" />
                  </div>
                  <div class="col-6">
                    <q-input dense outlined type="number" v-model.number="props.row[`${m}_venta`]" label="Venta" />
                  </div>
                </div>
              </q-td>
            </template>

            <template v-slot:body-cell-activo="props">
              <q-td class="text-center">
                <q-toggle v-model="props.row.activo" />
              </q-td>
            </template>

          </q-table>
        </q-card>
      </div>
    </div>

    <!-- ================= DIALOGS ================= -->
    <q-dialog v-model="dlgNac.open" persistent>
      <q-card style="width:520px">
        <q-card-section class="row items-center">
          <div class="text-subtitle1">{{ dlgNac.form.id ? 'Editar' : 'Nueva' }} nacionalidad</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="dlgNac.open=false" />
        </q-card-section>

        <q-card-section>
          <q-input v-model="dlgNac.form.nombre" dense outlined label="Nombre" />
          <q-input v-model="dlgNac.form.slug" dense outlined label="Slug" class="q-mt-sm" />
          <q-input v-model.number="dlgNac.form.orden" dense outlined type="number" label="Orden" class="q-mt-sm" />
          <q-toggle v-model="dlgNac.form.activo" label="Activo" />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" @click="dlgNac.open=false" />
          <q-btn color="primary" label="Guardar" :loading="dlgNac.loading" @click="saveNac" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dlgTipo.open" persistent>
      <q-card style="width:520px">
        <q-card-section class="row items-center">
          <div class="text-subtitle1">{{ dlgTipo.form.id ? 'Editar' : 'Nuevo' }} tipo</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="dlgTipo.open=false" />
        </q-card-section>

        <q-card-section>
          <q-input v-model="dlgTipo.form.nombre" dense outlined label="Nombre" />
          <q-input v-model="dlgTipo.form.slug" dense outlined label="Slug" class="q-mt-sm" />
          <q-input v-model.number="dlgTipo.form.orden" dense outlined type="number" label="Orden" class="q-mt-sm" />
          <q-toggle v-model="dlgTipo.form.activo" label="Activo" />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" @click="dlgTipo.open=false" />
          <q-btn color="primary" label="Guardar" :loading="dlgTipo.loading" @click="saveTipo" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </div>
</template>

<script>
export default {
  name: 'EventoPrecios',
  props: { eventoId: { required: true } },

  data () {
    return {
      loading: false,
      saving: false,

      nacionalidades: [],
      tipos: [],
      matrixRows: [],

      monedas: ['egp', 'eur', 'usd', 'usdt'],

      dlgNac: { open: false, loading: false, form: {} },
      dlgTipo: { open: false, loading: false, form: {} },

      colsNac: [
        { name: 'actions', label: '', align: 'center' },
        { name: 'nombre', label: 'Nombre', field: 'nombre' },
        { name: 'slug', label: 'Slug', field: 'slug' },
        { name: 'orden', label: 'Orden', field: 'orden' },
        { name: 'activo', label: 'Estado', field: 'activo' }
      ],

      colsTipo: [
        { name: 'actions', label: '', align: 'center' },
        { name: 'nombre', label: 'Nombre', field: 'nombre' },
        { name: 'slug', label: 'Slug', field: 'slug' },
        { name: 'orden', label: 'Orden', field: 'orden' },
        { name: 'activo', label: 'Estado', field: 'activo' }
      ],

      colsPrices: [
        { name: 'nac', label: 'Nacionalidad', field: 'nac_nombre' },
        { name: 'tipo', label: 'Tipo', field: 'tipo_nombre' },
        { name: 'egp', label: 'EGP' },
        { name: 'eur', label: 'EUR' },
        { name: 'usd', label: 'USD' },
        { name: 'usdt', label: 'USDT' },
        { name: 'activo', label: 'Activo', align: 'center' }
      ]
    }
  },

  mounted () {
    this.loadAll()
  },

  methods: {
    mkKey (n, t) { return `${n}|${t}` },

    async loadAll () {
      this.loading = true
      try {
        const [rn, rt, rp] = await Promise.all([
          this.$axios.get(`eventos/${this.eventoId}/nacionalidades`),
          this.$axios.get(`eventos/${this.eventoId}/tipos-entrada`),
          this.$axios.get(`eventos/${this.eventoId}/precios`)
        ])

        this.nacionalidades = rn.data.items
        this.tipos = rt.data.items

        const map = new Map()
        for (const p of rp.data.items) {
          map.set(this.mkKey(p.nacionalidad_id, p.tipo_entrada_id), p)
        }

        this.matrixRows = []
        for (const n of this.nacionalidades) {
          for (const t of this.tipos) {
            const base = map.get(this.mkKey(n.id, t.id)) || {
              nacionalidad_id: n.id,
              tipo_entrada_id: t.id,
              egp_compra: 0, egp_venta: 0,
              eur_compra: 0, eur_venta: 0,
              usd_compra: 0, usd_venta: 0,
              usdt_compra: 0, usdt_venta: 0,
              activo: true
            }

            this.matrixRows.push({
              key: this.mkKey(n.id, t.id),
              nac_nombre: n.nombre,
              tipo_nombre: t.nombre,
              ...JSON.parse(JSON.stringify(base))
            })
          }
        }
      } finally {
        this.loading = false
      }
    },

    async savePrices () {
      this.saving = true
      try {
        await this.$axios.post(`eventos/${this.eventoId}/precios/upsert`, {
          rows: this.matrixRows.map(r => ({
            nacionalidad_id: r.nacionalidad_id,
            tipo_entrada_id: r.tipo_entrada_id,
            egp_compra: r.egp_compra,
            egp_venta: r.egp_venta,
            eur_compra: r.eur_compra,
            eur_venta: r.eur_venta,
            usd_compra: r.usd_compra,
            usd_venta: r.usd_venta,
            usdt_compra: r.usdt_compra,
            usdt_venta: r.usdt_venta,
            activo: r.activo
          }))
        })
        this.$alert.success('Precios guardados')
        await this.loadAll()
      } finally {
        this.saving = false
      }
    },

    openNacNew () { this.dlgNac.form = { nombre: '', slug: '', orden: 0, activo: true }; this.dlgNac.open = true },
    openNacEdit (r) { this.dlgNac.form = { ...r }; this.dlgNac.open = true },
    async saveNac () {
      const f = this.dlgNac.form
      f.id
        ? await this.$axios.put(`evento-nacionalidades/${f.id}`, f)
        : await this.$axios.post(`eventos/${this.eventoId}/nacionalidades`, f)
      this.dlgNac.open = false
      this.loadAll()
    },
    removeNac (r) {
      this.$alert.dialog('¿Eliminar nacionalidad?').onOk(() =>
        this.$axios.delete(`evento-nacionalidades/${r.id}`).then(this.loadAll)
      )
    },

    openTipoNew () { this.dlgTipo.form = { nombre: '', slug: '', orden: 0, activo: true }; this.dlgTipo.open = true },
    openTipoEdit (r) { this.dlgTipo.form = { ...r }; this.dlgTipo.open = true },
    async saveTipo () {
      const f = this.dlgTipo.form
      f.id
        ? await this.$axios.put(`evento-tipos-entrada/${f.id}`, f)
        : await this.$axios.post(`eventos/${this.eventoId}/tipos-entrada`, f)
      this.dlgTipo.open = false
      this.loadAll()
    },
    removeTipo (r) {
      this.$alert.dialog('¿Eliminar tipo?').onOk(() =>
        this.$axios.delete(`evento-tipos-entrada/${r.id}`).then(this.loadAll)
      )
    }
  }
}
</script>
