import { defineBoot } from '#q-app/wrappers'
import axios from 'axios'
import {Alert} from "src/addons/Alert";
import {useCounterStore} from "stores/example-store";

const api = axios.create({ baseURL: 'https://api.example.com' })

export default defineBoot(({ app,router }) => {

  app.config.globalProperties.$axios = axios.create({ baseURL: import.meta.env.VITE_API_BACK })
  app.config.globalProperties.$alert = Alert
  app.config.globalProperties.$store = useCounterStore()
  app.config.globalProperties.$url = import.meta.env.VITE_API_BACK
  app.config.globalProperties.$version = import.meta.env.VITE_VERSION

  const token = localStorage.getItem('tokenTicket')
  if (token) {
    app.config.globalProperties.$axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    app.config.globalProperties.$axios.get('me').then(response => {
      useCounterStore().isLogged = true
      useCounterStore().user = response.data
      useCounterStore().permissions = (response.data.permissions || []).map(p => p.name)
      localStorage.setItem('user', JSON.stringify(response.data))
      // useCounterStore().permissions = response.data.permissions
    }).catch(error => {
      console.log(error)
      router.push('/login')
      localStorage.removeItem('tokenTicket')
      useCounterStore().isLogged = false
      // useCounterStore().permissions = []
      useCounterStore().user = {}
    })
  }
  app.config.globalProperties.$api = api

})

export { api }
