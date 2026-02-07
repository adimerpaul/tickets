<template>
  <q-page class="q-pa-md">
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center q-col-gutter-md">
        <div class="col-12">
          <div class="text-h6">Control de pagos</div>
          <div class="text-caption text-grey-7">
            Vista financiera con montos, estados y correos enviados
          </div>
        </div>

        <div class="col-12 col-md-3">
          <q-input v-model="filters.search" dense outlined debounce="300" label="Buscar (email, session, intent, localizador)">
            <template v-slot:append><q-icon name="search" /></template>
          </q-input>
        </div>

        <div class="col-12 col-sm-6 col-md-2">
          <q-input
            dense outlined
            label="Estado"
            :model-value="'Todos excepto PENDING'"
            disable
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

    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-item class="bg-positive text-white">
            <q-item-section avatar><q-icon name="check_circle" size="28px" /></q-item-section>
            <q-item-section>
              <q-item-label caption class="text-white">Pagos efectivos</q-item-label>
              <q-item-label class="text-h6">{{ totalRows }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-item class="bg-indigo-8 text-white">
            <q-item-section avatar><q-icon name="paid" size="28px" /></q-item-section>
            <q-item-section>
              <q-item-label caption class="text-white">Total (página)</q-item-label>
              <q-item-label class="text-h6">{{ formatMoney(pageTotal, pageCurrency) }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-card>
      </div>
    </div>

    <q-card flat bordered>
      <q-card-section class="row items-center">
        <div class="text-subtitle1 text-weight-bold">Listado</div>
        <q-space />

        <q-btn
          outline
          color="primary"
          no-caps
          icon="picture_as_pdf"
          label="PDF (sin pendientes)"
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
            <th class="text-left" style="width:120px">Evento</th>
            <th class="text-left" style="width:100px">Localizador</th>
            <th class="text-left">Estado</th>
            <th class="text-left">Email</th>
            <th class="text-right" style="width:130px">Total</th>
            <th class="text-left" style="width:90px">Moneda</th>
            <th class="text-left" style="width:170px">Creado</th>
            <th class="text-left" style="width:170px">Pagado</th>
            <th class="text-left">Session</th>
          </tr>
          </thead>

          <tbody>
          <tr v-if="loading">
            <td colspan="11" class="text-center q-pa-md">
              <q-spinner size="28px" />
              <div class="text-caption text-grey-7 q-mt-sm">Cargando...</div>
            </td>
          </tr>

          <tr v-else-if="!orders.length">
            <td colspan="11" class="text-center q-pa-md text-grey-7">
              Sin registros
            </td>
          </tr>

          <tr v-else v-for="o in orders" :key="o.id">
            <td class="text-center">
              <q-btn-dropdown dense no-caps size="10px" label="Opciones" color="primary">
                <q-list>
                  <q-item clickable @click="openDetail(o)" v-close-popup>
                    <q-item-section avatar><q-icon name="visibility" /></q-item-section>
                    <q-item-section>Ver detalle</q-item-section>
                  </q-item>
                  <q-item clickable @click="changueLocalizador(o)" v-close-popup>
                    <q-item-section avatar><q-icon name="edit_location" /></q-item-section>
                    <q-item-section>Cambiar localizador</q-item-section>
                  </q-item>
                  <q-separator />
                  <q-item clickable @click="openEmailDialog(o, 'PROCESSING')" v-close-popup>
                    <q-item-section avatar><q-icon name="email" /></q-item-section>
                    <q-item-section>Correo: En proceso</q-item-section>
                  </q-item>
                  <q-item clickable @click="openEmailDialog(o, 'ENTRADAS')" v-close-popup>
                    <q-item-section avatar><q-icon name="email" /></q-item-section>
                    <q-item-section>Correo: Enviar entradas (PDF)</q-item-section>
                  </q-item>
                  <q-item clickable @click="openEmailDialog(o, 'FAILED')" v-close-popup>
                    <q-item-section avatar><q-icon name="email" /></q-item-section>
                    <q-item-section>Correo: No se pudo completar</q-item-section>
                  </q-item>
                  <q-item clickable @click="openEmailDialog(o, 'REFUND')" v-close-popup>
                    <q-item-section avatar><q-icon name="email" /></q-item-section>
                    <q-item-section>Correo: Reembolso</q-item-section>
                  </q-item>
                  <q-separator />
                  <q-item clickable @click="openEmailHistory(o)" v-close-popup>
                    <q-item-section avatar><q-icon name="history" /></q-item-section>
                    <q-item-section>Historial de correos</q-item-section>
                  </q-item>
                  <q-separator />
                  <q-item clickable @click="refundOrder(o)" v-close-popup>
                    <q-item-section avatar><q-icon name="currency_exchange" /></q-item-section>
                    <q-item-section>Hacer reembolso</q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </td>
            <td class="text-left text-weight-bold">#{{ o.id }}</td>
            <td class="text-left">
              <div class="ellipsis" style="max-width: 140px">
                {{ o.evento?.nombre || '-' }}
              </div>
            </td>
            <td class="text-left">
              <div class="ellipsis" style="max-width: 100px">
                {{ o.localizador || '-' }}
              </div>
            </td>
            <td class="text-left">
              <q-chip dense text-color="white" :color="statusColor(o.status)" :label="o.status" />
            </td>
            <td class="text-left">
              <div class="text-weight-medium">{{ o.email || '-' }}</div>
              <div class="text-caption text-grey-7">PI: {{ o.payment_intent_id || '-' }}</div>
            </td>
            <td class="text-right text-weight-bold">
              {{ formatMoney(o.amount_total, o.currency) }}
            </td>
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

    <q-dialog v-model="detailDialog" persistent>
      <q-card style="width: 760px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1 text-weight-bold">Orden #{{ detail.id }}</div>
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
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat no-caps label="Cerrar" @click="detailDialog = false" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="emailDialog" persistent>
      <q-card style="width: 520px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1 text-weight-bold">Enviar correo</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="emailDialog = false" />
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <div class="text-caption text-grey-7 q-mb-sm">
            Orden #{{ emailForm.order?.id || '-' }} | Email: {{ emailForm.order?.email || '-' }}
          </div>

          <q-select
            v-model="emailForm.type"
            dense outlined
            label="Tipo de correo"
            :options="emailTypeOptions"
            emit-value map-options
          />

          <q-file
            v-model="emailForm.file"
            dense outlined
            accept=".pdf,application/pdf"
            label="Adjuntar PDF (solo para Entradas)"
            class="q-mt-md"
            clearable
          />

          <div class="text-caption text-grey-7 q-mt-xs">
            Para "Entradas" el PDF es obligatorio.
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" @click="emailDialog = false" />
          <q-btn color="primary" no-caps label="Enviar" :loading="loading" @click="sendStatusEmail" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="historyDialog">
      <q-card style="width: 760px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1 text-weight-bold">Historial de correos</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="historyDialog = false" />
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <div class="text-caption text-grey-7 q-mb-sm">
            Orden #{{ historyOrder?.id || '-' }} | Email: {{ historyOrder?.email || '-' }}
          </div>

          <q-markup-table dense flat bordered>
            <thead>
            <tr>
              <th class="text-left">Tipo</th>
              <th class="text-left">Asunto</th>
              <th class="text-left">Destinatario</th>
              <th class="text-left">Fecha</th>
              <th class="text-left">PDF</th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="historyLoading">
              <td colspan="5" class="text-center q-pa-md">
                <q-spinner size="24px" />
              </td>
            </tr>
            <tr v-else-if="!historyItems.length">
              <td colspan="5" class="text-center text-grey-7 q-pa-md">Sin registros</td>
            </tr>
            <tr v-else v-for="h in historyItems" :key="h.id">
              <td class="text-left">{{ h.type }}</td>
              <td class="text-left">{{ h.subject || '-' }}</td>
              <td class="text-left">{{ h.to_email || '-' }}</td>
              <td class="text-left">{{ formatDT(h.created_at) }}</td>
              <td class="text-left">
                <q-btn
                  v-if="h.pdf_url"
                  dense flat no-caps icon="picture_as_pdf"
                  label="Ver PDF"
                  @click="openPdf(h.pdf_url)"
                />
                <span v-else>-</span>
              </td>
            </tr>
            </tbody>
          </q-markup-table>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

export default {
  name: 'PaymentsControl',
  data () {
    return {
      loading: false,

      filters: {
        search: '',
        from: '',
        to: ''
      },

      orders: [],
      page: 1,
      perPage: 15,
      lastPage: 1,
      totalRows: 0,
      pageTotal: 0,
      pageCurrency: 'EUR',

      detailDialog: false,
      detail: {},

      emailDialog: false,
      emailForm: {
        order: null,
        type: 'PROCESSING',
        file: null
      },
      emailTypeOptions: [
        { label: 'En proceso', value: 'PROCESSING' },
        { label: 'Enviar entradas (PDF)', value: 'ENTRADAS' },
        { label: 'No se pudo completar', value: 'FAILED' },
        { label: 'Reembolso', value: 'REFUND' }
      ],

      historyDialog: false,
      historyLoading: false,
      historyItems: [],
      historyOrder: null
    }
  },

  mounted () {
    this.reloadAll()
  },

  methods: {
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
        exclude_pending: true,
        from: this.filters.from || null,
        to: this.filters.to || null,
        page: this.page,
        perPage: this.perPage,
        sortBy: 'id',
        sortDir: 'desc'
      }
    },

    resetFilters () {
      this.filters = { search: '', from: '', to: '' }
      this.page = 1
      this.reloadAll()
    },

    goPage (p) {
      this.page = p
      this.reloadAll()
    },

    reloadAll () {
      this.ordersGet()
    },

    async ordersGet () {
      this.loading = true
      try {
        const { data } = await this.$axios.get('orders-admin', { params: this.buildParams() })
        this.orders = data.data || []
        this.page = data.current_page || 1
        this.lastPage = data.last_page || 1
        this.totalRows = data.total || 0
        this.computePageTotals()
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error cargando órdenes')
      } finally {
        this.loading = false
      }
    },
    computePageTotals () {
      if (!this.orders.length) {
        this.pageTotal = 0
        this.pageCurrency = 'EUR'
        return
      }
      this.pageTotal = this.orders.reduce((sum, o) => sum + Number(o.amount_total || 0), 0)
      this.pageCurrency = (this.orders[0].currency || 'eur').toUpperCase()
    },

    openDetail (o) {
      this.detail = o
      this.detailDialog = true
    },

    openEmailDialog (o, type) {
      this.emailForm.order = o
      this.emailForm.type = type || 'PROCESSING'
      this.emailForm.file = null
      this.emailDialog = true
    },
    async sendStatusEmail () {
      const order = this.emailForm.order
      if (!order) return
      if (this.emailForm.type === 'ENTRADAS' && !this.emailForm.file) {
        this.$alert.error('Debe adjuntar un PDF para enviar las entradas')
        return
      }
      try {
        this.loading = true
        const form = new FormData()
        form.append('type', this.emailForm.type)
        if (this.emailForm.file) {
          form.append('pdf', this.emailForm.file)
        }
        await this.$axios.post(`orders/${order.id}/send-status-email`, form, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        this.$alert.success('Correo enviado correctamente')
        this.emailDialog = false
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error al enviar el correo')
      } finally {
        this.loading = false
      }
    },

    async openEmailHistory (o) {
      this.historyOrder = o
      this.historyItems = []
      this.historyDialog = true
      this.historyLoading = true
      try {
        const { data } = await this.$axios.get(`orders/${o.id}/email-history`)
        this.historyItems = data.items || []
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error cargando historial')
      } finally {
        this.historyLoading = false
      }
    },
    openPdf (url) {
      window.open(url, '_blank')
    },
    refundOrder (o) {
      this.$q.dialog({
        title: 'Hacer reembolso',
        message: `¿Deseas reembolsar la orden #${o.id}? Esta accion no se puede deshacer.`,
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          this.loading = true
          await this.$axios.post(`orders/${o.id}/refund`)
          this.$alert.success('Reembolso realizado')
          this.reloadAll()
        } catch (e) {
          this.$alert.error(e.response?.data?.message || 'Error al reembolsar')
        } finally {
          this.loading = false
        }
      })
    },

    changueLocalizador (o) {
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
          const updatedMetadata = { localizador: newLocalizador }
          await this.$axios.put(`orders/${o.id}`, updatedMetadata)
          this.$alert.success('Localizador actualizado correctamente')
          this.reloadAll()
        } catch (e) {
          this.$alert.error(e.response?.data?.message || 'Error al actualizar el localizador')
        } finally {
          this.loading = false
        }
      })
    },

    pdfListJs () {
      const doc = new jsPDF('l', 'mm', 'a4')
      doc.setFontSize(14)
      doc.text('Control de Pagos (sin pendientes)', 14, 14)

      const rows = this.orders.map(o => ([
        `#${o.id}`,
        o.status,
        o.evento?.nombre || '-',
        o.email || '-',
        this.formatMoney(o.amount_total, o.currency),
        (o.currency || '').toUpperCase(),
        this.formatDT(o.created_at),
        o.paid_at ? this.formatDT(o.paid_at) : '—',
        (o.session_id || '').slice(0, 28) + '...'
      ]))

      autoTable(doc, {
        startY: 20,
        head: [['ID','Estado','Evento','Email','Total','Moneda','Creado','Pagado','Session']],
        body: rows
      })

      doc.save('control_pagos.pdf')
    }
  }
}
</script>

<style scoped>
.ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
