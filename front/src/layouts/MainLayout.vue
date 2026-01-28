<template>
  <q-layout view="lHh Lpr lFf">
    <!-- HEADER -->
    <q-header class="bg-white text-black" bordered>
      <q-toolbar>
        <q-btn
          flat
          color="primary"
          :icon="leftDrawerOpen ? 'keyboard_double_arrow_left' : 'keyboard_double_arrow_right'"
          aria-label="Menu"
          @click="toggleLeftDrawer"
          dense
        />

        <div class="row items-center q-gutter-sm">
          <div class="text-subtitle1 text-weight-medium" style="line-height: 0.9">
            Dashboard de Tickets <br>
            <q-badge color="warning" text-color="black" v-if="roleText" class="text-bold">
              {{ roleText }}
            </q-badge>
          </div>
        </div>

        <q-space />

        <div class="row items-center q-gutter-sm">
          <q-btn-dropdown flat unelevated no-caps dropdown-icon="expand_more">
            <template v-slot:label>
              <div class="row items-center no-wrap q-gutter-sm">
                <q-avatar rounded>
                  <q-img
                    v-if="$store.user && $store.user.avatar"
                    :src="`${$url}../../images/${$store.user.avatar}`"
                    width="40px"
                    height="40px"
                  />
                  <q-icon name="person" v-else />
                </q-avatar>

                <div class="text-left" style="line-height: 1">
                  <div class="ellipsis" style="max-width: 130px;">
                    {{ $store.user ? $store.user.username : '' }}
                  </div>
                  <q-chip
                    dense
                    size="10px"
                    :color="$filters.color($store.user ? $store.user.role : '')"
                    text-color="white"
                  >
                    {{ $store.user ? $store.user.role : '' }}
                  </q-chip>
                </div>
              </div>
            </template>

            <q-item clickable v-close-popup>
              <q-item-section>
                <q-item-label class="text-grey-7">
                  Permisos asignados
                </q-item-label>
                <q-item-label caption class="q-mt-xs">
                  <div class="row q-col-gutter-xs" style="min-width: 150px; max-width: 150px;">
                    <q-chip
                      v-for="(p, i) in $store.permissions"
                      :key="i"
                      dense
                      color="grey-3"
                      text-color="black"
                      size="12px"
                      class="q-mr-xs q-mb-xs"
                    >
                      {{ p }}
                    </q-chip>
                    <q-badge v-if="!$store.permissions || !$store.permissions.length" color="grey-5" outline>
                      Sin permisos
                    </q-badge>
                  </div>
                </q-item-label>
              </q-item-section>
            </q-item>

            <q-separator />

            <q-item clickable v-ripple @click="logout" v-close-popup>
              <q-item-section avatar>
                <q-icon name="logout" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Salir</q-item-label>
              </q-item-section>
            </q-item>
          </q-btn-dropdown>
        </div>
      </q-toolbar>
    </q-header>

    <!-- DRAWER -->
    <q-drawer
      v-model="leftDrawerOpen"
      bordered
      show-if-above
      :width="220"
      :breakpoint="500"
      class="bg-primary text-white"
    >
      <q-list class="q-pb-none">
        <q-item-label header class="text-center q-pa-none q-pt-md">
          <q-avatar size="64px" class="q-mb-sm bg-white" rounded>
            <q-img src="/logo.png" width="90px" />
          </q-avatar>
          <div class="text-weight-bold text-white">TICKETS</div>
          <div class="text-caption text-white">Sistema de Gestión</div>
        </q-item-label>

        <q-item-label header class="q-px-md text-grey-3 q-mt-sm">
          Módulos del Sistema
        </q-item-label>

        <!-- DASHBOARD -->
        <q-item
          dense
          to="/"
          exact
          clickable
          class="menu-item"
          active-class="menu-active"
          v-close-popup
          v-if="hasPermission('Dashboard')"
        >
          <q-item-section avatar>
            <q-icon name="dashboard" class="text-white" />
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Dashboard</q-item-label>
          </q-item-section>
        </q-item>

        <!-- USUARIOS -->
        <q-item
          dense
          to="/usuarios"
          exact
          clickable
          class="menu-item"
          active-class="menu-active"
          v-close-popup
          v-if="hasPermission('Usuarios')"
        >
          <q-item-section avatar>
            <q-icon name="people" class="text-white" />
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Usuarios</q-item-label>
          </q-item-section>
        </q-item>
        <q-item
          dense
          to="/eventos"
          exact
          clickable
          class="menu-item"
          active-class="menu-active"
          v-close-popup
          v-if="hasPermission('Eventos')"
        >
          <q-item-section avatar>
            <q-icon name="event" class="text-white" />
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Eventos</q-item-label>
          </q-item-section>
        </q-item>
        <q-expansion-item
          dense
          expand-separator
          icon="public"
          label="Egipto"
          active-class="menu-active"
        >
          <q-list>

            <!-- Giza Plateau -->
            <q-item :inset-level="0.3" dense to="/evento/giza-plateau" clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="landscape" color="amber-4" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Giza Plateau</q-item-label>
              </q-item-section>
            </q-item>

            <!-- Egyptian Museum -->
            <q-item :inset-level="0.3" dense to="/evento/egyptian-museum" clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="museum" color="cyan-4" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Egyptian Museum</q-item-label>
              </q-item-section>
            </q-item>

            <!-- Sharm El Sheikh Museum -->
            <q-item :inset-level="0.3" dense to="/evento/sharm-el-sheikh-museum" clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="beach_access" color="light-blue-4" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Sharm El Sheikh Museum</q-item-label>
              </q-item-section>
            </q-item>

            <!-- Hurghada Museum -->
            <q-item :inset-level="0.3" dense to="/evento/hurghada-museum" clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="beach_access" color="teal-4" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Hurghada Museum</q-item-label>
              </q-item-section>
            </q-item>

            <!-- Luxor Temple -->
            <q-item :inset-level="0.3" dense to="/evento/luxor-temple" clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="account_balance" color="orange-4" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Luxor Temple</q-item-label>
              </q-item-section>
            </q-item>

            <!-- Karnak Temple -->
            <q-item :inset-level="0.3" dense to="/evento/karnak-temple" clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="account_balance" color="deep-orange-4" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Karnak Temple</q-item-label>
              </q-item-section>
            </q-item>

            <!-- Hatshepsut Temple -->
            <q-item :inset-level="0.3" dense to="/evento/hatshepsut-temple" clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="account_balance" color="brown-4" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Hatshepsut Temple</q-item-label>
              </q-item-section>
            </q-item>

            <!-- Abu Simbel -->
            <q-item :inset-level="0.3" dense to="/evento/abu-simbel-temple" clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="temple_buddhist" color="amber-6" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Abu Simbel</q-item-label>
              </q-item-section>
            </q-item>

            <!-- Coptic Museum -->
            <q-item :inset-level="0.3" dense to="/evento/coptic-museum" clickable class="menu-item" active-class="menu-active" v-close-popup>
              <q-item-section avatar>
                <q-icon name="church" color="purple-4" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Coptic Museum</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>


        <!-- IDIOMAS -->
<!--        <q-item-->
<!--          dense-->
<!--          to="/idiomas"-->
<!--          exact-->
<!--          clickable-->
<!--          class="menu-item"-->
<!--          active-class="menu-active"-->
<!--          v-close-popup-->
<!--          v-if="hasPermission('Idiomas')"-->
<!--        >-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="translate" class="text-white" />-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Idiomas</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->

<!--        &lt;!&ndash; PRECIOS &ndash;&gt;-->
<!--        <q-item-->
<!--          dense-->
<!--          to="/precios"-->
<!--          exact-->
<!--          clickable-->
<!--          class="menu-item"-->
<!--          active-class="menu-active"-->
<!--          v-close-popup-->
<!--          v-if="hasPermission('Precios')"-->
<!--        >-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="payments" class="text-white" />-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Precios</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->

<!--        &lt;!&ndash; REPORTES &ndash;&gt;-->
<!--        <q-item-->
<!--          dense-->
<!--          to="/reportes"-->
<!--          exact-->
<!--          clickable-->
<!--          class="menu-item"-->
<!--          active-class="menu-active"-->
<!--          v-close-popup-->
<!--          v-if="hasPermission('Reportes')"-->
<!--        >-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="bar_chart" class="text-white" />-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Reportes</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->
<!--        pedidos to-->
        <q-item
          dense
          to="/pedido"
          exact
          clickable
          class="menu-item"
          active-class="menu-active"
          v-close-popup
        >
          <q-item-section avatar>
            <q-icon name="shopping_cart" class="text-white" />
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Pedidos</q-item-label>
          </q-item-section>
        </q-item>

        <!-- FOOTER -->
        <div class="q-pa-md">
          <div class="text-white-7 text-caption">
            Tickets v{{ $version }}
          </div>
          <div class="text-white-7 text-caption">
            © {{ new Date().getFullYear() }} Sistema de Tickets
          </div>
        </div>

        <q-item clickable class="text-white" @click="logout" v-close-popup>
          <q-item-section avatar>
            <q-icon name="logout" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Salir</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <!-- PAGE -->
    <q-page-container class="bg-grey-2">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
export default {
  name: 'MainLayout',
  data () {
    return {
      leftDrawerOpen: false
    }
  },
  computed: {
    roleText () {
      const role = this.$store && this.$store.user ? this.$store.user.role : ''
      if (!role) return ''
      return role
    }
  },
  methods: {
    toggleLeftDrawer () {
      this.leftDrawerOpen = !this.leftDrawerOpen
    },
    hasPermission (perm) {
      return this.$store && this.$store.permissions
        ? this.$store.permissions.includes(perm)
        : false
    },
    logout () {
      this.$alert.dialog('¿Desea salir del sistema?')
        .onOk(() => {
          this.$axios.post('/logout')
            .then(() => {
              this.$store.isLogged = false
              this.$store.user = {}
              this.$store.permissions = []
              localStorage.removeItem('tokenSIL')
              this.$router.push('/login')
            })
            .catch(() => {
              this.$store.isLogged = false
              this.$store.user = {}
              this.$store.permissions = []
              localStorage.removeItem('tokenSIL')
              this.$router.push('/login')
            })
        })
    }
  }
}
</script>

<style scoped>
.menu-item {
  border-radius: 10px;
  margin: 4px 8px;
  padding: 4px 6px;
}
.menu-active {
  background: rgba(255, 255, 255, 0.15);
  color: #fff !important;
  border-radius: 10px;
}
</style>
