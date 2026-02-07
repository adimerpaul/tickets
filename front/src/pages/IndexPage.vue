<template>
  <q-page class="q-pa-md bg-grey-2">
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12">
        <q-card flat bordered>
          <q-card-section class="row items-center q-col-gutter-md">
            <div class="col-12">
              <div class="text-h6">Dashboard</div>
              <div class="text-caption text-grey-7">Resumen financiero (sin pendientes)</div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
              <q-input v-model="filters.from" dense outlined type="date" label="Desde" />
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <q-input v-model="filters.to" dense outlined type="date" label="Hasta" />
            </div>
            <div class="col-12 col-sm-6 col-md-2">
              <q-input v-model="filters.time_from" dense outlined type="time" label="Hora desde" />
            </div>
            <div class="col-12 col-sm-6 col-md-2">
              <q-input v-model="filters.time_to" dense outlined type="time" label="Hora hasta" />
            </div>

            <div class="col-12 col-md-auto">
              <q-btn color="primary" no-caps icon="refresh" label="Aplicar" :loading="loading" @click="reload" />
            </div>
            <div class="col-12 col-md-auto">
              <q-btn outline color="grey-8" no-caps icon="restart_alt" label="Limpiar" :disable="loading" @click="resetFilters" />
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-item class="bg-positive text-white">
            <q-item-section avatar><q-icon name="check_circle" size="28px" /></q-item-section>
            <q-item-section>
              <q-item-label caption class="text-white">Pagos efectivos</q-item-label>
              <q-item-label class="text-h6">{{ totals.count }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-item class="bg-indigo-8 text-white">
            <q-item-section avatar><q-icon name="paid" size="28px" /></q-item-section>
            <q-item-section>
              <q-item-label caption class="text-white">Total cobrado</q-item-label>
              <q-item-label class="text-h6">{{ formatMoney(totals.amount) }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-item class="bg-grey-9 text-white">
            <q-item-section avatar><q-icon name="receipt_long" size="28px" /></q-item-section>
            <q-item-section>
              <q-item-label caption class="text-white">Ticket promedio</q-item-label>
              <q-item-label class="text-h6">{{ formatMoney(avgTicket) }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-item class="bg-deep-purple-8 text-white">
            <q-item-section avatar><q-icon name="schedule" size="28px" /></q-item-section>
            <q-item-section>
              <q-item-label caption class="text-white">Rango aplicado</q-item-label>
              <q-item-label class="text-h6">{{ rangeLabel }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-card>
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <div class="col-12 col-lg-8">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle1 text-weight-bold q-mb-sm">Ingresos por día</div>
            <apexchart type="area" height="280" :options="dayChartOptions" :series="daySeries" />
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-lg-4">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle1 text-weight-bold q-mb-sm">Estado de pagos</div>
            <apexchart type="donut" height="280" :options="statusChartOptions" :series="statusSeries" />
          </q-card-section>
        </q-card>
      </div>
    </div>

    <div class="row q-col-gutter-md q-mt-md">
      <div class="col-12">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle1 text-weight-bold q-mb-sm">Ingresos por hora</div>
            <apexchart type="bar" height="260" :options="hourChartOptions" :series="hourSeries" />
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts'

export default {
  name: 'IndexPage',
  components: {
    apexchart: VueApexCharts
  },
  data () {
    return {
      loading: false,
      filters: {
        from: '',
        to: '',
        time_from: '',
        time_to: ''
      },
      totals: {
        count: 0,
        amount: 0
      },
      byDay: [],
      byHour: [],
      byStatus: []
    }
  },
  computed: {
    avgTicket () {
      if (!this.totals.count) return 0
      return this.totals.amount / this.totals.count
    },
    rangeLabel () {
      const f = this.filters.from || '—'
      const t = this.filters.to || '—'
      return `${f} / ${t}`
    },
    daySeries () {
      return [{
        name: 'Total',
        data: this.byDay.map(r => Number(r.t || 0))
      }]
    },
    dayChartOptions () {
      return {
        chart: { toolbar: { show: false } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: { categories: this.byDay.map(r => r.d) },
        yaxis: { labels: { formatter: v => this.formatMoney(v) } }
      }
    },
    hourSeries () {
      return [{
        name: 'Total',
        data: this.byHour.map(r => Number(r.t || 0))
      }]
    },
    hourChartOptions () {
      return {
        chart: { toolbar: { show: false } },
        plotOptions: { bar: { columnWidth: '40%' } },
        dataLabels: { enabled: false },
        xaxis: { categories: this.byHour.map(r => String(r.h).padStart(2, '0') + ':00') },
        yaxis: { labels: { formatter: v => this.formatMoney(v) } }
      }
    },
    statusSeries () {
      return this.byStatus.map(r => Number(r.t || 0))
    },
    statusChartOptions () {
      return {
        labels: this.byStatus.map(r => r.status),
        legend: { position: 'bottom' }
      }
    }
  },
  mounted () {
    this.reload()
  },
  methods: {
    formatMoney (n) {
      const num = Number(n || 0)
      try {
        return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(num)
      } catch (e) {
        return `${num} EUR`
      }
    },
    buildParams () {
      return {
        from: this.filters.from || null,
        to: this.filters.to || null,
        time_from: this.filters.time_from || null,
        time_to: this.filters.time_to || null
      }
    },
    resetFilters () {
      this.filters = { from: '', to: '', time_from: '', time_to: '' }
      this.reload()
    },
    async reload () {
      this.loading = true
      try {
        const { data } = await this.$axios.get('dashboard/summary', { params: this.buildParams() })
        this.totals = data.totals || { count: 0, amount: 0 }
        this.byDay = data.by_day || []
        this.byHour = data.by_hour || []
        this.byStatus = data.by_status || []
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error cargando dashboard')
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
</style>
