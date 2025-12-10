/**
 * Vue 3 Plugin for Offline Support
 * Initializes offline detection and auto-sync
 */

import { useOfflineStore } from '@/stores/offlineStore'
import { offlineApi } from '@/services/offlineApi'

export default {
  install(app) {
    // Make offline API available globally
    app.config.globalProperties.$offlineApi = offlineApi

    // Auto-sync when online
    const offlineStore = useOfflineStore()
    
    window.addEventListener('online', () => {
      console.log('📡 Back online - syncing...')
      offlineStore.setOnlineStatus(true)
      offlineStore.syncPendingRequests()
    })

    window.addEventListener('offline', () => {
      console.log('📴 Gone offline - queuing requests')
      offlineStore.setOnlineStatus(false)
    })

    // Initial check
    offlineStore.setOnlineStatus(navigator.onLine)
  }
}
