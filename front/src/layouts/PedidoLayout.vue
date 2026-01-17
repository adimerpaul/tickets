<template>
  <q-layout view="hHh lpR fFf" class="bg-grey-2">

    <!-- Barra superior de aviso -->
    <div class="top-alert bg-black text-white">
      <div class="container row items-center justify-center text-caption q-px-md" style="font-size: 10px">
        Atención: Las entradas se agotan por minutos, compra ya tu entrada oficial.
      </div>
    </div>

    <!-- Header (encima del hero, centrado y bordeado) -->
<!--    <q-header class="bg-transparent text-white" height-hint="72">-->
<!--      <div class="container q-pt-sm">-->
<!--        <div class="header-box">-->
<!--          <q-toolbar class="q-px-md">-->

<!--            &lt;!&ndash; Logo &ndash;&gt;-->
<!--            <div class="row items-center no-wrap">-->
<!--              <div class="text-h6 text-weight-medium" style="letter-spacing:.3px">-->
<!--                Louvre-->
<!--              </div>-->
<!--            </div>-->

<!--            <q-space />-->

<!--            &lt;!&ndash; Tabs desktop &ndash;&gt;-->
<!--            <q-tabs-->
<!--              v-if="$q.screen.gt.sm"-->
<!--              dense-->
<!--              no-caps-->
<!--              align="center"-->
<!--              class="text-white"-->
<!--              active-color="white"-->
<!--              indicator-color="white"-->
<!--            >-->
<!--              <q-tab label="Inicio" @click="go('inicio')" />-->
<!--              <q-tab label="Preguntas frecuentes" @click="go('faq')" />-->
<!--              <q-tab label="Galería" @click="go('galeria')" />-->
<!--              <q-tab label="Comprar entradas" @click="go('form')" />-->
<!--            </q-tabs>-->

<!--            <q-space />-->

<!--            &lt;!&ndash; Idioma + menú &ndash;&gt;-->
<!--            <div class="row items-center no-wrap q-gutter-sm">-->
<!--              <q-btn-->
<!--                flat-->
<!--                dense-->
<!--                no-caps-->
<!--                class="text-white"-->
<!--                icon="language"-->
<!--                :label="lang"-->
<!--              >-->
<!--                <q-menu>-->
<!--                  <q-list style="min-width: 140px">-->
<!--                    <q-item clickable v-close-popup @click="lang='ES'"><q-item-section>ES</q-item-section></q-item>-->
<!--                    <q-item clickable v-close-popup @click="lang='EN'"><q-item-section>EN</q-item-section></q-item>-->
<!--                    <q-item clickable v-close-popup @click="lang='FR'"><q-item-section>FR</q-item-section></q-item>-->
<!--                  </q-list>-->
<!--                </q-menu>-->
<!--              </q-btn>-->

<!--              &lt;!&ndash; Hamburger solo móvil &ndash;&gt;-->
<!--              <q-btn-->
<!--                v-if="$q.screen.lt.md"-->
<!--                flat dense round-->
<!--                icon="menu"-->
<!--                class="text-white"-->
<!--              >-->
<!--                <q-menu anchor="bottom right" self="top right">-->
<!--                  <q-list style="min-width: 220px">-->
<!--                    <q-item clickable v-close-popup @click="go('inicio')">-->
<!--                      <q-item-section>Inicio</q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-item clickable v-close-popup @click="go('faq')">-->
<!--                      <q-item-section>Preguntas frecuentes</q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-item clickable v-close-popup @click="go('galeria')">-->
<!--                      <q-item-section>Galería</q-item-section>-->
<!--                    </q-item>-->
<!--                    <q-item clickable v-close-popup @click="go('form')">-->
<!--                      <q-item-section>Comprar entradas</q-item-section>-->
<!--                    </q-item>-->
<!--                  </q-list>-->
<!--                </q-menu>-->
<!--              </q-btn>-->
<!--            </div>-->

<!--          </q-toolbar>-->
<!--        </div>-->
<!--      </div>-->
<!--    </q-header>-->

    <q-page-container>
      <q-page class="bg-grey-2">

        <!-- HERO -->
        <section ref="secInicio" class="hero">
          <q-img
            :src="heroImage"
            class="hero-img"
            fit="cover"
            no-spinner
          >
            <div class="absolute-full hero-overlay" style="background: rgba(255,255,255,0)" />

            <div class="absolute-full">
              <div class="container hero-content q-px-md">
                <div class="col-12 col-md-7">
                  <div class="text-h3 text-md-h2 text-weight-bold text-white" style="line-height:1.05">
                    Asegura tu acceso al<br> Museo del Louvre.
                  </div>

                  <div class="text-subtitle1 text-white q-mt-md" style="max-width: 720px; opacity:.92">
                    Evita las filas en taquilla y entra directo al Museo del Louvre con tu entrada digital.
                    Reserva online y recibe confirmación inmediata por email.
                  </div>

                  <q-btn
                    class="q-mt-lg"
                    outline
                    color="white"
                    no-caps
                    icon="travel_explore"
                    label="Explorar"
                    @click="go('galeria')"
                  />
                </div>
              </div>
            </div>
          </q-img>
        </section>

        <!-- FORM CARD (flotante, centrado) -->
        <section ref="secForm" class="container q-px-md">
          <q-card class="form-card" bordered>
            <q-card-section class="q-pa-md q-pa-lg-md">

              <div class="row items-center q-gutter-sm">
                <div class="text-subtitle1 text-weight-medium">
                  ¿Eres ciudadano de la Unión Europea?
                </div>
                <q-icon name="info" color="primary" />
              </div>

              <q-option-group
                v-model="isEU"
                inline
                class="q-mt-sm"
                :options="[
                  { label: 'sí', value: true },
                  { label: 'no', value: false }
                ]"
              />

              <div class="row q-col-gutter-md q-mt-sm">
                <div class="col-12 col-md-6">
                  <div class="text-caption text-grey-8 q-mb-xs">Seleccionar fecha</div>
                  <q-input v-model="date" type="date" dense outlined>
                    <template #prepend><q-icon name="event" /></template>
                  </q-input>
                </div>

                <div class="col-12 col-md-6">
                  <div class="text-caption text-grey-8 q-mb-xs">Hora</div>
                  <q-select
                    v-model="time"
                    :options="timeOptions"
                    dense
                    outlined
                    emit-value
                    map-options
                    placeholder="Seleccione una hora"
                  >
                    <template #prepend><q-icon name="schedule" /></template>
                  </q-select>
                </div>

                <div class="col-12 col-md-6">
                  <div class="text-caption text-grey-8 q-mb-xs">Adultos +17</div>
                  <q-input v-model.number="adults" type="number" dense outlined min="0">
                    <template #prepend><q-icon name="person" /></template>
                    <template #append>
                      <q-btn round dense flat icon="remove" @click="adults = Math.max(0, adults - 1)" />
                      <q-btn round dense flat icon="add" @click="adults = adults + 1" />
                    </template>
                  </q-input>
                </div>

                <div class="col-12 col-md-6">
                  <div class="text-caption text-grey-8 q-mb-xs">Niños -17</div>
                  <q-input v-model.number="kids" type="number" dense outlined min="0">
                    <template #prepend><q-icon name="child_care" /></template>
                    <template #append>
                      <q-btn round dense flat icon="remove" @click="kids = Math.max(0, kids - 1)" />
                      <q-btn round dense flat icon="add" @click="kids = kids + 1" />
                    </template>
                  </q-input>
                </div>

                <div class="col-12 q-mt-sm">
                  <div class="row items-center">
                    <div class="text-subtitle2 text-grey-9">Total:</div>
                    <q-space />
                    <div class="text-subtitle1 text-weight-bold">
                      {{ formatEUR(total) }}
                    </div>
                  </div>
                </div>

                <div class="col-12">
                  <q-btn
                    unelevated
                    color="black"
                    class="full-width"
                    no-caps
                    label="Comprar Ahora"
                    @click="onBuy"
                  />
                </div>
              </div>

            </q-card-section>
          </q-card>
        </section>

<!--        &lt;!&ndash; CARRUSEL (Galería) &ndash;&gt;-->
<!--        <section ref="secGaleria" class="container q-px-md q-mt-xl q-mb-xl">-->
<!--          <div class="text-h6 text-weight-medium q-mb-md">Galería</div>-->

<!--          <q-card bordered class="q-pa-sm">-->
<!--            <q-carousel-->
<!--              v-model="slide"-->
<!--              animated-->
<!--              arrows-->
<!--              navigation-->
<!--              height="360px"-->
<!--              class="rounded-borders"-->
<!--            >-->
<!--              <q-carousel-slide :name="1" :img-src="gallery[0]" />-->
<!--              <q-carousel-slide :name="2" :img-src="gallery[1]" />-->
<!--              <q-carousel-slide :name="3" :img-src="gallery[2]" />-->
<!--              <q-carousel-slide :name="4" :img-src="gallery[3]" />-->
<!--            </q-carousel>-->
<!--          </q-card>-->
<!--        </section>-->

<!--        &lt;!&ndash; FAQ (simple, opcional) &ndash;&gt;-->
<!--        <section ref="secFaq" class="container q-px-md q-mb-xl">-->
<!--          <div class="text-h6 text-weight-medium q-mb-md">Preguntas frecuentes</div>-->

<!--          <q-card bordered>-->
<!--            <q-expansion-item label="¿La entrada llega por email?" icon="mail">-->
<!--              <q-card-section class="text-grey-8">-->
<!--                Sí. Tras completar la compra recibirás confirmación y tu entrada digital por email.-->
<!--              </q-card-section>-->
<!--            </q-expansion-item>-->
<!--            <q-separator />-->
<!--            <q-expansion-item label="¿Puedo cambiar la hora?" icon="schedule">-->
<!--              <q-card-section class="text-grey-8">-->
<!--                Depende de disponibilidad. En un sistema real lo manejarías desde tu backend / proveedor de tickets.-->
<!--              </q-card-section>-->
<!--            </q-expansion-item>-->
<!--          </q-card>-->
<!--        </section>-->

      </q-page>
    </q-page-container>

  </q-layout>
</template>

<script setup>
import { computed, ref } from 'vue'

const lang = ref('ES')

const heroImage =
  'home-hero.464470cb.webp'

const gallery = [
  'https://images.unsplash.com/photo-1543349689-9a4d426bee8e?auto=format&fit=crop&w=2200&q=70',
  'https://images.unsplash.com/photo-1548013146-72479768bada?auto=format&fit=crop&w=2200&q=70',
  'https://images.unsplash.com/photo-1561214115-f2f134cc4912?auto=format&fit=crop&w=2200&q=70',
  'https://images.unsplash.com/photo-1561214115-72fdb0cbe4c4?auto=format&fit=crop&w=2200&q=70'
]

const slide = ref(1)

const isEU = ref(true)
const date = ref('2026-01-17')
const time = ref(null)

const timeOptions = [
  '09:00', '09:30', '10:00', '10:30',
  '11:00', '11:30', '12:00', '12:30',
  '13:00', '13:30', '14:00', '14:30',
  '15:00', '15:30', '16:00', '16:30'
].map(v => ({ label: v, value: v }))

const adults = ref(1)
const kids = ref(0)

const unitAdult = computed(() => 33)
const unitKid = computed(() => 0)
const total = computed(() => (adults.value * unitAdult.value) + (kids.value * unitKid.value))

function formatEUR (n) {
  return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(n)
}

function onBuy () {
  // Aquí conectas tu API real
  console.log({
    isEU: isEU.value,
    date: date.value,
    time: time.value,
    adults: adults.value,
    kids: kids.value,
    total: total.value
  })
}

/** scroll simple a secciones */
const secInicio = ref(null)
const secForm = ref(null)
const secGaleria = ref(null)
const secFaq = ref(null)

function go (where) {
  const map = {
    inicio: secInicio,
    form: secForm,
    galeria: secGaleria,
    faq: secFaq
  }
  const el = map[where]?.value
  if (el?.$el) el.$el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  else if (el?.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
}
</script>

<style scoped>
.text-md-h2 {
  line-height: 1.05;
}
/* contenedor centrado como en tu screenshot */
.container {
  max-width: 1200px;
  margin: 0 auto;
}

/* barra superior tipo aviso */
.top-alert {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 28px;
  z-index: 4000;
  display: flex;
  align-items: center;
}

/* header-box bordeado y centrado */
.header-box {
  border: 1px solid rgba(255,255,255,.20);
  border-radius: 10px;
  background: rgba(0,0,0,.35);
  backdrop-filter: blur(6px);
}

/* hero ajustado para que parezca “landing” */
.hero {
  padding-top: 28px; /* por la barra superior fija */
}
.hero-img {
  height: clamp(520px, 70vh, 760px);
}
.hero-overlay {
  background: rgba(255,255,255,0);
}
.hero-content {
  height: 100%;
  padding-top: 80px;
  text-align: left;
  //display: flex;
  //align-items: center;
}

/* card flotante como la web */
.form-card {
  margin-top: -90px;
  border-radius: 12px;
  box-shadow: 0 18px 45px rgba(0,0,0,.18);
}
</style>
