import axios, { AxiosError } from 'axios'
import router from '@/router'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api',
})

// Helpers simples para centralizar acceso a localStorage
export const getToken = () => localStorage.getItem('token')
export const setAuth = (token: string, user: any) => {
  localStorage.setItem('token', token)
  localStorage.setItem('user', JSON.stringify(user))
}
export const clearAuth = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
}

api.interceptors.request.use((config) => {
  // Rutas que no deben llevar token (relativas al baseURL)
  const noAuthEndpoints = ['/login', '/register']
  const urlPath = (config.url || '').replace(/^https?:\/\/[^/]+/, '') // normalizar
  if (!noAuthEndpoints.includes(urlPath)) {
    const token = getToken()
    if (token) config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  response => response,
  (error: AxiosError<any>) => {
    const status = error.response?.status
    if (status === 401) {
      // Limpia auth y redirige a login con redirect
      const current = router.currentRoute.value.fullPath
      clearAuth()
      if (current !== '/login') {
        router.push({ name: 'login', query: { redirect: current } })
      }
    }
    return Promise.reject(error)
  }
)

export default api
