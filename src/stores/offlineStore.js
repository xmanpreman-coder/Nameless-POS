import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useOfflineStore = defineStore('offline', () => {
  // State
  const isOnline = ref(navigator.onLine)
  const pendingRequests = ref([])
  const syncInProgress = ref(false)
  const lastSyncTime = ref(localStorage.getItem('lastSyncTime'))
  const offlineData = ref({
    sales: [],
    purchases: [],
    products: [],
    customers: [],
    suppliers: []
  })

  // Computed
  const hasPendingRequests = computed(() => pendingRequests.value.length > 0)
  const isOffline = computed(() => !isOnline.value)
  const canSync = computed(() => isOnline.value && !syncInProgress.value)

  // Methods
  const setOnlineStatus = (status) => {
    isOnline.value = status
    
    if (status && hasPendingRequests.value) {
      // Auto-sync when back online
      syncPendingRequests()
    }
  }

  const addPendingRequest = (request) => {
    const item = {
      id: Date.now() + Math.random(),
      ...request,
      timestamp: new Date(),
      status: 'pending'
    }
    
    pendingRequests.value.push(item)
    localStorage.setItem('pendingRequests', JSON.stringify(pendingRequests.value))
    
    return item.id
  }

  const removePendingRequest = (id) => {
    const index = pendingRequests.value.findIndex(r => r.id === id)
    if (index > -1) {
      pendingRequests.value.splice(index, 1)
      localStorage.setItem('pendingRequests', JSON.stringify(pendingRequests.value))
    }
  }

  const updatePendingRequestStatus = (id, status) => {
    const request = pendingRequests.value.find(r => r.id === id)
    if (request) {
      request.status = status
      localStorage.setItem('pendingRequests', JSON.stringify(pendingRequests.value))
    }
  }

  const syncPendingRequests = async () => {
    if (syncInProgress.value || !isOnline.value) return
    
    syncInProgress.value = true
    
    try {
      for (const request of pendingRequests.value) {
        try {
          updatePendingRequestStatus(request.id, 'syncing')
          
          const response = await fetch(request.url, {
            method: request.method || 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(request.data)
          })

          if (response.ok) {
            updatePendingRequestStatus(request.id, 'synced')
            removePendingRequest(request.id)
          } else {
            updatePendingRequestStatus(request.id, 'failed')
          }
        } catch (error) {
          console.error('Sync request failed:', error)
          updatePendingRequestStatus(request.id, 'failed')
        }
      }
      
      lastSyncTime.value = new Date().toISOString()
      localStorage.setItem('lastSyncTime', lastSyncTime.value)
    } finally {
      syncInProgress.value = false
    }
  }

  const saveOfflineData = (key, data) => {
    offlineData.value[key] = data
    localStorage.setItem(`offlineData_${key}`, JSON.stringify(data))
  }

  const getOfflineData = (key) => {
    const cached = localStorage.getItem(`offlineData_${key}`)
    return cached ? JSON.parse(cached) : []
  }

  const clearOfflineData = () => {
    Object.keys(offlineData.value).forEach(key => {
      offlineData.value[key] = []
      localStorage.removeItem(`offlineData_${key}`)
    })
    pendingRequests.value = []
    localStorage.removeItem('pendingRequests')
  }

  // Listen to online/offline events
  if (typeof window !== 'undefined') {
    window.addEventListener('online', () => setOnlineStatus(true))
    window.addEventListener('offline', () => setOnlineStatus(false))
  }

  // Load pending requests from storage
  const loadPendingRequests = () => {
    const stored = localStorage.getItem('pendingRequests')
    if (stored) {
      pendingRequests.value = JSON.parse(stored)
    }
  }

  loadPendingRequests()

  return {
    // State
    isOnline,
    pendingRequests,
    syncInProgress,
    lastSyncTime,
    offlineData,

    // Computed
    hasPendingRequests,
    isOffline,
    canSync,

    // Methods
    setOnlineStatus,
    addPendingRequest,
    removePendingRequest,
    updatePendingRequestStatus,
    syncPendingRequests,
    saveOfflineData,
    getOfflineData,
    clearOfflineData
  }
})
