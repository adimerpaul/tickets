<template>
  <q-page class="q-pa-md">

    <!-- HEADER + FILTROS -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center q-col-gutter-md">
        <div class="col-12 col-md-12">
          <div class="text-h6">Órdenes</div>
          <div class="text-caption text-grey-7">Listado de pagos Stripe (PENDING / PAID / EXPIRED / FAILED)</div>
        </div>

        <div class="col-12 col-md-3">
          <q-input v-model="filters.search" dense outlined debounce="300" label="Buscar (email, session, intent)">
            <template v-slot:append><q-icon name="search" /></template>
          </q-input>
        </div>

        <div class="col-12 col-sm-6 col-md-2">
          <q-select
            v-model="filters.status"
            dense outlined
            label="Estado"
            :options="statusOptions"
            emit-value map-options
            clearable
          />
        </div>

        <div class="col-12 col-sm-6 col-md-2">
          <q-input v-model="filters.from" dense outlined type="date" label="Desde" />
        </div>

        <div class="col-12 col-sm-6 col-md-2">
          <q-input v-model="filters.to" dense outlined type="date" label="Hasta" />
        </div>

        <div class="col-12 col-md-auto">
          <q-btn color="primary" no-caps icon="refresh" label="Aplicar" :loading="loading" @click="reloadAll" />
        </div>

        <div class="col-12 col-md-auto">
          <q-btn outline color="grey-8" no-caps icon="restart_alt" label="Limpiar" :disable="loading" @click="resetFilters" />
        </div>
      </q-card-section>
    </q-card>

    <!-- CARDS / STATS -->
<!--    <div class="row q-col-gutter-md q-mb-md">-->
<!--      <div class="col-12 col-sm-6 col-md-3">-->
<!--        <q-card flat bordered>-->
<!--          <q-item class="bg-grey-9 text-white">-->
<!--            <q-item-section avatar><q-icon name="all_inbox" size="34px" /></q-item-section>-->
<!--            <q-item-section>-->
<!--              <q-item-label caption class="text-white">Total</q-item-label>-->
<!--              <q-item-label class="text-h5">{{ stats.TOTAL || 0 }}</q-item-label>-->
<!--            </q-item-section>-->
<!--          </q-item>-->
<!--        </q-card>-->
<!--      </div>-->

<!--      <div class="col-12 col-sm-6 col-md-3">-->
<!--        <q-card flat bordered>-->
<!--          <q-item class="bg-warning text-white">-->
<!--            <q-item-section avatar><q-icon name="hourglass_top" size="34px" /></q-item-section>-->
<!--            <q-item-section>-->
<!--              <q-item-label caption class="text-white">Pendientes</q-item-label>-->
<!--              <q-item-label class="text-h5">{{ stats.PENDING || 0 }}</q-item-label>-->
<!--            </q-item-section>-->
<!--          </q-item>-->
<!--        </q-card>-->
<!--      </div>-->

<!--      <div class="col-12 col-sm-6 col-md-3">-->
<!--        <q-card flat bordered>-->
<!--          <q-item class="bg-positive text-white">-->
<!--            <q-item-section avatar><q-icon name="check_circle" size="34px" /></q-item-section>-->
<!--            <q-item-section>-->
<!--              <q-item-label caption class="text-white">Pagadas</q-item-label>-->
<!--              <q-item-label class="text-h5">{{ stats.PAID || 0 }}</q-item-label>-->
<!--            </q-item-section>-->
<!--          </q-item>-->
<!--        </q-card>-->
<!--      </div>-->

<!--      <div class="col-12 col-sm-6 col-md-3">-->
<!--        <q-card flat bordered>-->
<!--          <q-item class="bg-negative text-white">-->
<!--            <q-item-section avatar><q-icon name="error" size="34px" /></q-item-section>-->
<!--            <q-item-section>-->
<!--              <q-item-label caption class="text-white">Fallidas / Expiradas</q-item-label>-->
<!--              <q-item-label class="text-h5">{{ (stats.FAILED || 0) + (stats.EXPIRED || 0) }}</q-item-label>-->
<!--            </q-item-section>-->
<!--          </q-item>-->
<!--        </q-card>-->
<!--      </div>-->
<!--    </div>-->

    <!-- TABLA (QMarkupTable) -->
    <q-card flat bordered>
      <q-card-section class="row items-center">
        <div class="text-subtitle1 text-weight-bold">Listado</div>
        <q-space />

        <q-btn
          outline
          color="primary"
          no-caps
          icon="picture_as_pdf"
          label="PDF (lista filtrada)"
          :disable="loading || !orders.length"
          @click="pdfListJs()"
          class="q-mr-sm"
        />

        <q-btn
          color="primary"
          no-caps
          icon="refresh"
          label="Actualizar"
          :loading="loading"
          @click="reloadAll"
        />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-none">
        <q-markup-table dense flat wrap-cells>
          <thead>
          <tr>
            <th class="text-center" style="width:110px">Acciones</th>
            <th class="text-left" style="width:70px">#</th>
<!--            localizador-->
            <th class="text-left" style="width:100px">Localizador</th>
            <th class="text-left">Estado</th>
            <th class="text-left">Email</th>
<!--            <th class="text-right" style="width:120px">Total</th>-->
            <th class="text-left" style="width:90px">Moneda</th>
            <th class="text-left" style="width:170px">Creado</th>
            <th class="text-left" style="width:170px">Pagado</th>
            <th class="text-left">Session</th>
          </tr>
          </thead>

          <tbody>
          <tr v-if="loading">
            <td colspan="9" class="text-center q-pa-md">
              <q-spinner size="28px" />
              <div class="text-caption text-grey-7 q-mt-sm">Cargando...</div>
            </td>
          </tr>

          <tr v-else-if="!orders.length">
            <td colspan="9" class="text-center q-pa-md text-grey-7">
              Sin registros
            </td>
          </tr>

          <tr v-else v-for="o in orders" :key="o.id">
            <td class="text-center">
<!--              <q-btn dense flat round icon="visibility" @click="openDetail(o)" />-->
<!--              <q-btn dense flat round icon="picture_as_pdf" @click="pdfOneJs(o)" />-->
              <q-btn-dropdown dense no-caps size="10px" label="Opciones" color="primary">
                <q-list>
                  <q-item clickable @click="openDetail(o)" v-close-popup>
                    <q-item-section avatar><q-icon name="visibility" /></q-item-section>
                    <q-item-section>Ver detalle</q-item-section>
                  </q-item>
<!--                  <q-item clickable @click="pdfOneJs(o)" v-close-popup>-->
<!--                    <q-item-section avatar><q-icon name="picture_as_pdf" /></q-item-section>-->
<!--                    <q-item-section>Descargar PDF</q-item-section>-->
<!--                  </q-item>-->
<!--                  colocar localizador-->
                  <q-item clickable @click="changueLocalizador(o)" v-close-popup>
                    <q-item-section avatar><q-icon name="edit_location" /></q-item-section>
                    <q-item-section>Cambiar localizador</q-item-section>
                  </q-item>
<!--                  mandar correo-->
<!--                  <q-item clickable @click="enviarCorreo(o)" v-close-popup>-->
<!--                    <q-item-section avatar><q-icon name="email" /></q-item-section>-->
<!--                    <q-item-section>Enviar correo</q-item-section>-->
<!--                  </q-item>-->
<!--                  enviar 4 correos-->
<!--                  4 Correos, 1 su pedpio est hacienod en proceso, 2 envio de las entradas , 3 su pedido no se pudo completar , 4 reembols, que haga el rembolso y cancelaro de stripe,  shodawn-->
<!--                  <q-item clickable @click="enviarCorreo(o)" v-close-popup>-->
<!--                    <q-item-section avatar><q-icon name="email" /></q-item-section>-->
<!--                    <q-item-section> -->
<!--                  </q-item>-->
                </q-list>
              </q-btn-dropdown>
            </td>
            <td class="text-left text-weight-bold">#{{ o.id }}</td>
            <td class="text-left">
              <div class="ellipsis" style="max-width: 100px">
                {{ o.localizador || '-' }}
              </div>
            </td>

            <td class="text-left">
              <q-chip
                dense
                text-color="white"
                :color="statusColor(o.status)"
                :label="o.status"
              />
            </td>

            <td class="text-left">
              <div class="text-weight-medium">{{ o.email || '-' }}</div>
              <div class="text-caption text-grey-7">
                PI: {{ o.payment_intent_id || '-' }}
              </div>
            </td>

<!--            <td class="text-right text-weight-bold">-->
<!--              {{ formatMoney(o.amount_total, o.currency) }}-->
<!--            </td>-->

            <td class="text-left">
              <q-chip dense color="grey-3" text-color="black">
                {{ (o.currency || '').toUpperCase() }}
              </q-chip>
            </td>

            <td class="text-left">
              <div>{{ formatDT(o.created_at) }}</div>
            </td>

            <td class="text-left">
              <div v-if="o.paid_at">{{ formatDT(o.paid_at) }}</div>
              <q-badge v-else outline color="grey-6">—</q-badge>
            </td>

            <td class="text-left">
              <div class="ellipsis" style="max-width: 320px">
                {{ o.session_id }}
              </div>
            </td>

          </tr>
          </tbody>
        </q-markup-table>
      </q-card-section>

      <!-- PAGINACIÓN -->
      <q-separator />
      <q-card-section class="row items-center q-col-gutter-md">
        <div class="col-12 col-sm-auto">
          <q-select
            v-model="perPage"
            dense outlined
            style="width:120px"
            label="Por página"
            :options="[10, 15, 25, 50]"
            @input="goPage(1)"
          />
        </div>

        <div class="col-12 col-sm">
          <q-pagination
            v-model="page"
            :max="lastPage"
            max-pages="8"
            boundary-numbers
            direction-links
            @input="ordersGet"
          />
        </div>

        <div class="col-12 col-sm-auto text-caption text-grey-7">
          Total: {{ totalRows }} | Página {{ page }} / {{ lastPage }}
        </div>
      </q-card-section>
    </q-card>

    <!-- DIALOG DETALLE -->
    <q-dialog v-model="detailDialog" persistent>
      <q-card style="width: 760px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1 text-weight-bold">
            Orden #{{ detail.id }}
          </div>
          <q-space />
          <q-chip dense text-color="white" :color="statusColor(detail.status)">
            {{ detail.status }}
          </q-chip>
          <q-btn icon="close" flat round dense @click="detailDialog = false" />
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <q-list bordered separator class="rounded-borders">
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Session ID</q-item-label>
                    <q-item-label class="ellipsis">{{ detail.session_id || '-' }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </div>
            <div class="col-12 col-md-6">
              <q-list bordered separator class="rounded-borders">
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Email</q-item-label>
                    <q-item-label>{{ detail.email || '-' }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Total</q-item-label>
                    <q-item-label>{{ formatMoney(detail.amount_total, detail.currency) }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Payment Intent</q-item-label>
                    <q-item-label class="text-grey-8">{{ detail.payment_intent_id || '-' }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </div>

            <div class="col-12 col-md-6">
              <q-list bordered separator class="rounded-borders">
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Creado</q-item-label>
                    <q-item-label>{{ formatDT(detail.created_at) }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Pagado</q-item-label>
                    <q-item-label>{{ detail.paid_at ? formatDT(detail.paid_at) : '—' }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </div>

            <div class="col-12">
              <div class="text-subtitle2 text-weight-bold q-mb-sm">Items</div>

              <q-markup-table dense flat bordered>
                <thead>
                <tr>
                  <th class="text-left">Nombre</th>
                  <th class="text-right" style="width:110px">Cantidad</th>
                  <th class="text-right" style="width:160px">Unit (centavos)</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(it, idx) in (detail.items || [])" :key="idx">
                  <td>{{ it.name }}</td>
                  <td class="text-right">{{ it.qty }}</td>
                  <td class="text-right">{{ it.unit_amount }}</td>
                </tr>
                <tr v-if="!(detail.items || []).length">
                  <td colspan="3" class="text-center text-grey-7">Sin items</td>
                </tr>
                </tbody>
              </q-markup-table>
            </div>

            <div class="col-12">
              <div class="text-subtitle2 text-weight-bold q-mb-sm">Metadata</div>
              <q-card flat bordered class="q-pa-sm bg-grey-1">
                <pre style="margin:0; white-space: pre-wrap">{{ detail.metadata || {} }}</pre>
              </q-card>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat no-caps label="Cerrar" @click="detailDialog = false" />
          <q-btn color="primary" no-caps icon="picture_as_pdf" label="PDF" @click="pdfOneJs(detail)" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script>
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

export default {
  name: 'OrdersPage',
  data () {
    return {
      loading: false,

      filters: {
        search: '',
        status: null,
        from: '',
        to: ''
      },

      statusOptions: [
        { label: 'PENDING', value: 'PENDING' },
        { label: 'PAID', value: 'PAID' },
        { label: 'EXPIRED', value: 'EXPIRED' },
        { label: 'FAILED', value: 'FAILED' }
      ],

      stats: { TOTAL: 0, PENDING: 0, PAID: 0, EXPIRED: 0, FAILED: 0 },

      orders: [],
      page: 1,
      perPage: 15,
      lastPage: 1,
      totalRows: 0,

      detailDialog: false,
      detail: {}
    }
  },

  watch: {
    'filters.search' () { this.debouncedReload() },
    'filters.status' () { this.goPage(1) },
    'filters.from' () { this.goPage(1) },
    'filters.to' () { this.goPage(1) }
  },

  mounted () {
    this.reloadAll()
  },

  methods: {
    debouncedReload: (() => {
      let t = null
      return function () {
        clearTimeout(t)
        t = setTimeout(() => this.goPage(1), 350)
      }
    })(),

    statusColor (st) {
      st = (st || '').toUpperCase()
      if (st === 'PAID') return 'positive'
      if (st === 'PENDING') return 'warning'
      if (st === 'EXPIRED') return 'grey-7'
      if (st === 'FAILED') return 'negative'
      return 'grey-6'
    },

    formatDT (v) {
      if (!v) return '—'
      // si te llega ISO, esto está ok
      const d = new Date(v)
      return isNaN(d.getTime()) ? String(v) : d.toLocaleString()
    },

    formatMoney (n, cur) {
      const c = (cur || 'eur').toUpperCase()
      const num = Number(n || 0)
      try {
        return new Intl.NumberFormat('es-ES', { style: 'currency', currency: c }).format(num)
      } catch (e) {
        return `${num} ${c}`
      }
    },

    buildParams () {
      return {
        search: this.filters.search || null,
        status: this.filters.status || null,
        from: this.filters.from || null,
        to: this.filters.to || null,
        page: this.page,
        perPage: this.perPage,
        sortBy: 'id',
        sortDir: 'desc'
      }
    },

    resetFilters () {
      this.filters = { search: '', status: null, from: '', to: '' }
      this.page = 1
      this.reloadAll()
    },

    goPage (p) {
      this.page = p
      this.reloadAll()
    },

    reloadAll () {
      this.ordersGet()
      this.statsGet()
    },

    async ordersGet () {
      this.loading = true
      try {
        const { data } = await this.$axios.get('orders', { params: this.buildParams() })
        // Laravel paginate: data.data, data.current_page, data.last_page, data.total
        this.orders = data.data || []
        this.page = data.current_page || 1
        this.lastPage = data.last_page || 1
        this.totalRows = data.total || 0
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error cargando órdenes')
      } finally {
        this.loading = false
      }
    },

    async statsGet () {
      try {
        const { data } = await this.$axios.get('orders/stats', { params: {
            search: this.filters.search || null,
            from: this.filters.from || null,
            to: this.filters.to || null
          }})
        this.stats = data || {}
      } catch (e) {
        // silencioso
      }
    },

    async openDetail (o) {
      this.detail = o
      this.detailDialog = true
    },
    enviarCorreo(o) {
      console.log('Enviar correo para orden', o)
      this.$q.dialog({
        title: 'Enviar Correo',
        message: '¿Desea enviar un correo con los detalles de la orden #' + o.id + ' a ' + (o.email || 'el email registrado') + '?',
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          this.loading = true
          await this.$axios.post(`orders/${o.id}/sendEmail`)
          this.$alert.success('Correo enviado correctamente')
        } catch (e) {
          this.$alert.error(e.response?.data?.message || 'Error al enviar el correo')
        } finally {
          this.loading = false
        }
      })
    },
    changueLocalizador(o) {
      console.log('Cambiar localizador para orden', o)
      this.$q.dialog({
        title: 'Cambiar Localizador',
        message: 'Ingrese el nuevo localizador para la orden #' + o.id,
        prompt: {
          model: o.localizador || '',
          type: 'text'
        },
        cancel: true,
        persistent: true
      }).onOk(async newLocalizador => {
        try {
          this.loading = true
          const updatedMetadata = {localizador: newLocalizador }
          await this.$axios.put(`orders/${o.id}`,  updatedMetadata)
          this.$alert.success('Localizador actualizado correctamente')
          this.reloadAll()
        } catch (e) {
          this.$alert.error(e.response?.data?.message || 'Error al actualizar el localizador')
        } finally {
          this.loading = false
        }
      })
    },

    // ===== PDF (JS) =====
    pdfOneJs (o) {
      this.loading = true
      this.$axios.get(`orders/${o.id}/pdfEntradas`, { responseType: 'blob' })
        .then(({ data }) => {
          const blob = new Blob([data], { type: 'application/pdf' })
          const link = document.createElement('a')
          link.href = window.URL.createObjectURL(blob)
          link.download = `order_${o.id}.pdf`
          link.click()
          window.URL.revokeObjectURL(link.href)
        })
        .catch(e => {
          this.$alert.error(e.response?.data?.message || 'No se pudo descargar el PDF')
        })
        .finally(() => {
          this.loading = false
        })
    },

    pdfListJs () {
      const doc = new jsPDF('l', 'mm', 'a4')
      doc.setFontSize(14)
      doc.text('Listado de Órdenes', 14, 14)

      const rows = this.orders.map(o => ([
        `#${o.id}`,
        o.status,
        o.email || '-',
        this.formatMoney(o.amount_total, o.currency),
        (o.currency || '').toUpperCase(),
        this.formatDT(o.created_at),
        o.paid_at ? this.formatDT(o.paid_at) : '—',
        (o.session_id || '').slice(0, 28) + '...'
      ]))

      autoTable(doc, {
        startY: 20,
        head: [['ID','Estado','Email','Total','Moneda','Creado','Pagado','Session']],
        body: rows
      })

      doc.save('orders_list.pdf')
    }
  }
}
</script>

<style scoped>
/* solo micro-estilos; lo demás es Quasar */
.ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
