<template>
  <div class="q-pa-sm">

    <!-- HEADER -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6 text-weight-bold">Precios</div>
          <div class="text-caption text-grey-7">
            Nacionalidades + Tipos de entrada + Segmentos + Matriz (por Monedas)
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
      <div class="col-12 col-md-4">
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

      <!-- ================= TIPOS ENTRADA ================= -->
      <div class="col-12 col-md-4">
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

      <!-- ================= SEGMENTOS ================= -->
      <div class="col-12 col-md-4">
        <q-card flat bordered>
          <q-card-section class="row items-center">
            <div class="text-subtitle1 text-weight-bold">Segmentos</div>
            <q-space />
            <q-btn color="positive" no-caps dense icon="add" label="Nuevo" @click="openSegNew" />
          </q-card-section>
          <q-separator />

          <q-table
            :rows="segmentos"
            :columns="colsSeg"
            row-key="id"
            dense flat bordered
            :rows-per-page-options="[0]"
          >
            <template v-slot:body-cell-actions="props">
              <q-td class="text-center">
                <q-btn flat round dense icon="edit" @click="openSegEdit(props.row)" />
                <q-btn flat round dense icon="delete" color="negative" @click="removeSeg(props.row)" />
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
            <q-select
              v-model="selectedMonedaIds"
              :options="monedas.map(m => ({ label: m.codigo + ' - ' + m.nombre, value: m.id }))"
              emit-value
              map-options
              multiple
              dense
              outlined
              label="Monedas a mostrar"
              style="min-width: 320px"
            />

            <q-btn
              color="primary"
              no-caps
              icon="save"
              label="Guardar precios"
              :loading="saving"
              :disable="matrixRows.length === 0 || monedasSel.length === 0"
              @click="savePrices"
            />
          </q-card-section>

          <q-separator />

          <q-card-section v-if="matrixRows.length === 0">
            <q-banner rounded class="bg-grey-2">
              Crea al menos una nacionalidad, un tipo de entrada y un segmento.
            </q-banner>
          </q-card-section>

          <q-table
            v-else
            :rows="matrixRows"
            :columns="colsPrices"
            row-key="key"
            dense
            flat
            bordered
            :rows-per-page-options="[0]"
            wrap-cells
            hide-header
          >
            <!-- columnas dinámicas por moneda -->
            <template v-for="m in monedasSel" v-slot:[`body-cell-moneda_${m.id}`]="props">
              <q-td :key="m.id">
                <div class="row q-col-gutter-xs">
                  <div class="col-6">
                    <q-input
                      dense outlined type="number"
                      v-model.number="props.row.prices[m.id].compra"
                      :label="`Compra ${m.codigo}`"
                    />
                  </div>
                  <div class="col-6">
                    <q-input
                      dense outlined type="number"
                      v-model.number="props.row.prices[m.id].venta"
                      :label="`Venta ${m.codigo}`"
                    />
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

    <q-dialog v-model="dlgSeg.open" persistent>
      <q-card style="width:520px">
        <q-card-section class="row items-center">
          <div class="text-subtitle1">{{ dlgSeg.form.id ? 'Editar' : 'Nuevo' }} segmento</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="dlgSeg.open=false" />
        </q-card-section>

        <q-card-section>
          <q-input v-model="dlgSeg.form.nombre" dense outlined label="Nombre" />
          <q-input v-model="dlgSeg.form.slug" dense outlined label="Slug" class="q-mt-sm" />
          <q-input v-model.number="dlgSeg.form.orden" dense outlined type="number" label="Orden" class="q-mt-sm" />
          <q-toggle v-model="dlgSeg.form.activo" label="Activo" />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" @click="dlgSeg.open=false" />
          <q-btn color="primary" label="Guardar" :loading="dlgSeg.loading" @click="saveSeg" />
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
      selectedMonedaIds: [],
      loading: false,
      saving: false,

      nacionalidades: [],
      tipos: [],
      segmentos: [],
      monedas: [],

      matrixRows: [],

      dlgNac: { open: false, loading: false, form: {} },
      dlgTipo: { open: false, loading: false, form: {} },
      dlgSeg: { open: false, loading: false, form: {} },

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
      colsSeg: [
        { name: 'actions', label: '', align: 'center' },
        { name: 'nombre', label: 'Nombre', field: 'nombre' },
        { name: 'slug', label: 'Slug', field: 'slug' },
        { name: 'orden', label: 'Orden', field: 'orden' },
        { name: 'activo', label: 'Estado', field: 'activo' }
      ],
      colsPrices: [
        { name: 'nac', label: 'Nacionalidad', field: 'nac_nombre' },
        { name: 'tipo', label: 'Tipo', field: 'tipo_nombre' },
        { name: 'seg', label: 'Segmento', field: 'seg_nombre' },
        { name: 'activo', label: 'Activo', align: 'center' }
      ]
    }
  },
  computed: {
    monedasSel () {
      const set = new Set(this.selectedMonedaIds)
      return (this.monedas || []).filter(m => set.has(m.id))
    }
  },
  mounted () {
    this.loadAll()
  },

  methods: {
    mkKey (n, t, s) { return `${n}|${t}|${s}` },
    mkKeyPrice (n, t, s, m) { return `${n}|${t}|${s}|${m}` },

    buildColsPrices () {
      const base = [
        { name: 'nac', label: 'Nacionalidad', field: 'nac_nombre' },
        { name: 'tipo', label: 'Tipo', field: 'tipo_nombre' },
        { name: 'seg', label: 'Segmento', field: 'seg_nombre' }
      ]
      const dyn = (this.monedas || []).map(m => ({
        name: `moneda_${m.id}`,
        label: `${m.codigo}`,
        align: 'left'
      }))
      const end = [{ name: 'activo', label: 'Activo', align: 'center' }]

      this.colsPrices = [...base, ...dyn, ...end]
    },

    async loadAll () {
      this.loading = true
      try {
        const [rn, rt, rs, rm, rp] = await Promise.all([
          this.$axios.get(`eventos/${this.eventoId}/nacionalidades`),
          this.$axios.get(`eventos/${this.eventoId}/tipos-entrada`),
          this.$axios.get(`eventos/${this.eventoId}/segmentos`),
          this.$axios.get(`monedas`, { params: { solo_activos: 1 } }),
          this.$axios.get(`eventos/${this.eventoId}/precios`)
        ])

        this.nacionalidades = rn.data.items || []
        this.tipos = rt.data.items || []
        this.segmentos = rs.data.items || []
        this.monedas = rm.data.items || []
        const dyn = (this.monedasSel || []).map(m => ({
          name: `moneda_${m.id}`,
          label: `${m.codigo}`,
          align: 'left'
        }))

        this.buildColsPrices()

        // map precios existentes
        const priceMap = new Map()
        for (const p of (rp.data.items || [])) {
          priceMap.set(
            this.mkKeyPrice(p.nacionalidad_id, p.tipo_entrada_id, p.segmento_id, p.moneda_id),
            p
          )
        }

        // matriz
        this.matrixRows = []
        for (const n of this.nacionalidades) {
          for (const t of this.tipos) {
            for (const s of this.segmentos) {
              const row = {
                key: this.mkKey(n.id, t.id, s.id),
                nacionalidad_id: n.id,
                tipo_entrada_id: t.id,
                segmento_id: s.id,
                nac_nombre: n.nombre,
                tipo_nombre: t.nombre,
                seg_nombre: s.nombre,
                activo: true,
                prices: {} // { monedaId: { compra, venta, activo } }
              }

              for (const m of this.monedas) {
                const found = priceMap.get(this.mkKeyPrice(n.id, t.id, s.id, m.id))
                row.prices[m.id] = {
                  moneda_id: m.id,
                  compra: found ? Number(found.compra || 0) : 0,
                  venta:  found ? Number(found.venta  || 0) : 0,
                  activo: found ? !!found.activo : true
                }
                // si alguno estaba inactivo, no “apaga” la fila; fila controla visualmente, pero guardamos activo por precio.
              }

              this.matrixRows.push(row)
            }
          }
        }
      } finally {
        this.loading = false
      }
    },

    async savePrices () {
      this.saving = true
      try {
        const rows = []

        for (const r of this.matrixRows) {
          for (const m of this.monedasSel) {
            const pr = r.prices[m.id]
            rows.push({
              nacionalidad_id: r.nacionalidad_id,
              tipo_entrada_id: r.tipo_entrada_id,
              segmento_id: r.segmento_id,
              moneda_id: m.id,
              compra: pr.compra,
              venta: pr.venta,
              activo: pr.activo
            })
          }
        }

        await this.$axios.post(`eventos/${this.eventoId}/precios/upsert`, { rows })
        this.$alert.success('Precios guardados')
        await this.loadAll()
      } finally {
        this.saving = false
      }
    },

    // ===== NACIONALIDADES =====
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
        this.$axios.delete(`evento-nacionalidades/${r.id}`).then(() => this.loadAll())
      )
    },

    // ===== TIPOS ENTRADA =====
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
        this.$axios.delete(`evento-tipos-entrada/${r.id}`).then(() => this.loadAll())
      )
    },

    // ===== SEGMENTOS =====
    openSegNew () { this.dlgSeg.form = { nombre: '', slug: '', orden: 0, activo: true }; this.dlgSeg.open = true },
    openSegEdit (r) { this.dlgSeg.form = { ...r }; this.dlgSeg.open = true },
    async saveSeg () {
      const f = this.dlgSeg.form
      f.id
        ? await this.$axios.put(`evento-segmentos/${f.id}`, f)
        : await this.$axios.post(`eventos/${this.eventoId}/segmentos`, f)
      this.dlgSeg.open = false
      this.loadAll()
    },
    removeSeg (r) {
      this.$alert.dialog('¿Eliminar segmento?').onOk(() =>
        this.$axios.delete(`evento-segmentos/${r.id}`).then(() => this.loadAll())
      )
    }
  }
}
</script>
